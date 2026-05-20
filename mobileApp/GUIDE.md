# Полное руководство по проекту flutter_application_diplom

## 1. Введение

Этот документ подробно разбирает мобильное приложение из папки `mobileApp`. Проект называется `flutter_application_diplom`, а в пользовательском интерфейсе приложение отображается как медицинское приложение клиники «Маяк Здоровья».

Главная мысль: это Flutter-приложение для пациента клиники. Пользователь проходит онбординг, регистрируется или входит по SMS-коду, настраивает PIN и биометрию, затем попадает в личный кабинет с врачами, услугами, записями, блогом, акциями и профилем.

Проект написан не на HTML/CSS/JS как классический сайт, а на **Flutter + Dart**. Flutter сам рисует интерфейс на Android, iOS, Web, Linux, macOS и Windows. HTML здесь есть только в web-обёртке `web/index.html`, которая запускает Flutter-приложение в браузере.

## 2. Короткая карта технологий

| Технология | Где используется | Зачем нужна |
|---|---|---|
| Flutter | весь `lib/` | UI, экраны, анимации, навигация, работа под разные платформы |
| Dart | весь код приложения | язык программирования Flutter |
| Riverpod | `flutter_riverpod` | состояние приложения, зависимости, загрузка данных |
| GoRouter | `go_router` | маршруты `/splash`, `/dashboard`, `/booking`, `/blog/:slug` и т.д. |
| Dio | `core/network/dio_client.dart` | HTTP-запросы к Laravel API |
| SharedPreferences | `core/storage/storage_service.dart` | простые локальные данные: имя, телефон, PIN, флаг регистрации |
| Flutter Secure Storage | `core/storage/secure_storage.dart` | защищённое хранение access/refresh token |
| Firebase Messaging | `core/notifications/*` | push-уведомления |
| Local Notifications | `core/notifications/local_notifications_service.dart` | показ уведомлений, когда приложение открыто |
| local_auth | `core/biometric/biometric_auth_service.dart` | Face ID / Touch ID / отпечаток |
| flutter_html | `dashboard/blog`, `promotions` | отображение HTML-контента статей и акций |
| google_fonts | UI-файлы | шрифт Inter |
| flutter_animate | экраны | плавные появления, слайды, анимации |

### 2.1. Как думать об этом проекте новичку

Удобно представить приложение как несколько слоёв. Каждый слой отвечает только за свою часть работы. Это очень важно: если всё смешать в одном файле, проект быстро станет нечитаемым.

```text
Пользователь нажимает кнопку
→ Widget получает событие onTap / onChanged
→ Widget вызывает controller или notifier
→ Controller меняет state или вызывает repository
→ Repository делает HTTP-запрос через Dio
→ Dio автоматически добавляет token и обрабатывает ошибки
→ Backend возвращает JSON
→ Model.fromJson превращает JSON в Dart-объект
→ Provider уведомляет экран
→ Flutter перерисовывает только нужную часть UI
```

Главное правило чтения Flutter-кода: **экран почти всегда описывает не “как нарисовать пиксели”, а “из каких виджетов состоит интерфейс и что делать при событиях”**. Например, кнопка не сама знает, как регистрировать пользователя. Она вызывает метод controller/repository, а дальше работу выполняют другие слои.

### 2.2. Почему тут Riverpod, а не просто setState

`setState` хорошо подходит для маленькой локальной логики внутри одного экрана: например, показать ошибку под полем или обновить введённый текст. Но в этом приложении много состояния, которое нужно разным экранам:

- авторизован пользователь или нет;
- какой активен tab в dashboard;
- какие данные профиля сохранены;
- какие записи нужно обновить после создания новой записи;
- какой врач или категория должны открыться после перехода с главной.

Для такого состояния используется Riverpod. Он позволяет вынести данные из конкретного виджета в provider. Тогда один экран может изменить provider, а другой экран автоматически увидит новое значение.

Пример из dashboard:

```dart
final currentIndex = ref.watch(dashboardTabIndexProvider);

ref.read(dashboardTabIndexProvider.notifier).state = 3;
```

Первая строка читает текущую вкладку. Вторая строка меняет вкладку на «Записи». Из-за `ref.watch` UI перерисуется.

### 2.3. Почему тут GoRouter, а не Navigator везде

В проекте есть два вида навигации:

1. **Глобальная навигация** — `/splash`, `/auth`, `/dashboard`, `/booking`, `/blog/:slug`. Её ведёт GoRouter.
2. **Локальная навигация внутри сценария** — например, после OTP открыть `PinSetupScreen` через `Navigator.of(context).push`.

GoRouter особенно важен из-за `redirect`: приложение может централизованно решить, можно ли пользователю открыть экран. Без этого пришлось бы в каждом экране вручную проверять авторизацию.

### 2.4. Почему есть repository-слой

Repository — это “переводчик” между UI и backend. UI мыслит словами “получить врачей”, “создать запись”, “обновить профиль”. Backend мыслит URL-ами, JSON-полями и HTTP-методами. Repository прячет детали backend от экрана.

Плохой вариант был бы таким: экран содержит `dio.get('/doctors')`, вручную парсит JSON, вручную показывает ошибки. В этом проекте лучше:

```text
DoctorsScreen
→ doctorsListProvider
→ DoctorsRepository.fetchList()
→ Dio GET /doctors
→ DoctorModel.fromApiList()
```

Так легче поддерживать проект: если backend поменяет endpoint, чаще всего достаточно исправить repository, а не весь UI.

## 3. Как приложение работает в целом

### 3.1. Запуск приложения

1. Операционная система запускает нативный контейнер:
   - Android: `MainActivity.kt`;
   - iOS: `AppDelegate.swift`;
   - Web: `web/index.html`;
   - Desktop: CMake/runner-файлы.
2. Flutter передаёт управление в `lib/main.dart`.
3. `main.dart` вызывает `WidgetsFlutterBinding.ensureInitialized()`, чтобы Flutter успел подготовить движок до асинхронных операций.
4. Если включён Firebase (`USE_FIREBASE=true`), приложение пытается инициализировать Firebase и регистрирует фоновый обработчик push-сообщений.
5. Запускается `runApp(const ProviderScope(child: App()))`.
6. `ProviderScope` включает Riverpod для всего приложения.
7. `App` создаёт `MaterialApp.router`, подключает русскую локализацию, тему, роутер и сервисы уведомлений.

Ключевой код:

```dart
Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  if (kFirebaseEnabled) {
    await Firebase.initializeApp();
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);
  }

  runApp(const ProviderScope(child: App()));
}
```

### 3.2. Главная идея состояния авторизации

В приложении есть enum `AuthStatus`:

```dart
enum AuthStatus {
  unknown,
  unregistered,
  registeredLoggedOut,
  authenticated,
}
```

Он отвечает на вопрос: «что сейчас известно о пользователе?»

- `unknown` — приложение только стартовало и ещё читает локальное хранилище.
- `unregistered` — локально нет признака регистрации.
- `registeredLoggedOut` — пользователь зарегистрирован, но ещё не ввёл PIN / Face ID в этой сессии.
- `authenticated` — пользователь полностью вошёл и может видеть личный кабинет.

Именно этот статус управляет маршрутизацией. Например, если пользователь не вошёл и пытается открыть `/dashboard`, роутер отправит его на `/login/pin` или `/login/faceid`.

### 3.3. Главный пользовательский сценарий

Типичный путь нового пользователя:

1. Splash-экран.
2. Онбординг.
3. Экран выбора: войти или зарегистрироваться.
4. Регистрация:
   - личные данные;
   - телефон;
   - SMS OTP;
   - PIN;
   - Face ID / биометрия.
5. Dashboard.
6. Просмотр врачей, услуг, записей, блога, акций, профиля.
7. Создание записи на приём через booking wizard.

Типичный путь уже зарегистрированного пользователя:

1. Splash-экран.
2. Bootstrap читает локальные данные.
3. Если включена биометрия — `/login/faceid`, иначе `/login/pin`.
4. После успешного подтверждения — `/dashboard`.

## 3.4. Подробный разбор жизненного цикла одного экрана

Разберём на примере списка врачей. Это хороший пример, потому что в нём есть загрузка API, состояние loading/error/data, фильтры и переход в детальную карточку.

1. Пользователь находится на `/dashboard`.
2. `DashboardScreen` показывает `IndexedStack`, где вкладка врачей — это `DoctorsScreen`.
3. В `DoctorsScreen` выполняется:

```dart
final async = ref.watch(doctorsListProvider);
```

4. `doctorsListProvider` запускает `DoctorsRepository.fetchList()`.
5. Repository делает `GET /doctors`.
6. Пока запрос идёт, `async.when(loading: ...)` показывает `CircularProgressIndicator`.
7. Если запрос упал, `async.when(error: ...)` показывает экран ошибки и кнопку «Повторить».
8. Если запрос успешен, JSON превращается в список `DoctorModel`.
9. UI применяет локальные фильтры: поиск, специальность, категория, возраст.
10. Отфильтрованные врачи отображаются карточками.
11. При тапе на врача provider `doctorsSubNavProvider` получает selected slug.
12. `AnimatedSwitcher` меняет список на detail-экран.

Важно: загрузка данных и локальный поиск — разные уровни состояния. Данные с API живут в `doctorsListProvider`, а текст поиска `_query` живёт внутри `DoctorsScreen`, потому что он нужен только этому экрану.

## 3.5. Как Flutter перерисовывает интерфейс

Во Flutter вы не говорите “поставь этот текст в этот div”, как в DOM. Вы описываете дерево виджетов для текущего состояния. Когда состояние меняется, Flutter снова вызывает `build`, сравнивает новое дерево со старым и обновляет экран.

Простой пример из логики PIN:

```dart
setState(() {
  _current = next;
  _status = _Status.idle;
});
```

После `setState` Flutter снова вызывает `build`. Точки PIN, текст ошибки, цвет состояния и кнопки пересчитываются из новых значений `_current` и `_status`.

В Riverpod похожий принцип, но состояние меняется не через `setState`, а через provider/notifier:

```dart
state = state.copyWith(step: 2, phone: phone);
```

Все виджеты, которые делают `ref.watch(loginFlowControllerProvider)`, увидят новый `step` и перерисуются.

## 3.6. Как обрабатываются ошибки

Ошибки в проекте идут по цепочке:

```text
Backend возвращает ошибку
→ Dio получает DioException
→ _ErrorInterceptor пытается достать message
→ создаётся ApiException / UnauthorizedException
→ Repository пробрасывает исключение выше
→ Screen ловит DioException
→ Screen показывает SnackBar или текст ошибки
```

