<?php

namespace Gridbase\BillsCore;

use Gridbase\BillsCore\Services\Dgii\DgiiApiService;
use Gridbase\BillsCore\Services\Dgii\DgiiAuthService;
use Gridbase\BillsCore\Services\Dgii\EcfManagerService;
use Gridbase\BillsCore\Services\Dgii\XmlBuilderService;
use Gridbase\BillsCore\Services\Dgii\XmlSignatureService;
use Gridbase\BillsCore\Services\DgiiPrevalidatorService;
use Gridbase\BillsCore\Services\DgiiReportBuilderService;
use Gridbase\BillsCore\Services\DgiiRncLookupService;
use Gridbase\BillsCore\Services\DocumentTotalsCalculator;
use Illuminate\Support\ServiceProvider;

class BillsCoreServiceProvider extends ServiceProvider
{
    public const VERSION = '2.4.0-ecf';
    public const ENGINE_NAME = 'Gridbase Bills Core';

    public function register(): void
    {
        $this->app->singleton(DocumentTotalsCalculator::class, fn () => new DocumentTotalsCalculator());
        $this->app->singleton(XmlSignatureService::class, fn () => new XmlSignatureService());
        $this->app->singleton(XmlBuilderService::class, fn () => new XmlBuilderService());
        $this->app->singleton(DgiiApiService::class, fn () => new DgiiApiService());

        $this->app->singleton(DgiiAuthService::class, function ($app) {
            return new DgiiAuthService($app->make(XmlSignatureService::class));
        });

        $this->app->singleton(EcfManagerService::class, function ($app) {
            return new EcfManagerService(
                $app->make(XmlBuilderService::class),
                $app->make(XmlSignatureService::class),
                $app->make(DgiiAuthService::class),
                $app->make(DgiiApiService::class),
            );
        });

        $this->app->singleton(DgiiPrevalidatorService::class, fn () => new DgiiPrevalidatorService());

        $this->app->singleton(DgiiReportBuilderService::class, function ($app) {
            return new DgiiReportBuilderService($app->make(DgiiPrevalidatorService::class));
        });

        $this->app->singleton(DgiiRncLookupService::class, fn () => new DgiiRncLookupService());

        if (class_exists(\App\Services\BillsCore\AcademicIssuerConfig::class)) {
            $this->app->singleton(
                \Gridbase\BillsCore\Contracts\IssuerConfigInterface::class,
                fn () => new \App\Services\BillsCore\AcademicIssuerConfig()
            );
        }
    }

    public function boot(): void
    {
        // Powered by Gridbase Bills Core
    }
}
