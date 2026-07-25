<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginPagePresentationTest extends TestCase
{
    public function test_login_page_hides_navigation_and_has_accessible_login_controls(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('class="site-header"', false)
            ->assertSee('id="remember"', false)
            ->assertSee('for="remember"', false)
            ->assertSee('Remember me')
            ->assertSee('id="login-password"', false)
            ->assertSee('type="password"', false)
            ->assertSee('data-password-toggle', false)
            ->assertSee('aria-label="Show password"', false)
            ->assertSee('StockManager Developed by Muhammad Ali Nawaz');
    }
}
