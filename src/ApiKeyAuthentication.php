<?php

declare(strict_types=1);

namespace Kynx\Mezzio\Authentication\ApiKey;

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ApiKeyAuthentication implements AuthenticationInterface
{
    public function __construct(
        private RequestParserInterface $requestParser,
        private UserRepositoryInterface $userRepository,
        private ResponseFactoryInterface $responseFactory
    ) {
    }

    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        $apiKey = $this->requestParser->getApiKey($request);
        if ($apiKey === null) {
            return null;
        }

        return $this->userRepository->authenticate($apiKey);
    }

    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory->createResponse(401);
    }
}
