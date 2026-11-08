<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginBackToLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_offers_a_link_back_to_the_landing_page()
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Back to Home')
            ->assertSee(route('home'), false);
    }

    public function test_the_back_link_leaves_the_login_form_and_keeps_it_usable()
    {
        $response = $this->get(route('login'));
        $html = $response->getContent();

        // The home link must be an anchor, not something that submits the login form.
        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="'.preg_quote(route('home'), '/').'"[^>]*>\s*<svg/i',
            $html,
            'Expected a standalone home anchor with the back arrow icon.'
        );
        $this->assertStringNotContainsString('Back to Home</button>', $html);
        $this->assertStringContainsString('action="'.route('login').'"', $html);
    }

    public function test_landing_page_is_reachable_without_authentication()
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kid', false);
    }

    public function test_login_page_does_not_leak_an_output_buffer()
    {
        $before = ob_get_level();
        $this->get(route('login'))->assertOk();
        $this->assertSame(
            $before,
            ob_get_level(),
            'The login view opened an output buffer it never closed.'
        );
    }
}
