<?php

declare(strict_types=1);

namespace KynxTest\Mezzio\Authentication\ApiKey;

use Kynx\ApiKey\KeyGenerator;
use Kynx\ApiKey\KeyGeneratorChain;
use Kynx\Mezzio\Authentication\ApiKey\KeyGeneratorFactory;
use Mezzio\Authentication\Exception\InvalidConfigException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Kynx\Mezzio\Authentication\ApiKey\KeyGeneratorFactory
 */
final class KeyGeneratorFactoryTest extends TestCase
{
    public function testMissingPrimaryKeyConfigurationThrowsException(): void
    {
        $factory   = new KeyGeneratorFactory();
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', []],
            ]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Primary API key configuration not present');
        $factory($container);
    }

    public function testMissingPrefixThrowsException(): void
    {
        $factory   = new KeyGeneratorFactory();
        $config    = [
            'authentication' => [
                'api-key' => [
                    'primary' => [],
                ],
            ],
        ];
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
            ]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Prefix not present');
        $factory($container);
    }

    public function testPrimaryOnlyReturnsConfiguredInstance(): void
    {
        $expected  = 'test';
        $factory   = new KeyGeneratorFactory();
        $config    = [
            'authentication' => [
                'api-key' => [
                    'primary' => [
                        'prefix' => $expected,
                    ],
                ],
            ],
        ];
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
            ]);

        $instance = $factory($container);
        self::assertInstanceOf(KeyGenerator::class, $instance);
        $actual = $instance->generate()->getPrefix();
        self::assertStringStartsWith($expected, $actual);
    }

    public function testWithFallbacksReturnsKeyGeneratorChain(): void
    {
        $factory   = new KeyGeneratorFactory();
        $config    = [
            'authentication' => [
                'api-key' => [
                    'primary'   => [
                        'prefix' => 'new',
                    ],
                    'fallbacks' => [
                        [
                            'prefix' => 'old',
                        ],
                    ],
                ],
            ],
        ];
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
            ]);

        $instance = $factory($container);
        self::assertInstanceOf(KeyGeneratorChain::class, $instance);
    }
}
