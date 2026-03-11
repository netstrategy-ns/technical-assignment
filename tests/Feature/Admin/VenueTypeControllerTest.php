<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\VenueTypeController;

class VenueTypeControllerTest extends AdminControllersTestCase
{
    public function test_index_can_be_called_directly_and_returns_expected_payload(): void
    {
        $request = $this->makeInertiaRequest('/admin/venue-types');
        $response = (new VenueTypeController())->index($request);

        $payload = $this->extractInertiaPayload($response, $request);
        $this->assertInertiaPayloadIndexShape($payload, 'admin/venue-types/Index', 'venue-types');
        $this->assertSame(24, $payload['props']['rows']['per_page']);
    }
}
