<?php

declare(strict_types=1);

namespace Kynx\Mezzio\Authentication\ApiKey;

use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\UserRepositoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class ApiKeyAuthenticationFactory
{
    public function __invoke(ContainerInterface $container): ApiKeyAuthentication
    {
        $requestParser   = $container->has(RequestParserInterface::class)
            ? $container->get(RequestParserInterface::class)
            : null;
        $userRepository  = $container->has(UserRepositoryInterface::class)
            ? $container->get(UserRepositoryInterface::class)
            : null;
        $responseFactory = $container->has(ResponseFactoryInterface::class)
            ? $container->get(ResponseFactoryInterface::class)
            : null;

        if ($requestParser === null) {
            throw new InvalidConfigException("Request parser not found in container");
        }
        if ($userRepository === null) {
            throw new InvalidConfigException("User repository not found in container");
        }
        if ($responseFactory === null) {
            throw new InvalidConfigException("Response factory not found in container");
        }

        return new ApiKeyAuthentication(
            $requestParser,
            $userRepository,
            $responseFactory
        );
    }
}
