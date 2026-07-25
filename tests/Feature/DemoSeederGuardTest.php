<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DemoSeederGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_rejects_demo_seeding_even_when_opted_in(): void
    {
        $this->setApplicationEnvironment('production');
        $this->configureDemoSeeder(
            true,
            'StrongLocalPassword!2026'
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Demo seeding is allowed only in local and testing environments.'
        );

        $this->runDemoSeeder();
    }

    public function test_staging_rejects_demo_seeding(): void
    {
        $this->setApplicationEnvironment('staging');
        $this->configureDemoSeeder(
            true,
            'StrongLocalPassword!2026'
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Demo seeding is allowed only in local and testing environments.'
        );

        $this->runDemoSeeder();
    }

    public function test_local_requires_explicit_opt_in(): void
    {
        $this->setApplicationEnvironment('local');
        $this->configureDemoSeeder(
            false,
            'StrongLocalPassword!2026'
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Set ALLOW_DEMO_SEEDING=true to create demo records.'
        );

        $this->runDemoSeeder();
    }

    public function test_local_rejects_an_empty_password(): void
    {
        $this->setApplicationEnvironment('local');
        $this->configureDemoSeeder(true, '');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'DEMO_PASSWORD must contain at least 12 characters.'
        );

        $this->runDemoSeeder();
    }

    public function test_local_rejects_a_weak_password(): void
    {
        $this->setApplicationEnvironment('local');
        $this->configureDemoSeeder(true, 'too-short');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'DEMO_PASSWORD must contain at least 12 characters.'
        );

        $this->runDemoSeeder();
    }

    public function test_local_with_explicit_strong_credentials_succeeds(): void
    {
        $this->setApplicationEnvironment('local');
        $this->configureDemoSeeder(
            true,
            'StrongLocalPassword!2026'
        );

        $this->runDemoSeeder();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertNotNull($admin->email_verified_at);
    }

    public function test_testing_environment_has_no_hardcoded_password_fallback(): void
    {
        $this->setApplicationEnvironment('testing');
        $this->configureDemoSeeder(true, null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'DEMO_PASSWORD must contain at least 12 characters.'
        );

        $this->runDemoSeeder();
    }

    private function runDemoSeeder(): void
    {
        app(DatabaseSeeder::class)->run();
    }

    private function configureDemoSeeder(
        bool $allowed,
        ?string $password
    ): void {
        config()->set([
            'stockmanager.allow_demo_seeding' => $allowed,
            'stockmanager.demo_name' => 'StockManager Admin',
            'stockmanager.demo_email' => 'admin@example.com',
            'stockmanager.demo_password' => $password,
        ]);
    }

    private function setApplicationEnvironment(
        string $environment
    ): void {
        $this->app->instance('env', $environment);
        config()->set('app.env', $environment);
    }
}