Например, экран входа по телефону отдельно обрабатывает статус `422`, потому что backend может вернуть валидационную ошибку “Номер не найден”. Это не системная ошибка приложения, а нормальная бизнес-ситуация: пользователь ввёл номер, которого нет в базе.

## 3.7. Как работает адаптивность

Проект в основном ориентирован на мобильные экраны. Адаптивность достигается не через CSS media queries, а Flutter-инструментами:

- `SafeArea` — не залезать под вырезы, статус-бар и системные панели;
- `SingleChildScrollView` / `CustomScrollView` — контент можно прокрутить на маленьком экране;
- `Expanded` / `Flexible` — элементы занимают доступное место;
- `MediaQuery.of(context).padding.bottom` — учёт нижней системной области;
- `LayoutBuilder` иногда используют, когда нужно знать доступную ширину/высоту.

В dashboard нижняя навигация обёрнута в `SafeArea(top: false)`, чтобы кнопки не конфликтовали с системной навигацией устройства.

## 4. Очень подробные разборы кода: читаем проект “по косточкам”

В этом разделе специально много кода. Цель — не просто показать фрагменты, а объяснить, **что именно делает каждая часть и как она связана с остальным приложением**.

### 4.1. Точка входа: `main.dart`

Упрощённо `main.dart` выглядит так:

```dart
Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  if (kFirebaseEnabled) {
    try {
      await Firebase.initializeApp();
      FirebaseMessaging.onBackgroundMessage(
        firebaseMessagingBackgroundHandler,
      );
    } catch (e, st) {
      if (kDebugMode) {
        debugPrint('[main] Firebase init failed: $e\n$st');
      }
    }
  }

  runApp(const ProviderScope(child: App()));
}
```

Разбираем по шагам:

1. `Future<void> main() async` — приложение стартует с функции `main`. Она асинхронная, потому что Firebase и другие сервисы могут требовать `await`.
2. `WidgetsFlutterBinding.ensureInitialized()` — говорит Flutter: “подготовь системные связи, потому что сейчас мы будем делать async-операции до `runApp`”. Без этого некоторые плагины могут работать нестабильно.
3. `if (kFirebaseEnabled)` — Firebase можно включать/выключать через dart-define. Это полезно, если локально Firebase не настроен.
4. `Firebase.initializeApp()` — подключает Firebase SDK.
5. `FirebaseMessaging.onBackgroundMessage(...)` — регистрирует функцию, которая будет вызываться, если push пришёл в фоне.
6. `runApp(...)` — реальный старт UI.
7. `ProviderScope` — корневой контейнер Riverpod. Без него `ref.watch(...)` и providers в приложении не работали бы.
8. `App()` — корневой виджет из `lib/app/app.dart`.

Мысленная модель:

```text
ОС открыла приложение
→ main()
→ подготовка Flutter
→ подготовка Firebase
→ включение Riverpod
→ App
→ MaterialApp.router
→ router решает первый экран
```

### 4.2. Корневой виджет: `App`

Фрагмент:

```dart
class App extends ConsumerWidget {
  const App({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    ref.watch(notificationControllerProvider);
    ref.watch(pushTokenSyncProvider);

    ref.listen<AuthState>(authControllerProvider, (previous, current) {
      if (current.status == AuthStatus.authenticated &&
          previous?.status != AuthStatus.authenticated) {
        ref.read(pushTokenSyncProvider).resendAfterLogin();
      }
    });

    final router = ref.watch(appRouterProvider);

    return MaterialApp.router(
      title: 'Маяк Здоровья',
      routerConfig: router,
      locale: const Locale('ru', 'RU'),
      theme: ThemeData(...),
    );
  }
}
```

Что тут важно:

- `ConsumerWidget` — это Flutter widget, который умеет читать Riverpod через `ref`.
- `ref.watch(notificationControllerProvider)` не просто “читает значение”. Он запускает provider, а provider внутри настраивает уведомления.
- `ref.watch(pushTokenSyncProvider)` запускает синхронизацию FCM-токена.
- `ref.listen<AuthState>(...)` — подписка на изменение авторизации. Это не рисует UI, а выполняет побочный эффект.
- `previous?.status != AuthStatus.authenticated` защищает от повторного вызова при каждом rebuild.
- `MaterialApp.router` говорит Flutter: “навигацией управляет router, а не ручной список routes”.

Почему это сделано в `App`, а не на каждом экране? Потому что уведомления, router и auth listener — глобальные механизмы. Они должны жить один раз на всё приложение.

### 4.3. Router: как приложение решает, куда пустить пользователя

Ключевой фрагмент из `router.dart`:

```dart
redirect: (context, state) {
  final auth = ref.read(authControllerProvider);
  final status = auth.status;
  final loc = state.matchedLocation;

  if (status == AuthStatus.unknown) {
    return loc == '/splash' ? null : '/splash';
  }

  const publicRoutes = [
    '/splash', '/onboarding', '/auth', '/register',
    '/login', '/login/pin', '/login/faceid'
  ];
  final isPublic = publicRoutes.any((r) => loc.startsWith(r));

  if (status == AuthStatus.unregistered) {
    return isPublic ? null : '/auth';
  }

  if (status == AuthStatus.registeredLoggedOut) {
    if (loc == '/login' ||
        loc == '/login/pin' ||
        loc == '/login/faceid' ||
        loc.startsWith('/register')) {
      return null;
    }
    return auth.faceIdEnabled ? '/login/faceid' : '/login/pin';
  }

  if (status == AuthStatus.authenticated && isPublic) {
    return '/dashboard';
  }

  return null;
}
```

Разбор логики:

- `loc` — текущий путь, например `/dashboard`.
- `return null` означает: “ничего не менять, путь разрешён”.
- `return '/auth'` означает: “перенаправить пользователя”.
- Когда status `unknown`, приложение ещё не знает, зарегистрирован пользователь или нет, поэтому держит его на splash.
- Когда `unregistered`, приватные экраны запрещены.
- Когда `registeredLoggedOut`, пользователь известен, но должен подтвердить вход через PIN/Face ID.
- Когда `authenticated`, публичные auth-экраны уже не нужны, поэтому пользователь отправляется в dashboard.

Новичку важно понять: **не экраны сами себя защищают**, а router централизованно контролирует доступ.

### 4.4. Dio client: почему каждый запрос автоматически получает token

Фрагмент:

```dart
final dioProvider = Provider<Dio>((ref) {
  final secure = ref.watch(secureStorageProvider);

  final dio = Dio(
    BaseOptions(
      baseUrl: resolvedApiBaseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 20),
      sendTimeout: const Duration(seconds: 20),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ),
  );

  dio.interceptors.add(_AuthInterceptor(secure));
  dio.interceptors.add(_ErrorInterceptor());

  return dio;
});
```

Здесь создаётся один настроенный HTTP-клиент. Все repositories берут именно его, поэтому все запросы имеют одинаковые timeout, baseUrl и headers.

Auth interceptor:

```dart
class _AuthInterceptor extends Interceptor {
  final SecureStorage _storage;

  _AuthInterceptor(this._storage);

  @override
  Future<void> onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final token = await _storage.readAccessToken();
    if (token != null && token.isNotEmpty) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }
}
```

Пошагово:

1. Любой repository вызывает `_dio.get(...)` или `_dio.post(...)`.
2. Перед отправкой Dio вызывает `onRequest`.
3. Interceptor читает access token из secure storage.
4. Если token есть, добавляет HTTP-заголовок `Authorization`.
5. `handler.next(options)` говорит Dio: “продолжай запрос”.

Итог: `BookingRepository`, `DoctorsRepository`, `BlogRepository` не думают об авторизации. Они просто делают запросы, а token добавляется автоматически.

### 4.5. Error interceptor: как техническая ошибка превращается в понятное сообщение

Фрагмент:

```dart
void onError(DioException err, ErrorInterceptorHandler handler) {
  final status = err.response?.statusCode;

  if (status == 401) {
    handler.reject(DioException(
      requestOptions: err.requestOptions,
      response: err.response,
      error: const UnauthorizedException(),
      type: err.type,
    ));
    return;
  }

  final message = _extractMessage(err);

  handler.reject(DioException(
    requestOptions: err.requestOptions,
    response: err.response,
    error: ApiException(message, statusCode: status, raw: err),
    type: err.type,
  ));
}
```

Почему это полезно:

- backend может вернуть JSON `{ "message": "Телефон уже занят" }`;
- Dio сам по себе дал бы технический `DioException`;
- interceptor достаёт `message` и кладёт его в `ApiException`;
- экран может показать пользователю понятный текст.

Метод `_extractMessage` ищет сообщение в двух популярных местах:

```dart
if (data['message'] is String) {
  return data['message'] as String;
}
final errors = data['errors'];
if (errors is Map && errors.isNotEmpty) {
  final first = errors.values.first;
  if (first is List && first.isNotEmpty && first.first is String) {
    return first.first as String;
  }
}
```

Это типично для Laravel API: обычное сообщение лежит в `message`, а ошибки валидации — в `errors`.

### 4.6. AuthController: bootstrap — почему приложение помнит пользователя

Фрагмент:

```dart
Future<void> bootstrap() async {
  final isRegistered = await _storage.isRegistered();
  if (!isRegistered) {
    state = state.copyWith(status: AuthStatus.unregistered);
    return;
  }

  final firstName = await _storage.getFirstName();
  final lastName = await _storage.getLastName();
  final phone = await _storage.getPhone();
  final faceId = await _storage.isFaceIdEnabled();

  final nextStatus = state.status == AuthStatus.authenticated
      ? AuthStatus.authenticated
      : AuthStatus.registeredLoggedOut;

  state = AuthState(
    status: nextStatus,
    firstName: firstName,
    lastName: lastName,
    phone: phone,
    faceIdEnabled: faceId,
  );

  final token = await _secureStorage.readAccessToken();
  if (token != null && token.isNotEmpty) {
    try {
      final p = await _authRepository.me();
      await _applyPatientData(p, keepPhone: phone);
    } on DioException {
      // Сеть / протухший токен — остаёмся на локальных данных
    }
  }
}
```

Что здесь происходит:

