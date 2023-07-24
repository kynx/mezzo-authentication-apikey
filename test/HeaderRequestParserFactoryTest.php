<?php

declare(strict_types=1);

namespace KynxTest\Mezzio\Authentication\ApiKey;

use Kynx\ApiKey\ApiKey;
use Kynx\ApiKey\KeyGeneratorInterface;
use Kynx\Mezzio\Authentication\ApiKey\HeaderRequestParserFactory;
use Mezzio\Authentication\Exception\InvalidConfigException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @uses \Kynx\Mezzio\Authentication\ApiKey\HeaderRequestParser
 *
 * @covers \Kynx\Mezzio\Authentication\ApiKey\HeaderRequestParserFactory
 */
final class HeaderRequestParserFactoryTest extends TestCase
{
    public function testInvokeMissingHeaderNameConfigThrowsException(): void
    {
        $factory   = new HeaderRequestParserFactory();
        $container = $this->createStub(ContainerInterface::class);

        self::expectException(InvalidConfigException::class);
        $factory($container);
    }

    public function testInvokeReturnsConfiguredInstance(): void
    {
        $expected = new ApiKey('test', 'aaaaaaaa', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
        $factory  = new HeaderRequestParserFactory();

        $config       = [
            'authentication' => [
                'api-key' => [
                    'header-name' => 'x-api-key',
                ],
            ],
        ];
        $keyGenerator = $this->createStub(KeyGeneratorInterface::class);
        $container    = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['config', $config],
                [KeyGeneratorInterface::class, $keyGenerator],
            ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('hasHeader')
            ->willReturn(true);
        $request->method('getHeaderLine')
            ->willReturn('foo');
        $keyGenerator->method('parse')
            ->willReturn($expected);

        $instance = $factory($container);
        $actual   = $instance->getApiKey($request);
        self::assertSame($expected, $actual);
    }
}
