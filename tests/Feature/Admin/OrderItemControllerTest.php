<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\OrderItemController;

class OrderItemControllerTest extends AdminControllersTestCase
{
    public function test_index_can_be_called_directly_and_returns_expected_payload(): void
    {
        $request = $this->makeInertiaRequest('/admin/order-items');
        $response = (new OrderItemController())->index($request);

        $payload = $this->extractInertiaPayload($response, $request);
        $this->assertInertiaPayloadIndexShape($payload, 'admin/order-items/Index', 'order-items');
        $this->assertSame(24, $payload['props']['rows']['per_page']);
    }
}