1. Сначала читается `isRegistered`. Это быстрый локальный ответ: есть ли сохранённый профиль.
2. Если регистрации нет — статус `unregistered`, router отправит на auth/onboarding.
3. Если регистрация есть — читаются имя, фамилия, телефон и Face ID.
4. Статус становится `registeredLoggedOut`, потому что приложение помнит пользователя, но ещё не разблокировано.
5. Потом читается token из secure storage.
6. Если token есть, приложение пробует обновить профиль через `/me`.
7. Если сеть недоступна или token протух, приложение не падает, а остаётся на локальных данных.

Очень важная идея: bootstrap даёт “мягкий старт”. Пользователь может хотя бы попасть на PIN/Face ID, даже если backend временно недоступен.

### 4.7. AuthController: завершение регистрации

Фрагмент:

```dart
Future<void> completeRegistrationWithApiToken({
  required String accessToken,
  required String firstName,
  required String lastName,
  required String phone,
  String middleName = '',
  String birthDate = '',
  String gender = '',
}) async {
  await _secureStorage.saveTokens(accessToken: accessToken);
  await _storage.saveRegistration(
    firstName: firstName,
    lastName: lastName,
    middleName: middleName,
    phone: phone,
    birthDate: birthDate,
    gender: gender,
  );
  state = state.copyWith(
    status: AuthStatus.registeredLoggedOut,
    firstName: firstName,
    lastName: lastName,
    middleName: middleName,
    phone: phone,
    birthDate: birthDate,
    gender: gender,
  );
}
```

Почему status не сразу `authenticated`? Потому что после SMS-кода пользователь ещё должен создать PIN и пройти/пропустить Face ID. Поэтому приложение говорит: “регистрация подтверждена, но локальная защита ещё не завершена”.

Разделение хранилищ:

```text
accessToken → SecureStorage
ФИО/телефон/дата/пол → SharedPreferences через StorageService
текущее состояние UI → AuthState в памяти
```

### 4.8. Riverpod provider на простом примере dashboard tab

Файл `dashboard_tab_provider.dart` очень маленький, но важный:

```dart
final dashboardTabIndexProvider = StateProvider<int>((ref) => 0);
```

Это означает:

- есть глобальное число `int`;
- начальное значение `0`;
- `0` — главная вкладка;
- другие экраны могут это число читать и менять.

Чтение:

```dart
final currentIndex = ref.watch(dashboardTabIndexProvider);
```

Изменение:

```dart
ref.read(dashboardTabIndexProvider.notifier).state = 3;
```

Почему не обычная переменная? Потому что обычная переменная не уведомит Flutter, что UI нужно перерисовать. `StateProvider` уведомляет всех подписчиков.

### 4.9. FutureProvider.family на примере блога

Фрагмент:

```dart
final articleDetailProvider =
    FutureProvider.family<ArticleModel, String>((ref, slug) async {
  return ref.watch(blogRepositoryProvider).fetchDetail(slug);
});
```

Разбор:

- `FutureProvider` — provider для асинхронной операции.
- `.family` означает: provider зависит от параметра.
- `ArticleModel` — что вернётся после загрузки.
- `String` — тип параметра, здесь `slug`.
- `fetchDetail(slug)` — API-запрос за статьёй.

Использование на экране статьи примерно такое:

```dart
final articleAsync = ref.watch(articleDetailProvider(slug));

return articleAsync.when(
  loading: () => const CircularProgressIndicator(),
  error: (error, stack) => Text('Ошибка: $error'),
  data: (article) => Text(article.title),
);
```

`AsyncValue.when` заставляет разработчика явно обработать три состояния: загрузка, ошибка, данные. Это снижает шанс забыть loading или error UI.

### 4.10. BookingWizardNotifier: как строятся шаги записи

Фрагмент:

```dart
static List<BookingStep> _computeSteps({
  required bool hasDoctor,
  required bool hasService,
}) {
  final steps = <BookingStep>[];
  if (!hasService) steps.add(BookingStep.service);
  if (!hasDoctor) steps.add(BookingStep.doctor);
  steps.add(BookingStep.date);
  steps.add(BookingStep.slot);
  steps.add(BookingStep.confirm);
  return steps;
}
```

Эта функция делает wizard гибким:

- если пользователь пришёл просто на `/booking`, нужно выбрать услугу и врача;
- если пришёл с карточки услуги `/booking?service=...`, услуга уже известна, шаг услуги можно пропустить;
- если пришёл с врача и услуги, можно сразу выбирать дату;
- если это перенос записи, часть данных тоже может быть заранее известна.

Примеры:

```text
/booking
→ service → doctor → date → slot → confirm

/booking?service=uzi
→ doctor → date → slot → confirm

/booking?doctor=ivanov&service=uzi
→ date → slot → confirm
```

### 4.11. BookingWizardNotifier: почему при выборе сбрасываются следующие шаги

Фрагмент:

```dart
void selectService(BookingServiceItem item) {
  state = state.copyWith(
    service: item,
    clearDoctor: true,
    clearDate: true,
    clearSlot: true,
    clearError: true,
    stepIndex: state.stepIndex + 1,
  );
}
```

Представьте пользователь выбрал:

```text
Услуга: УЗИ
Врач: Иванов
Дата: 20 мая
Слот: 12:00
```

Потом он вернулся назад и поменял услугу на “Кардиология”. Старый врач Иванов может не оказывать эту услугу, старая дата может быть недоступна, старый слот может быть занят. Поэтому код очищает всё, что зависит от услуги:

```text
service меняется
→ doctor очищается
→ date очищается
→ slot очищается
```

То же самое происходит при выборе врача: дата и слот очищаются, потому что расписание у другого врача другое.

### 4.12. Booking submit: создание и перенос записи в одном месте

Фрагмент:

```dart
Future<bool> submit(String? note) async {
  var service = state.service;
  var doctor = state.doctor;
  final slot = state.slot;

  if (service == null || doctor == null || slot == null) {
    state = state.copyWith(error: 'Заполните все шаги перед отправкой');
    return false;
  }

  state = state.copyWith(isSubmitting: true, clearError: true);

  try {
    if (arg.isReschedule) {
      await ref
          .read(appointmentsRepositoryProvider)
          .rescheduleAppointment(arg.appointmentId!, slot);
    } else {
      await ref.read(bookingRepositoryProvider).createAppointment(
            serviceId: service.id,
            doctorId: doctor.id,
            startAt: slot,
            note: note,
          );
    }

    ref.invalidate(upcomingAppointmentsProvider);
    ref.invalidate(pastAppointmentsProvider);

    state = state.copyWith(isSubmitting: false, done: true);
    return true;
  } catch (e) {
    final msg = _parseError(e);
    state = state.copyWith(isSubmitting: false, error: msg);
    return false;
  }
}
```

Пошагово:

1. Берём выбранные `service`, `doctor`, `slot` из state.
2. Если чего-то нет — показываем ошибку и не отправляем запрос.
3. Ставим `isSubmitting=true`, чтобы UI мог показать loader и заблокировать кнопку.
4. Если это перенос (`arg.isReschedule`) — вызываем repository записей.
5. Иначе создаём новую запись.
6. После успеха инвалидируем списки upcoming/past appointments.
7. `invalidate` говорит Riverpod: “эти данные устарели, при следующем чтении загрузить заново”.
8. `done=true` сообщает экрану, что сценарий завершён.
9. При ошибке `_parseError` превращает исключение в текст.

### 4.13. Как `copyWith` помогает не ломать состояние

Во многих state-классах есть `copyWith`. Идея простая: вместо изменения объекта “на месте” создаётся новый объект с частью новых полей.

Пример из booking:

```dart
state = state.copyWith(
  date: date,
  clearSlot: true,
  clearError: true,
  stepIndex: state.stepIndex + 1,
);
```

Это значит:

```text
оставь service как был
оставь doctor как был
поставь новую date
очисти slot
очисти error
увеличь stepIndex
создай новый BookingWizardState
```

Почему это важно: Riverpod лучше работает с immutable-state подходом. Когда создаётся новый объект state, подписчики точно понимают, что состояние изменилось.

### 4.14. Как экран превращает AsyncValue в UI

Почти любой экран с API живёт по схеме:

```dart
final data = ref.watch(someFutureProvider);

return data.when(
  loading: () => const Center(child: CircularProgressIndicator()),
  error: (e, st) => ErrorView(message: e.toString()),
  data: (items) => ListView.builder(
    itemCount: items.length,
    itemBuilder: (context, index) => ItemCard(item: items[index]),
  ),
);
```

Новичку важно запомнить: сетевой запрос — это не “один результат”. У него всегда минимум три состояния:

```text
ещё грузится
упал с ошибкой
успешно вернул данные
```

Riverpod `AsyncValue` заставляет явно обработать все три. Поэтому UI не зависает в пустоте.

### 4.15. Как связаны файлы на примере “пользователь нажал Записаться”

```text
Doctor detail / Service detail
→ context.push('/booking?...') или context.go('/booking?...')
→ app/router.dart создаёт BookingScreen с query parameters
→ BookingScreen создаёт bookingWizardProvider(params)
→ BookingWizardNotifier.build() вычисляет нужные шаги
→ step widget показывает данные через bookingServicesProvider/bookingDoctorsProvider
→ пользователь выбирает дату/слот
→ BookingConfirmStep вызывает notifier.submit(note)
→ BookingRepository.createAppointment или AppointmentsRepository.rescheduleAppointment
→ Dio отправляет запрос с Authorization
→ backend создаёт/переносит запись
→ providers записей invalidated
→ dashboard tab index = 3
→ пользователь видит обновлённые записи
```

Это пример полной цепочки “UI → router → state → repository → API → refresh UI”. Именно так устроена большая часть приложения.

### 4.16. Как связаны файлы на примере “пользователь открыл статью блога”

```text
HomeScreen или BlogScreen
→ пользователь нажал карточку статьи
→ context.push('/blog/$slug')
→ GoRouter route /blog/:slug
→ ArticleScreen(slug: state.pathParameters['slug'])
→ articleDetailProvider(slug)
→ BlogRepository.fetchDetail(slug)
→ Dio GET /articles/{slug}
→ ArticleModel.fromJson
→ ArticleScreen показывает title, cover, content
→ flutter_html рендерит HTML body
```

Здесь `slug` — ключ связи между UI и backend. Карточка статьи знает slug, route передаёт slug, repository запрашивает slug, backend возвращает статью.

### 4.17. Как связаны файлы на примере “пользователь вышел из аккаунта”

```text
ProfileScreen кнопка logout
→ AuthController.logout()
→ PatientAuthRepository.logout() отправляет запрос на backend
→ SecureStorage очищает token
→ StorageService очищает локальный профиль/PIN/FaceID
→ AuthState становится unregistered или registeredLoggedOut в зависимости от логики
→ context.go('/auth')
→ GoRouter больше не пустит в /dashboard
```

