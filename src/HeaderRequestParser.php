<?php

declare(strict_types=1);

namespace Kynx\Mezzio\Authentication\ApiKey;

use Psr\Http\Message\ServerRequestInterface;

final readonly class HeaderRequestParser implements RequestParserInterface
{
    public function __construct(private string $headerName)
    {
    }

    public function getApiKey(ServerRequestInterface $request): ?string
    {
        if ($request->hasHeader($this->headerName)) {
            return $request->getHeaderLine($this->headerName);
        }

        return null;
    }
}
