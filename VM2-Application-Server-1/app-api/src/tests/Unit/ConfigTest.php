<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function test_it_returns_values_and_defaults(): void
    {
        $config = new Config(['APP_ENV' => 'production']);

        $this->assertSame('production', $config->get('APP_ENV'));
        $this->assertNull($config->get('MISSING'));
        $this->assertSame('fallback', $config->get('MISSING', 'fallback'));
    }

    public function test_it_casts_booleans(): void
    {
        $config = new Config(['A' => 'true', 'B' => '1', 'C' => 'yes', 'D' => 'false', 'E' => '0']);

        $this->assertTrue($config->bool('A'));
        $this->assertTrue($config->bool('B'));
        $this->assertTrue($config->bool('C'));
        $this->assertFalse($config->bool('D'));
        $this->assertFalse($config->bool('E'));
        $this->assertFalse($config->bool('MISSING'));
        $this->assertTrue($config->bool('MISSING', true));
    }

    public function test_it_casts_integers(): void
    {
        $config = new Config(['DB_PORT' => '3306']);

        $this->assertSame(3306, $config->int('DB_PORT'));
        $this->assertSame(8083, $config->int('MISSING', 8083));
    }

    public function test_loading_a_missing_env_file_does_not_fail(): void
    {
        $config = Config::load(sys_get_temp_dir() . '/app-api-no-such-directory');

        // APP_URL is not exported by phpunit.xml, so nothing can supply it.
        $this->assertNull($config->get('APP_URL'));
    }

    public function test_real_environment_variables_win_over_the_file(): void
    {
        // phpunit.xml exports APP_ENV=testing for the whole suite.
        $config = Config::load(sys_get_temp_dir() . '/app-api-no-such-directory');

        $this->assertSame('testing', $config->get('APP_ENV'));
    }
}