Главная мысль: logout — это не просто “перейти на экран входа”. Нужно также убрать token и локальные данные, иначе приложение может снова считать пользователя вошедшим.

## 4. Mermaid-диаграммы

### 4.1. Диаграмма архитектуры

```mermaid
flowchart TB
    Native[Android / iOS / Web / Desktop shell] --> Main[lib/main.dart]
    Main --> App[App / MaterialApp.router]
    App --> Router[GoRouter]
    App --> Notifications[Notification providers]
    Router --> AuthState[AuthController + AuthState]
    AuthState --> LocalStorage[SharedPreferences]
    AuthState --> SecureStorage[Flutter Secure Storage]
    Router --> Screens[Feature screens]
    Screens --> Riverpod[Riverpod providers]
    Riverpod --> Repositories[Repositories]
    Repositories --> Dio[Dio client]
    Dio --> API[Laravel API /api/v1]
    Notifications --> Firebase[Firebase Cloud Messaging]
    Notifications --> LocalPush[Local Notifications]
```

Пояснение: экран не ходит напрямую в сеть. Обычно экран читает Riverpod provider, provider вызывает repository, repository использует Dio, Dio отправляет запрос в backend.

### 4.2. Поток данных

```mermaid
sequenceDiagram
    participant U as Пользователь
    participant S as Flutter Screen
    participant P as Riverpod Provider
    participant R as Repository
    participant D as Dio
    participant A as Laravel API
    participant L as Local/Secure Storage

    U->>S: нажимает кнопку / вводит данные
    S->>P: вызывает controller/provider
    P->>R: бизнес-операция
    R->>D: HTTP request
    D->>L: читает access token
    D->>A: запрос с Authorization
    A-->>D: JSON response
    D-->>R: нормализованные данные или ApiException
    R-->>P: модели Dart
    P-->>S: AsyncValue / StateNotifier state
    S-->>U: обновлённый UI
```

### 4.3. Пользовательский путь регистрации

```mermaid
flowchart LR
    Splash[/Splash/] --> Onboarding[/Onboarding/]
    Onboarding --> Auth[/Auth screen/]
    Auth --> Step1[ФИО, дата рождения, пол]
    Step1 --> Step2[Телефон]
    Step2 --> API1[POST register/request-otp]
    API1 --> Step3[SMS OTP]
    Step3 --> API2[POST register/verify]
    API2 --> Pin[Создание PIN]
    Pin --> Bio[Настройка Face ID]
    Bio --> Dashboard[/Dashboard/]
```

### 4.4. Карта мобильного приложения

```mermaid
flowchart TB
    Splash["/splash"] --> Onboarding["/onboarding"]
    Onboarding --> Auth["/auth"]
    Auth --> Register["/register"]
    Auth --> Login["/login"]
    Login --> PinLogin["/login/pin"]
    Login --> FaceLogin["/login/faceid"]
    Register --> Dashboard["/dashboard"]
    PinLogin --> Dashboard
    FaceLogin --> Dashboard
    Dashboard --> Home["Главная tab"]
    Dashboard --> Doctors["Врачи tab"]
    Dashboard --> Services["Услуги tab"]
    Dashboard --> Appointments["Записи tab"]
    Dashboard --> Profile["Профиль tab"]
    Home --> Blog["/blog"]
    Blog --> Article["/blog/:slug"]
    Home --> Promo["/promotion/:slug"]
    Doctors --> Booking["/booking?doctor=..."]
    Services --> Booking2["/booking?service=..."]
    Appointments --> Reschedule["/booking?appointmentId=..."]
```

### 4.5. Диаграмма компонентов и классов

```mermaid
classDiagram
    class App {
      build()
    }
    class GoRouter {
      redirect()
      routes
    }
    class AuthController {
      bootstrap()
      completeRegistrationWithApiToken()
      completeLoginWithApiToken()
      updateProfile()
      markAuthenticated()
      logout()
    }
    class AuthState {
      AuthStatus status
      String firstName
      String phone
      bool faceIdEnabled
    }
    class PatientAuthRepository {
      registerRequestOtp()
      registerVerify()
      requestLoginOtp()
      verifyLoginOtp()
      me()
      updateMe()
      logout()
    }
    class BookingWizardNotifier {
      selectService()
      selectDoctor()
      selectDate()
      selectSlot()
      submit()
    }
    class Dio {
      interceptors
    }

    App --> GoRouter
    GoRouter --> AuthController
    AuthController --> AuthState
    AuthController --> PatientAuthRepository
    PatientAuthRepository --> Dio
    BookingWizardNotifier --> Dio
```

### 4.6. Диаграмма записи на приём

```mermaid
stateDiagram-v2
    [*] --> Service: выбор услуги
    Service --> Doctor: выбрана услуга
    Doctor --> Date: выбран врач
    Date --> Slot: выбрана дата
    Slot --> Confirm: выбран слот
    Confirm --> Submit: подтвердить
    Submit --> Done: POST /appointments
    Submit --> Error: ошибка API
    Error --> Confirm: исправить / повторить
    Done --> [*]: переход на вкладку Записи
```

## 5. Структура проекта

На верхнем уровне `mobileApp` — стандартный Flutter-проект:

```text
mobileApp/
  android/              Android-обёртка
  ios/                  iOS-обёртка
  lib/                  основной Dart-код приложения
  linux/                Linux desktop-обёртка
  macos/                macOS desktop-обёртка
  web/                  Web-обёртка
  windows/              Windows desktop-обёртка
  pubspec.yaml          зависимости Flutter
  analysis_options.yaml правила анализа Dart
  README.md             краткое описание проекта
```

Самая важная папка — `lib/`. В ней код разделён по смыслу:

- `app/` — корневой виджет и роутинг.
- `core/` — общие сервисы: сеть, хранилище, уведомления, цвета, кнопки.
- `features/` — функциональные модули приложения: auth, registration, login, dashboard, booking.

## 6. Подробный разбор файлов верхнего уровня

### `.gitignore`

Файл Git с правилами игнорирования. В Flutter обычно исключает build-артефакты, временные файлы IDE и сгенерированные локальные данные. Нужен, чтобы не коммитить мусор вроде `build/`.

### `.metadata`

Служебный файл Flutter. Хранит информацию о версии Flutter, платформенных проектах и миграциях. Его обычно не редактируют руками.

### `README.md`

Содержит только стандартный шаблон:

```md
# flutter_application_diplom

A new Flutter project.
```

То есть README пока не объясняет архитектуру. Этот `GUIDE.md` фактически закрывает этот пробел.

### `analysis_options.yaml`

Подключает стандартные правила линтинга Flutter:

```yaml
include: package:flutter_lints/flutter.yaml
```

Это значит, что `flutter analyze` будет проверять код по набору рекомендаций `flutter_lints`.

### `pubspec.yaml`

Главная конфигурация Flutter-пакета. Важные части:

```yaml
name: flutter_application_diplom
version: 0.1.0
environment:
  sdk: ^3.10.8
dependencies:
  go_router: ^17.2.0
  flutter_riverpod: ^2.6.1
  dio: ^5.7.0
  shared_preferences: ^2.5.5
  flutter_secure_storage: ^9.2.2
  local_auth: ^2.3.0
  firebase_core: ^3.6.0
  firebase_messaging: ^15.1.3
```

Роль файла: Flutter читает его при `flutter pub get` и скачивает зависимости. Если здесь убрать `dio`, сетевой слой перестанет собираться. Если убрать `go_router`, сломается `app/router.dart`.

### `pubspec.lock`

Автоматически сгенерированный lock-файл. В нём зафиксированы точные версии пакетов, которые были установлены. Его обычно не редактируют вручную.

## 7. Подробный разбор `lib/`

### `lib/main.dart`

Точка входа. Здесь приложение стартует.

Логические блоки:

1. Импорт Firebase, Flutter и Riverpod.
2. `WidgetsFlutterBinding.ensureInitialized()` — подготовка Flutter engine.
3. Условная инициализация Firebase.
4. Регистрация background handler для push-уведомлений.
5. `runApp(const ProviderScope(child: App()))`.

Связи:

- Импортирует `app/app.dart`.
- Импортирует `core/firebase_config.dart`.
- Импортирует `core/notifications/firebase_messaging_service.dart`.

### `lib/app/app.dart`

Корневой виджет приложения.

Ключевые действия:

- запускает notification controller через `ref.watch(notificationControllerProvider)`;
- запускает синхронизацию FCM-токена через `ref.watch(pushTokenSyncProvider)`;
- слушает `authControllerProvider` и после логина переотправляет push token уже с авторизацией;
- настраивает `MaterialApp.router`;
- включает русскую локализацию;
- задаёт тему приложения.

Важный фрагмент:

```dart
ref.listen<AuthState>(authControllerProvider, (previous, current) {
  if (current.status == AuthStatus.authenticated &&
      previous?.status != AuthStatus.authenticated) {
    ref.read(pushTokenSyncProvider).resendAfterLogin();
  }
});
```

Новичку: `listen` — это реакция на изменение состояния. Когда пользователь стал authenticated, приложение понимает: «теперь backend сможет связать FCM-токен с конкретным пациентом».

### `lib/app/router.dart`

Центральная карта маршрутов.

Маршруты:

| Route | Экран |
|---|---|
| `/splash` | `SplashScreen` |
| `/onboarding` | `OnboardingScreen` |
| `/auth` | `AuthScreen` |
| `/register` | `RegistrationScreen` |
| `/login` | `LoginScreen` |
| `/login/pin` | `LoginPinScreen` |
| `/login/faceid` | `LoginFaceIdScreen` |
| `/dashboard` | `DashboardScreen` |
| `/blog` | `BlogScreen` |
| `/blog/:slug` | `ArticleScreen` |
| `/promotion/:slug` | `PromotionDetailScreen` |
| `/booking` | `BookingScreen` |

Самое важное — `redirect`. Он смотрит на `AuthStatus`:

- `unknown` держит пользователя на splash;
- `unregistered` не пускает в приватные экраны;
- `registeredLoggedOut` отправляет на PIN или Face ID;
- `authenticated` не пускает обратно на авторизацию.

`_AuthRefreshNotifier` связывает Riverpod и GoRouter: когда меняется auth status, GoRouter пересчитывает redirect.

## 8. Core-слой

### `lib/core/firebase_config.dart`

Содержит один флаг:

