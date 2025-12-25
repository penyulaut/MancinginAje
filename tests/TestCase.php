<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure any lingering DB transactions are rolled back to avoid
        // "current transaction is aborted" errors when tests run together.
        try {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        } catch (\Throwable $e) {
            // best-effort cleanup; do not fail tests because of this
        }
    }

    protected function tearDown(): void
    {
        // Final safety: roll back any remaining transactions
        try {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        parent::tearDown();
    }
}
