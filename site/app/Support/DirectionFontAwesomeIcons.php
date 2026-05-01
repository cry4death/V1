<?php

namespace App\Support;

/**
 * Пресеты иконок Font Awesome (класс без префикса fas — в шаблонах используется class="fas …").
 */
final class DirectionFontAwesomeIcons
{
    /**
     * @return array<string, string> class => подпись
     */
    public static function selectOptions(): array
    {
        return [
            'fa-stethoscope' => 'Стетоскоп (терапия)',
            'fa-user-doctor' => 'Врач',
            'fa-pills' => 'Таблетки / эндокринология',
            'fa-heart-pulse' => 'Пульс / функциональная диагностика',
            'fa-heartbeat' => 'Сердцебиение / кардиология',
            'fa-brain' => 'Мозг / неврология',
            'fa-procedures' => 'Процедуры / проктология',
            'fa-syringe' => 'Шприц / хирургия',
            'fa-wand-magic-sparkles' => 'Лазер',
            'fa-wave-square' => 'УЗИ',
            'fa-hand-holding-medical' => 'Флебология',
            'fa-utensils' => 'ЖКТ / диетология',
            'fa-vial' => 'Анализы / нефрология',
            'fa-ribbon' => 'Лента / онкология',
            'fa-heart' => 'Сердце / маммология',
            'fa-head-side-virus' => 'Психология',
            'fa-comments' => 'Психотерапия',
            'fa-kit-medical' => 'Аптечка',
            'fa-tooth' => 'Стоматология',
            'fa-eye' => 'Офтальмология',
            'fa-ear-listen' => 'ЛОР',
            'fa-bone' => 'Кости / ортопедия',
            'fa-dna' => 'Генетика / лаборатория',
            'fa-x-ray' => 'Рентген / визуализация',
        ];
    }
}
