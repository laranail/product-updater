<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Product\Updater\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\Product\Updater\Tests\TestCase;
use Simtabi\Laranail\Package\Tools\Testing\NamingScope;
use Simtabi\Laranail\Package\Tools\Testing\AssertsRegisteredNames;
use Simtabi\Laranail\Package\Tools\Support\Routing\BareRouteNameAliases;

/**
 * The opt-in API route's name, read from the live router with the API enabled.
 *
 * Ownership is judged from `src/` (package-tools v0.1.3 defaults to the package root, which in this
 * repository also holds `vendor/`).
 */
final class ApiRouteNamingTest extends TestCase
{
    use AssertsRegisteredNames;

    protected function setUp(): void
    {
        parent::setUp();

        BareRouteNameAliases::forgetWarnings();
    }

    public function test_the_health_route_is_vendor_scoped(): void
    {
        $scope = NamingScope::for(
            'laranail/product-updater',
            'Simtabi\\Laranail\\Product\\Updater\\',
            basePath: dirname(__DIR__, 2) . '/src',
        );

        $this->assertContains('laranail-product-updater.health', $this->assertRouteNamesScoped($scope, atLeast: 1));
        $this->assertFalse(Route::has('product-updater.health'));
    }

    public function test_the_deprecated_health_name_still_resolves(): void
    {
        $this->assertDeprecatedRouteNamesResolve(['product-updater.health' => 'laranail-product-updater.health']);
    }

    public function test_the_deprecated_health_name_is_announced(): void
    {
        $notices = [];
        set_error_handler(static function (int $level, string $message) use (&$notices): bool {
            $notices[] = $message;

            return true;
        }, E_USER_DEPRECATED);

        try {
            $url = route('product-updater.health');
        } finally {
            restore_error_handler();
        }

        $this->assertSame(route('laranail-product-updater.health'), $url);
        $this->assertStringContainsString('laranail-product-updater.health', implode("\n", $notices));
    }

    public function test_the_health_endpoint_still_answers(): void
    {
        $this->getJson(route('laranail-product-updater.health'))->assertJsonStructure(['status', 'checks']);
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('product-updater.api.enabled', true);
    }
}
