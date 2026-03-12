<?php

namespace Tests\Unit\Services;

use App\Contracts\GatewayContract;
use App\DTOs\PaymentData;
use App\DTOs\TransactionResult;
use App\Exceptions\PaymentFailedException;
use App\Models\Gateway;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentData $paymentData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentData = new PaymentData(1000, 'Test', 'test@test.com', '5569000000006063', '010');
    }

    public function test_uses_first_gateway_on_success(): void
    {
        Gateway::create(['name' => 'gateway_one', 'priority' => 1, 'is_active' => true]);
        Gateway::create(['name' => 'gateway_two', 'priority' => 2, 'is_active' => true]);

        $mockGw1 = $this->createMock(GatewayContract::class);
        $mockGw1->method('getName')->willReturn('gateway_one');
        $mockGw1->expects($this->once())->method('authenticate');
        $mockGw1->expects($this->once())->method('createTransaction')
            ->willReturn(new TransactionResult('ext-1', 'paid'));

        $mockGw2 = $this->createMock(GatewayContract::class);
        $mockGw2->method('getName')->willReturn('gateway_two');
        $mockGw2->expects($this->never())->method('createTransaction');

        $service = new PaymentService(['gateway_one' => $mockGw1, 'gateway_two' => $mockGw2]);
        $result = $service->process($this->paymentData);

        $this->assertEquals('ext-1', $result->externalId);
        $this->assertEquals('gateway_one', $service->getUsedGatewayName());
    }

    public function test_falls_back_to_second_gateway_on_first_failure(): void
    {
        Gateway::create(['name' => 'gateway_one', 'priority' => 1, 'is_active' => true]);
        Gateway::create(['name' => 'gateway_two', 'priority' => 2, 'is_active' => true]);

        $mockGw1 = $this->createMock(GatewayContract::class);
        $mockGw1->method('getName')->willReturn('gateway_one');
        $mockGw1->method('createTransaction')->willThrowException(new \Exception('GW1 failed'));

        $mockGw2 = $this->createMock(GatewayContract::class);
        $mockGw2->method('getName')->willReturn('gateway_two');
        $mockGw2->method('createTransaction')
            ->willReturn(new TransactionResult('ext-2', 'paid'));

        $service = new PaymentService(['gateway_one' => $mockGw1, 'gateway_two' => $mockGw2]);
        $result = $service->process($this->paymentData);

        $this->assertEquals('ext-2', $result->externalId);
        $this->assertEquals('gateway_two', $service->getUsedGatewayName());
    }

    public function test_throws_when_all_gateways_fail(): void
    {
        Gateway::create(['name' => 'gateway_one', 'priority' => 1, 'is_active' => true]);
        Gateway::create(['name' => 'gateway_two', 'priority' => 2, 'is_active' => true]);

        $mockGw1 = $this->createMock(GatewayContract::class);
        $mockGw1->method('getName')->willReturn('gateway_one');
        $mockGw1->method('createTransaction')->willThrowException(new \Exception('GW1 failed'));

        $mockGw2 = $this->createMock(GatewayContract::class);
        $mockGw2->method('getName')->willReturn('gateway_two');
        $mockGw2->method('createTransaction')->willThrowException(new \Exception('GW2 failed'));

        $service = new PaymentService(['gateway_one' => $mockGw1, 'gateway_two' => $mockGw2]);

        $this->expectException(PaymentFailedException::class);
        $service->process($this->paymentData);
    }

    public function test_skips_inactive_gateways(): void
    {
        Gateway::create(['name' => 'gateway_one', 'priority' => 1, 'is_active' => false]);
        Gateway::create(['name' => 'gateway_two', 'priority' => 2, 'is_active' => true]);

        $mockGw1 = $this->createMock(GatewayContract::class);
        $mockGw1->method('getName')->willReturn('gateway_one');
        $mockGw1->expects($this->never())->method('createTransaction');

        $mockGw2 = $this->createMock(GatewayContract::class);
        $mockGw2->method('getName')->willReturn('gateway_two');
        $mockGw2->method('createTransaction')
            ->willReturn(new TransactionResult('ext-2', 'paid'));

        $service = new PaymentService(['gateway_one' => $mockGw1, 'gateway_two' => $mockGw2]);
        $result = $service->process($this->paymentData);

        $this->assertEquals('ext-2', $result->externalId);
    }
}
