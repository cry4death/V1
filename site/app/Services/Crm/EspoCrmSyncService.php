<?php

namespace App\Services\Crm;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Синхронизация пациента (Contact) и записи (Meeting) с EspoCRM по REST API.
 *
 * Локальная БД хранит связи (espo_contact_id, espo_entity_id); «толстые» данные для CRM уходят в Espo.
 */
class EspoCrmSyncService
{
    public function syncPatient(Patient $patient): bool
    {
        if (! config('espo.enabled')) {
            return true;
        }

        if (config('espo.dry_run')) {
            Log::channel('crm')->info('Espo dry-run: syncPatient', ['patient_id' => $patient->id]);

            return true;
        }

        if (config('espo.api_key') === '') {
            $this->markPatientFailed($patient, 'Не задан ESPO_API_KEY в .env');

            return false;
        }

        try {
            if ($patient->espo_contact_id) {
                $this->updateContact($patient->espo_contact_id, $patient);
            } else {
                $existingId = $this->findContactIdByPhone((string) $patient->phone);
                if ($existingId !== null) {
                    $patient->forceFill([
                        'espo_contact_id' => $existingId,
                    ])->save();
                    $this->updateContact($existingId, $patient);
                } else {
                    $id = $this->createContact($patient);
                    $patient->forceFill(['espo_contact_id' => $id])->save();
                }
            }

            $patient->forceFill([
                'espo_sync_status' => 'synced',
                'espo_synced_at' => now(),
                'espo_sync_error' => null,
            ])->save();

            return true;
        } catch (Throwable $e) {
            $this->markPatientFailed($patient, $e->getMessage());
            Log::channel('crm')->error('Espo syncPatient failed', [
                'patient_id' => $patient->id,
                'exception' => $e,
            ]);

            return false;
        }
    }

    public function syncAppointment(Appointment $appointment): void
    {
        if (! config('espo.enabled')) {
            return;
        }

        $appointment->loadMissing(['patient', 'doctor', 'service']);

        if (config('espo.dry_run')) {
            Log::channel('crm')->info('Espo dry-run: syncAppointment', ['appointment_id' => $appointment->id]);
            $this->markAppointmentSkipped($appointment, 'dry_run');

            return;
        }

        if (config('espo.api_key') === '') {
            $this->markAppointmentFailed($appointment, 'Не задан ESPO_API_KEY в .env');

            return;
        }

        try {
            $patient = $appointment->patient;
            if ($patient === null) {
                $this->markAppointmentFailed($appointment, 'Нет пациента у записи');

                return;
            }

            $entity = config('espo.meeting_entity');
            $entityId = $appointment->espo_entity_id;

            $isCancelledShape = in_array($appointment->status, [
                AppointmentStatus::Cancelled,
                AppointmentStatus::Rescheduled,
            ], true);

            $entityMatch = $appointment->espo_entity_type === null || $appointment->espo_entity_type === $entity;

            if ($isCancelledShape && $entityId && $entityMatch) {
                $this->cancelMeetingInEspo($entityId, $appointment);

                $appointment->forceFill([
                    'espo_sync_status' => 'synced',
                    'espo_synced_at' => now(),
                    'espo_sync_error' => null,
                ])->save();

                return;
            }

            if ($isCancelledShape && ! $entityId) {
                $this->markAppointmentSkipped($appointment, 'Нет espo_entity_id — в CRM не выгружалась');

                return;
            }

            if (! in_array($appointment->status, [AppointmentStatus::New, AppointmentStatus::Processing], true)) {
                return;
            }

            if ($entityId) {
                return;
            }

            if (! $this->syncPatient($patient->fresh())) {
                $this->markAppointmentFailed($appointment, 'Не удалось синхронизировать пациента с Espo CRM');

                return;
            }
            $patient->refresh();

            if (! $patient->espo_contact_id) {
                $this->markAppointmentFailed($appointment, 'Не удалось получить Contact в Espo для пациента');

                return;
            }

            $assignedUserId = $this->resolveAssignedUserId($appointment->doctor);
            if ($assignedUserId === '') {
                $this->markAppointmentFailed(
                    $appointment,
                    'Не задан ответственный в Espo: укажите ESPO_DEFAULT_ASSIGNED_USER_ID или у врача поле «Espo: ID пользователя».',
                );

                return;
            }

            $meetingId = $this->createMeeting($appointment, $patient->espo_contact_id, $assignedUserId);

            $appointment->forceFill([
                'espo_entity_id' => $meetingId,
                'espo_entity_type' => $entity,
                'espo_sync_status' => 'synced',
                'espo_synced_at' => now(),
                'espo_sync_error' => null,
            ])->save();
        } catch (Throwable $e) {
            $this->markAppointmentFailed($appointment, $e->getMessage());
            Log::channel('crm')->error('Espo syncAppointment failed', [
                'appointment_id' => $appointment->id,
                'exception' => $e,
            ]);
        }
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(config('espo.base_url'))
            ->withHeaders(['X-Api-Key' => config('espo.api_key')])
            ->timeout(45)
            ->acceptJson()
            ->asJson();
    }

