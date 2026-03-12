<?php

namespace Database\Seeders;

use App\Models\Gateway;
use Illuminate\Database\Seeder;

class GatewaySeeder extends Seeder
{
    public function run(): void
    {
        Gateway::firstOrCreate(
            ['name' => 'gateway_one'],
            ['is_active' => true, 'priority' => 1]
        );

        Gateway::firstOrCreate(
            ['name' => 'gateway_two'],
            ['is_active' => true, 'priority' => 2]
        );
    }
}
