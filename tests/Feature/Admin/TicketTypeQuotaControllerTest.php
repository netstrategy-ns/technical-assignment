<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\TicketTypeQuotaController;

class TicketTypeQuotaControllerTest extends AdminControllersTestCase
{
    public function test_index_can_be_called_directly_and_returns_expected_payload(): void
    {
        $request = $this->makeInertiaRequest('/admin/ticket-type-quotas');
        $response = (new TicketTypeQuotaController())->index($request);

        $payload = $this->extractInertiaPayload($response, $request);
        $this->assertInertiaPayloadIndexShape($payload, 'admin/ticket-type-quotas/Index', 'ticket-type-quotas');
        $this->assertSame(24, $payload['props']['rows']['per_page']);
    }
}
