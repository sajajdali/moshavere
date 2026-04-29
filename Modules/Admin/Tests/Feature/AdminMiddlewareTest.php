<?php

namespace Modules\Admin\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Modules\Admin\Http\Middleware\AdminMiddleware;
use Modules\User\Entities\User;
use Tests\TestCase;

uses(TestCase::class);

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if the AdminMiddleware allows access for users with 'ADMIN_ACCESS' permission.
     *
     * @return void
     */
    public function testAdminAccess()
    {
        // Mocking a user with the 'ADMIN_ACCESS' permission
        $user = factory(User::class)->create();
        $user->givePermissionTo('ADMIN_ACCESS');
        $this->actingAs($user);

        // Create a mock request for testing
        $request = Request::create('/some-admin-route', 'GET');

        // Create an instance of the middleware
        $middleware = new AdminMiddleware();

        // Execute the middleware
        $response = $middleware->handle($request, function ($req) {
            return response('Success', 200);
        });

        // Assert that the middleware allowed access (200 status code)
        $this->assertEquals(200, $response->status());
    }

    /**
     * Test if the AdminMiddleware redirects authenticated non-admin users to the user profile page.
     *
     * @return void
     */
    public function testNonAdminRedirectToUserProfile()
    {
        // Mocking a user without the 'ADMIN_ACCESS' permission
        $user = factory(User::class)->create();
        $this->actingAs($user);

        // Create a mock request for testing
        $request = Request::create('/some-admin-route', 'GET');

        // Create an instance of the middleware
        $middleware = new AdminMiddleware();

        // Execute the middleware
        $response = $middleware->handle($request, function ($req) {
            return response('Success', 200);
        });

        // Assert that the middleware redirected to the user profile route (302 status code)
        $this->assertEquals(302, $response->status());
        $this->assertStringContainsString('profile', $response->headers->get('Location'));
    }

    /**
     * Test if the AdminMiddleware aborts with a 404 for unauthenticated users.
     *
     * @return void
     */
    public function testUnauthenticatedUsersReceive404()
    {
        // Create a mock request for testing
        $request = Request::create('/some-admin-route', 'GET');

        // Create an instance of the middleware
        $middleware = new AdminMiddleware();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $middleware->handle($request, function ($req) {
            return response('Success', 200);
        });
    }
}
