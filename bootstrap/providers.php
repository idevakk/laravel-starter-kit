<?php

use App\Providers\AppServiceProvider;
use App\Providers\DynamicMailConfigServiceProvider;
use App\Providers\Filament\DashPanelProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    DynamicMailConfigServiceProvider::class,
    DashPanelProvider::class,
    FortifyServiceProvider::class,
];
