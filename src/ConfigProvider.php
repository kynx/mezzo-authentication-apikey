<?php

declare(strict_types=1);

namespace Kynx\Mezzio\Authentication\ApiKey;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'authentication' => $this->getAuthenticationConfig(),
            'dependencies'   => $this->getDependencies(),
        ];
    }

    public function getAuthenticationConfig(): array
    {
        return [
            'api-key' => [
                'request-parser' => HeaderRequestParser::class,
                'header-name'    => 'X-API-Key',
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                ApiKeyAuthentication::class => ApiKeyAuthenticationFactory::class,
                HeaderRequestParser::class  => HeaderRequestParserFactory::class,
            ],
        ];
    }
}
