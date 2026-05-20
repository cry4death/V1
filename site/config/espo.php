<?php

return [

    /*
    |--------------------------------------------------------------------------
    | EspoCRM REST API (источник правды для CRM)
    |--------------------------------------------------------------------------
    |
    | В Espo: Администрирование → Роли → API; пользователь с API-ключом.
    | Base URL — до /api/v1 включительно, например: http://127.0.0.1:8080/api/v1
    |
    */

    'enabled' => (bool) env('ESPO_ENABLED', false),

    /** Не вызывать API, только писать в лог (безопасно для первых прогонов). */
    'dry_run' => (bool) env('ESPO_DRY_RUN', true),

    'base_url' => rtrim((string) env('ESPO_BASE_URL', 'http://127.0.0.1:8080/api/v1'), '/'),

    'api_key' => (string) env('ESPO_API_KEY', ''),

    /**
     * ID пользователя Espo (User), на которого вешается Meeting (обязательное поле в CRM).
     * Можно переопределить на каждого врача: колонка doctors.espo_assigned_user_id.
     */
    'default_assigned_user_id' => (string) env('ESPO_DEFAULT_ASSIGNED_USER_ID', ''),

    /** Сущность встречи в Espo (по умолчанию стандартная Meeting). */
    'meeting_entity' => (string) env('ESPO_MEETING_ENTITY', 'Meeting'),

];
