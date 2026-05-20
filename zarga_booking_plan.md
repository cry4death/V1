# Пошаговый план: рабочая запись к врачу + личный кабинет (гибрид Laravel ↔ Espo CRM)
**Репозиторий:** `cry4death/-forPlans_Zarga` (моно-репо: `site/` — Laravel 13 + Filament v5 + Blade; `mobileApp/` — Flutter + Riverpod + Dio + go_router; `dumpSite.sql` — текущий дамп БД)

> Этот план рассчитан на исполнителя‑ИИ. Каждый шаг — конкретная задача с указанием файлов, таблиц, ожидаемого результата и подходов. Следовать порядку строго.

---

## A. Архитектурная установка: Laravel ↔ Espo CRM (источник правды)

В проекте планируется **подключение Espo CRM**. С этого момента все решения должны исходить из чёткого разделения зон ответственности:

### A.1. Принцип
| Зона | Laravel БД (локально) | Espo CRM (источник правды) |
|---|---|---|
| Кто вошёл на сайт | `patients.id`, нормализованный `phone`, `password` (хэш‑заглушка), `personal_access_tokens` (Sanctum), сессии. | — |
| Кто этот человек | `patients.espo_contact_id` (FK на CRM) **+ кэш-копия `last_name/first_name/middle_name/birth_date/gender`** для UI/форм/денормализации старых заявок (на MVP). | ФИО, пол, дата рождения, мед.поля, тэги, история визитов — всё «настоящее» лежит здесь. |
| Что заказал на сайте | `appointments` — локальный «черновик/зеркало» заявки: `patient_id`, `service_id`, `doctor_id`, `start_at`, `status`, `message`, технические флаги. | Реальная запись (Meeting / кастомная сущность) со слотом, статусом оператора, историей переносов/отмен. |
| Связка систем | `patients.espo_contact_id` (string), `appointments.espo_entity_id` + `appointments.espo_entity_type` (varchar 64). | Обратные ссылки в CRM не обязательны (CRM — ведущая система). |

> Поля `patients.espo_contact_id`, `appointments.espo_entity_id`, `appointments.espo_entity_type` **уже существуют** в актуальных миграциях (`2026_05_02_000001_create_patients_table.php`, `2026_05_02_000003_add_patient_and_espo_to_appointments_table.php`). План на них опирается — менять не нужно.

### A.2. Что значит «локальное зеркало» для `appointments`
- Сразу после `BookingService::book()` запись в Laravel создаётся со `status='new'` и `espo_entity_id = NULL`.
- Job `SyncAppointmentToEspo` (queue `crm`, `tries=5`, exponential backoff) пушит её в CRM, по успеху записывает `espo_entity_id`/`espo_entity_type`.
- Любые изменения статуса/времени в CRM возвращаются в Laravel либо webhook’ом (`POST /webhooks/espo/appointment`), либо периодическим pull‑sync (раз в N минут).
- Если CRM недоступна, заявка **всё равно сохраняется локально** (доступна оператору в Filament + видна пациенту в кабинете) — это и есть причина существования зеркала.

### A.3. Что значит «кэш профиля» в `patients`
- На MVP `patients.last_name/first_name/middle_name/birth_date/gender` — это **кэш**, заполняемый при регистрации и перезаписываемый при `pull` из CRM.
- Источник правды — Espo Contact. При расхождении побеждает CRM.
- В перспективе (фаза 2) эти поля можно удалить из `patients`, заменив на joinы к CRM API через сервисный слой `PatientProfileResolver`. На MVP не делаем — формы регистрации и денормализованные старые заявки опираются на эти колонки.

### A.4. Технические флаги синхронизации
К `patients` миграцией добавить:
- `espo_synced_at TIMESTAMP NULL` — когда последний раз успешно синхронизирован профиль.
- `espo_sync_status ENUM('pending','synced','failed') DEFAULT 'pending'`.
- `espo_sync_error TEXT NULL`.

К `appointments` миграцией добавить:
- `espo_synced_at TIMESTAMP NULL`,
- `espo_sync_status ENUM('pending','synced','failed','skipped') DEFAULT 'pending'`,
- `espo_sync_error TEXT NULL`.

---

## 0. Анализ текущего проекта (то, что уже есть; ломать нельзя)

### 0.1. Бэкенд (`site/`)
- Laravel **13**, PHP **8.4**, Filament **v5**, Livewire **v4**, Pest **v4**, Pint **v1**, Tailwind v4. Запуск: `php artisan serve`, `composer run dev`, `npm run build|dev`.
- `routes/web.php` содержит публичные страницы (`home`, `services.index/show`, `doctors.index/show`, `blog.*`, `promotions.*`, `contacts`, `about`, `documents`, `vacancies`, `insurance`, `medical-device`, `equipment.show`, `search`).
- Уже реализована **OTP-авторизация пациента** (guard `patient`):
  - `POST /patient/register/profile` (FormRequest `StorePatientRegisterProfileRequest`: ФИО Cyrillic, `birth_date` `dd.mm.yyyy`, `gender` male/female).
  - `POST /patient/register/request-otp` → `App\Services\PatientOtpService::issue($phone)` (демо‑код `111111`, 15 минут, throttle `12,1`).
  - `POST /patient/register/complete` (`VerifyPatientOtpRequest`: 6 цифр) — создаёт `Patient`, делает `Auth::guard('patient')->login()`.
  - Симметричный логин: `/patient/login` + `/patient/login/request-otp` + `/patient/login/verify`.
  - Middleware-алиас `patient.auth` (`bootstrap/app.php` → `App\Http\Middleware\EnsurePatientAuthenticated`), редиректит на `patient.login`.
- Уже реализован **примитивный «черновик» записи**: группа `Route::middleware(['patient.auth','throttle:30,1'])`:
  - `GET /booking` (`BookingController@index`) — `select` услуг + (после выбора услуги) `select` врачей. **Без даты/времени.**
  - `POST /booking` (`BookingController@store`, `StoreBookingRequest` валидирует только `service_id` и `doctor_id`) — создаёт `Appointment` со статусом `new`, `preferred_date = null`, `message = "Онлайн-запись без выбора времени (черновик для проверки)."`.
- API под мобильное приложение: префикс `api/v1` (см. `bootstrap/app.php` → `apiPrefix`):
  - `GET /api/v1/doctors`, `GET /api/v1/doctors/{slug}` (с фильтрами `?specialization=`, `?patient_age=`).
  - `GET /api/v1/service-directions`, `GET /api/v1/service-directions/{slug}/services`.
  - `GET /api/v1/articles*`, `GET /api/v1/promotions*`.
  - **Нет** API для записи / слотов / личного кабинета.
- Модели и миграции (`site/app/Models`, `site/database/migrations`):
  - `Doctor` (`belongsTo Specialization`, `belongsToMany Service` через pivot `doctor_service`, `hasMany Appointment`, `hasMany Review`; колонки: `last_name`, `first_name`, `middle_name`, `slug`, `category`, `experience_years`, `experience_since`, `patient_age`, `academic_degree`, `photo`, `description`, `education`, `status`, `sort_order`, `rating`).
  - `Service` (`belongsTo Direction`, `belongsToMany Doctor`; колонки: `direction_id`, `name`, `slug`, `price`, `description`, `indications`, `preparation`, `status`, `sort_order`).
  - `Direction`, `Specialization` — справочники.
  - `Appointment` (поля уже есть): `patient_id` *(nullable, FK → patients)*, `espo_entity_id`, `espo_entity_type`, `doctor_id`, `service_id`, `patient_name`, `phone`, `email`, `type` enum(`appointment`,`feedback`), `message`, `status` enum(`new`,`processing`,`completed`), `admin_comment`, `preferred_date` (datetime, **сейчас всегда null**), таймстампы.
  - `Patient` extends `Authenticatable`: `phone`, `espo_contact_id`, `last_name`, `first_name`, `middle_name`, `birth_date`, `gender`, `password` (cast `hashed`, но в `PatientRegisterController::complete` сейчас ставится `Str::random(64)` — пароль фактически не используется).
  - `PatientOtp`: `phone`, `code`, `expires_at`, `consumed_at` (демо‑код `111111`).
  - **`doctor_schedules`** — таблица существует **только в `dumpSite.sql`** (поля `doctor_id`, `weekday` (0–6), `start_time`, `end_time`), но **миграции нет** и модели нет. План должен ввести её формально.
  - Есть legacy-таблицы в дампе: `clients`, `phone_otps` — это устаревшая схема, новая использует `patients` + `patient_otps`. План их игнорирует.
- Кнопки «Записаться» сейчас:
  - `resources/views/partials/header.blade.php` (lines 105, 146, 168) → `route('booking.index')` ✅ (рабочая ссылка, но ведёт на минимальный `/booking`).
  - `resources/views/services/show.blade.php` (line 77) → `route('booking.index', ['service' => $service->slug])` ✅.
  - `resources/views/partials/doctor-card.blade.php` (line 31) → `route('contacts')` ❌ (нерабочая, ведёт в /contacts).
  - `resources/views/doctors/show.blade.php` (line 104) → `route('contacts')` ❌.
  - `resources/views/home.blade.php` (line 30) → `<a href="#appointment">` ❌ (якоря `#appointment` на странице нет).
  - `public/scripts/script.js` (lines 281–286) добавляет в мобильное меню кнопку `<a href="#appointment">` ❌.
  - `public/scripts/script-index-doctors.js` (line 38) рендерит `<a href="contacts-page.html">Записаться</a>` (статический HTML‑путь, остаток от старого прототипа) ❌.
