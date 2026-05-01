<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Equipment;
use App\Models\PromoSlide;
use App\Models\Promotion;
use App\Models\Vacancy;
use Illuminate\Database\Seeder;

class InitialContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedEquipment();
        $this->seedPromoSlides();
        $this->seedDocuments();
        $this->seedVacancies();
    }

    protected function seedEquipment(): void
    {
        $items = [
            [
                'name' => 'Ультразвуковая система «Philips ClearVue 550» (США) высокого класса',
                'slug' => 'philips-clearvue-550',
                'tag' => 'УЗ-диагностика',
                'kicker' => 'Медицинское оборудование',
                'subtitle' => 'Современная экспертная УЗ-система',
                'summary' => 'Позволяет выполнять широкий спектр ультразвуковых исследований: органы брюшной полости, малые и поверхностно расположенные органы, педиатрия, скелетно-мышечная система, урология.',
                'description' => "Philips ClearVue 550 — ультразвуковая система высокого класса.\n\nПозволяет выполнять широкий спектр ультразвуковых исследований: органы брюшной полости, малые и поверхностно расположенные органы, педиатрия, скелетно-мышечная система, урология, предстательная железа, акушерство и гинекология, сердце, сосуды и транскраниальная доплерография.\n\nВысокая детализация изображения и точность измерений делают этот аппарат универсальным инструментом для постановки диагноза.",
                'image' => 'images/medical_equipment/clearvue550_front.png',
                'card_image' => 'images/medical_equipment/clearvue550_front.png',
                'sort_order' => 10,
                'status' => 'active',
            ],
            [
                'name' => 'Видеокольпоскоп «3MVC LED USB» (Германия) с 3-х ступенчатым увеличением',
                'slug' => '3mvc-led-usb',
                'tag' => 'Гинекология',
                'kicker' => 'Медицинское оборудование',
                'subtitle' => 'Высокоточный видеокольпоскоп',
                'summary' => 'Позволяет врачу вывести на экран в высоком разрешении имеющиеся патологии у пациента при осмотре.',
                'description' => "Видеокольпоскоп 3MVC LED USB — компактный аппарат для современной гинекологии.\n\nПозволяет врачу вывести на экран в высоком разрешении имеющиеся патологии у пациента при осмотре. Три ступени увеличения дают возможность детально изучить состояние тканей.\n\nУдобный USB-интерфейс и LED-подсветка обеспечивают качественное изображение и простоту использования.",
                'image' => 'images/medical_equipment/videokalposkop-1.png',
                'card_image' => 'images/medical_equipment/videokalposkop-1.png',
                'sort_order' => 20,
                'status' => 'active',
            ],
            [
                'name' => 'Фракционный лазер «CO2RE» (США) выделяющий волны двойного лазера',
                'slug' => 'co2re-laser',
                'tag' => 'Лазерные технологии',
                'kicker' => 'Медицинское оборудование',
                'subtitle' => 'Единственный CO2 лазер с 7 вариантами лечения',
                'summary' => 'Единственный CO2 лазер, имеющий 7 вариантов лечения, 5 из которых предназначены для обновления кожи.',
                'description' => "Фракционный лазер CO2RE — это единственный CO2 лазер, имеющий 7 вариантов лечения, 5 из которых предназначены для обновления кожи.\n\nДвойная лазерная волна обеспечивает одновременную обработку поверхностного и глубокого слоёв кожи, что позволяет добиться выраженного омолаживающего эффекта за минимальное количество процедур.\n\nПрименяется при:\n- возрастных изменениях кожи\n- рубцах постакне\n- пигментации\n- мелких морщинах и потере упругости\n\nПроцедуры проводятся в комфортных условиях с применением современных протоколов обезболивания.",
                'image' => 'images/medical_equipment/frakczionnyj-lazer-1-1.png',
                'card_image' => 'images/medical_equipment/frakczionnyj-lazer-1-1.png',
                'sort_order' => 30,
                'status' => 'active',
            ],
            [
                'name' => 'Лазерный аппарат «Mediola Compact» для широкого спектра процедур',
                'slug' => 'mediola-compact',
                'tag' => 'Аппаратные методики',
                'kicker' => 'Медицинское оборудование',
                'subtitle' => 'Универсальный лазерный аппарат',
                'summary' => 'Позволяет проводить хирургические манипуляции в разных направлениях медицинских услуг.',
                'description' => "Mediola Compact — компактный универсальный лазерный аппарат.\n\nПозволяет проводить хирургические манипуляции в разных направлениях медицинских услуг: от косметологии до хирургии.\n\nОсобенности аппарата:\n- высокая точность воздействия\n- минимальная травматичность\n- быстрое восстановление\n- широкий выбор насадок и режимов",
                'image' => 'images/medical_equipment/mediola-compact-4-1.png',
                'card_image' => 'images/medical_equipment/mediola-compact-4-1.png',
                'sort_order' => 40,
                'status' => 'active',
            ],
            [
                'name' => 'Радиоволновой генератор «Surgitron® Dual EMC™ 90» для хирургических процедур',
                'slug' => 'surgitron-dual-emc-90',
                'tag' => 'Радиоволновая хирургия',
                'kicker' => 'Медицинское оборудование',
                'subtitle' => 'Точные атравматичные разрезы',
                'summary' => 'Генератор позволяет врачу-хирургу виртуозно выполнять точные, атравматичные разрезы кожи и тканей при температуре от 38° до 80° С.',
                'description' => "Радиоволновой генератор Surgitron Dual EMC 90 — современный аппарат для хирургических вмешательств.\n\nГенератор позволяет врачу-хирургу виртуозно выполнять точные, атравматичные разрезы кожи и тканей при температуре от 38° до 80° С с абсолютной безопасностью для себя и пациента, обеспечивая великолепный результат.\n\nПреимущества метода:\n- минимальная кровопотеря\n- быстрое заживление\n- отсутствие грубых рубцов\n- безболезненность процедуры",
                'image' => 'images/medical_equipment/generator-radiovolnovoj-40-mgcz-surgitron™-dual-emc-90-1-1.png',
                'card_image' => 'images/medical_equipment/generator-radiovolnovoj-40-mgcz-surgitron™-dual-emc-90-1-1.png',
                'sort_order' => 50,
                'status' => 'active',
            ],
            [
                'name' => 'Ультразвуковая система «Philips Affiniti 50» (США) экспертного класса',
                'slug' => 'philips-affiniti-50',
                'tag' => 'Экспертное УЗИ',
                'kicker' => 'Медицинское оборудование',
                'subtitle' => 'Экспертная УЗ-диагностика',
                'summary' => 'Способен выводить на монитор высокодетализированные изображения для эффективной и точной диагностики.',
                'description' => "Philips Affiniti 50 — ультразвуковая система экспертного класса.\n\nСпособен выводить на монитор высокодетализированные изображения, с которыми диагностика всегда будет максимально эффективной, точной и достоверной.\n\nИспользуется для исследований сердца, сосудов, органов брюшной полости и малого таза. Обеспечивает максимальную диагностическую точность даже в самых сложных клинических ситуациях.",
                'image' => 'images/medical_equipment/uzi-affiniti-50-1-1.png',
                'card_image' => 'images/medical_equipment/uzi-affiniti-50-1-1.png',
                'sort_order' => 60,
                'status' => 'active',
            ],
            [
                'name' => 'Ультразвуковой сканер «VINNO 6» высокого класса',
                'slug' => 'vinno-6',
                'tag' => 'Портативное сканирование',
                'kicker' => 'Медицинское оборудование',
                'subtitle' => 'Портативный УЗ-сканер',
                'summary' => 'Благодаря этому аппарату специалист имеет возможность получить сведения о структуре, размерах и расположении внутренних органов пациента.',
                'description' => "VINNO 6 — портативный ультразвуковой сканер высокого класса.\n\nБлагодаря этому аппарату специалист имеет возможность получить сведения о структуре, размерах и расположении внутренних органов пациента.\n\nПодходит для широкого спектра исследований и удобен в применении благодаря компактности и интуитивному интерфейсу.",
                'image' => 'images/medical_equipment/perenosnoj-skaner-vysokogo-klassa-vinno-6-3-1.png',
                'card_image' => 'images/medical_equipment/perenosnoj-skaner-vysokogo-klassa-vinno-6-3-1.png',
                'sort_order' => 70,
                'status' => 'active',
            ],
            [
                'name' => 'Центрифуга «Medifuge» (США) для проведения процедур с плазмой крови',
                'slug' => 'medifuge',
                'tag' => 'Плазмотерапия',
                'kicker' => 'Медицинское оборудование',
                'subtitle' => 'Подготовка аутологичной плазмы',
                'summary' => 'За 15 минут подготавливает аутологичную плазму для проведения процедур с её применением.',
                'description' => "Medifuge — центрифуга для подготовки аутологичной плазмы крови.\n\nЗа 15 минут подготавливает аутологичную плазму для проведения процедур с её применением.\n\nПлазмолифтинг применяется в:\n- косметологии — омоложение кожи лица и тела\n- ортопедии — лечение суставов\n- гинекологии — восстановительные процедуры\n- трихологии — стимуляция роста волос",
                'image' => 'images/medical_equipment/czentrifuga-1-1.png',
                'card_image' => 'images/medical_equipment/czentrifuga-1-1.png',
                'sort_order' => 80,
                'status' => 'active',
            ],
        ];

        foreach ($items as $item) {
            Equipment::updateOrCreate(['slug' => $item['slug']], $item);
        }
    }

    protected function seedPromoSlides(): void
    {
        // Use up to 5 active promotions for slides
        $promotions = Promotion::query()
            ->where('status', 'active')
            ->latest('id')
            ->limit(5)
            ->get();

        if ($promotions->isEmpty()) {
            // Fallback: 3 placeholder slides
            $fallbacks = [
                ['image' => 'images/banner.png', 'title' => 'Профилактика женского здоровья', 'subtitle' => 'Скидка до 20% на комплекс обследования', 'link_url' => '/promotions', 'sort_order' => 10],
                ['image' => 'images/banner.png', 'title' => 'УЗИ всего тела', 'subtitle' => 'Полное обследование за один визит', 'link_url' => '/promotions', 'sort_order' => 20],
                ['image' => 'images/banner.png', 'title' => 'Лазерная косметология', 'subtitle' => 'Специальные цены на курс процедур', 'link_url' => '/promotions', 'sort_order' => 30],
            ];
            foreach ($fallbacks as $i => $f) {
                PromoSlide::updateOrCreate(
                    ['title' => $f['title']],
                    array_merge($f, ['button_text' => 'Узнать больше', 'is_active' => true])
                );
            }

            return;
        }

        $sort = 10;
        foreach ($promotions as $promo) {
            $img = $promo->image ?: 'images/banner.png';
            PromoSlide::updateOrCreate(
                ['title' => $promo->title],
                [
                    'image' => $img,
                    'subtitle' => $promo->short_description ?? '',
                    'link_url' => '/promotions/'.$promo->slug,
                    'button_text' => 'Подробнее',
                    'sort_order' => $sort,
                    'is_active' => true,
                ]
            );
            $sort += 10;
        }
    }

    protected function seedDocuments(): void
    {
        $docs = [
            ['url' => 'https://license.gov.by/onelicense/174652', 'label' => 'Основной документ', 'title' => 'Лицензия на медицинскую деятельность', 'text' => 'Официальная лицензия, подтверждающая право клиники на оказание медицинских услуг.', 'sort_order' => 10],
            ['url' => 'https://lighthouse.by/dogovor.pdf', 'label' => 'Для пациентов', 'title' => 'Договор публичной оферты', 'text' => 'Условия оказания услуг, порядок взаимодействия и основные положения публичного договора.', 'sort_order' => 20],
            ['url' => 'https://lighthouse.by/politika-konfidenczialnosti/', 'label' => 'Конфиденциальность', 'title' => 'Политика конфиденциальности', 'text' => 'Информация об обработке персональных данных, защите приватности и правилах хранения данных.', 'sort_order' => 30],
            ['url' => 'https://lighthouse.by/pvr-dlya-pacientov.pdf', 'label' => 'Порядок посещения', 'title' => 'Правила внутреннего распорядка для пациентов', 'text' => 'Правила посещения клиники, взаимодействия с персоналом и порядок получения медицинской помощи.', 'sort_order' => 40],
            ['url' => 'https://lighthouse.by/registraciya.pdf', 'label' => 'Регистрация', 'title' => 'Свидетельство о государственной регистрации', 'text' => 'Регистрационный документ, подтверждающий официальную деятельность медицинского центра.', 'sort_order' => 50],
        ];

        foreach ($docs as $doc) {
            Document::updateOrCreate(
                ['title' => $doc['title']],
                array_merge($doc, ['is_active' => true])
            );
        }
    }

    protected function seedVacancies(): void
    {
        $vacancies = [
            'Врач-невролог',
            'Врач-уролог',
            'Врач-терапевт',
            'Врач УЗ-диагностики',
            'Врач-кардиолог',
        ];
        $sort = 10;
        foreach ($vacancies as $title) {
            Vacancy::updateOrCreate(
                ['title' => $title],
                ['sort_order' => $sort, 'is_active' => true]
            );
            $sort += 10;
        }
    }
}
