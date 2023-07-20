<?php

declare(strict_types=1);

namespace Kynx\Mezzio\Authentication\ApiKey;

use Mezzio\Authentication\Exception\InvalidConfigException;
use Psr\Container\ContainerInterface;

final class HeaderRequestParserFactory
{
    public function __invoke(ContainerInterface $container): HeaderRequestParser
    {
        /** @var string|null $headerName */
        $headerName = $container->get('config')['authentication']['api-key']['header-name'] ?? null;

        if ($headerName === null) {
            throw new InvalidConfigException("Header name not present in authentication config");
        }

        return new HeaderRequestParser($headerName);
    }
}
