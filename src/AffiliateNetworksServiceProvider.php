<?php
namespace FireItSoft\AffiliateNetworks;

use Illuminate\Support\ServiceProvider;

class AffiliateNetworksServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([
            \FireItSoft\AffiliateNetworks\Commands\AffiliateManager ::class,
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Connection::class, function ($app) {
            return new AffiliateNetworksManager();
        });
      //  $this->app->alias('AffiliateNetworksManager', AffiliateNetworksManager::class);
    }
    
    public function provides()
    {
        return [
            'AffiliateNetworksManager'
        ];
    }    
       
    
}