```dart
const bool kFirebaseEnabled = bool.fromEnvironment(
  'USE_FIREBASE',
  defaultValue: true,
);
```

При запуске можно отключить Firebase:

```bash
flutter run --dart-define=USE_FIREBASE=false
```

Это полезно, если Firebase ещё не настроен.

### `lib/core/network/api_exception.dart`

Определяет типы ошибок API:

- `ApiException` — обычная ошибка backend;
- `NetworkException` — ошибка сети без HTTP-статуса;
- `UnauthorizedException` — 401, пользователь не авторизован.

Зачем это нужно: экраны не должны вручную разбирать все варианты `DioException`. Они могут получить нормализованное сообщение.

### `lib/core/network/dio_client.dart`

Создаёт общий `Dio`.

Важные части:

- `resolvedApiBaseUrl` выбирает базовый URL:
  - сначала `API_BASE`;
  - иначе `API_BASE_URL`;
  - иначе `http://10.0.2.2:8000/api/v1`.
- `_AuthInterceptor` добавляет `Authorization: Bearer <token>`.
- `_ErrorInterceptor` превращает ошибки backend в `ApiException`.

Пример:

```dart
final token = await _storage.readAccessToken();
if (token != null && token.isNotEmpty) {
  options.headers['Authorization'] = 'Bearer $token';
}
```

Новичку: interceptor — это «перехватчик». Он автоматически срабатывает перед каждым запросом или после каждой ошибки. Поэтому каждому repository не нужно отдельно добавлять токен.

### `lib/core/storage/storage_service.dart`

Обёртка над `SharedPreferences`.

Хранит:

- флаг регистрации;
- имя, фамилию, отчество;
- телефон;
- дату рождения;
- пол;
- PIN;
- включён ли Face ID.

Важно: PIN здесь хранится в обычном preferences. Access token хранится отдельно в secure storage.

### `lib/core/storage/secure_storage.dart`

Обёртка над `FlutterSecureStorage`.

Хранит:

- `auth_access_token`;
- `auth_refresh_token`.

Это более защищённое хранилище, чем SharedPreferences, поэтому оно подходит для токенов.

### `lib/core/storage/providers.dart`

Riverpod providers для сервисов:

```dart
final storageServiceProvider = Provider<StorageService>((ref) {
  return StorageService();
});
```

Зачем: вместо создания `StorageService()` в каждом экране приложение берёт сервис через `ref.watch` / `ref.read`.

### `lib/core/biometric/biometric_auth_service.dart`

Обёртка над `local_auth`.

Решает задачи:

- проверить, доступна ли биометрия;
- показать системный диалог Face ID / Touch ID / отпечатка;
- вернуть понятные русские сообщения ошибок.

Особенность Android: в комментариях указано, что на части Android-устройств для приложений может требоваться отпечаток, а не только face unlock.

### `lib/core/notifications/firebase_messaging_service.dart`

Сервис Firebase Cloud Messaging.

Что делает:

- запрашивает permission на уведомления;
- настраивает foreground presentation options;
- слушает `FirebaseMessaging.onMessage`;
- слушает `FirebaseMessaging.onMessageOpenedApp`;
- читает initial message, если приложение открыли тапом по push;
- отдаёт stream обновления FCM-токена.

Фоновый обработчик:

```dart
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  debugPrint('[FCM:background] ${message.messageId} ${message.data}');
}
```

Он top-level, потому что Firebase требует функцию верхнего уровня для background isolate.

### `lib/core/notifications/local_notifications_service.dart`

Показывает локальные уведомления, когда приложение открыто. На iOS/Android push в foreground часто не показывается автоматически так, как хочется приложению, поэтому используется `flutter_local_notifications`.

### `lib/core/notifications/notification_payload.dart`

Типизированная модель payload push-уведомления:

```json
{
  "type": "news",
  "entity_id": "123",
  "screen": "/news/123",
  "title": "...",
  "body": "..."
}
```

Класс достаёт `type`, `entityId`, `screen`, `title`, `body`.

### `lib/core/notifications/notification_router.dart`

Решает, куда вести пользователя после тапа по уведомлению.

Если payload содержит `screen`, приложение делает:

```dart
_router.go(screen);
```

Иначе смотрит на `type`: `news`, `appointment`, `promo` и т.д.

### `lib/core/notifications/providers.dart`

Склеивает FCM, local notifications и backend.

Главные элементы:

- `firebaseMessagingServiceProvider`;
- `localNotificationsServiceProvider`;
- `notificationControllerProvider`;
- `pushTokenSyncProvider`.

`PushTokenSync` отправляет FCM-токен на backend:

```dart
await dio.post(
  '/devices/register',
  data: {
    'fcm_token': token,
    'platform': defaultTargetPlatform.name,
  },
);
```

### `lib/core/constants/app_colors.dart`

Цветовая палитра приложения: primary, background, surface, text colors, error, success, gradients.

Используется почти во всех экранах, чтобы UI был единым.

### `lib/core/constants/app_text_styles.dart`

Единые текстовые стили на базе `GoogleFonts.inter`: `h1`, `h2`, `body`, `caption`, `buttonLarge` и т.д.

Если проект расширять, лучше использовать эти стили, а не каждый раз писать новый `TextStyle`.

### `lib/core/widgets/primary_button.dart`

Переиспользуемая основная кнопка с градиентом. Есть состояние `isEnabled`, иконка, высота, радиус.

### `lib/core/widgets/outline_button.dart`

Переиспользуемая контурная кнопка. Используется там, где действие вторичное.

### `lib/core/widgets/clinic_logo.dart`

Виджет логотипа клиники. SVG встроен строкой и перекрашивается через переданный `color`.

## 9. Auth, registration и login

### `lib/features/auth/domain/auth_state.dart`

Модель состояния авторизации.

Содержит:

- `status`;
- ФИО;
- телефон;
- дату рождения;
- пол;
- `faceIdEnabled`.

Есть вычисляемые поля:

- `fullName`;
- `shortName`;
- `copyWith`.

### `lib/features/auth/data/patient_auth_repository.dart`

Repository для API авторизации пациента.

Методы:

| Метод | Endpoint |
|---|---|
| `registerRequestOtp` | `POST /auth/register/request-otp` |
| `registerVerify` | `POST /auth/register/verify` |
| `requestLoginOtp` | `POST /auth/login/request-otp` |
| `verifyLoginOtp` | `POST /auth/login/verify` |
| `me` | `GET /me` |
| `updateMe` | `PATCH /me` |
| `logout` | `POST /me/logout` |

Новичку: repository — это слой, который знает про backend API. Экран не должен знать точные URL.

### `lib/features/auth/data/patient_auth_providers.dart`

Создаёт `patientAuthRepositoryProvider`, который отдаёт `PatientAuthRepository` с подключенным `dioProvider`.

### `lib/features/auth/presentation/controllers/auth_controller.dart`

Главная state machine авторизации.

Ключевые методы:

- `bootstrap()` — читает storage и определяет статус пользователя;
- `completeRegistrationWithApiToken()` — сохраняет токен и регистрационные данные;
- `completeLoginWithApiToken()` — сохраняет токен и данные пациента после входа;
- `updateProfile()` — обновляет профиль через API и локальное состояние;
- `savePin()` / `getStoredPin()` — работа с PIN;
- `setFaceIdEnabled()` — сохраняет настройку биометрии;
- `markAuthenticated()` — переводит состояние в authenticated;
- `logout()` — вызывает backend logout и чистит локальные данные.

Связан с:

- `StorageService`;
- `SecureStorage`;
- `PatientAuthRepository`;
- `GoRouter` через `authControllerProvider`.

### `lib/features/auth/auth_screen.dart`

Экран выбора «Войти» или «Зарегистрироваться». Это публичная точка входа после онбординга.

### `lib/features/registration/domain/registration_data.dart`

Модель временных данных регистрации.

Важный getter:

```dart
String get phoneE164 => '375$phone';
```

В форме пользователь вводит 9 национальных цифр, а API получает номер в формате без плюса: `375XXXXXXXXX`.

### `lib/features/registration/presentation/controllers/registration_controller.dart`

Riverpod `StateNotifier` для wizard регистрации.

Хранит:

- текущий шаг;
- введённые данные;
- методы перехода вперёд/назад.

### `lib/features/registration/registration_screen.dart`

Собирает wizard регистрации. По `state.step` показывает один из экранов:

1. `Step1Personal`;
2. `Step2Phone`;
3. `Step3Otp`;
4. `PinSetupScreen`;
5. `FaceIdSetupScreen`.

Использует `AnimatedSwitcher`, чтобы переходы между шагами были плавными.

### `lib/features/registration/steps/step1_personal.dart`

Первый шаг регистрации: ФИО, дата рождения, пол.

Проверяет ввод:

- кириллица;
- валидная дата;
- выбран пол.

После успешной валидации сохраняет данные в `RegistrationController`.

### `lib/features/registration/steps/step2_phone.dart`

Второй шаг: ввод телефона.

Делает API-запрос:

```dart
registerRequestOtp(phone: data.phoneE164)
```

Если backend вернул ошибку, показывает snackbar или сообщение возле поля.

### `lib/features/registration/steps/step3_otp.dart`

Третий шаг: ввод SMS-кода из 6 цифр.

Логика:

1. пользователь вводит код;
2. когда код заполнен, вызывается `registerVerify`;
3. backend возвращает token и patient;
4. `AuthController.completeRegistrationWithApiToken` сохраняет token и данные;
5. wizard идёт к настройке PIN.

### `lib/features/registration/steps/pin_setup.dart`

Экран создания PIN-кода. Обычно используется после регистрации и после OTP-входа.

Сценарий:

1. пользователь вводит 4 цифры;
2. подтверждает их повторным вводом;
3. PIN сохраняется через `AuthController.savePin`;
4. вызывается `onNext`.

### `lib/features/registration/steps/face_id_setup.dart`

Настройка биометрии.

Если пользователь включает Face ID:

1. вызывается `BiometricAuthService.authenticate`;
2. при успехе сохраняется `faceIdEnabled=true`;
3. `AuthController.markAuthenticated()`;
4. переход на `/dashboard`.

Если пользователь пропускает — приложение всё равно может перейти дальше, но вход будет через PIN.

### `lib/features/registration/steps/_numpad.dart`

Переиспользуемая цифровая клавиатура для PIN-экранов.

### `lib/features/registration/steps/_progress_bar.dart`

Индикатор прогресса регистрации.

### `lib/features/registration/steps/_reg_field.dart`

Переиспользуемый виджет поля формы регистрации.

