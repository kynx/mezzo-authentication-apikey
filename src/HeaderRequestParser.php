<?php

declare(strict_types=1);

namespace Kynx\Mezzio\Authentication\ApiKey;

use Kynx\ApiKey\ApiKey;
use Kynx\ApiKey\KeyGeneratorInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class HeaderRequestParser implements RequestParserInterface
{
    public function __construct(private KeyGeneratorInterface $keyGenerator, private string $apiKeyHeader)
    {
    }

    public function getApiKey(ServerRequestInterface $request): ?ApiKey
    {
        if ($request->hasHeader($this->apiKeyHeader)) {
            return $this->keyGenerator->parse($request->getHeaderLine($this->apiKeyHeader));
        }

        return null;
    }
}