- Отправка уведомлений: `MAIL_MAILER=log`, нет SMS-провайдера, очередь `database` (`QUEUE_CONNECTION=database`) — настроена.
- Filament admin: ресурсов для `Appointment`, `Patient`, `DoctorSchedule` **нет** (есть только `Services`, `Doctors`, `Specializations`, `Directions`, `Articles*`, `Promotions*`, `Documents`, `Vacancies`, `Reviews`, `Equipment`, `Licenses`, `PromoSlides` и страница `SettingsPage`).

### 0.2. Мобильное приложение (`mobileApp/`)
- Flutter, `flutter_riverpod`, `go_router`, `dio`, `flutter_secure_storage`, `shared_preferences`, `firebase_messaging`.
- 5‑шаговый мастер регистрации (`features/registration/registration_screen.dart`):
  1. **Step1Personal** (`steps/step1_personal.dart`): поля
     - `Фамилия *` — `^[а-яёА-ЯЁ\s\-]+$`, ≥ 2 символов;
     - `Имя *` — то же правило;
     - `Отчество` — то же правило, опционально;
     - `Дата рождения *` — маска `dd.MM.yyyy`, `_isValidDate` проверяет `checkdate` + не в будущем;
     - `Пол *` — кнопки «Мужской / Женский» → `gender = 'male' | 'female'`.
  2. **Step2Phone** (`steps/step2_phone.dart`): жёстко зашит префикс `+375`, поле — 9 цифр (`FilteringTextInputFormatter.digitsOnly` + `LengthLimitingTextInputFormatter(9)`), плейсхолдер `(XX) XXX-XX-XX`.
  3. **Step3Otp** (`steps/step3_otp.dart`): 6 цифр, авто‑переход между `_OtpBox`. Сейчас вызывает `authControllerProvider.completeRegistration(...)` — заглушка, реального запроса в API нет.
  4. **PinSetupScreen** — установка PIN.
  5. **FaceIdSetupScreen** — биометрия (опционально).
- Состояние и данные: `RegistrationData { lastName, firstName, middleName, birthDate, gender, phone }` + `RegistrationController` (Riverpod), `reset()` при заходе.
- Уже подключено к API сайта: `dashboard/services`, `dashboard/doctors`, `dashboard/blog`, `dashboard/promotions`. Экран `appointments/appointments_screen.dart` существует, но пока **без живых данных**.

### 0.3. Сводка дельты (что нужно сделать)
1. Перевести «Записаться» из набора заглушек в **полный двухэтапный флоу с выбором даты/времени**.
2. Ввести **расписание врача** + **слоты** (длительность услуги, шаг сетки) + **проверку пересечений**.
3. Достроить **личный кабинет** пациента (предстоящие/прошедшие записи, отмена/перенос, профиль).
4. Достроить **API под мобильное приложение** (регистрация/логин по OTP, токен Sanctum, список и создание записей, профиль) с теми же правилами валидации, что и в `Step1/Step2`.
5. Обработать неавторизованного пользователя (вход в флоу записи прямо из карточки врача/услуги, регистрация по ходу).
6. Заменить демо‑OTP `111111` на нормальный SMS‑провайдер (через интерфейс) с фолбэком на email/log в `local`.
7. Filament: **AppointmentResource**, **PatientResource (read‑only)**, **DoctorScheduleRelationManager** на докторе.
8. **CRM-интеграция (Espo)**: с самого начала писать под гибрид «Laravel = аккаунт+зеркало, CRM = источник правды профиля и записи»; зарезервировать поля `espo_*`, написать `CrmClient` с реализациями `EspoClient` и `DryRunEspoClient`, push/pull‑sync, webhook. См. этап 6.6.
9. Тесты Pest 4 (Feature + Browser, включая CRM с `Http::fake()`) + Flutter widget‑тесты на ключевые экраны.

---

## 1. Этап 1. Проектирование данных и доменной логики

### 1.1. Модель «Расписание врача» (`doctor_schedules`)
1. **Создать миграцию** `php artisan make:migration create_doctor_schedules_table --no-interaction`. Структура:
   - `id`, `doctor_id` FK→`doctors.id` `cascadeOnDelete`,
   - `weekday` `tinyInteger unsigned` (0=Mon … 6=Sun **— зафиксировать в PHPDoc и enum-классе**),
   - `start_time` `time`, `end_time` `time`,
   - `created_at/updated_at`,
   - `unique(['doctor_id','weekday'])` (как в `dumpSite.sql`).
2. Создать `App\Enums\Weekday` (TitleCase кейсы: `Monday=0`, `Tuesday=1`, …) — использовать в Filament select и в `BookingService`.
3. Создать модель `php artisan make:model DoctorSchedule --factory --seeder --no-interaction`. Связь `belongsTo(Doctor::class)`. На `Doctor` добавить `hasMany(DoctorSchedule::class)`.
4. Перенести данные из дампа `doctor_schedules` через сидер `DoctorScheduleSeeder` (по умолчанию Пн–Пт `08:00–13:40` для всех докторов).

### 1.2. Длительность приёма и сетка
1. Добавить миграцию `add_duration_to_services_table`: `unsignedSmallInteger duration_minutes default 30 nullable(false)`. Семантика: длительность одного приёма по этой услуге.
2. (Опционально) добавить `slot_step_minutes` в `Setting` группа `booking` (например, `20`). Если не задано — `duration_minutes` услуги.
3. На `Service` добавить cast `'duration_minutes' => 'integer'` и поле в Filament `ServiceForm` (`TextInput::make('duration_minutes')->numeric()->minValue(5)->maxValue(240)->step(5)->default(30)->required()`).
4. (Опционально, фаза 2) ввести `service_doctor.duration_minutes` (override длительности у конкретного врача) — расширение pivot. Для MVP — *не делать*.

### 1.3. Расширение модели «Запись» (`appointments`)
1. Миграция `extend_appointments_for_booking`:
   - `start_at` `dateTime` (NULL допустим только для legacy строк, для новых — required).
   - `end_at` `dateTime` (вычисляется из `start_at + service.duration_minutes`).
   - `cancellation_reason` `text nullable`,
   - `cancelled_at` `timestamp nullable`,
   - `rescheduled_from_id` `bigint unsigned nullable` FK→`appointments.id` `nullOnDelete` (история переносов).
   - В enum `status` добавить значения `'cancelled'`, `'rescheduled'`. Использовать `php artisan make:enum AppointmentStatus` + cast (PHP 8.1+).
   - Индексы: `index(['doctor_id','start_at'])`, `index(['patient_id','start_at'])`, `index(['status'])`.
   - Уникальный constraint: `unique(['doctor_id','start_at'])` для статусов != cancelled — реализовать через **partial index** в MySQL невозможно, поэтому проверять в `BookingService` + добавить `index(['doctor_id','start_at'])` + транзакция с `lockForUpdate()`.
2. Сохраняемое заполнение:
   - При `store` — `start_at = $request->slot_start`, `end_at = $start_at + service.duration_minutes`.
   - `preferred_date` — оставить для обратной совместимости (заполнять = `start_at`).
   - `message` — заполнять из формы (необязательное поле «Комментарий»).

### 1.4. Доменный сервис `BookingService`
1. Создать `app/Services/BookingService.php`. Публичный API:
   - `availableDates(Doctor $doctor, Service $service, CarbonInterval $window): array` — список дат (в горизонте, например, 30 дней) на которые есть хотя бы один свободный слот.
   - `availableSlots(Doctor $doctor, Service $service, CarbonImmutable $date): Collection<CarbonImmutable>` — список начал свободных слотов с шагом `slot_step_minutes` (или `service.duration_minutes`), не пересекающихся с существующими `appointments` врача и попадающих внутрь `doctor_schedules.weekday=$date->dayOfWeek` интервал.
   - `book(Patient $patient, Doctor $doctor, Service $service, CarbonImmutable $startsAt, ?string $note): Appointment` — создание в транзакции с `Doctor::lockForUpdate()` и повторной валидацией слота (защита от гонок).
   - `cancel(Appointment $appointment, Patient $patient, ?string $reason): void` — устанавливает `status=cancelled`, `cancelled_at=now()`, `cancellation_reason=$reason`. Не позволяет отменять, если до приёма меньше N часов (настройка `Setting::getValue('booking','cancel_window_hours','3')`).
   - `reschedule(Appointment $old, CarbonImmutable $newStart, ?Doctor $newDoctor=null): Appointment` — создаёт новую запись с `rescheduled_from_id=$old->id`, помечает старую `rescheduled` + `cancelled_at`.
2. Все проверки доступности — внутри одной DB-транзакции, чтобы исключить двойную бронь. **Источник правды для расписания — Laravel** (`doctor_schedules`); CRM в этом расчёте не участвует.
3. **CRM‑hook**: после `commit` транзакции в `book()`/`cancel()`/`reschedule()` диспатчить соответствующие job‑ы (см. этап 6.6.2). Сами методы CRM не дёргают синхронно — это отдельная зона ответственности.
4. Покрыть Pest unit‑тестами (`tests/Unit/BookingServiceTest.php`): «слоты не пересекаются», «нельзя забронировать вне расписания», «нельзя забронировать в прошлом», «гонка двух одинаковых записей» (в `RefreshDatabase` + `assertDatabaseCount`), «после успешного book диспатчится `SyncAppointmentToEspo`» (`Bus::fake()`).

