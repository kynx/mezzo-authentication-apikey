<?php

declare(strict_types=1);

namespace KynxTest\Mezzio\Authentication\ApiKey;

use Kynx\ApiKey\ApiKey;
use Kynx\Mezzio\Authentication\ApiKey\ApiKeyAuthentication;
use Kynx\Mezzio\Authentication\ApiKey\RequestParserInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers \Kynx\Mezzio\Authentication\ApiKey\ApiKeyAuthentication
 */
final class ApiKeyAuthenticationTest extends TestCase
{
    private RequestParserInterface&Stub $requestParser;
    private UserRepositoryInterface&MockObject $userRepository;
    private ResponseFactoryInterface&MockObject $responseFactory;
    private ApiKeyAuthentication $authentication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestParser   = $this->createStub(RequestParserInterface::class);
        $this->userRepository  = $this->createMock(UserRepositoryInterface::class);
        $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $this->authentication = new ApiKeyAuthentication(
            $this->requestParser,
            $this->userRepository,
            $this->responseFactory
        );
    }

    public function testAuthenticateMissingKeyDoesNotAuthenticateAgainstUserRepository(): void
    {
        $this->requestParser->method('getApiKey')
            ->willReturn(null);
        $this->userRepository->expects(self::never())
            ->method('authenticate');

        $actual = $this->authentication->authenticate($this->createStub(ServerRequestInterface::class));
        self::assertNull($actual);
    }

    public function testAuthenticateAuthenticatesAgainstUserRepository(): void
    {
        $expected   = $this->createStub(UserInterface::class);
        $identifier = 'aaaaaaaa';
        $secret     = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $apiKey     = new ApiKey('foo', $identifier, $secret);
        $this->requestParser->method('getApiKey')
            ->willReturn($apiKey);
        $this->userRepository->method('authenticate')
            ->with($identifier, $secret)
            ->willReturn($expected);

        $actual = $this->authentication->authenticate($this->createStub(ServerRequestInterface::class));
        self::assertSame($expected, $actual);
    }

    public function testUnauthorizedResponseReturns401Response(): void
    {
        $expected = $this->createStub(ResponseInterface::class);
        $this->responseFactory->method('createResponse')
            ->with(401)
            ->willReturn($expected);

        $actual = $this->authentication->unauthorizedResponse($this->createStub(ServerRequestInterface::class));
        self::assertSame($expected, $actual);
    }
}
