<?php

declare(strict_types=1);

namespace KynxTest\Mezzio\Authentication\ApiKey;

use Kynx\Mezzio\Authentication\ApiKey\ConfigProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kynx\Mezzio\Authentication\ApiKey\ConfigProvider
 */
final class ConfigProviderTest extends TestCase
{
    public function testInvokeReturnsConfig(): void
    {
        $configProvider = new ConfigProvider();
        $actual         = $configProvider();
        self::assertArrayHasKey('authentication', $actual);
        self::assertArrayHasKey('dependencies', $actual);
    }

    #[Depends('testInvokeReturnsConfig')]
    public function testInvokeReturnsApiKeyConfig(): void
    {
        $configProvider = new ConfigProvider();
        $actual         = $configProvider()['authentication'];
        self::assertIsArray($actual);
        self::assertArrayHasKey('api-key', $actual);
    }

    #[Depends('testInvokeReturnsConfig')]
    public function testInvokeReturnsFactories(): void
    {
        $configProvider = new ConfigProvider();
        $actual         = $configProvider()['dependencies'];
        self::assertIsArray($actual);
        self::assertArrayHasKey('factories', $actual);
    }
}
