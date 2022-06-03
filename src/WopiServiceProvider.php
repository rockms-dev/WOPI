<?php

namespace MS\Wopi;

use MS\Wopi\Components\Frame;
use MS\Wopi\Contracts\AbstractDocumentManager;
use MS\Wopi\Contracts\ConfigRepositoryInterface;
use MS\Wopi\Contracts\WopiInterface;
use MS\Wopi\Http\Requests\WopiRequest;
use MS\Wopi\Services\Discovery;
use MS\Wopi\Services\ProofValidator;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WopiServiceProvider extends PackageServiceProvider
{
    public function packageRegistered()
    {
        $this->app->singleton(
            ConfigRepositoryInterface::class,
            $this->app['config']['wopi.config_repository']
        );

        $this->app->singleton(WopiInterface::class, $this->app['config']['wopi.wopi_implementation']);

        $this->app->bind(AbstractDocumentManager::class, $this->app['config']['wopi.document_manager']);

        $this->app->bind(WopiRequest::class, $this->app['config']['wopi.wopi_request']);

        $this->app->bind(Discovery::class);

        $this->app->bind(ProofValidator::class);
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('wopi')
            ->hasMigration('create_files_table')
            ->hasRoute('wopi')
            ->hasViews('wopi')
            ->hasViewComponent('wopi', Frame::class)
            ->hasConfigFile();
    }

    public function bootingPackage()
    {
        $this->publishes([
            __DIR__.'/Services/SampleDBDocumentManager.php' => app_path('Services/DBDocumentManager.php')
        ], 'wopi-document-manager');

        $this->publishes([
            __DIR__.'/Models/File.php' => app_path('Models/File.php')
        ], 'wopi-file-model');
    }
}