### 1.5. Аудит‑таблица (опционально, фаза 2)
- `appointment_events` (`appointment_id`, `actor_type` `enum('patient','admin','system')`, `actor_id`, `action` `enum('created','rescheduled','cancelled','status_changed')`, `payload` json, timestamps). Не блокирует MVP.

---

## 2. Этап 2. Бэкенд: HTTP API и контроллеры

### 2.1. Аутентификация (Web + API)
1. Уже работает Web‑сессионная OTP‑авторизация для сайта — **сохранить**.
2. Подключить `laravel/sanctum` для API мобильного приложения: `composer require laravel/sanctum`, `php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"`, миграция `personal_access_tokens`. На `Patient` добавить трейт `HasApiTokens`.
3. Гард `auth:sanctum` использовать в API‑роутах личного кабинета (см. ниже).
4. Унифицировать **формат телефона**: на бэке хранить «полный» E.164 без `+` — `375XXXXXXXXX` (уже так в `App\Support\PatientPhone::normalize`, фронт передаёт 9 цифр и + код страны). Mobile приложение должно отправлять `phone = "375" + 9digits`.

### 2.2. Web‑контроллеры (Blade сайт)
Группа `routes/web.php`:
```
Route::prefix('booking')->name('booking.')->middleware(['throttle:30,1'])->group(function () {
    Route::get('/start', [BookingFlowController::class, 'start'])->name('start');             // выбор «отправной точки»
    Route::get('/service', [BookingFlowController::class, 'pickService'])->name('pickService');  // ?from=doctor:slug | from=any
    Route::get('/doctor', [BookingFlowController::class, 'pickDoctor'])->name('pickDoctor');     // ?service=slug
    Route::get('/slot', [BookingFlowController::class, 'pickSlot'])->name('pickSlot');           // ?service=slug&doctor=slug
    Route::middleware('patient.auth')->post('/confirm', [BookingFlowController::class, 'confirm'])->name('confirm');
});
```
- `BookingFlowController@start`: отдаёт страницу-распутыватель «Запись на приём». Принимает `?from=doctor:{slug}` или `?from=service:{slug}`.
- `pickService`: рендерит модалку/страницу со списком услуг. Если пришли «от врача» (`from=doctor:slug`) — показать только `Doctor::findBySlug($slug)->services()->active()`.
- `pickDoctor`: при выбранной услуге рендерит врачей `$service->doctors()->active()->whereHas('schedules')`.
- `pickSlot`: подгружает `availableDates()` и для выбранной даты — `availableSlots()`. Использует `BookingService` (см. 1.4). На странице — календарь и сетка времени.
- `confirm`: `StoreBookingRequest` (расширить — добавить `start_at`, опц. `note`, `service_id`, `doctor_id`); вызывает `BookingService::book()`; пушит уведомления (см. 2.5); редирект → `cabinet.appointments.show($id)` с `flash('status')`.

**Если пользователь не авторизован**, любой из `start/pickService/pickDoctor/pickSlot` сохраняет выбранные параметры в сессии (`session(['booking_pending' => [...]])`) и редиректит на `patient.login`. После логина `PatientLoginController::verify` использует `redirect()->intended()` — а `intended` указывает на `/booking/slot?...`. После успешного бронирования сессионный ключ очищается.

### 2.3. API под мобильное приложение (новые `api/v1` роуты)
**Публичные (`routes/api.php`):**
```
Route::prefix('auth')->group(function () {
    Route::post('/register/request-otp', ...);   // {phone, last_name, first_name, middle_name?, birth_date, gender}  → throttle:6,1
    Route::post('/register/verify', ...);         // {phone, otp}  → создаёт Patient, возвращает {token, patient}
    Route::post('/login/request-otp', ...);       // {phone}  → throttle:6,1
    Route::post('/login/verify', ...);            // {phone, otp}  → {token, patient}
});

Route::get('/booking/services', ...);                                            // ?doctor={slug}? — список доступных услуг
Route::get('/booking/doctors', ...);                                             // ?service={slug}? — врачи, оказывающие услугу
Route::get('/booking/slots', ...);                                               // ?service=&doctor=&date=YYYY-MM-DD → массив start_at
Route::get('/booking/dates', ...);                                               // ?service=&doctor=&from=&to= → массив дат с свободными слотами
```
**Защищённые (`auth:sanctum`):**
```
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [PatientApiController::class, 'me']);
    Route::patch('/me', [PatientApiController::class, 'update']);
    Route::post('/me/logout', [PatientApiController::class, 'logout']);

    Route::get('/appointments', [AppointmentApiController::class, 'index']);   // ?status=upcoming|past
    Route::post('/appointments', [AppointmentApiController::class, 'store']);  // {service_id, doctor_id, start_at, note?}
    Route::get('/appointments/{id}', [AppointmentApiController::class, 'show']);
    Route::post('/appointments/{id}/cancel', [AppointmentApiController::class, 'cancel']);  // {reason?}
    Route::post('/appointments/{id}/reschedule', [AppointmentApiController::class, 'reschedule']); // {start_at, doctor_id?}
});
```
- Все ответы — `JsonResource`/`AnonymousResourceCollection` с обёрткой `data`. Существующие `Api/*Resource.php` — образец.
- Ошибки бизнес‑логики — кастомное исключение `App\Exceptions\BookingException` с маппингом в HTTP 422 + `errors`.

### 2.4. FormRequests (валидация)
- `App\Http\Requests\Booking\PickSlotRequest`: `service_id|exists`, `doctor_id|exists`, `date|date_format:Y-m-d|after_or_equal:today`.
- `App\Http\Requests\Booking\StoreBookingRequest` (заменить текущий):
  - `service_id` `required|integer|exists:services,id`,
  - `doctor_id` `required|integer|exists:doctors,id`,
  - `start_at` `required|date|after:now`,
  - `note` `nullable|string|max:500`,
  - `withValidator()` дополнительно: `service↔doctor` связаны (`Service::find()->doctors()->whereKey($doctor)->exists()`).
- `App\Http\Requests\Booking\CancelRequest`: `reason|nullable|string|max:500`.
- `App\Http\Requests\Booking\RescheduleRequest`: `start_at|required|date|after:now`, `doctor_id|nullable|exists:doctors,id`.
- `App\Http\Requests\Api\Auth\RegisterRequestOtpRequest`: набор полей **строго** соответствует мобильному `Step1+Step2` (см. 0.2):
  - `last_name`, `first_name` — `required|string|max:120|regex:/^[а-яёА-ЯЁ\s\-]+$/u`;
  - `middle_name` — `nullable|string|max:120|regex:/^[а-яёА-ЯЁ\s\-]*$/u`;
  - `birth_date` — `required|date_format:d.m.Y|before:today`;
  - `gender` — `required|in:male,female`;
  - `phone` — `required|regex:/^[0-9]{10,15}$/`. `prepareForValidation()` использует `App\Support\PatientPhone::normalize()`.

### 2.5. Уведомления
1. `php artisan make:notification AppointmentCreatedNotification` — каналы `mail` + кастомный `sms` (см. ниже). Темплейт mail — Markdown mailable: дата/время, врач, услуга, ссылка на отмену.
2. `php artisan make:notification AppointmentCancelledNotification`, `AppointmentRescheduledNotification`.
3. SMS‑канал: интерфейс `App\Contracts\SmsSender` с реализациями `LogSmsSender` (env `SMS_DRIVER=log`) и `SmsBy` (заглушка под белорусского провайдера: BeSMS/Rocket SMS — на этапе MVP не подключаем, оставляем интерфейс). Биндинг в `AppServiceProvider`.
4. Доставка через очередь (`ShouldQueue`, `->onQueue('notifications')`) — конфиг `QUEUE_CONNECTION=database` уже есть. Очередь `notifications` физически отделена от `crm`, чтобы зависание CRM не блокировало уведомления пациенту.
5. Уведомления **независимы от CRM**: пациент получает email/SMS сразу после `BookingService::book()`, даже если CRM недоступна. Уведомления оператору о новой заявке могут идти как из Laravel (mail на `notify_email_admin`), так и из CRM (стандартный workflow Espo) — выбрать одно, чтобы не дублировать (рекомендация плана: операторская часть — на стороне CRM, чтобы не плодить две точки правды).

### 2.6. Безопасность
- CSRF: на формах сайта — `@csrf`. На API — Sanctum stateless tokens (без CSRF, `EnsureFrontendRequestsAreStateful` не подключаем для мобильного).
- Хеширование пароля: пока не используется (auth по OTP), но при добавлении пароля (см. 4.1) — `Hash::make` (Bcrypt 12 rounds, env `BCRYPT_ROUNDS=12`).
- Rate limit:
  - запрос OTP — `throttle:6,1` (ровно как сейчас стоит `throttle:12,1` — снизить в обоих контроллерах + по сегменту IP+phone);
  - `pickSlot/availableSlots` — `throttle:60,1` (часто опрашивается фронтом);
  - `confirm/store` — `throttle:10,1`.
