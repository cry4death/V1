<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Review;
use App\Models\Service;
use App\Models\Specialization;
use App\Support\Slug;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Макеева Алеся Викторовна — эталонный доктор (из doctor-page.html) =====
        $makeevaSpec = Specialization::firstOrCreate(
            ['name' => 'Врач функциональной диагностики'],
            ['slug' => Slug::make('Врач функциональной диагностики')]
        );

        $makeevaEducation = [
            ['type' => 'experience', 'period' => 'Август 2004 — ноябрь 2004',  'title' => 'Врач-гастроэнтеролог',                            'institution' => 'ГУ «Борисовское территориальное медицинское объединение»'],
            ['type' => 'experience', 'period' => 'Ноябрь 2004 — декабрь 2006',  'title' => 'Заведующий приёмным отделением',                  'institution' => 'ГУ «Борисовское территориальное медицинское объединение»'],
            ['type' => 'experience', 'period' => 'Декабрь 2006 — май 2007',     'title' => 'Заместитель главного врача по медицинской части', 'institution' => 'ГУ «Борисовское территориальное медицинское объединение»'],
            ['type' => 'experience', 'period' => 'Май 2007 — ноябрь 2007',      'title' => 'Врач ультразвуковой диагностики',                 'institution' => 'УЗ «Борисовская центральная районная больница»'],
            ['type' => 'experience', 'period' => 'Ноябрь 2007 — декабрь 2015',  'title' => 'Заведующий',                                      'institution' => 'УЗ «Борисовская больница № 2»'],
            ['type' => 'experience', 'period' => 'Декабрь 2015 — март 2020',    'title' => 'Врач ультразвуковой диагностики',                 'institution' => 'УЗ «Борисовская центральная районная больница»'],
            ['type' => 'experience', 'period' => 'Апрель 2020 — июнь 2021',     'title' => 'Врач ультразвуковой диагностики',                 'institution' => 'ООО «ЛаВита-центр»'],
            ['type' => 'experience', 'period' => 'Июль 2021 — август 2022',     'title' => 'Врач ультразвуковой диагностики, заместитель директора по медицинской части', 'institution' => 'ООО «Клиника в Уручье»'],
            ['type' => 'experience', 'period' => 'Сентябрь 2022 — март 2023',   'title' => 'Врач ультразвуковой диагностики',                 'institution' => 'ООО «Центр магнитно-резонансной томографии Ортоклиник»'],
            ['type' => 'experience', 'period' => 'Апрель 2023 — апрель 2025',   'title' => 'Врач ультразвуковой диагностики',                 'institution' => 'ГУ «Республиканский научно-практический центр неврологии и нейрохирургии»'],
            ['type' => 'experience', 'period' => 'Апрель 2025 — н.в.',          'title' => 'Врач ультразвуковой диагностики',                 'institution' => 'Медицинский центр «Маяк здоровья» ООО «ЗАРГА Медика»'],
            ['type' => 'education',  'period' => '2003–2004', 'title' => 'Интернатура «Терапия»',                                                      'institution' => ''],
            ['type' => 'education',  'period' => '2006',      'title' => 'ПК «Клиническая артрология»',                                                 'institution' => ''],
            ['type' => 'education',  'period' => '2007',      'title' => 'ППГ «Ультразвуковая диагностика»',                                            'institution' => ''],
            ['type' => 'education',  'period' => '2010',      'title' => 'ППГ «Организация здравоохранения»',                                           'institution' => ''],
            ['type' => 'education',  'period' => '2012',      'title' => 'ПК «Эхокардиография в диагностике приобретённых пороков сердца, болезней эндокарда и перикарда»', 'institution' => ''],
            ['type' => 'education',  'period' => '2013',      'title' => 'ПК «Радиационная безопасность»',                                              'institution' => ''],
            ['type' => 'education',  'period' => '2013',      'title' => 'ПК «Основы эхокардиографии»',                                                 'institution' => ''],
            ['type' => 'education',  'period' => '2017',      'title' => 'ПК «Ультразвуковая диагностика патологии сосудов нижних конечностей»',        'institution' => ''],
            ['type' => 'education',  'period' => '2017–2020', 'title' => 'Ординатура «Ультразвуковая диагностика»',                                     'institution' => ''],
            ['type' => 'education',  'period' => '2018',      'title' => 'ПК «Основы управления здравоохранением в Республике Беларусь»',              'institution' => ''],
            ['type' => 'education',  'period' => '2018',      'title' => 'ПК «Медицинская информатика и компьютерные технологии»',                    'institution' => ''],
            ['type' => 'education',  'period' => '2022',      'title' => 'ПК «Ультразвуковое исследование периферических сосудов»',                     'institution' => ''],
        ];

        $makeevaDescription = 'Алеся Викторовна — врач ультразвуковой диагностики первой квалификационной категории с 22-летним стажем работы. В медицинском центре «Маяк Здоровья» выполняет широкий спектр ультразвуковых исследований: УЗИ органов брюшной полости, сосудов (БЦА, ТКДС, артерии и вены конечностей), щитовидной железы, молочных желез, почек, суставов и мягких тканей.';

        $makeeva = Doctor::updateOrCreate(
            ['slug' => Slug::make('Макеева-Алеся-Викторовна')],
            [
                'specialization_id' => $makeevaSpec->id,
                'last_name' => 'Макеева',
                'first_name' => 'Алеся',
                'middle_name' => 'Викторовна',
                'category' => 'first',
                'experience_years' => 22,
                'experience_since' => 2004,
                'patient_age' => 'both',
                'photo' => 'images/doctor-profile.png',
                'description' => $makeevaDescription,
                'education' => $makeevaEducation,
                'status' => 'active',
                'sort_order' => 0,
            ]
        );

        $uziServiceIds = Service::whereHas('direction', fn ($q) => $q->where('name', 'УЗИ'))
            ->orderBy('sort_order')
            ->pluck('id');
        if ($uziServiceIds->isNotEmpty()) {
            $makeeva->services()->sync($uziServiceIds);
        }

        $makeevaReviews = [
            ['Наталья, 70 лет',   '2026-03-02', 5, 'Была сегодня на УЗИ у врача Алеси Викторовны. Всё быстро, чётко, компетентно. Врач внимательный, опытный и быстро обнаружила полип, который не находили другие врачи. Я очень ей признательна за профессиональную работу и необходимые советы. Спасибо!'],
            ['Светлана',          '2026-03-02', 5, 'Огромная благодарность врачу УЗИ Макеевой Алесе Викторовне за профессиональный подход и чуткость. Исследование провели быстро, качественно и, главное, информативно. Врач сразу указала на проблему и дала ценные рекомендации.'],
            ['Иванко Данила',     '2025-11-24', 5, 'Идеальный врач.'],
        ];

        Review::where('doctor_id', $makeeva->id)->delete();
        foreach ($makeevaReviews as [$author, $date, $rating, $text]) {
            Review::create([
                'doctor_id' => $makeeva->id,
                'author_name' => $author,
                'rating' => $rating,
                'text' => $text,
                'status' => 'approved',
                'published_at' => $date,
            ]);
        }

        $makeeva->syncRatingFromReviews();

        $doctors = [
            ['Кузнецов',   'Владимир', 'Сергеевич', 'Проктолог',                      'highest', 12, 'adults',   'doctor-m1.jpg', ['Проктология']],
            ['Смирнова',   'Ольга',    'Игоревна',  'Проктолог',                      'first',   8,  'adults',   'doctor-f1.jpg', ['Проктология']],
            ['Петров',     'Андрей',   'Викторович', 'Проктолог',                      'second',  6,  'adults',   'doctor-m2.jpg', ['Проктология']],
            ['Иванов',     'Пётр',     'Сергеевич', 'Терапевт',                       'first',   11, 'both',     'doctor-m1.jpg', ['Терапия']],
            ['Лебедев',    'Игорь',    'Александрович', 'Терапевт',                   'highest', 16, 'adults',   'doctor-m5.jpg', ['Терапия']],
            ['Сидоров',    'Михаил',   'Андреевич', 'Кардиолог',                      'highest', 14, 'adults',   'doctor-m2.jpg', ['Кардиология']],
            ['Павлов',     'Николай',  'Олегович',  'Кардиолог',                      'first',   7,  'adults',   'doctor-m2.jpg', ['Кардиология']],
            ['Новикова',   'Елена',    'Дмитриевна', 'Невролог',                       'first',   10, 'both',     'doctor-f3.jpg', ['Неврология']],
            ['Орлова',     'Наталья',  'Владимировна', 'Невролог',                    'highest', 13, 'adults',   'doctor-f2.jpg', ['Неврология']],
            ['Денисевич',  'Юлия',     'Александровна', 'Акушер-гинеколог',           'highest', 8,  'adults',   'doctor-f1.jpg', ['Гинекология', 'Лазерная гинекология']],
            ['Белова',     'Ольга',    'Сергеевна', 'Акушер-гинеколог',               'first',   10, 'adults',   'doctor-f1.jpg', ['Гинекология']],
            ['Волкова',    'Ольга',    'Николаевна', 'Эндокринолог',                   'highest', 12, 'adults',   'doctor-f1.jpg', ['Эндокринология']],
            ['Фёдоров',    'Андрей',   'Владимирович', 'Гастроэнтеролог',             'first',   9,  'both',     'doctor-m4.jpg', ['Гастроэнтерология']],
            ['Громов',     'Александр', 'Николаевич', 'Хирург',                         'highest', 15, 'both',     'doctor-m4.jpg', ['Хирургия', 'Лазерная хирургия']],
            ['Морозов',    'Дмитрий',  'Игоревич',  'Уролог',                         'second',  6,  'both',     'doctor-m3.jpg', ['Урология']],
            ['Егорова',    'Мария',    'Сергеевна', 'Флеболог',                       'first',   8,  'adults',   'doctor-f2.jpg', ['Флебология']],
            ['Козлова',    'Анна',     'Викторовна', 'Маммолог',                       'second',  7,  'adults',   'doctor-f2.jpg', ['Маммология']],
            ['Романова',   'Татьяна',  'Ивановна',  'Диетолог',                       'first',   9,  'both',     'doctor-f3.jpg', ['Диетология']],
            ['Зайцева',    'Ирина',    'Олеговна',  'Психолог',                       'first',   8,  'both',     'doctor-f2.jpg', ['Психология']],
            ['Соколов',    'Алексей',  'Петрович',  'Психотерапевт',                  'highest', 14, 'adults',   'doctor-m5.jpg', ['Психотерапия']],
            ['Кравцов',    'Игорь',    'Васильевич', 'Нефролог',                     'first',   9,  'adults',   'doctor-m2.jpg', ['Нефрология']],
            ['Михайлова',  'Светлана', 'Романовна', 'Онколог',                       'highest', 18, 'adults',   'doctor-f3.jpg', ['Онкология']],
            ['Кузьмина',   'Вера',     'Анатольевна', 'Акушер-гинеколог',            'first',   7,  'adults',   'doctor-f1.jpg', ['Лазерная гинекология']],
            ['Бондаренко', 'Елена',    'Павловна', 'Врач функциональной диагностики', 'first',   11, 'adults',   'doctor-f2.jpg', ['Функциональная диагностика']],
            ['Ким',        'Даниил',   'Степанович', 'Уролог',                       'first',   8,  'adults',   'doctor-m3.jpg', ['Урология']],
            ['Тихонов',    'Артём',    'Игоревич', 'Терапевт',                       'second',  5,  'both',     'doctor-m4.jpg', ['Терапия']],
        ];

        $sort = 1;
        foreach ($doctors as [$last, $first, $middle, $specName, $category, $years, $age, $photo, $dirNames]) {
            $spec = Specialization::firstOrCreate(
                ['name' => $specName],
                ['slug' => Slug::make($specName)]
            );

            $slug = Slug::make("{$last}-{$first}-{$middle}");
            $since = (int) date('Y') - $years;

            $doctor = Doctor::updateOrCreate(
                ['slug' => $slug],
                [
                    'specialization_id' => $spec->id,
                    'last_name' => $last,
                    'first_name' => $first,
                    'middle_name' => $middle,
                    'category' => $category,
                    'experience_years' => $years,
                    'experience_since' => $since,
                    'patient_age' => $age,
                    'photo' => "images/doctors/{$photo}",
                    'description' => $this->minimalDescription($first, $middle, $specName, $category, $years),
                    'education' => $this->minimalEducationTimeline($specName, $years, $since),
                    'status' => 'active',
                    'sort_order' => $sort++,
                ]
            );

            $serviceIds = Service::whereHas('direction', fn ($q) => $q->whereIn('name', $dirNames))
                ->orderBy('sort_order')
                ->pluck('id');
            if ($serviceIds->isNotEmpty()) {
                $doctor->services()->sync($serviceIds);
            }

            $this->minimalReviews($doctor, $last);
            $doctor->syncRatingFromReviews();
        }
    }

    private function categoryPhrase(string $category): string
    {
        return match ($category) {
            'highest' => 'высшей квалификационной категории',
            'first' => 'первой квалификационной категории',
            'second' => 'второй квалификационной категории',
            default => '',
        };
    }

    private function roleTitle(string $specName): string
    {
        $s = trim($specName);
        if (str_starts_with(mb_strtolower($s), 'врач')) {
            return $s;
        }

        return 'Врач-'.mb_strtolower(mb_substr($s, 0, 1)).mb_substr($s, 1);
    }

    private function minimalDescription(string $first, string $middle, string $specName, string $category, int $years): string
    {
        $phrase = $this->categoryPhrase($category);
        $role = $this->roleTitle($specName);
        $catPart = $phrase !== '' ? " {$phrase}" : '';

        return "{$first} {$middle} — {$role}{$catPart} с {$years}-летним стажем работы. В медицинском центре «Маяк Здоровья» ведёт приём пациентов, проводит диагностику и подбор тактики лечения в соответствии с клиническими рекомендациями. Уделяет внимание разъяснению диагноза и плану обследований.";
    }

    /**
     * @return list<array{type: string, period: string, title: string, institution: string}>
     */
    private function minimalEducationTimeline(string $specName, int $years, int $sinceYear): array
    {
        $y = (int) date('Y');
        $role = $this->roleTitle($specName);
        $gradEnd = $sinceYear - 1;
        $gradStart = $gradEnd - 5;

        $ordEnd = $sinceYear + 1;
        $hospFrom = $ordEnd + 1;
        $hospTo = min($hospFrom + max(2, $years - 3), $y - 2);
        if ($hospTo < $hospFrom) {
            $hospTo = $hospFrom + 1;
        }
        $clinicFrom = min($hospTo + 1, $y - 1);
        if ($clinicFrom > $y - 1) {
            $clinicFrom = $y - 1;
        }

        return [
            ['type' => 'experience', 'period' => "{$sinceYear}–{$ordEnd}", 'title' => 'Ординатура / интернатура по специальности', 'institution' => 'Белорусский государственный медицинский университет'],
            ['type' => 'experience', 'period' => "{$hospFrom}–{$hospTo}", 'title' => $role, 'institution' => 'УЗ «Городская клиническая больница»'],
            ['type' => 'experience', 'period' => "{$clinicFrom} — н.в.", 'title' => $role, 'institution' => 'Медицинский центр «Маяк здоровья» ООО «ЗАРГА Медика»'],
            ['type' => 'education', 'period' => (string) $gradStart.'–'.(string) $gradEnd, 'title' => 'Факультет «Лечебное дело»', 'institution' => 'Белорусский государственный медицинский университет'],
            ['type' => 'education', 'period' => (string) ($sinceYear + 2), 'title' => 'ПК «Актуальные вопросы клинической практики»', 'institution' => ''],
            ['type' => 'education', 'period' => (string) ($y - 1), 'title' => 'ПК «Организация здравоохранения и качество медицинской помощи»', 'institution' => ''],
        ];
    }

    private function minimalReviews(Doctor $doctor, string $lastName): void
    {
        Review::where('doctor_id', $doctor->id)->delete();

        $templates = [
            ['Анна К.', 'Внимательный специалист, всё объяснил понятно. Рекомендую клинику и доктора.', 5],
            ['Дмитрий', 'Приём прошёл спокойно, назначили обследование без лишнего. Спасибо!', 5],
        ];

        $i = crc32($doctor->slug) % 2;
        $base = $templates[$i];
        $alt = $templates[1 - $i];

        Review::create([
            'doctor_id' => $doctor->id,
            'author_name' => $base[0],
            'rating' => $base[2],
            'text' => $base[1],
            'status' => 'approved',
            'published_at' => now()->subDays(14),
        ]);

        Review::create([
            'doctor_id' => $doctor->id,
            'author_name' => $alt[0] === $base[0] ? 'Марина В.' : $alt[0],
            'rating' => max(4, $alt[2] - 1),
            'text' => 'Хороший врач, грамотный подход. Доктор '.$lastName.' — профессионал.',
            'status' => 'approved',
            'published_at' => now()->subDays(45),
        ]);
    }
}
