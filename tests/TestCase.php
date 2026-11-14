<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep relative booking fixtures independent from the weekday on
        // which PHPUnit happens to run. Monday + one day is an operating day;
        // tests that explicitly exercise weekends still use their own dates.
        Carbon::setTestNow(Carbon::create(2026, 10, 19, 10, 0, 0, 'Asia/Jakarta'));

        // Feature tests exercise authorization and business rules without
        // manufacturing a browser CSRF token for every request. Production
        // and browser middleware remain enabled; this test-only override is
        // never registered by the application runtime.
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $connection = (string) config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if ($database !== 'yza_automotive_laravel_test') {
            throw new RuntimeException(
                "Refusing to run tests against non-test database: {$database}"
            );
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}