- Логировать в `Log::channel('audit')` все cancel/reschedule с IP и user‑agent.
- В `BookingService::book` — повторная проверка `start_at >= now() + min_lead_minutes` (Setting `booking.min_lead_minutes`, по умолчанию `60`).

---

## 3. Этап 3. Фронтенд сайта (Blade + лёгкий JS)

> Стек уже Blade + Tailwind + кастомные `public/scripts/*.js`. Не вносить React/Vue. Для интерактива — Alpine.js (`@alpine` в layout) либо «обычный» JS с `fetch` к новым API. Календарь — `flatpickr` (lightweight) или собственный Alpine-компонент. Не подключать тяжёлые UI-фреймворки.

### 3.1. Привязка кнопок «Записаться»
1. `resources/views/partials/doctor-card.blade.php` — заменить
   ```
   <a href="{{ route('contacts') }}" class="doctor-btn">Записаться</a>
   ```
   на
   ```
   <a href="{{ route('booking.start', ['from' => 'doctor:'.$doctor->slug]) }}" class="doctor-btn">Записаться</a>
   ```
2. `resources/views/doctors/show.blade.php` (line 104) — аналогично, `class="btn doctor-hero-cta"`.
3. `resources/views/services/show.blade.php` (line 77) — оставить как есть, но направить на новый `route('booking.start', ['from' => 'service:'.$service->slug])`.
4. `resources/views/home.blade.php` (line 30) — `<a href="#appointment">` заменить на `route('booking.start')`.
5. `resources/views/partials/header.blade.php` (lines 105, 146, 168) — оставить `route('booking.index')` (см. ниже редирект) **или** заменить на `route('booking.start')`. Лучше `start`.
6. `public/scripts/script.js` (lines 281–286) — кнопка мобильного меню: заменить `mobileAppointmentBtn.href = '#appointment'` на серверно отрендеренную ссылку (передавать `data-booking-url` в HTML, читать в JS).
7. `public/scripts/script-index-doctors.js` (line 38) — заменить `'<a href="contacts-page.html" class="doctor-btn">Записаться</a>'` на `<a href="${doctor.bookingUrl}">…</a>` (или вообще удалить блок генерации, если карточки уже отдаёт сервер).
8. Старый `BookingController@index` оставить как ре-директ на `booking.start` (`return redirect()->route('booking.start');`) для обратной совместимости.

### 3.2. Состояния и UX мастера записи
Страницы (Blade) внутри одного layout с прогресс‑шагами «1/3 Услуга → 2/3 Врач → 3/3 Дата и время»:

**`resources/views/booking/start.blade.php`** — лендинг с двумя кнопками: «Выбрать услугу» / «Выбрать врача». При query `?from=doctor:{slug}` или `?from=service:{slug}` сразу пробрасывает на следующий шаг.

**`resources/views/booking/service.blade.php`** — список услуг (по `directions` → `services`), либо отфильтрованный (`$onlyDoctorServices`). Каждая карточка ведёт на `booking.pickDoctor` с `?service={slug}`.

**`resources/views/booking/doctor.blade.php`** — сетка `partials.doctor-card` (с одинаковой вёрсткой), но карточка → `booking.pickSlot?service={slug}&doctor={slug}`. Если врач один — авто‑редирект.

**`resources/views/booking/slot.blade.php`** — две колонки:
- слева: календарь (`flatpickr inline`) с подсветкой «доступных дат» (`availableDates(...)`);
- справа: сетка кнопок‑времени (`availableSlots(...)`); меняется AJAX‑ом при клике по дате (`fetch('/api/v1/booking/slots?...').then(...)`);
- снизу: textarea «Комментарий» + чекбокс согласия + кнопка «Подтвердить запись».
- Если пользователь не авторизован — кнопка превращается в «Войти и подтвердить» → редирект на `patient.login` с сохранением выбора в session‑store (см. 2.2).
- После подтверждения — flash‑сообщение и редирект на `cabinet.appointments.show($id)`.

**Состояния (для всех шагов):**
- loading: skeleton-блоки с `animate-pulse` (Tailwind v4);
- empty: «К сожалению, у этой услуги нет привязанных врачей» / «Нет свободных слотов в ближайшие 30 дней — оставьте заявку, и мы свяжемся»;
- error: красный inline alert с `session('error')` и текстами из `BookingException`.

### 3.3. Личный кабинет (Blade)
Группа `Route::middleware('patient.auth')->prefix('cabinet')->name('cabinet.')->group(...)`:
- `GET /cabinet` → `CabinetController@dashboard` — карточка профиля + следующая запись + ссылки.
- `GET /cabinet/appointments` → `CabinetController@appointments` — две вкладки: «Предстоящие» / «Прошедшие». Пагинация ленивая.
- `GET /cabinet/appointments/{id}` → `CabinetController@show` — детали записи + кнопки «Отменить» / «Перенести».
- `POST /cabinet/appointments/{id}/cancel` → `CabinetController@cancel` (`CancelRequest`).
- `GET /cabinet/appointments/{id}/reschedule` → переиспользует `booking.pickSlot.blade.php` с предзаполненными `service` и `doctor`, флагом `mode=reschedule`.
- `POST /cabinet/appointments/{id}/reschedule` → `CabinetController@reschedule` (`RescheduleRequest`).
- `GET /cabinet/profile`, `PATCH /cabinet/profile` → редактирование `last_name/first_name/middle_name/birth_date/gender/email`. Телефон не редактируется (он же логин); смена телефона — отдельный сценарий (повторная OTP‑верификация нового номера).
- **Источник данных**: контроллер тянет `appointments` пациента из локальной таблицы (зеркало). Локальный `status` обновляется по pull‑sync/webhook из CRM (см. этап 6.6.3). Кэш ФИО берётся из `patients` (этап A.3); если в фазе 2 будет переход на «без кэша» — заменить на `PatientProfileResolver`, контроллеры менять не придётся.
- **Действия пациента (cancel/reschedule)** меняют локальное зеркало синхронно и диспатчат job в CRM (см. этап 6.6.2). При сбое job‑а пациенту видна актуальная локальная картина, а оператор в Filament видит badge «не синхронизировано».

**Файлы:**
- `app/Http/Controllers/Cabinet/CabinetController.php`,
- `resources/views/cabinet/{dashboard,appointments,show,profile}.blade.php`,
- `resources/views/partials/cabinet-nav.blade.php` (боковое меню).

### 3.4. Хедер и состояние авторизации
- В `resources/views/partials/header.blade.php` отображать кнопку профиля для авторизованных пациентов (`@auth('patient') … @endauth`): аватар‑заглушка + ФИО → ссылка на `cabinet.dashboard`. Иначе — кнопки «Войти / Регистрация».
- Кнопку «Записаться» оставить всегда — она ведёт в флоу, который сам обрабатывает анонимного посетителя.

---

## 4. Этап 4. Мобильное приложение (Flutter)

> Все правила валидации в `Step1Personal` и `Step2Phone` уже совпадают с серверной частью (см. 2.4). Сохранять их 1:1.

### 4.1. Настройка сети и хранилища
1. Добавить `lib/core/network/dio_client.dart`: базовый URL из `--dart-define=API_BASE=https://...`, заголовок `Accept: application/json`. Bearer‑токен подставлять интерсептором из `flutter_secure_storage` (`auth_token`).
2. `lib/core/auth/auth_repository.dart`:
   - `requestRegisterOtp({phone, lastName, firstName, middleName, birthDate, gender})` → `POST /api/v1/auth/register/request-otp`;
   - `verifyRegisterOtp({phone, otp})` → `POST /api/v1/auth/register/verify` → сохранить `token` в `secure_storage`;
   - `requestLoginOtp({phone})`, `verifyLoginOtp({phone, otp})` — симметрично;
   - `me()`, `logout()`.
3. `authControllerProvider` (`features/auth/presentation/controllers/auth_controller.dart`): заменить заглушку `completeRegistration` на реальные вызовы выше; обновить состояние `AuthState`.

### 4.2. Регистрация
1. В `Step3Otp._confirm` сейчас:
   ```
   await ref.read(authControllerProvider.notifier).completeRegistration(...)
   ```
   Заменить на:
   - до Step3 (в `Step2Phone.handleNext`): вызвать `requestRegisterOtp(...)` со всеми полями `RegistrationData`. При успехе — переход к Step3.
   - В `Step3Otp._confirm`: `verifyRegisterOtp(phone: data.phone, otp: _otp)`. Если 200 — `goToStep(4)` (PIN). Если 4xx — показать ошибку под полем (`'Неверный или просроченный код'`).
2. `RegistrationData` дополнить геттером `phoneE164 => '375$phone'` и использовать его при отправке.
3. `PinSetupScreen` и `FaceIdSetupScreen` — это локальные данные (PIN хранится через `flutter_secure_storage`, биометрия — `local_auth`). Не отправляем на сервер.

### 4.3. Логин
1. Заменить заглушку в `features/login/login_phone.dart` + `login_otp.dart` на `auth_repository.requestLoginOtp / verifyLoginOtp`.
2. Если `phone` не зарегистрирован — серверный 422 с `errors.phone[0]='Номер не найден'`. Показывать с CTA «Зарегистрироваться».

