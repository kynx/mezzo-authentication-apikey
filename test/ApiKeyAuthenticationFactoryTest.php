<?php

declare(strict_types=1);

namespace KynxTest\Mezzio\Authentication\ApiKey;

use Kynx\ApiKey\ApiKey;
use Kynx\Mezzio\Authentication\ApiKey\ApiKeyAuthenticationFactory;
use Kynx\Mezzio\Authentication\ApiKey\RequestParserInterface;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @uses \Kynx\Mezzio\Authentication\ApiKey\ApiKeyAuthentication
 *
 * @covers \Kynx\Mezzio\Authentication\ApiKey\ApiKeyAuthenticationFactory
 */
final class ApiKeyAuthenticationFactoryTest extends TestCase
{
    public function testMissingRequestParserThrowsException(): void
    {
        $factory   = new ApiKeyAuthenticationFactory();
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturnMap([
                [RequestParserInterface::class, false],
                [UserRepositoryInterface::class, false],
                [ResponseFactoryInterface::class, false],
            ]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Request parser not found in container');
        $factory($container);
    }

    public function testMissingUserRepositoryThrowsException(): void
    {
        $factory   = new ApiKeyAuthenticationFactory();
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturnMap([
                [RequestParserInterface::class, true],
                [UserRepositoryInterface::class, false],
                [ResponseFactoryInterface::class, false],
            ]);
        $container->method('get')
            ->willReturnMap([
                [RequestParserInterface::class, $this->createStub(RequestParserInterface::class)],
            ]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('User repository not found in container');
        $factory($container);
    }

    public function testMissingResponseFactoryThrowsException(): void
    {
        $factory   = new ApiKeyAuthenticationFactory();
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturnMap([
                [RequestParserInterface::class, true],
                [UserRepositoryInterface::class, true],
                [ResponseFactoryInterface::class, false],
            ]);
        $container->method('get')
            ->willReturnMap([
                [RequestParserInterface::class, $this->createStub(RequestParserInterface::class)],
                [UserRepositoryInterface::class, $this->createStub(UserRepositoryInterface::class)],
            ]);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Response factory not found in container');
        $factory($container);
    }

    public function testInvokeReturnsConfiguredInstance(): void
    {
        $factory = new ApiKeyAuthenticationFactory();

        $identifier = 'aaaaaaaa';
        $secret     = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $apiKey     = new ApiKey('foo', $identifier, $secret);
        $user       = $this->createStub(UserInterface::class);
        $response   = $this->createStub(ResponseInterface::class);

        $requestParser = $this->createMock(RequestParserInterface::class);
        $requestParser->method('getApiKey')
            ->willReturn($apiKey);
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('authenticate')
            ->with($identifier, $secret)
            ->willReturn($user);
        $responseFactory = $this->createStub(ResponseFactoryInterface::class);
        $responseFactory->method('createResponse')
            ->willReturn($response);

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturnMap([
                [RequestParserInterface::class, true],
                [UserRepositoryInterface::class, true],
                [ResponseFactoryInterface::class, true],
            ]);
        $container->method('get')
            ->willReturnMap([
                [RequestParserInterface::class, $requestParser],
                [UserRepositoryInterface::class, $userRepository],
                [ResponseFactoryInterface::class, $responseFactory],
            ]);

        $request = $this->createStub(ServerRequestInterface::class);

        $instance      = $factory($container);
        $authenticated = $instance->authenticate($request);
        self::assertSame($user, $authenticated);
        $unauthorised = $instance->unauthorizedResponse($request);
        self::assertSame($response, $unauthorised);
    }
}