### `lib/features/login/presentation/controllers/login_flow_controller.dart`

Контроллер короткого login wizard:

- `step=1` — ввод телефона;
- `step=2` — ввод OTP.

`autoDispose` означает, что состояние сбрасывается, когда экран больше не нужен.

### `lib/features/login/login_screen.dart`

Переключает `LoginPhoneScreen` и `LoginOtpScreen` через `AnimatedSwitcher`.

### `lib/features/login/login_phone.dart`

Экран ввода телефона для входа.

Делает:

```dart
requestLoginOtp(phone: '375$_phone')
```

Если backend отвечает «Номер не найден», экран показывает CTA для регистрации.

### `lib/features/login/login_otp.dart`

Экран ввода OTP при входе.

После успешного `verifyLoginOtp`:

1. сохраняет token и patient через `completeLoginWithApiToken`;
2. показывает success view;
3. открывает `PinSetupScreen`;
4. потом открывает `FaceIdSetupScreen`;
5. после этого пользователь попадает в dashboard.

Важно: после OTP-входа приложение не сразу отправляет в dashboard. Оно заново настраивает PIN/биометрию.

### `lib/features/login/login_pin.dart`

Экран входа по PIN.

Логика:

- читает сохранённый PIN;
- разрешает максимум 5 попыток;
- после 5 ошибок блокирует ввод на 5 минут;
- при правильном PIN вызывает `markAuthenticated()` и `context.go('/dashboard')`;
- также умеет пробовать биометрию.

### `lib/features/login/login_face_id.dart`

Экран входа через Face ID / Touch ID / отпечаток.

Содержит анимированную область сканирования. При успехе:

```dart
ref.read(authControllerProvider.notifier).markAuthenticated();
context.go('/dashboard');
```

При ошибке показывает возможность повторить или войти по PIN.

## 10. Splash и onboarding

### `lib/features/splash/splash_screen.dart`

Стартовый экран. Ждёт около 3 секунд, вызывает `authController.bootstrap()` и решает, куда вести пользователя:

- registeredLoggedOut → PIN/Face ID;
- authenticated → dashboard;
- unregistered/unknown → onboarding.

### `lib/features/onboarding/onboarding_screen.dart`

Три слайда знакомства с приложением. Есть auto-advance примерно раз в 10 секунд, кнопка «Пропустить» и переход на `/auth`.

## 11. Dashboard

### `lib/features/dashboard/dashboard_tab_provider.dart`

Хранит индекс активной вкладки dashboard. Вкладки:

0. Главная.
1. Врачи.
2. Услуги.
3. Записи.
4. Профиль.

### `lib/features/dashboard/dashboard_screen.dart`

Главный экран после входа.

Использует `IndexedStack`, поэтому вкладки не пересоздаются при переключении. Это важно: если пользователь ввёл поисковый запрос во вкладке врачей, переключился и вернулся — состояние может сохраниться.

Также обрабатывает кнопку Back:

- если открыт detail врача — закрывает его;
- если открыта вложенная страница услуг — возвращает на уровень выше;
- иначе требует двойное нажатие Back для выхода.

### `lib/features/dashboard/home/home_screen.dart`

Главная вкладка.

Содержит:

- приветствие по времени суток;
- промо-слайдер;
- ближайшие записи;
- категории услуг;
- врачей;
- блог;
- акции.

Она читает много providers:

- `authControllerProvider`;
- `upcomingAppointmentsProvider`;
- `blogPreviewProvider`;
- `doctorsListProvider`;
- `homePromotionsProvider`;
- `serviceDirectionsProvider`.

### `lib/features/dashboard/widgets/doctor_grid_card.dart`

Переиспользуемая карточка врача для сеток и блоков услуг. Позволяет не дублировать UI врача в разных экранах.

### `lib/features/dashboard/common/html_prose.dart`

Общие стили для HTML-контента. Используется статьями и акциями, где backend может прислать HTML.

## 12. Врачи

### `lib/features/dashboard/doctors/doctor_models.dart`

Модели врача:

- `DoctorModel`;
- `DoctorWorkItem`;
- `DoctorEduItem`;
- `DoctorReviewVm`;
- enum категории врача;
- enum возрастной группы.

Есть много helper-getters:

- `fullName`;
- `compactName`;
- `experienceYearsLabel`;
- `gradeName`;
- `gradeColor`.

Это не просто DTO, а view model с логикой отображения.

### `lib/features/dashboard/doctors/doctors_repository.dart`

Repository врачей.

Endpoints:

- `GET /doctors`;
- `GET /doctors/{slug}`.

Поддерживает фильтры:

- specialization;
- patient_age.

### `lib/features/dashboard/doctors/doctors_providers.dart`

Providers врачей:

- `doctorsRepositoryProvider`;
- `doctorsListProvider`;
- `doctorDetailProvider`;
- `doctorsPendingSlugProvider`;
- `doctorsSubNavProvider`.

`doctorsSubNavProvider` — важная деталь. Он хранит вложенную навигацию внутри вкладки «Врачи»: список → детальная карточка.

### `lib/features/dashboard/doctors/doctors_screen.dart`

Экран врачей.

Умеет:

- загружать список врачей;
- показывать loading/error/data;
- фильтровать по поиску, специальности, категории, возрасту;
- открывать детальную карточку врача;
- переходить к записи на приём.

## 13. Услуги

### `lib/features/dashboard/services/services_data.dart`

Модели услуг:

- `ServiceCategory`;
- `ServiceOffer`;
- `ServiceDoctorSnapshot`.

Также содержит helper-функции склонения:

- `categoryWord`;
- `serviceWord`.

Есть временный список `serviceDoctorsSnapshots`, помеченный комментарием как пока не подключённый к API.

### `lib/features/dashboard/services/services_repository.dart`

Repository услуг.

Endpoints:

- `GET /service-directions`;
- `GET /service-directions/{slug}/services`.

### `lib/features/dashboard/services/services_providers.dart`

Providers услуг:

- `servicesRepositoryProvider`;
- `serviceDirectionsProvider`;
- `servicesInDirectionProvider`;
- `servicesPendingCategorySlugProvider`;
- `serviceDoctorsProvider`;
- `servicesSubNavProvider`.

`ServicesSubNavState` хранит:

- выбранную категорию;
- выбранную услугу.

Это даёт вложенную навигацию: категории → список услуг → карточка услуги.

### `lib/features/dashboard/services/services_screen.dart`

Экран услуг.

Сценарии:

- показать категории;
- поиск по категориям;
- открыть категорию;
- поиск по услугам внутри категории;
- открыть детальную услугу;
- увидеть врачей, оказывающих услугу;
- перейти в booking.

## 14. Записи

### `lib/features/dashboard/appointments/appointment_model.dart`

Модели записи:

- `AppointmentModel`;
- `AppointmentDoctorInfo`;
- `AppointmentServiceInfo`;
- enum `AppointmentApiStatus`.

Есть computed properties:

- `isUpcoming`;
- `isCompleted`;
- `isCancelled`.

Они помогают UI не сравнивать raw-строки статусов.

### `lib/features/dashboard/appointments/appointments_repository.dart`

Repository записей.

Endpoints:

- `GET /appointments?status=upcoming`;
- `GET /appointments?status=past`;
- `POST /appointments/{id}/cancel`;
- `POST /appointments/{id}/reschedule`.

### `lib/features/dashboard/appointments/appointments_providers.dart`

Providers:

- `appointmentsRepositoryProvider`;
- `upcomingAppointmentsProvider`;
- `pastAppointmentsProvider`;
- `appointmentsPendingDetailProvider`.

`appointmentsPendingDetailProvider` нужен, чтобы с главной открыть конкретную запись во вкладке «Записи».

### `lib/features/dashboard/appointments/appointments_screen.dart`

Экран записей.

Содержит локальную UI-модель `_Apt`, которая преобразует backend model в удобный формат для карточек: дата, время, врач, услуга, статус, цена, можно ли отменить.

Умеет:

- переключать будущие/прошедшие записи;
- показывать детали записи;
- отменять запись;
- переносить запись через `/booking?appointmentId=...`;
- повторно записаться к тому же врачу/на ту же услугу.

## 15. Блог

### `lib/features/dashboard/blog/article_model.dart`

Модель статьи:

- id;
- slug;
- title;
- category;
- author;
- publishedAt;
- readingTime;
- coverUrl;
- content.

Есть `formattedDate` и `readTimeLabel`.

### `lib/features/dashboard/blog/blog_repository.dart`

Repository блога.

Endpoints:

- `GET /articles`;
- `GET /article-categories`;
- `GET /articles/{slug}`.

Поддерживает query:

- category;
- q;
- limit.

### `lib/features/dashboard/blog/blog_providers.dart`

Providers:

- `blogPreviewProvider` — короткий список для главной;
- `blogFilteredProvider` — список с фильтрами;
- `articleCategoriesProvider`;
- `articleDetailProvider`.

### `lib/features/dashboard/blog/blog_screen.dart`

Экран списка статей.

Особенность: поиск debounced на 350 мс. Это значит, что запрос не обновляется на каждую букву мгновенно, а ждёт, пока пользователь чуть остановится.

### `lib/features/dashboard/blog/article_screen.dart`

Экран одной статьи.

Загружает статью по slug через `articleDetailProvider(slug)`, показывает обложку, метаданные и HTML-контент через `flutter_html`.

## 16. Акции

### `lib/features/dashboard/promotions/promotion_model.dart`

Модель акции:

- id;
- slug;
- title;
- shortDescription;
- imageUrl;
- category;
- dates;
- items;
- fullDescription.

Extension `displayImageUrl` превращает относительный URL картинки в абсолютный через `resolvedApiBaseUrl`.

### `lib/features/dashboard/promotions/promotions_repository.dart`

Repository акций.

Endpoints:

- `GET /promotions?limit=...`;
- `GET /promotions/{slug}`.

### `lib/features/dashboard/promotions/promotions_providers.dart`

Providers:

- `homePromotionsProvider`;
- `promotionDetailProvider`.

### `lib/features/dashboard/promotions/promotion_detail_screen.dart`

Экран детальной акции. Показывает обложку, период действия, описание и HTML-контент.

## 17. Профиль

### `lib/features/dashboard/profile/profile_screen.dart`

Экран профиля.

Показывает:

- ФИО;
- телефон;
- дату рождения;
- пол;
- меню разделов.

Действия:

- открыть редактирование профиля;
- перейти в историю записей;
- выйти из аккаунта.

Logout вызывает:

```dart
await ref.read(authControllerProvider.notifier).logout();
context.go('/auth');
```

### `lib/features/dashboard/profile/edit_profile_screen.dart`

Экран редактирования профиля.

Отправляет `PATCH /me` через:

```dart
ref.read(authControllerProvider.notifier).updateProfile(...)
```

Телефон read-only. Это явно указано в комментарии: смена номера — отдельный wizard вне MVP.

## 18. Booking wizard

### `lib/features/booking/data/booking_models.dart`

Модели для записи:

- `BookingServiceItem`;
- `BookingDoctorItem`.

Они легче, чем полноценные модели услуг/врачей, потому что wizard'у нужны только id, slug, имя, цена, описание, врач и рейтинг.

### `lib/features/booking/data/booking_catalog_repository.dart`

Repository каталога booking.

Endpoints:

- `GET /booking/services`;
- `GET /booking/doctors?service=...`;
- `GET /booking/dates`;
- `GET /booking/slots`.

Важная деталь по слотам:

```dart
return DateTime.tryParse('${raw}Z');
```

Сервер возвращает время без timezone suffix, поэтому код добавляет `Z`, чтобы Dart интерпретировал его как UTC, а потом переводит в local time.

### `lib/features/booking/data/booking_repository.dart`

Создание записи:

```dart
POST /appointments
```

Тело:

```json
{
  "service_id": 1,
  "doctor_id": 2,
  "start_at": "...",
  "note": "..."
}
```

### `lib/features/booking/data/booking_providers.dart`

Providers для booking:

- `bookingCatalogRepositoryProvider`;
- `bookingRepositoryProvider`;
- `bookingServicesProvider`;
- `bookingDoctorsProvider`;
- `bookingDatesProvider`;
- `bookingSlotsProvider`.

Используются family providers, потому что данные зависят от параметров: slug врача, услуги, даты.

### `lib/features/booking/state/booking_wizard_state.dart`

Состояние wizard:

- список шагов;
- текущий индекс;
- выбранная услуга;
- выбранный врач;
- выбранная дата;
- выбранный слот;
- loading submit;
- error;
- done.

### `lib/features/booking/state/booking_wizard_notifier.dart`

Логика wizard.

Умеет:

- строить список шагов с учётом query-параметров;
- выбирать услугу/врача/дату/слот;
- идти назад;
- submit;
- создавать новую запись или переносить существующую.

При успешном submit:

```dart
ref.invalidate(upcomingAppointmentsProvider);
ref.invalidate(pastAppointmentsProvider);
state = state.copyWith(isSubmitting: false, done: true);
```

То есть список записей сбрасывается, чтобы при возвращении загрузиться заново.

### `lib/features/booking/presentation/booking_screen.dart`

UI-контейнер wizard.

Принимает:

- `doctorSlug`;
- `serviceSlug`;
- `appointmentId`.

Если `appointmentId` есть, экран работает в режиме переноса записи.

### `lib/features/booking/presentation/steps/booking_service_step.dart`

Шаг выбора услуги. Загружает услуги, показывает список, сохраняет выбранную услугу в wizard state.

### `lib/features/booking/presentation/steps/booking_doctor_step.dart`

Шаг выбора врача. Загружает врачей, которые оказывают выбранную услугу.

### `lib/features/booking/presentation/steps/booking_date_step.dart`

Шаг выбора даты. Загружает доступные даты для пары услуга + врач.

### `lib/features/booking/presentation/steps/booking_slot_step.dart`

Шаг выбора времени. Загружает слоты для выбранной даты.

### `lib/features/booking/presentation/steps/booking_confirm_step.dart`

Финальный шаг. Показывает выбранные данные и отправляет запись.

### `lib/features/booking/presentation/widgets/booking_progress_bar.dart`

Индикатор шагов booking wizard.

## 19. Платформенные папки

Платформенные директории в Flutter нужны, чтобы один Dart-код запускался как нативное приложение.

### Android

| Файл | Назначение |
|---|---|
| `android/.gitignore` | игнор Android build-файлов |
| `android/build.gradle.kts` | общая Gradle-конфигурация Android-проекта |
| `android/settings.gradle.kts` | подключение Flutter Gradle plugin и Android module |
| `android/gradle.properties` | свойства Gradle |
| `android/gradle/wrapper/gradle-wrapper.properties` | версия Gradle wrapper |
| `android/app/build.gradle.kts` | конфигурация Android app: namespace, applicationId, Java 17, google-services |
| `android/app/google-services.json` | конфигурация Firebase Android |
| `android/app/src/main/AndroidManifest.xml` | permissions, activity, cleartext traffic, biometric permission |
| `android/app/src/debug/AndroidManifest.xml` | debug-настройки Android |
| `android/app/src/profile/AndroidManifest.xml` | profile-настройки Android |
| `android/app/src/main/kotlin/com/example/flutter_application_diplom/MainActivity.kt` | нативная Android activity, запускающая Flutter |
| `android/app/src/main/res/drawable/launch_background.xml` | splash background до старта Flutter |
| `android/app/src/main/res/drawable-v21/launch_background.xml` | splash background для Android API 21+ |
| `android/app/src/main/res/values/styles.xml` | Android темы |
| `android/app/src/main/res/values-night/styles.xml` | Android night-темы |
| `android/app/src/main/res/mipmap-*/*` | иконки приложения разных плотностей |

Важное в Android manifest:

```xml
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.USE_BIOMETRIC" />
<application android:usesCleartextTraffic="true">
```

`usesCleartextTraffic=true` нужен, потому что API по умолчанию может быть `http://10.0.2.2:8000`.

### iOS

| Файл/группа | Назначение |
|---|---|
| `ios/.gitignore` | игнор Xcode/Flutter временных файлов |
| `ios/Flutter/AppFrameworkInfo.plist` | настройки Flutter framework |
| `ios/Flutter/Debug.xcconfig` | debug config |
| `ios/Flutter/Release.xcconfig` | release config |
| `ios/Runner/AppDelegate.swift` | iOS entry point |
| `ios/Runner/Info.plist` | имя приложения, Face ID permission, App Transport Security |
| `ios/Runner/Runner-Bridging-Header.h` | bridging header |
| `ios/Runner/Base.lproj/LaunchScreen.storyboard` | launch screen |
| `ios/Runner/Base.lproj/Main.storyboard` | main storyboard |
| `ios/Runner/Assets.xcassets/AppIcon.appiconset/*` | iOS app icons |
| `ios/Runner/Assets.xcassets/LaunchImage.imageset/*` | launch images |
| `ios/Runner.xcodeproj/*` | Xcode project |
| `ios/Runner.xcworkspace/*` | Xcode workspace |
| `ios/RunnerTests/RunnerTests.swift` | заготовка iOS tests |

Важное в `Info.plist`:

```xml
<key>NSFaceIDUsageDescription</key>
<string>Разблокировка приложения Face ID или Touch ID</string>
<key>NSAllowsArbitraryLoads</key>
<true/>
```

Первое нужно для Face ID. Второе разрешает небезопасные HTTP-загрузки, что удобно для локальной разработки.

### Web

| Файл | Назначение |
|---|---|
| `web/index.html` | HTML-оболочка, которая загружает Flutter web |
| `web/manifest.json` | PWA manifest |
| `web/favicon.png` | favicon |
| `web/icons/Icon-192.png` | PWA icon |
| `web/icons/Icon-512.png` | PWA icon |
| `web/icons/Icon-maskable-192.png` | maskable PWA icon |
| `web/icons/Icon-maskable-512.png` | maskable PWA icon |

В `index.html` нет бизнес-логики. Главная строка:

```html
<script src="flutter_bootstrap.js" async></script>
```

Она запускает Flutter web bundle.

### Linux

| Файл | Назначение |
|---|---|
| `linux/.gitignore` | игнор Linux build-файлов |
| `linux/CMakeLists.txt` | CMake-конфигурация Linux app |
| `linux/flutter/CMakeLists.txt` | подключение Flutter Linux engine |
| `linux/flutter/generated_plugin_registrant.cc` | регистрация Flutter plugins |
| `linux/flutter/generated_plugin_registrant.h` | header регистрации plugins |
| `linux/flutter/generated_plugins.cmake` | список plugins для CMake |
| `linux/runner/CMakeLists.txt` | сборка runner |
| `linux/runner/main.cc` | desktop entry point |
| `linux/runner/my_application.cc` | GTK application implementation |
| `linux/runner/my_application.h` | header GTK application |

### macOS

| Файл/группа | Назначение |
|---|---|
| `macos/.gitignore` | игнор macOS/Xcode временных файлов |
| `macos/Flutter/Flutter-Debug.xcconfig` | debug config |
| `macos/Flutter/Flutter-Release.xcconfig` | release config |
| `macos/Flutter/GeneratedPluginRegistrant.swift` | регистрация plugins |
| `macos/Runner/AppDelegate.swift` | macOS app delegate |
| `macos/Runner/MainFlutterWindow.swift` | главное Flutter-окно |
| `macos/Runner/Info.plist` | настройки приложения |
| `macos/Runner/Base.lproj/MainMenu.xib` | меню macOS-приложения |
| `macos/Runner/Configs/*.xcconfig` | конфигурации Xcode |
| `macos/Runner/*.entitlements` | permissions sandbox/network |
| `macos/Runner/Assets.xcassets/AppIcon.appiconset/*` | иконки macOS |
| `macos/Runner.xcodeproj/*` | Xcode project |
| `macos/Runner.xcworkspace/*` | Xcode workspace |
| `macos/RunnerTests/RunnerTests.swift` | заготовка тестов |

### Windows

| Файл | Назначение |
|---|---|
| `windows/.gitignore` | игнор Windows build-файлов |
| `windows/CMakeLists.txt` | CMake-конфигурация Windows app |
| `windows/flutter/CMakeLists.txt` | Flutter Windows engine |
| `windows/flutter/generated_plugin_registrant.cc` | регистрация plugins |
| `windows/flutter/generated_plugin_registrant.h` | header регистрации plugins |
| `windows/flutter/generated_plugins.cmake` | список plugins |
| `windows/runner/CMakeLists.txt` | runner build |
| `windows/runner/Runner.rc` | ресурсы Windows app |
| `windows/runner/flutter_window.cpp` | окно Flutter |
| `windows/runner/flutter_window.h` | header окна Flutter |
| `windows/runner/main.cpp` | Windows entry point |
| `windows/runner/resource.h` | resource IDs |
| `windows/runner/resources/app_icon.ico` | иконка |
| `windows/runner/runner.exe.manifest` | Windows manifest |
| `windows/runner/utils.cpp` | utility-функции |
| `windows/runner/utils.h` | utility header |
| `windows/runner/win32_window.cpp` | Win32 window implementation |
| `windows/runner/win32_window.h` | Win32 window header |