### 4.4. Запись на приём из мобильного приложения
1. Новый модуль `features/booking/`:
   - `booking_repository.dart` — обёртки над `/api/v1/booking/services|doctors|dates|slots`, `/api/v1/appointments`.
   - `booking_providers.dart` — Riverpod провайдеры (`servicesByDoctorProvider(family slug)`, `doctorsByServiceProvider(family slug)`, `availableSlotsProvider(family ({serviceId, doctorId, date}))`, `availableDatesProvider`).
   - Экраны: `BookingEntryScreen` (выбор «по услуге / по врачу»), `ServicePickerScreen`, `DoctorPickerScreen`, `SlotPickerScreen`, `BookingConfirmScreen`.
2. На `DoctorsScreen`/`DoctorDetailScreen` и `ServicesScreen`/`ServiceDetailScreen` добавить FAB/CTA «Записаться» с переходом в флоу.
3. `appointments_screen.dart` — заменить заглушку на `appointmentsProvider` (вызов `GET /api/v1/appointments?status=upcoming|past`); вкладки «Предстоящие/История»; pull‑to‑refresh; tap → `AppointmentDetailScreen` с кнопками «Отменить» / «Перенести» (вызывает `/cancel` и `/reschedule`).

### 4.5. Профиль
1. Экран `profile_screen.dart` — отображение `last_name/first_name/middle_name/birth_date/gender/phone` из `me()`. Редактирование через `PATCH /api/v1/me`. Поле `phone` — read‑only, кнопка «Сменить номер» открывает мини‑визард (новый OTP, отдельный API `/api/v1/me/phone/request-otp`/`/verify` — вне MVP).
2. Кнопка «Выйти» — `auth_repository.logout()` → очистка токена + редирект на `/auth`.

---

## 5. Этап 5. Filament admin (для оператора клиники)

### 5.1. AppointmentResource
1. `php artisan make:filament-resource Appointment --model=Appointment --no-interaction`. Колонки таблицы: `start_at` (sortable), пациент (`patient.full_name` или `patient_name`), услуга, врач, статус (badge с цветом), `phone`, `created_at`. Фильтры: статус, диапазон дат, врач, услуга. Bulk action: «Подтвердить» (`status=processing|completed`).
2. Действия: `cancel` (с диалогом причины), `reschedule` (открывает форму со слот-пикером — переиспользовать `BookingService::availableSlots`).
3. На странице `EditAppointment` запретить менять `patient_id`, `service_id`, `doctor_id` напрямую (через FormSchema `disabled()`); поменять можно только статус и `admin_comment`.

### 5.2. PatientResource (read‑only)
1. Список пациентов (`last_name`, `first_name`, `phone`, `birth_date`, кол‑во записей), просмотр детальной карточки. Без `Create/Delete`.

### 5.3. DoctorScheduleRelationManager
1. На `DoctorResource` добавить `RelationManagers\SchedulesRelationManager` с CRUD `weekday/start_time/end_time`. Используется `App\Enums\Weekday` для select.

### 5.4. Settings
1. На `app/Filament/Pages/SettingsPage.php` добавить секцию `booking`:
   - `slot_step_minutes` (default 20),
   - `min_lead_minutes` (default 60),
   - `cancel_window_hours` (default 3),
   - `notify_email_from`, `notify_email_admin`.

---

## 6. Этап 6. Интеграция и сквозные сценарии

### 6.1. Анонимный пользователь, флоу «от врача»
1. На `/doctors/{slug}` → клик «Записаться» → `/booking/start?from=doctor:{slug}`.
2. Шаг услуги: показываются только услуги этого врача (`Service::query()->whereHas('doctors', fn($q)=>$q->whereKey($doctor->id))`). Выбирает услугу → `/booking/slot?service={slug}&doctor={slug}`.
3. Шаг слота: выбирает дату и время. Жмёт «Подтвердить».
4. Так как `auth('patient')` пуст: контроллер сохраняет `session(['booking_pending'=>['service'=>..., 'doctor'=>..., 'start_at'=>...]])` и редиректит на `patient.login` с `redirect()->guest()` (intended URL = `cabinet.appointments.create-from-pending` или просто `booking.confirm`).
5. После логина (или регистрации) — авто‑POST на `booking.confirm` с восстановленными параметрами; запись создаётся; пользователь оказывается в кабинете с подтверждением.

### 6.2. Анонимный пользователь, флоу «от услуги»
- Аналогично, но `from=service:{slug}` пропускает шаг услуги.

### 6.3. Авторизованный пользователь
- Пропускаются шаги логина. После «Подтвердить» — мгновенно создаётся запись, отправляются уведомления (job в очередь), редирект в кабинет.

### 6.4. Edge cases
- Слот занят между «Подтвердить» и реальной транзакцией: `BookingException(SLOT_TAKEN)` → редирект на тот же `pickSlot` с `flash('error', ...)`.
- Запись в прошлое или ближе чем `min_lead_minutes`: `BookingException(SLOT_TOO_SOON)`.
- Врач не оказывает услугу (мутировал URL): `BookingException(SERVICE_DOCTOR_MISMATCH)`.
- Множественные записи одного пациента в один и тот же интервал: запретить через `BookingService` (поиск пересечений по `patient_id`).
- Отмена ближе чем `cancel_window_hours`: 422.
- Перенос меняет `service_id`? — для MVP: только время и/или врач, услугу не менять (UI не предоставляет такой кейс).

### 6.5. Уведомления — конкретика
- **Создание**: `AppointmentCreatedNotification` → email пациенту (Markdown mailable) + `LogSmsSender` (заглушка `info()`). В env `local` — только log.
- **Отмена/перенос**: соответствующие notifications.
- **Напоминание за 24 часа**: команда `php artisan booking:remind` (`schedule()->everyMinute()` в `routes/console.php`, идемпотентная по `last_reminded_at` колонке — добавить миграцией).

---

## 6.6. Этап 6.5. CRM-интеграция (Espo) — push, pull, webhook, fallback

> Этот этап вводится одновременно с этапом 1–4: писать сразу под гибрид. Если на момент разработки боевой Espo ещё не готов — реализуется **`DryRunEspoClient`** (логирует операции, ничего не делает), включается флагом `Setting::getValue('crm', 'dry_run', true)`. Локальная логика записи и кабинета этим не блокируется.

### 6.6.1. Сервисный слой
1. Создать интерфейс `App\Contracts\Crm\CrmClient` со следующими методами:
   - `findOrCreateContact(Patient $patient): string` → возвращает `espo_contact_id`. Идемпотентен по `phone`.
   - `updateContact(string $contactId, array $payload): void`.
   - `createAppointment(Appointment $appointment): array` → возвращает `['id' => '...', 'type' => 'Meeting']`.
   - `updateAppointment(string $entityType, string $entityId, array $payload): void`.
   - `cancelAppointment(string $entityType, string $entityId, ?string $reason): void`.
   - `pullAppointment(string $entityType, string $entityId): array` *(чтение для cron‑sync)*.
2. Реализации:
   - `App\Services\Crm\EspoClient` — реальный клиент на `Http::baseUrl(...)->withHeaders(['X-Api-Key'=>$apiKey])`. Все методы оборачивают REST API Espo (`/api/v1/Contact`, `/api/v1/{entityType}`).
   - `App\Services\Crm\DryRunEspoClient` — пишет в `Log::channel('crm')` и возвращает заглушки `'dry-run-'.Str::uuid()`.
3. Биндинг в `App\Providers\AppServiceProvider::register()`:
   ```
   $this->app->bind(CrmClient::class, fn () =>
       Setting::getValue('crm','enabled','false') === 'true' && Setting::getValue('crm','dry_run','true') !== 'true'
           ? new EspoClient(...config from Setting+env)
           : new DryRunEspoClient()
   );
   ```
4. Конфиг Espo живёт **в `settings`** (см. `crm` в этапе 7) + `env('ESPO_API_KEY')`. API key — только в `.env`, никогда в Setting.

### 6.6.2. Push‑синхронизация (Laravel → Espo)
1. **Контакт**:
   - В `PatientRegisterController::complete` после `Patient::create(...)` диспатчить `SyncPatientToEspo::dispatch($patient)`.
   - Job: `findOrCreateContact()` → если новый — `updateContact()` с ФИО/ДР/полом/email → записать `espo_contact_id`, `espo_synced_at`, `espo_sync_status='synced'`. При ошибке — `failed` + `espo_sync_error`, ретрай по очереди.
   - При `PATCH /api/v1/me` (см. этап 2.3) — диспатчить `SyncPatientToEspo` снова.
2. **Запись** (Appointment):
   - В `BookingService::book()` после успешной транзакции — `SyncAppointmentToEspo::dispatch($appointment)`.
   - Job: если у пациента нет `espo_contact_id` → сначала `SyncPatientToEspo::dispatchSync()` (синхронно, чтобы получить id) → потом `createAppointment()` → записать `espo_entity_id`, `espo_entity_type`, `espo_sync_status='synced'`.
   - При `cancel`/`reschedule` в Laravel — соответственно `SyncAppointmentCancellationToEspo` и `SyncAppointmentRescheduleToEspo` (создаёт новую сущность в CRM или обновляет старую — выбрать стратегию по обсуждению с заказчиком; см. вопрос в разделе «Открытые вопросы»).