    private function findContactIdByPhone(string $phone): ?string
    {
        foreach ($this->phoneVariants($phone) as $variant) {
            // Espo требует where как массив (PHP array), а не JSON-строку:
            // SearchParamsFetcher проверяет is_array($params['where']) и игнорирует строку.
            $response = $this->http()->get('Contact', [
                'maxSize' => 5,
                'select' => 'id,phoneNumber',
                'where' => [
                    [
                        'type' => 'equals',
                        'attribute' => 'phoneNumber',
                        'value' => $variant,
                    ],
                ],
            ]);

            if ($response->failed()) {
                continue;
            }

            $list = $response->json('list');
            if (is_array($list) && $list !== []) {
                $first = $list[0];

                return is_array($first) && isset($first['id']) ? (string) $first['id'] : null;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function phoneVariants(string $phone): array
    {
        $e164 = $this->phoneForEspo($phone);

        // Ищем только по канонической E.164 — не перебираем «сырые» варианты,
        // чтобы не зацепить чужой контакт по совпадению цифровой подстроки.
        return array_values(array_unique(array_filter([$e164, $phone !== $e164 ? $phone : null])));
    }

    /**
     * Нормализует телефон в E.164 для Espo.
     * Espo использует brick/phonenumber: номер должен точно совпадать с канонической формой.
     *
     * Поддерживаемые форматы Беларуси:
     *  +375XXXXXXXXX  → +375XXXXXXXXX
     *  375XXXXXXXXX   → +375XXXXXXXXX
     *  80XXXXXXXXX    → +375XXXXXXXXX (замена нац. 80 → 375)
     *  29XXXXXXX      → +37529XXXXXXX (9 цифр без кода)
     */
    private function phoneForEspo(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return $phone;
        }

        // Белорусский национальный формат: 80XX... (11 цифр, начинается с 80)
        if (str_starts_with($digits, '80') && strlen($digits) === 11) {
            $digits = '375'.substr($digits, 2);
        }

        // Уже с кодом страны (12 цифр для BY: 375 + 9)
        if (str_starts_with($digits, '375') && strlen($digits) === 12) {
            return '+'.$digits;
        }

        // Только номер без кода (9 цифр)
        if (! str_starts_with($digits, '375') && strlen($digits) === 9) {
            return '+375'.$digits;
        }

        // Уже с плюсом или неизвестный формат — вернуть как есть с +
        return str_starts_with($phone, '+') ? $phone : '+'.$digits;
    }

    private function createContact(Patient $patient): string
    {
        $phone = $this->phoneForEspo((string) $patient->phone);
        $payload = [
            'firstName' => (string) $patient->first_name,
            'lastName' => (string) $patient->last_name,
            'phoneNumber' => $phone,
            'description' => $this->contactDescription($patient),
        ];

        $response = $this->http()->post('Contact', $payload);

        // Если Espo отклонил номер как невалидный — повторяем без phoneNumber.
        // Контакт создаётся; телефон менеджер уточнит вручную в CRM.
        if ($this->isPhoneValidationError($response)) {
            Log::channel('crm')->warning('Espo: номер телефона отклонён при создании Contact, создаём без номера', [
                'patient_id' => $patient->id,
                'phone_sent' => $phone,
            ]);
            unset($payload['phoneNumber']);
            $response = $this->http()->post('Contact', $payload);
        }

        $this->throwIfFailed($response, 'Contact create');

        $id = $response->json('id');

        return is_string($id) && $id !== '' ? $id : throw new RuntimeException('Espo: нет id у созданного Contact');
    }

    private function updateContact(string $espoId, Patient $patient): void
    {
        $phone = $this->phoneForEspo((string) $patient->phone);
        $payload = [
            'firstName' => (string) $patient->first_name,
            'lastName' => (string) $patient->last_name,
            'phoneNumber' => $phone,
            'description' => $this->contactDescription($patient),
        ];

        $response = $this->http()->put('Contact/'.$espoId, $payload);

        // Если Espo отклонил номер — обновляем без phoneNumber, остальные поля сохраняем.
        if ($this->isPhoneValidationError($response)) {
            Log::channel('crm')->warning('Espo: номер телефона отклонён при обновлении Contact, обновляем без номера', [
                'espo_id' => $espoId,
                'patient_id' => $patient->id,
                'phone_sent' => $phone,
            ]);
            unset($payload['phoneNumber']);
            $response = $this->http()->put('Contact/'.$espoId, $payload);
        }

        $this->throwIfFailed($response, 'Contact update');
    }

    /**
     * Проверяет, что Espo вернул HTTP 400 именно из-за невалидного номера телефона.
     */
    private function isPhoneValidationError(Response $response): bool
    {
        if ($response->status() !== 400) {
            return false;
        }

        $body = $response->json();

        if (! is_array($body)) {
            return false;
        }

        $field = $body['messageTranslation']['data']['field'] ?? null;
        $type = $body['messageTranslation']['data']['type'] ?? null;

        return $field === 'phoneNumber' && $type === 'valid';
    }

    private function contactDescription(Patient $patient): string
    {
        $lines = [
            'Источник: сайт / мобильное приложение',
            'ID пациента (Laravel): '.$patient->id,
            'Дата рождения: '.$patient->birth_date?->format('d.m.Y'),
            'Пол: '.(string) $patient->gender,
        ];
        if ($patient->email) {
            $lines[] = 'Email: '.$patient->email;
        }

        return implode("\n", array_filter($lines, fn ($l) => is_string($l) && $l !== ''));
    }

    private function createMeeting(Appointment $appointment, string $contactId, string $assignedUserId): string
    {
        $doctor = $appointment->doctor;
        $service = $appointment->service;
        $tz = config('app.timezone');
        $start = $appointment->start_at?->timezone($tz);
        $end = $appointment->end_at?->timezone($tz);
        if ($start === null || $end === null) {
            throw new RuntimeException('У записи нет start_at / end_at');
        }

        $source = $appointment->source ?? AppointmentSource::Web;
        $sourceLabel = $source->label(); // «Сайт» или «Мобильное приложение»

        $name = trim(($service?->name ?? 'Услуга').' — '.($doctor?->full_name ?? 'Врач'));

        $description = implode("\n", array_filter([
            "Источник записи: {$sourceLabel}",
            'Laravel appointment_id: '.$appointment->id,
            $appointment->message ? 'Комментарий пациента: '.$appointment->message : null,
            'Телефон в заявке: '.$appointment->phone,
        ]));

        $entity = config('espo.meeting_entity');
        $payload = [
            'name' => $name,
            'dateStart' => $start->format('Y-m-d H:i:s'),
            'dateEnd' => $end->format('Y-m-d H:i:s'),
            'assignedUserId' => $assignedUserId,
            'contactsIds' => [$contactId],
            'status' => 'Planned',
            'description' => $description,
        ];

        $response = $this->http()->post($entity, $payload);
        $this->throwIfFailed($response, $entity.' create');

        $id = $response->json('id');

        return is_string($id) && $id !== '' ? $id : throw new RuntimeException('Espo: нет id у созданной встречи');
    }

    private function cancelMeetingInEspo(string $meetingId, Appointment $appointment): void
    {
        $entity = config('espo.meeting_entity');
        $source = $appointment->source ?? AppointmentSource::Web;
        $reason = (string) ($appointment->cancellation_reason ?? '');
        $description = 'Отмена / перенос ('.$source->label().'). Laravel appointment_id: '.$appointment->id;
        if ($reason !== '') {
            $description .= "\nПричина: ".$reason;
        }

        $payload = [
            'status' => 'Not Held',
            'description' => $description,
        ];

        $response = $this->http()->put($entity.'/'.$meetingId, $payload);
        $this->throwIfFailed($response, $entity.' cancel');
    }

    private function resolveAssignedUserId(?Doctor $doctor): string
    {
        $fromDoctor = $doctor?->espo_assigned_user_id;

        return is_string($fromDoctor) && $fromDoctor !== ''
            ? trim($fromDoctor)
            : trim((string) config('espo.default_assigned_user_id'));
    }

    private function throwIfFailed(Response $response, string $context): void
    {
        if ($response->successful()) {
            return;
        }

        $body = $response->json();
        $msg = is_array($body)
            ? (string) ($body['message'] ?? json_encode($body, JSON_UNESCAPED_UNICODE))
            : $response->body();

        throw new RuntimeException($context.': HTTP '.$response->status().' — '.$msg);
    }

    private function markPatientFailed(Patient $patient, string $message): void
    {
        $patient->forceFill([
            'espo_sync_status' => 'failed',
            'espo_synced_at' => now(),
            'espo_sync_error' => $message,
        ])->save();
    }

    private function markAppointmentFailed(Appointment $appointment, string $message): void
    {
        $appointment->forceFill([
            'espo_sync_status' => 'failed',
            'espo_synced_at' => now(),
            'espo_sync_error' => $message,
        ])->save();
    }

    private function markAppointmentSkipped(Appointment $appointment, string $reason): void
    {
        $appointment->forceFill([
            'espo_sync_status' => 'skipped',
            'espo_synced_at' => now(),
            'espo_sync_error' => $reason,
        ])->save();
    }
}