## 20. Частые сценарии разработки

### 20.1. Как добавить новый экран

Типичный порядок:

1. Создать файл экрана в подходящей папке `features/...`.
2. Если экрану нужны данные API — создать или расширить repository.
3. Создать provider, который отдаёт данные экрану.
4. Добавить route в `lib/app/router.dart`.
5. Если экран приватный, убедиться, что auth redirect его защищает.
6. Добавить переход на экран из существующего UI.

Мини-пример route:

```dart
GoRoute(
  path: '/example',
  pageBuilder: (context, state) => _buildPage(
    state,
    const ExampleScreen(),
  ),
),
```

### 20.2. Как добавить новый API-запрос

Правильный путь:

```text
1. Добавить model/fromJson, если backend возвращает новый объект.
2. Добавить метод в repository.
3. Добавить provider.
4. В экране читать provider через ref.watch.
5. Обработать loading/error/data.
```

Пример структуры метода repository:

```dart
Future<List<SomethingModel>> fetchSomething() async {
  final res = await _dio.get<Map<String, dynamic>>('/something');
  final data = res.data?['data'];
  if (data is! List) {
    throw StateError('Invalid API response: expected data array');
  }
  return data
      .map((e) => SomethingModel.fromJson(Map<String, dynamic>.from(e as Map)))
      .toList();
}
```

### 20.3. Как добавить новое поле профиля

Нужно проверить всю цепочку:

```text
Backend JSON /me
→ PatientAuthRepository.me() / updateMe()
→ AuthController._applyPatientData()
→ AuthState новое поле
→ StorageService ключ + save/get
→ ProfileScreen отображение
→ EditProfileScreen форма
```

Если добавить поле только на экран, оно исчезнет после перезапуска. Если добавить только в storage, оно не придёт с backend. Поэтому профиль — хороший пример, где нужно обновлять несколько слоёв одновременно.

### 20.4. Как добавить новую вкладку в dashboard

Нужно изменить:

1. `dashboard_tab_provider.dart` — если нужна новая логика индекса.
2. `DashboardScreen` → список `children` в `IndexedStack`.
3. `_BottomNav` → список `_NavItem`.
4. Обработку Back, если у вкладки будет вложенная навигация.
5. Переходы с главной или других экранов, если они должны открывать новую вкладку.

Важно: индекс вкладки используется в разных местах. Например, главная переводит пользователя на записи так:

```dart
ref.read(dashboardTabIndexProvider.notifier).state = 3;
```

Если поменять порядок вкладок, нужно найти такие места и обновить индексы.

### 20.5. Как безопасно менять авторизацию

Авторизация затрагивает много файлов:

- router redirect;
- `AuthStatus`;
- `AuthController.bootstrap`;
- local storage;
- secure storage;
- login/register screens;
- backend endpoints;
- push token sync после входа.

Перед изменениями нужно нарисовать сценарии:

```text
новый пользователь
зарегистрированный пользователь без Face ID
зарегистрированный пользователь с Face ID
пользователь после logout
пользователь с протухшим token
```

Иначе легко получить баг: например, пользователь authenticated, но router всё равно отправляет его на onboarding.

### 20.6. Как читать большой UI-файл

В проекте есть большие экраны вроде `home_screen.dart`, `doctors_screen.dart`, `appointments_screen.dart`. Их не нужно читать сверху вниз как роман. Лучше так:

1. Найти главный public widget (`HomeScreen`, `DoctorsScreen`).
2. Прочитать его `build`.
3. Выписать дочерние private widgets (`_Header`, `_PromoSlider`, `_SectionHeader`).
4. Читать каждый private widget отдельно.
5. Helper-классы и функции читать после понимания общей структуры.

Во Flutter большие UI-файлы часто устроены как “главный экран + много маленьких приватных виджетов ниже”. Это нормально, если эти виджеты используются только внутри одного экрана.

## 20. Как читать и расширять проект

Если нужно понять новый экран, двигайтесь так:

1. Найдите route в `lib/app/router.dart`.
2. Откройте экран из `features/...`.
3. Посмотрите, какие providers он читает через `ref.watch`.
4. Найдите provider.
5. Найдите repository.
6. Посмотрите endpoint и модель ответа.
7. Вернитесь в экран и проверьте, как AsyncValue превращается в UI.

Пример для врачей:

```text
/dashboard tab Врачи
→ DoctorsScreen
→ doctorsListProvider
→ DoctorsRepository.fetchList()
→ GET /doctors
→ DoctorModel.fromApiList()
→ карточки врачей
```

## 21. Глоссарий

- **Widget** — строительный блок UI во Flutter.
- **StatefulWidget** — widget с внутренним изменяемым состоянием.
- **ConsumerWidget / ConsumerStatefulWidget** — widget, который умеет читать Riverpod providers.
- **Provider** — способ дать приложению зависимость или состояние.
- **StateNotifier** — объект, который хранит state и изменяет его методами.
- **FutureProvider** — provider для асинхронной загрузки данных.
- **Repository** — слой, который работает с API.
- **Interceptor** — перехватчик HTTP-запросов/ответов.
- **Route** — адрес экрана.
- **Slug** — человекочитаемый идентификатор в URL, например `cardiologist`.
- **OTP** — одноразовый SMS-код.
- **FCM** — Firebase Cloud Messaging.
- **Secure Storage** — защищённое хранилище токенов.

## 22. Подробные учебные разборы ключевых цепочек

### 22.1. Цепочка “регистрация → token → dashboard”

Самое важное в регистрации — понять, что пользователь становится “настоящим” для приложения не после ввода имени, а после успешной проверки OTP и получения access token от backend.

```text
Step1Personal собирает ФИО/дату/пол
→ RegistrationController хранит эти данные временно
→ Step2Phone отправляет телефон на backend
→ backend отправляет SMS
→ Step3Otp отправляет phone + otp
→ backend возвращает token + patient
→ AuthController сохраняет token в SecureStorage
→ AuthController сохраняет профиль в SharedPreferences
→ пользователь создаёт PIN
→ пользователь включает/пропускает Face ID
→ markAuthenticated()
→ GoRouter пускает на /dashboard
```

Почему token хранится отдельно? Потому что token — чувствительная информация. Его нельзя хранить как обычную настройку UI. Поэтому `SecureStorage` используется для token, а `SharedPreferences` — для менее критичных данных профиля и настроек.

### 22.2. Цепочка “открытие приложения зарегистрированным пользователем”

При следующем запуске приложение не спрашивает SMS сразу. Оно делает bootstrap:

```text
SplashScreen
→ AuthController.bootstrap()
→ StorageService.isRegistered()
→ StorageService читает имя/телефон/PIN/FaceID
→ SecureStorage может иметь token
→ AuthState становится registeredLoggedOut
→ Router отправляет на /login/faceid или /login/pin
```

Важно: `registeredLoggedOut` не означает, что пользователь неизвестен. Это значит: “мы знаем этого пользователя локально, но он ещё не подтвердил доступ к приложению в текущей сессии”.

### 22.3. Цепочка “создание записи на приём”

Booking wizard построен так, чтобы каждый следующий шаг зависел от предыдущего:

```text
Услуга определяет список врачей
Врач + услуга определяют доступные даты
Дата + врач + услуга определяют слоты
Слот + врач + услуга отправляются в /appointments
```

Поэтому при выборе новой услуги нужно сбрасывать врача, дату и слот. Иначе можно получить невозможную комбинацию: старая дата от другого врача + новая услуга. Именно поэтому state содержит `clearDoctor`, `clearDate`, `clearSlot`.

### 22.4. Цепочка “перенос записи”

Перенос использует тот же wizard, но с `appointmentId`. Если `appointmentId` есть, submit делает не создание новой записи, а reschedule:

```dart
if (arg.isReschedule) {
  await ref
      .read(appointmentsRepositoryProvider)
      .rescheduleAppointment(arg.appointmentId!, slot);
} else {
  await ref.read(bookingRepositoryProvider).createAppointment(...);
}
```

Это хороший пример переиспользования: UI выбора даты/слота тот же, а финальное действие другое.

### 22.5. Цепочка “push token до и после логина”

При старте приложение может получить FCM token ещё до авторизации. Оно пытается отправить token на backend анонимно. После входа `App` слушает `AuthState` и вызывает `resendAfterLogin()`.

Зачем повторно? Потому что после логина у Dio уже есть Authorization header, и backend может привязать устройство к конкретному пациенту.

### 22.6. Цепочка “ошибка API на экране”

Пример: backend вернул 401.

```text
Dio получает HTTP 401
→ _ErrorInterceptor создаёт UnauthorizedException
→ DioException.error содержит UnauthorizedException
→ верхний слой может показать “Необходима авторизация”
```

Пример: backend вернул validation error.

```text
Backend JSON содержит message/errors
→ _extractMessage достаёт человекочитаемый текст
→ ApiException(message, statusCode)
→ экран показывает SnackBar или inline error
```

Это лучше, чем показывать пользователю сырой `DioException`, потому что такие ошибки обычно непонятны новичку и пользователю.

## 22. Итоговая картина

`mobileApp` — это уже не пустой Flutter-шаблон, а полноценное пациентское приложение:

- есть авторизация через OTP;
- есть локальный PIN и Face ID;
- есть защищённое хранение токена;
- есть централизованный сетевой слой;
- есть вкладочный dashboard;
- есть API-интеграция с врачами, услугами, записями, блогом и акциями;
- есть booking wizard;
- есть push-уведомления;
- есть платформенные настройки Android/iOS/Web/Desktop.

Самые важные файлы для понимания всего проекта:

1. `lib/main.dart` — старт приложения.
2. `lib/app/app.dart` — корневой Flutter app.
3. `lib/app/router.dart` — маршруты и auth redirects.
4. `lib/features/auth/presentation/controllers/auth_controller.dart` — состояние авторизации.
5. `lib/core/network/dio_client.dart` — HTTP и токены.
6. `lib/features/dashboard/dashboard_screen.dart` — главный экран после входа.
7. `lib/features/booking/state/booking_wizard_notifier.dart` — логика записи на приём.

Если понять эти семь файлов, остальная структура становится намного проще: большинство экранов повторяют один и тот же паттерн «экран → provider → repository → API → модель → UI».