3. **Очередь и устойчивость**:
   - Очередь `crm` (отделить от `default`): в `config/queue.php` или через `->onQueue('crm')`.
   - `tries=5`, `backoff=[10,30,60,180,600]`, `failOnTimeout=true`.
   - Падающие job’ы попадают в `failed_jobs`, оператор видит их в Filament (стандартный `php artisan queue:failed`/`retry`). На странице записи в Filament — индикатор «не синхронизировано» (badge по `espo_sync_status`).

### 6.6.3. Pull / Webhook (Espo → Laravel)
1. **Webhook**: в Laravel — публичный POST‑эндпоинт `/webhooks/espo/{event}` (например, `appointment.updated`, `contact.updated`).
   - Группа `Route::post('/webhooks/espo/{event}', [EspoWebhookController::class, 'handle'])->middleware('throttle:60,1');`
   - Подпись HMAC‑SHA256: заголовок `X-Espo-Signature` против `Setting::getValue('crm','webhook_secret')`. Сравнение через `hash_equals`. Без CSRF (это API‑эндпоинт; гарантировать exclude в `bootstrap/app.php` или вынести в `routes/api.php`).
   - Внутри: парсить JSON, диспатчить `ApplyEspoChangesToLocal::dispatch($event, $payload)` job.
   - **Безопасность:** до проверки подписи не трогать БД; логировать невалидные попытки.
2. **Pull‑sync** (на случай, если Espo не умеет webhook’и или они теряются):
   - Команда `php artisan crm:pull-appointments --since=15min` — запускается планировщиком каждые 5 минут (`schedule()->everyFiveMinutes()` в `routes/console.php`).
   - Берёт все `appointments` со `status IN ('new','processing')` и `espo_entity_id IS NOT NULL` и `espo_synced_at < now() - 5min` — для каждой делает `pullAppointment()`, мапит CRM‑статус в локальный (см. ниже).
3. **Маппинг статусов** CRM → Laravel (зафиксировать в `App\Services\Crm\StatusMapper`):
   - Espo `Planned` → `processing`.
   - Espo `Held` → `completed`.
   - Espo `Not Held` / `Canceled` → `cancelled`.
   - Espo любые другие → не трогать локальный статус (no‑op).
   - Обратный маппинг — для push (Laravel `cancelled` → Espo `Canceled` и т.д.).

### 6.6.4. Контакт пациента: «найти или создать»
1. Идентификация в CRM по `phone` (E.164 без «+»). Если в CRM **уже есть** контакт с этим телефоном (например, оператор завёл его руками), `findOrCreateContact()` берёт существующий и просто пишет его id в `patients.espo_contact_id` — без перезаписи ФИО.
2. При следующем `pull` ФИО/ДР/пол **в Laravel перезаписываются из CRM** (CRM — источник правды). Это решает кейс, когда оператор подправил ФИО в CRM.
3. Расхождения логируются в `Log::channel('crm')` уровнем `info`.

### 6.6.5. Поведение при недоступности CRM (graceful degradation)
1. Создание записи **не блокируется** недоступностью CRM: запись создаётся локально, job уходит в очередь, пациент получает подтверждение.
2. В кабинете и в Filament запись показывается **сразу**, со значком «синхронизация: ожидает».
3. Уведомления (email/SMS) отправляются **независимо** от CRM (не зависят от `espo_synced_at`).
4. Если CRM недоступна > N минут — Filament-баннер «CRM недоступна, синхронизация приостановлена» (виджет, читающий метрику последнего успешного `EspoClient::ping()`).

### 6.6.6. Filament‑интеграция
1. На `AppointmentResource` (этап 5.1) добавить колонку `espo_sync_status` (badge + tooltip с `espo_sync_error`).
2. Кнопка action «Синхронизировать вручную» — диспатчит `SyncAppointmentToEspo::dispatchSync()` и показывает результат.
3. На `PatientResource` — аналогично + ссылка `https://crm.example.com/#Contact/view/{espo_contact_id}` (URL берётся из `Setting`).

### 6.6.7. Файлы (создать)
- `app/Contracts/Crm/CrmClient.php`
- `app/Services/Crm/{EspoClient.php, DryRunEspoClient.php, StatusMapper.php}`
- `app/Jobs/Crm/{SyncPatientToEspo.php, SyncAppointmentToEspo.php, SyncAppointmentCancellationToEspo.php, SyncAppointmentRescheduleToEspo.php, ApplyEspoChangesToLocal.php}`
- `app/Http/Controllers/Webhooks/EspoWebhookController.php`
- `app/Console/Commands/CrmPullAppointments.php`, `app/Console/Commands/CrmBackfillContacts.php`, `app/Console/Commands/CrmBackfillAppointments.php`
- `database/migrations/<ts>_add_espo_sync_columns_to_patients_appointments.php`
- `tests/Feature/Crm/{AppointmentSyncTest.php, ContactSyncTest.php, WebhookTest.php}` *(с фейк‑клиентом `Http::fake()`)*

### 6.6.8. Тесты (Pest 4)
1. `it('creates contact in CRM after registration')` — проверить, что job диспатчится и `espo_contact_id` записан (через `Bus::fake()` + `Http::fake()`).
2. `it('keeps booking visible to patient when CRM is down')` — Http::fake с 500‑ками, запись в кабинете должна быть видна, `espo_sync_status='failed'`.
3. `it('updates local appointment status from webhook')` — вызов `POST /webhooks/espo/appointment.updated` с валидной подписью, проверка маппинга статуса.
4. `it('rejects webhook with invalid signature')` — 401, БД не меняется.

---

## 7. Этап 7. Структура данных (итоговая, после доработок) — **с разметкой Laravel/CRM**

> 🟦 — поле «локально, источник правды Laravel». 🟧 — кэш или зеркало, источник правды Espo CRM. 🟨 — связующее поле (id внешней сущности).

### Таблицы (только новое/изменённое):
- **`patients`** *(уже есть)*:
  - 🟦 `id`, `phone` *(unique, E.164 без «+»)*,
  - 🟨 `espo_contact_id` *(nullable string)*,
  - 🟧 `last_name`, `first_name`, `middle_name?`, `birth_date`, `gender` — **кэш профиля** из CRM (см. A.3),
  - 🟦 `password` *(в MVP не используется, ставится `Str::random(64)`)*, `remember_token`,
  - 🟦 `email?` *(добавить миграцией; используется только для уведомлений; в CRM это поле тоже хранится, но Laravel может писать сам)*,
  - 🟦 `espo_synced_at? timestamp`, `espo_sync_status enum('pending','synced','failed') default 'pending'`, `espo_sync_error? text` *(добавить миграцией)*.
- **`patient_otps`** *(уже есть)*: 🟦 `phone`, `code`, `expires_at`, `consumed_at?`, `created_at`. **Дополнить:** `purpose enum('register','login') default 'login'`, `attempts unsignedTinyInteger default 0`. (Совпадает с legacy `phone_otps` из дампа.)
- **`personal_access_tokens`** *(Sanctum)*: 🟦 стандарт.
- **`doctor_schedules`** *(новая)*: 🟦 `id`, `doctor_id`, `weekday tinyint`, `start_time time`, `end_time time`, unique `(doctor_id,weekday)`. **Источник правды — Laravel** (расписание не нужно дублировать в CRM; CRM берёт уже забронированные слоты «как факт»).
- **`services`**, **`doctors`**, **`directions`**, **`specializations`** — 🟦 источник правды **Laravel** (это публичный каталог сайта, оператор клиники в CRM не управляет ассортиментом). Дополнить `services`: `+ duration_minutes unsignedSmallInteger default 30`. На фазе 2 при необходимости можно реплицировать из CRM, но не на MVP.
- **`appointments`** *(дополнить, остаётся «зеркалом»)*:
  - 🟦 `+ start_at datetime nullable index('doctor_id','start_at')`, `+ end_at datetime nullable`,
  - 🟦 `+ cancellation_reason text nullable`, `+ cancelled_at timestamp nullable`,
  - 🟦 `+ rescheduled_from_id` FK→`appointments.id` nullable,
  - 🟦 `+ last_reminded_at timestamp nullable`,
  - 🟦 `enum status` расширить: `new|processing|completed|cancelled|rescheduled` (статус для оператора в Filament и для пациента в кабинете),
  - 🟨 `espo_entity_id`, `espo_entity_type` *(уже есть)*, `+ espo_synced_at`, `+ espo_sync_status enum('pending','synced','failed','skipped') default 'pending'`, `+ espo_sync_error? text`.
  - **Семантика статусов в гибриде:** локальный `status` отражает «состояние заявки с точки зрения сайта» (`new` → `processing` → `completed`/`cancelled`/`rescheduled`), а CRM хранит свой статус Meeting/кастомной сущности. Webhook/pull‑job (см. этап CRM ниже) маппит CRM‑статус → локальный.
- **`settings`** *(уже есть)*: 🟦 добавить группы:
  - `booking`: `slot_step_minutes`, `min_lead_minutes`, `cancel_window_hours`, `notify_email_admin`;
  - `crm`: `base_url`, `api_user_id`, `webhook_secret`, `appointment_entity_type` (например, `Meeting` или `CMedicalAppointment`), `enabled bool`, `dry_run bool`.

