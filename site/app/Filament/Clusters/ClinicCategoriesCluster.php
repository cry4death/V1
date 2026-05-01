<?php

namespace App\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ClinicCategoriesCluster extends Cluster
{
    protected static ?string $clusterBreadcrumb = 'Услуги';

    protected static string|UnitEnum|null $navigationGroup = 'Врачи и услуги';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Услуги';

    protected static ?string $title = 'Услуги';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
}
