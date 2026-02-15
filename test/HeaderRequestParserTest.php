<?php

declare(strict_types=1);

namespace KynxTest\Mezzio\Authentication\ApiKey;

use Kynx\ApiKey\ApiKey;
use Kynx\ApiKey\KeyGeneratorInterface;
use Kynx\Mezzio\Authentication\ApiKey\HeaderRequestParser;
use PHPUnit\Framework\MockObject\Stub; // phpcs:ignore
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class HeaderRequestParserTest extends TestCase
{
    private const API_KEY = 'test-api-key';

    public function testGetApiKeyReturnsKeyFromHeader(): void
    {
        $expected     = new ApiKey('test', 'aaaaaaaa', 'aaaaaaaaaaaaaaaaaaa');
        $keyGenerator = $this->createMock(KeyGeneratorInterface::class);
        $parser       = new HeaderRequestParser($keyGenerator, 'X-API-Key');
        $request      = $this->getRequest();
        $keyGenerator->expects(self::once())
            ->method('parse')
            ->with(self::API_KEY)
            ->willReturn($expected);

        $actual = $parser->getApiKey($request);
        self::assertSame($expected, $actual);
    }

    public function testGetApiKeyReturnsNullForMissingHeader(): void
    {
        $request      = $this->getRequest();
        $keyGenerator = self::createStub(KeyGeneratorInterface::class);
        $parser       = new HeaderRequestParser($keyGenerator, 'X-Missing-Header');

        $actual = $parser->getApiKey($request);
        self::assertNull($actual);
    }

    private function getRequest(): ServerRequestInterface&Stub
    {
        $request = self::createStub(ServerRequestInterface::class);

        $request->method('hasHeader')
            ->willReturnMap([
                ['X-API-Key', true],
                ['X-Missing-Header', false],
            ]);
        $request->method('getHeaderLine')
            ->willReturnMap([
                ['X-API-Key', self::API_KEY],
                ['X-Missing-Header', ''],
            ]);

        return $request;
    }
}
