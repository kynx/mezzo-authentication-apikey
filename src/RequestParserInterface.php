<?php

declare(strict_types=1);

namespace Kynx\Mezzio\Authentication\ApiKey;

use Psr\Http\Message\ServerRequestInterface;

interface RequestParserInterface
{
    /**
     * Returns API key from request if present, otherwise returns null
     */
    public function getApiKey(ServerRequestInterface $request): ?string;
}
