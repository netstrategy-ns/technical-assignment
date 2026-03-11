<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\TicketTypeController;

class TicketTypeControllerTest extends AdminControllersTestCase
{
    public function test_index_can_be_called_directly_and_returns_expected_payload(): void
    {
        $request = $this->makeInertiaRequest('/admin/ticket-types');
        $response = (new TicketTypeController())->index($request);

        $payload = $this->extractInertiaPayload($response, $request);
        $this->assertInertiaPayloadIndexShape($payload, 'admin/ticket-types/Index', 'ticket-types');
        $this->assertSame(24, $payload['props']['rows']['per_page']);
    }
}
