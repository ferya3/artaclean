<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /*
         * The PHP suite exercises routes, Livewire components and business
         * logic — not the asset pipeline. Left switched on, every test that
         * renders a page dies with "Vite manifest not found" unless someone has
         * run the bundler first, which on CI nobody had: the test job installs
         * Composer packages and runs the suite, and the front end is built in a
         * separate job on a separate runner. That is why 31 of 84 tests failed
         * there while all 84 passed on any machine that happened to have a
         * public/build lying around.
         *
         * UrlSchemeTest, which asserts on the generated asset URLs themselves,
         * switches it back on and supplies its own manifest.
         */
        $this->withoutVite();
    }
}
