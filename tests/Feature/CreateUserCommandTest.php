<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_a_staff_user_without_printing_the_password(): void
    {
        $password = 'StrongStaffPassword!2026';

        $this->artisan('stockmanager:create-user')
            ->expectsQuestion('Name', 'Inventory Staff')
            ->expectsQuestion('Email', 'staff@example.com')
            ->expectsQuestion(
                'Password (minimum 12 characters)',
                $password
            )
            ->expectsChoice(
                'Role',
                'staff',
                ['admin', 'staff']
            )
            ->expectsOutput(
                'Created staff account for staff@example.com.'
            )
            ->doesntExpectOutput($password)
            ->assertSuccessful();

        $user = User::query()
            ->where('email', 'staff@example.com')
            ->firstOrFail();

        $this->assertTrue($user->hasRole('staff'));
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotSame($password, $user->password);
    }

    public function test_command_rejects_a_duplicate_email(): void
    {
        Role::findOrCreate('admin', 'web');

        User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->artisan('stockmanager:create-user')
            ->expectsQuestion('Name', 'Duplicate Admin')
            ->expectsQuestion('Email', 'admin@example.com')
            ->expectsQuestion(
                'Password (minimum 12 characters)',
                'StrongAdminPassword!2026'
            )
            ->expectsChoice(
                'Role',
                'admin',
                ['admin', 'staff']
            )
            ->assertFailed();

        $this->assertSame(
            1,
            User::query()
                ->where('email', 'admin@example.com')
                ->count()
        );
    }

    public function test_command_rejects_a_password_shorter_than_twelve_characters(): void
    {
        $this->artisan('stockmanager:create-user')
            ->expectsQuestion('Name', 'Weak Password User')
            ->expectsQuestion('Email', 'weak@example.com')
            ->expectsQuestion(
                'Password (minimum 12 characters)',
                'too-short'
            )
            ->expectsChoice(
                'Role',
                'staff',
                ['admin', 'staff']
            )
            ->assertFailed();

        $this->assertDatabaseMissing('users', [
            'email' => 'weak@example.com',
        ]);
    }
}
