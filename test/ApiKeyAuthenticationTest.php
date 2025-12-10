<?php

declare(strict_types=1);

namespace KynxTest\Mezzio\Authentication\ApiKey;

use Kynx\ApiKey\ApiKey;
use Kynx\Mezzio\Authentication\ApiKey\ApiKeyAuthentication;
use Kynx\Mezzio\Authentication\ApiKey\RequestParserInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(ApiKeyAuthentication::class)]
final class ApiKeyAuthenticationTest extends TestCase
{
    private RequestParserInterface&Stub $requestParser;
    private UserRepositoryInterface&Stub $userRepository;
    private ResponseFactoryInterface&Stub $responseFactory;
    private ApiKeyAuthentication $authentication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestParser   = self::createStub(RequestParserInterface::class);
        $this->userRepository  = self::createStub(UserRepositoryInterface::class);
        $this->responseFactory = self::createStub(ResponseFactoryInterface::class);

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
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::never())
            ->method('authenticate');

        $authentication = new ApiKeyAuthentication(
            $this->requestParser,
            $userRepository,
            $this->responseFactory
        );

        $actual = $authentication->authenticate(self::createStub(ServerRequestInterface::class));
        self::assertNull($actual);
    }

    public function testAuthenticateAuthenticatesAgainstUserRepository(): void
    {
        $expected   = self::createStub(UserInterface::class);
        $identifier = 'aaaaaaaa';
        $secret     = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $apiKey     = new ApiKey('foo', $identifier, $secret);
        $this->requestParser->method('getApiKey')
            ->willReturn($apiKey);
        $this->userRepository->method('authenticate')
            ->with($identifier, $secret)
            ->willReturn($expected);

        $actual = $this->authentication->authenticate(self::createStub(ServerRequestInterface::class));
        self::assertSame($expected, $actual);
    }

    public function testUnauthorizedResponseReturns401Response(): void
    {
        $expected = self::createStub(ResponseInterface::class);
        $this->responseFactory->method('createResponse')
            ->with(401)
            ->willReturn($expected);

        $actual = $this->authentication->unauthorizedResponse(self::createStub(ServerRequestInterface::class));
        self::assertSame($expected, $actual);
    }
}
