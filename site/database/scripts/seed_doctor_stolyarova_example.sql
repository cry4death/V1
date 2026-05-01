-- Быстрое заполнение БД: пример врача (Столярова Т.И., терапевт) + отзывы.
-- MySQL / MariaDB, utf8mb4. Перед запуском: убедитесь, что slug свободен.
--
-- Специализация берётся по slug `terapevt`. Если у вас другой id — замените @spec_id.

SET NAMES utf8mb4;

SET @spec_id = (SELECT id FROM specializations WHERE slug = 'terapevt' LIMIT 1);

START TRANSACTION;

INSERT INTO doctors (
    specialization_id,
    last_name,
    first_name,
    middle_name,
    slug,
    category,
    experience_years,
    experience_since,
    patient_age,
    photo,
    description,
    rating,
    education,
    status,
    sort_order,
    created_at,
    updated_at
) VALUES (
    @spec_id,
    'Столярова',
    'Татьяна',
    'Игоревна',
    'stolyarova-tatyana-igorevna',
    'first',
    18,
    NULL,
    'adults',
    NULL,
    'Врач-терапевт, первая квалификационная категория. Стаж 18 лет.\n\n'
        'В медицинском центре «Маяк Здоровья»:\n'
        '- диагностика и лечение анемии;\n'
        '- диагностика и лечение основных проявлений остеохондроза;\n'
        '- диагностика и лечение заболеваний суставов (артроз – артрит);\n'
        '- диагностика и ведение пациентов с сахарным диабетом 2 типа;\n'
        '- диагностика, лечение и профилактика сердечно-сосудистых заболеваний: артериальная гипертензия, ИБС, нарушения ритма, облитерирующий атеросклероз нижних конечностей;\n'
        '- диагностика и лечение инфекционных и неинфекционных заболеваний органов пищеварения;\n'
        '- диагностика, лечение и профилактика заболеваний органов дыхания: ОРВИ, внебольничные пневмонии, острые бронхиты, ХОБЛ, острые и хронические тонзиллиты, фарингиты.',
    5.00,
    JSON_ARRAY(
        JSON_OBJECT(
            'type', 'experience',
            'period', '01.08.2005-01.07.2006',
            'title', 'Стажировка УЗ «Минская областная клиническая больница» по специальности – терапия',
            'institution', ''
        ),
        JSON_OBJECT(
            'type', 'experience',
            'period', '09.01.2006-11.04.2006',
            'title', 'Переподготовка ГУО «Белорусская медицинская академия последипломного образования» по специальности – терапия',
            'institution', ''
        ),
        JSON_OBJECT(
            'type', 'experience',
            'period', '24.01.2011-20.05.2011',
            'title', 'Переподготовка ГУО «Белорусская медицинская академия последипломного образования» по специальности – общая врачебная практика',
            'institution', ''
        ),
        JSON_OBJECT(
            'type', 'education',
            'period', '15.04.2011',
            'title', 'Профилактика и ранняя диагностика злокачественных новообразований на амбулаторном этапе',
            'institution', ''
        ),
        JSON_OBJECT(
            'type', 'education',
            'period', '11.03.2013-22.03.2013',
            'title', 'Неотложная помощь и лечение наиболее распространенной патологии в амбулаторных условиях',
            'institution', ''
        ),
        JSON_OBJECT(
            'type', 'education',
            'period', '04.04.2014',
            'title', 'Современные подходы к лечению ИБС. Метаболическая терапия',
            'institution', ''
        ),
        JSON_OBJECT(
            'type', 'education',
            'period', '16.05.2016-20.05.2016',
            'title', 'Охрана труда в организациях здравоохранения',
            'institution', ''
        ),
        JSON_OBJECT(
            'type', 'education',
            'period', '06.10.2016',
            'title', 'Инновационные методы в гериатрии',
            'institution', ''
        ),
        JSON_OBJECT(
            'type', 'education',
            'period', '03.10.2016-14.10.2016',
            'title', 'Принципы фармакотерапии и вторичной профилактики заболеваний сердечно-сосудистой и нервной систем у пациентов пожилого возраста',
            'institution', ''
        ),
        JSON_OBJECT(
            'type', 'education',
            'period', '05.02.2018-16.02.2018',
            'title', 'Наблюдение на педиатрическом участке, домашнее визитирование детей первого года жизни, лечение и профилактика возраст-ассоциированных заболеваний',
            'institution', ''
        ),
        JSON_OBJECT(
            'type', 'education',
            'period', '02.09.2019-13.09.2019',
            'title', 'Оптимизация методов лечения и профилактики сердечно-сосудистой патологии в гериатрической практике',
            'institution', ''
        ),
        JSON_OBJECT(
            'type', 'education',
            'period', '23.01.2023-03.02.2023',
            'title', 'Коморбидный пациент в практике врача первичного звена здравоохранения',
            'institution', ''
        )
    ),
    'active',
    100,
    NOW(),
    NOW()
);

SET @doc_id = LAST_INSERT_ID();

INSERT INTO reviews (doctor_id, author_name, rating, text, status, published_at, created_at, updated_at) VALUES
(
    @doc_id,
    'Валерий',
    5,
    'Хочу ещё раз на прием к ней, очень внимательная и приятная врач. Спасибо за таких специалистов.',
    'approved',
    '2024-01-22 12:00:00',
    NOW(),
    NOW()
),
(
    @doc_id,
    'Александр',
    5,
    'Прекрасна как рождественское утро',
    'approved',
    '2023-12-26 12:00:00',
    NOW(),
    NOW()
);

-- Пересчёт рейтинга врача по одобренным отзывам (как в syncRatingFromReviews):
UPDATE doctors d
SET d.rating = (
    SELECT COALESCE(ROUND(AVG(r.rating), 2), 5.00)
    FROM reviews r
    WHERE r.doctor_id = d.id AND r.status = 'approved'
)
WHERE d.id = @doc_id;

COMMIT;

-- Проверка:
-- SELECT id, last_name, first_name, slug, rating FROM doctors WHERE id = @doc_id;
-- SELECT * FROM reviews WHERE doctor_id = @doc_id;
