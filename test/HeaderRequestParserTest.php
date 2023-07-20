<?php

declare(strict_types=1);

namespace KynxTest\Mezzio\Authentication\ApiKey;

use Kynx\Mezzio\Authentication\ApiKey\HeaderRequestParser;
use PHPUnit\Framework\MockObject\Stub; // phpcs:ignore
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class HeaderRequestParserTest extends TestCase
{
    private const API_KEY = 'test-api-key';

    public function testGetApiKeyReturnsKeyFromHeader(): void
    {
        $parser  = new HeaderRequestParser('X-API-Key');
        $request = $this->getRequest();

        $actual = $parser->getApiKey($request);
        self::assertSame(self::API_KEY, $actual);
    }

    public function testGetApiKeyReturnsNullForMissingHeader(): void
    {
        $parser  = new HeaderRequestParser('X-Missing-Header');
        $request = $this->getRequest();

        $actual = $parser->getApiKey($request);
        self::assertNull($actual);
    }

    private function getRequest(): ServerRequestInterface&Stub
    {
        $request = $this->createStub(ServerRequestInterface::class);

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
