<?php

namespace App\Providers;

use Filament\Forms\Components\Select as FilamentSelect;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentSelect::configureUsing(function (FilamentSelect $select): void {
            $select->native(false);
        });

        Model::preventLazyLoading(! $this->app->isProduction());

        Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation): void {
            info(sprintf(
                'Lazy loading detected: relation [%s] on model [%s].',
                $relation,
                $model::class
            ));
        });
    }
}
