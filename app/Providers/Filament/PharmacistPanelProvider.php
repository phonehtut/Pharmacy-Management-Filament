<?php

namespace App\Providers\Filament;

use App\Filament\Cashier\Pages\SaleHistory;
use App\Filament\Pharmacist\Widgets\ExpiringStocksTable;
use App\Filament\Pharmacist\Widgets\PharmacistOverview;
use App\Filament\Resources\Purchases\PurchaseResource;
use App\Filament\Resources\Stocks\StockResource;
use App\Filament\Resources\StockTransfers\StockTransferResource;
use App\Models\Branch;
use AzGasim\FilamentUnsavedChangesModal\FilamentUnsavedChangesModalPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PharmacistPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('pharmacist')
            ->path('pharmacist')
            ->login()
            ->spa()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->databaseNotifications()
            ->resources([
                StockResource::class,
                PurchaseResource::class,
                StockTransferResource::class,
            ])
            ->discoverResources(in: app_path('Filament/Pharmacist/Resources'), for: 'App\Filament\Pharmacist\Resources')
            ->discoverPages(in: app_path('Filament/Pharmacist/Pages'), for: 'App\Filament\Pharmacist\Pages')
            ->pages([
                Dashboard::class,
                SaleHistory::class,
            ])
//            ->discoverWidgets(in: app_path('Filament/Pharmacist/Widgets'), for: 'App\Filament\Pharmacist\Widgets')
            ->widgets([
                PharmacistOverview::class,
                ExpiringStocksTable::class,
                //                AccountWidget::class,
            ])
            ->sidebarCollapsibleOnDesktop(true)
            ->tenant(Branch::class, slugAttribute: 'slug', ownershipRelationship: 'branch')
            ->tenantMenu(true)
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->unsavedChangesAlerts()
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentUnsavedChangesModalPlugin::make()
            ]);
    }
}