### Связи (текстом):
- `Patient` 1—N `Appointment` (FK `appointments.patient_id`).
- `Doctor` 1—N `Appointment`, `Doctor` 1—N `DoctorSchedule`, `Doctor` N—M `Service` через `doctor_service`.
- `Service` 1—N `Appointment`, `Service` N—1 `Direction`.
- `Specialization` 1—N `Doctor`.
- `Appointment` self‑referential `rescheduled_from_id` (история переносов).

---

## 8. Этап 8. Контракты эндпоинтов (REST, минимальные сигнатуры)

> Все ответы — `application/json`, обёрнуты в `{ "data": ... }` для коллекций (как уже принято в `app/Http/Resources/Api/*`). Ошибки валидации — стандартный Laravel `{ "message", "errors": { field: [..] } }` со статусом 422.

| Метод | URI | Auth | Body / Query | 200 ответ |
|---|---|---|---|---|
| POST | `/api/v1/auth/register/request-otp` | — | `phone, last_name, first_name, middle_name?, birth_date(d.m.Y), gender(male\|female)` | `{ "message": "OTP отправлен", "expires_in": 900 }` |
| POST | `/api/v1/auth/register/verify` | — | `phone, otp` | `{ "token": "...", "patient": {...} }` |
| POST | `/api/v1/auth/login/request-otp` | — | `phone` | `{ "message": "OTP отправлен" }` |
| POST | `/api/v1/auth/login/verify` | — | `phone, otp` | `{ "token", "patient" }` |
| GET | `/api/v1/me` | bearer | — | `{ "data": Patient }` |
| PATCH | `/api/v1/me` | bearer | `last_name?, first_name?, middle_name?, birth_date?, gender?, email?` | `{ "data": Patient }` |
| POST | `/api/v1/me/logout` | bearer | — | `204` |
| GET | `/api/v1/booking/services` | — | `?doctor=slug` | `{ "data": Service[] }` |
| GET | `/api/v1/booking/doctors` | — | `?service=slug` | `{ "data": Doctor[] }` |
| GET | `/api/v1/booking/dates` | — | `?service=&doctor=&from=&to=` | `{ "data": ["YYYY-MM-DD", ...] }` |
| GET | `/api/v1/booking/slots` | — | `?service=&doctor=&date=YYYY-MM-DD` | `{ "data": ["2026-05-12T08:00:00+03:00", ...] }` |
| GET | `/api/v1/appointments` | bearer | `?status=upcoming\|past&page=` | пагинированная коллекция |
| POST | `/api/v1/appointments` | bearer | `service_id, doctor_id, start_at(ISO), note?` | `{ "data": Appointment }` |
| GET | `/api/v1/appointments/{id}` | bearer | — | `{ "data": Appointment }` |
| POST | `/api/v1/appointments/{id}/cancel` | bearer | `reason?` | `{ "data": Appointment }` |
| POST | `/api/v1/appointments/{id}/reschedule` | bearer | `start_at, doctor_id?` | `{ "data": Appointment }` |

**Schema объектов (поля, не код):**
- `Patient`: `id, phone, last_name, first_name, middle_name, birth_date, gender, email, created_at`.
- `Service`: `id, slug, name, direction:{id,slug,name}, price, price_label, duration_minutes`.
- `Doctor`: `id, slug, full_name, specialty, photo_url, experience_summary, services?`.
- `Appointment`: `id, status, service:{...}, doctor:{...}, start_at, end_at, note, cancellation_reason?, cancelled_at?, can_cancel(bool), can_reschedule(bool)`.

---

## 9. Этап 9. Фронтенд‑компоненты (Blade, Alpine + минимум JS)

> Не использовать SPA‑фреймворки. Обмен с API — `fetch`. Компонент календаря — `flatpickr` (CDN/npm).

| Компонент | Файл | Пропсы / атрибуты | Состояния |
|---|---|---|---|
| `<x-booking.progress>` | `resources/views/components/booking/progress.blade.php` | `:step (1..3)` | static |
| `<x-booking.service-list>` | `.../service-list.blade.php` | `:services`, `:selectedSlug` | empty / list |
| `<x-booking.doctor-list>` | `.../doctor-list.blade.php` | `:doctors`, `:serviceSlug` | empty / list |
| `<x-booking.calendar>` (Alpine) | `.../calendar.blade.php` | `data-slots-url`, `data-service`, `data-doctor` | loading / loaded / error |
| `<x-booking.slot-grid>` (Alpine) | `.../slot-grid.blade.php` | `data-slots`, `:selectedSlot` | loading / empty / has-slots |
| `<x-booking.confirm-card>` | `.../confirm-card.blade.php` | `:service, :doctor, :startAt, :note` | idle / submitting / success / error |
| `<x-cabinet.appointment-row>` | `resources/views/components/cabinet/appointment-row.blade.php` | `:appointment` | upcoming / past / cancelled |

Состояния визуально (Tailwind v4):
- loading: `bg-slate-100 animate-pulse h-10 rounded`;
- success: green badge `bg-emerald-50 text-emerald-700`;
- error: `bg-rose-50 text-rose-700 border border-rose-200`.

---

## 10. Этап 10. Тестирование

### 10.1. Pest 4 (бэкенд)
1. `tests/Feature/Booking/BookingFlowTest.php`:
   - `it('redirects unauthenticated patient to login from confirm step')`;
   - `it('creates appointment for service+doctor pair')` (с factory `Doctor`, `Service`, `DoctorSchedule`, `Patient`, `actingAs($patient,'patient')`);
   - `it('rejects booking when doctor does not provide service')`;
   - `it('rejects booking when slot already taken')` (создать предсуществующую запись);
   - `it('rejects booking in the past')`;
   - `it('respects min_lead_minutes setting')`.
2. `tests/Feature/Booking/SlotsApiTest.php` — `GET /api/v1/booking/slots` корректно вычитает уже занятые.
3. `tests/Feature/Cabinet/AppointmentsTest.php` — отмена/перенос, ограничение по окну отмены, запрет видеть чужие записи.
4. `tests/Feature/Auth/PatientOtpTest.php` (расширить существующий, если есть): `register-otp throttling`, `wrong code`, `expired code`, `register success`.
5. `tests/Unit/BookingServiceTest.php` — генерация слотов (учёт пересечений, обеденного перерыва нет в MVP, но выходных и `cancelled` записей — учитывается).
6. `tests/Browser/BookingFlowBrowserTest.php` (Pest 4 browser) — сквозной кейс: открыть `/services/{slug}`, кликнуть «Записаться», выбрать врача, дату, время, подтвердить, увидеть в кабинете.

### 10.2. Flutter (mobileApp)
1. `test/features/registration/step1_personal_test.dart` — валидация полей.
2. `test/features/registration/step2_phone_test.dart` — формат `+375 XX XXX-XX-XX`.
3. `test/features/booking/booking_repository_test.dart` — мок Dio (json fixtures), проверка маппинга в модели.
4. (smoke) `integration_test/booking_e2e.dart` — на эмуляторе, против локально поднятого Laravel: регистрация → запись → отмена.

### 10.3. CI
1. GitHub Actions workflow:
   - jobs `php-tests` (PHP 8.4 + MySQL 8 service): `composer install`, `php artisan test --compact`.
   - job `pint`: `vendor/bin/pint --test --format agent` *(локально использовать `vendor/bin/pint --dirty --format agent` согласно AGENTS.md)*.
   - job `flutter-tests`: `flutter test`.

---

## 11. Этап 11. Миграционная стратегия (последовательно, без даунтайма)

1. Добавить миграции в порядке: `create_doctor_schedules` → `add_duration_to_services` (`default(30)`) → `extend_appointments_for_booking` → `extend_patient_otps_purpose_attempts` → `add_espo_sync_columns_to_patients_appointments` → `personal_access_tokens` (Sanctum). Seeders: `DoctorScheduleSeeder` из дампа, `ServiceDurationSeeder` 30 мин по умолчанию.
2. Старые `Appointment` (две записи в дампе) имеют `start_at = null` — оставляем как `legacy/manual`. В Filament скрыть их из «Предстоящих» (фильтр `whereNotNull('start_at')`).
3. `BookingController` (старый) → редирект на `booking.start`.
4. **CRM**: при первом деплое включить `Setting::crm.enabled=false` и/или `dry_run=true`. После сетапа Espo и API key в `.env` — переключить через `SettingsPage` в Filament. Запустить ad‑hoc команду `php artisan crm:backfill-contacts` (написать как часть этапа 6.6) для создания CRM‑контактов под уже зарегистрированных пациентов и `php artisan crm:backfill-appointments` — для существующих заявок. Обе команды идемпотентны (используют `findOrCreateContact` и `espo_entity_id IS NULL`).
5. На проде: запустить миграции, прогнать seed `DoctorScheduleSeeder`, развернуть фронт (`npm run build`), запустить worker очередей (`php artisan queue:work --queue=notifications,crm,default --tries=5`). После прохождения CI — публикация.

---

## 12. Этап 12. Чек‑лист «готово»

