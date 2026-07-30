<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Collection;
use App\Support\Sorter;
use App\Support\StringHelper;
use App\Support\Validator;
use LegacyTimer;
use PHPUnit\Framework\TestCase;

/**
 * Characterisation tests for the legacy helpers.
 *
 * They pin down the behaviour the code has on PHP 7.4, which is what makes the
 * PHP 8 upgrade measurable: after the upgrade these tests are the first thing
 * the Jenkins pipeline reports as broken, before anything reaches a server.
 *
 * Every case is cross-referenced with MIGRATION.md.
 */
class LegacySupportTest extends TestCase
{
    /**
     * Incompatibility #1 - curly brace string offsets.
     */
    public function test_the_string_helper_builds_a_short_code(): void
    {
        $this->assertSame('ACW', StringHelper::shortCode('app-company-website'));
        $this->assertSame('AA', StringHelper::shortCode('app-api'));
        $this->assertSame('AUD', StringHelper::shortCode('app-user-dashboard'));
    }

    public function test_the_string_helper_formats_labels_and_php_branches(): void
    {
        $this->assertSame('App User Dashboard', StringHelper::label('app-user-dashboard'));
        $this->assertSame('7.4', StringHelper::majorMinor('7.4.33'));
        $this->assertSame('8.3', StringHelper::majorMinor('8.3'));
    }

    /**
     * Incompatibility #2 - create_function().
     */
    public function test_the_sorter_orders_rows_by_a_field(): void
    {
        $rows = [
            ['server_key' => 'vm3'],
            ['server_key' => 'vm1'],
            ['server_key' => 'vm2'],
        ];

        $this->assertSame(
            ['vm1', 'vm2', 'vm3'],
            array_column(Sorter::byField($rows, 'server_key'), 'server_key')
        );
    }

    public function test_the_sorter_groups_rows_by_a_field(): void
    {
        $grouped = Sorter::groupBy([
            ['server_key' => 'vm2', 'name' => 'app-api'],
            ['server_key' => 'vm2', 'name' => 'app-user-dashboard'],
            ['server_key' => 'vm3', 'name' => 'app-crm'],
        ], 'server_key');

        $this->assertCount(2, $grouped['vm2']);
        $this->assertCount(1, $grouped['vm3']);
    }

    /**
     * Incompatibility #3 - each().
     */
    public function test_the_collection_summarises_values(): void
    {
        $rows = [
            ['result' => 'success'],
            ['result' => 'failed'],
            ['result' => 'success'],
        ];

        $this->assertSame(['success' => 2, 'failed' => 1], Collection::summarise($rows, 'result'));
    }

    public function test_the_collection_averages_a_numeric_column(): void
    {
        $rows = [
            ['duration_seconds' => 100],
            ['duration_seconds' => 50],
            ['duration_seconds' => null],
        ];

        $this->assertSame(75.0, Collection::average($rows, 'duration_seconds'));
        $this->assertSame(0.0, Collection::average([], 'duration_seconds'));
    }

    /**
     * Incompatibility #4 - PHP 4 style constructor.
     *
     * On PHP 8 the constructor is no longer called and this test fails, which is
     * exactly the signal the pipeline needs.
     */
    public function test_the_legacy_timer_initialises_itself(): void
    {
        $timer = new LegacyTimer();

        $this->assertTrue($timer->isInitialised(), 'The PHP 4 style constructor did not run.');
        $this->assertGreaterThanOrEqual(0.0, $timer->elapsed());
    }

    /**
     * Incompatibility #6 - null passed to internal string functions.
     */
    public function test_the_validator_accepts_a_valid_payload(): void
    {
        $validator = new Validator();

        $valid = $validator->validate(
            ['application' => 'app-api', 'result' => 'success'],
            ['application' => ['required', 'max:120'], 'result' => ['required', 'in:success,failed']]
        );

        $this->assertTrue($valid);
        $this->assertSame([], $validator->errors());
    }

    public function test_the_validator_reports_missing_and_invalid_fields(): void
    {
        $validator = new Validator();

        $valid = $validator->validate(
            ['result' => 'exploded'],
            ['application' => ['required'], 'result' => ['required', 'in:success,failed']]
        );

        $this->assertFalse($valid);
        $this->assertArrayHasKey('application', $validator->errors());
        $this->assertArrayHasKey('result', $validator->errors());
    }

    public function test_the_validator_enforces_maximum_lengths(): void
    {
        $validator = new Validator();

        $this->assertFalse($validator->validate(
            ['notes' => str_repeat('a', 20)],
            ['notes' => ['max:10']]
        ));
    }
}
