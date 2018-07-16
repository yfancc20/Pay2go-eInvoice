<?php

namespace Yfancc20\Pay2goInvoice;

use Illuminate\Support\ServiceProvider;

class Pay2goInvoiceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/pay2goinv.php' => config_path('pay2goinv.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