- [ ] Все 7 точек «Записаться» (header×3, doctor‑card, doctor.show, service.show, home, JS‑генерируемые карточки) ведут на единый флоу.
- [ ] Анонимный пользователь может пройти флоу до экрана подтверждения, после логина/регистрации запись создаётся в один клик.
- [ ] Авторизованный пользователь может выбрать дату/время из реальных слотов.
- [ ] Слоты исключают пересечения и учитывают `doctor_schedules.weekday + start/end`.
- [ ] Запись сохраняется со `start_at`, `end_at`, `service_id`, `doctor_id`, `patient_id`.
- [ ] Email‑уведомление отправляется (в локале — в `storage/logs/laravel.log` через `MAIL_MAILER=log`).
- [ ] Личный кабинет показывает «Предстоящие» / «Прошедшие», поддерживает `cancel` и `reschedule` с серверной валидацией.
- [ ] Мобильное приложение: 5‑шаговый мастер регистрации работает против реального API; авторизация по OTP сохраняет Bearer‑токен; экран «Мои записи» показывает реальные данные; флоу записи доступен из карточки услуги/врача.
- [ ] Filament: `AppointmentResource` (фильтры, отмена, перенос, badge `espo_sync_status`), `PatientResource` (read‑only, ссылка в CRM по `espo_contact_id`), `DoctorScheduleRelationManager` на докторе.
- [ ] CRM (Espo): после `book()` создаётся `Contact` (если нужно) и `Appointment` в CRM, `espo_contact_id`/`espo_entity_id` записываются обратно в Laravel; webhook на изменение статуса в CRM обновляет локальное зеркало; при выключенной CRM (`Setting::crm.dry_run=true`) — `DryRunEspoClient` логирует и не блокирует флоу.
- [ ] Pest: `php artisan test --compact` зелёный; `vendor/bin/pint --format agent` без правок.
- [ ] Flutter: `flutter analyze` без ошибок, `flutter test` зелёный.

---

## 13. Конкретные файлы, которые создаются/правятся

**Создать:**
- `database/migrations/<ts>_create_doctor_schedules_table.php`
- `database/migrations/<ts>_add_duration_to_services_table.php`
- `database/migrations/<ts>_extend_appointments_for_booking.php`
- `database/migrations/<ts>_extend_patient_otps_purpose_attempts.php`
- `database/seeders/DoctorScheduleSeeder.php`, `ServiceDurationSeeder.php`
- `app/Models/DoctorSchedule.php`
- `app/Enums/Weekday.php`, `app/Enums/AppointmentStatus.php`
- `app/Services/BookingService.php`
- `app/Exceptions/BookingException.php`
- `app/Notifications/AppointmentCreatedNotification.php`, `AppointmentCancelledNotification.php`, `AppointmentRescheduledNotification.php`
- `app/Contracts/SmsSender.php`, `app/Services/Sms/LogSmsSender.php`
- `app/Contracts/Crm/CrmClient.php`
- `app/Services/Crm/{EspoClient.php, DryRunEspoClient.php, StatusMapper.php}`
- `app/Jobs/Crm/{SyncPatientToEspo.php, SyncAppointmentToEspo.php, SyncAppointmentCancellationToEspo.php, SyncAppointmentRescheduleToEspo.php, ApplyEspoChangesToLocal.php}`
- `app/Http/Controllers/Webhooks/EspoWebhookController.php`
- `app/Console/Commands/{CrmPullAppointments.php, CrmBackfillContacts.php, CrmBackfillAppointments.php}`
- `database/migrations/<ts>_add_espo_sync_columns_to_patients_appointments.php`
- `app/Http/Controllers/BookingFlowController.php`
- `app/Http/Controllers/Cabinet/CabinetController.php`
- `app/Http/Controllers/Api/V1/Auth/{RegisterOtpController.php, LoginOtpController.php}`
- `app/Http/Controllers/Api/V1/PatientApiController.php`
- `app/Http/Controllers/Api/V1/AppointmentApiController.php`
- `app/Http/Controllers/Api/V1/Booking/{ServicesController.php, DoctorsController.php, SlotsController.php}`
- `app/Http/Resources/Api/{PatientResource.php, AppointmentResource.php, ServiceBookingResource.php, DoctorBookingResource.php}`
- `app/Http/Requests/Booking/{StoreBookingRequest.php, CancelRequest.php, RescheduleRequest.php, PickSlotRequest.php}`
- `app/Http/Requests/Api/Auth/{RegisterRequestOtpRequest.php, LoginRequestOtpRequest.php, VerifyOtpRequest.php}`
- `app/Filament/Resources/Appointments/AppointmentResource.php` (+ Pages/Tables/Schemas)
- `app/Filament/Resources/Patients/PatientResource.php` (read‑only)
- `app/Filament/Resources/Doctors/RelationManagers/SchedulesRelationManager.php`
- `resources/views/booking/{start,service,doctor,slot}.blade.php`
- `resources/views/cabinet/{dashboard,appointments,show,profile}.blade.php`
- `resources/views/partials/cabinet-nav.blade.php`
- `resources/views/components/booking/*.blade.php`, `resources/views/components/cabinet/*.blade.php`
- `tests/Unit/BookingServiceTest.php`, `tests/Feature/Booking/*`, `tests/Feature/Cabinet/*`, `tests/Feature/Crm/*`, `tests/Browser/BookingFlowBrowserTest.php`
- `mobileApp/lib/core/network/dio_client.dart`, `mobileApp/lib/core/auth/auth_repository.dart`
- `mobileApp/lib/features/booking/{booking_repository.dart, booking_providers.dart, screens/*.dart}`
- `mobileApp/lib/features/dashboard/appointments/appointments_repository.dart` (+ обновить `appointments_screen.dart`)
- `mobileApp/lib/features/dashboard/profile/profile_repository.dart` (+ обновить `profile_screen.dart`)

**Править:**
- `routes/web.php` — новая группа `booking.*` и `cabinet.*`; старый `Route::get('/booking', BookingController@index)` → редирект.
- `routes/api.php` — добавить блок `auth/*`, `booking/*`, `me`, `appointments/*` + публичный POST `/webhooks/espo/{event}` (если решено держать в `routes/api.php` без CSRF; иначе — в `routes/web.php` с явным `withoutMiddleware([VerifyCsrfToken::class])`).
- `routes/console.php` — `Schedule::command('crm:pull-appointments')->everyFiveMinutes()` и `Schedule::command('booking:remind')->everyMinute()`.
- `bootstrap/app.php` — *ничего не менять* (middleware-alias `patient.auth` уже настроен, Sanctum подключится автоматически).
- `app/Providers/AppServiceProvider.php` — биндинг `CrmClient::class`, биндинг `SmsSender::class`.
- `app/Models/{Doctor,Service,Patient,Appointment}.php` — связи, casts, флаги синхронизации.
- `app/Http/Controllers/Patient/PatientRegisterController.php`, `app/Http/Controllers/Patient/PatientLoginController.php` — после успешной операции диспатчить `SyncPatientToEspo`.
- `resources/views/partials/{header,doctor-card}.blade.php`, `resources/views/{home,doctors/show,services/show}.blade.php` — поправить ссылки «Записаться».
- `public/scripts/script.js` (lines 281–286) и `public/scripts/script-index-doctors.js` (line 38) — поправить URL «Записаться».
- `mobileApp/lib/features/registration/steps/{step2_phone.dart, step3_otp.dart}` и `auth_controller.dart` — реальные сетевые вызовы.
- `mobileApp/lib/features/login/{login_phone.dart, login_otp.dart}` — реальные вызовы.

---

## 14. Открытые вопросы для уточнения у заказчика (НЕ блокирующие план)

1. SMS‑провайдер: какой? (BeSMS / Rocket SMS / собственный шлюз). Без него — log‑заглушка.
2. Длительность услуг: единая 30 минут или разная? (поле уже добавляется, заполнение — отдельная задача оператора).
3. Окно отмены `cancel_window_hours` (по умолчанию 3) — подтвердить.
4. Нужен ли «обеденный перерыв» в `doctor_schedules` (одна запись с двумя интервалами)? — для MVP считаем «нет», добавим интервал‑записи в фазе 2 (ENUM `weekday` + `slot_index`).
5. Видны ли все услуги в API сайта анониму — или только активные (`status='active'`)? — план предполагает только активные.
6. **CRM (Espo)**:
   - Какая сущность в Espo соответствует записи к врачу: стандартная `Meeting` или кастомная (`CMedicalAppointment` / иная)?
   - Есть ли в Espo сущность «Услуга» и «Врач», на которые нужно ссылаться? Или достаточно текстового описания + `notes` с услугой/врачом?
   - Как идентифицировать пациента в CRM: по `phoneNumber` или по кастомному полю `siteAccountId = patients.id`?
   - Стратегия переноса: создавать в CRM **новую** запись с пометкой `rescheduledFromId` или **обновлять** старую с новым `dateStart`? (Рекомендация плана — создавать новую и менять статус старой на `Canceled` через причину `Rescheduled` — сохраняется аудит.)
   - Поддерживает ли CRM webhook’и наружу (Workflow → external call) или нужен только pull‑sync через cron?
   - URL CRM, тестовый и боевой, для Filament‑ссылок на контакт.
7. **Безопасность синхронизации**: где хранить `ESPO_API_KEY` — в `.env` боевого сервера (рекомендация плана) или в Vault/SSM? Разделять ли ключи `read`/`write`?
8. Нужно ли пациенту видеть в кабинете «техническое» сообщение «синхронизация с CRM в процессе»? Рекомендация плана — **не показывать** (внутренняя кухня), статус только в Filament.
