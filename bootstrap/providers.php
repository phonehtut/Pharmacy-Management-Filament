<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\CashierPanelProvider;
use App\Providers\Filament\PharmacistPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    CashierPanelProvider::class,
    PharmacistPanelProvider::class,
];
