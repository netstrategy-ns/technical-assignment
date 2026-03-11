<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Inertia\Response as InertiaResponse;
use Tests\TestCase;

abstract class AdminControllersTestCase extends TestCase
{
    use RefreshDatabase;

    protected function adminUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge(['is_admin' => true], $attributes));
    }

    protected function makeInertiaRequest(string $path, array $query = []): Request
    {
        $request = Request::create($path, 'GET', $query);
        $request->headers->set('X-Inertia', 'true');

        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    protected function extractInertiaPayload(InertiaResponse $response, Request $request): array
    {
        $httpResponse = $response->toResponse($request);

        $this->assertSame(200, $httpResponse->getStatusCode());

        $payload = json_decode($httpResponse->getContent(), true);
        $this->assertNotNull($payload);
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('component', $payload);
        $this->assertArrayHasKey('props', $payload);
        $this->assertIsArray($payload['props']);

        return $payload;
    }

    protected function assertInertiaPayloadIndexShape(array $payload, string $expectedComponent, string $expectedResource): void
    {
        $this->assertSame($expectedComponent, $payload['component']);
        $this->assertSame($expectedResource, $payload['props']['resource']);
        $this->assertArrayHasKey('rows', $payload['props']);
        $this->assertArrayHasKey('sort', $payload['props']);
        $this->assertArrayHasKey('filters', $payload['props']);
        $this->assertArrayHasKey('columns', $payload['props']);
    }
}
