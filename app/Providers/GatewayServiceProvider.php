<?php

namespace App\Providers;

use App\Contracts\PaymentServiceContract;
use App\Gateways\GatewayOne;
use App\Gateways\GatewayTwo;
use App\Services\PaymentService;
use Illuminate\Support\ServiceProvider;

class GatewayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentServiceContract::class, function ($app) {
            $gateways = [
                'gateway_one' => new GatewayOne(),
                'gateway_two' => new GatewayTwo(),
            ];

            return new PaymentService($gateways);
        });
    }
}
