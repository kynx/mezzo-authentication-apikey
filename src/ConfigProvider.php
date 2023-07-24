<?php

declare(strict_types=1);

namespace Kynx\Mezzio\Authentication\ApiKey;

use Kynx\ApiKey\KeyGeneratorInterface;

/**
 * @psalm-type ApiKeyConfig=array{
 *   prefix?: string,
 *   secret-length?: int,
 *   identifier-length?: int,
 *   characters?: string
 * }
 * @psalm-type ApiAuthenticationConfig=array{
 *   header-name: string,
 *   primary?: ApiKeyConfig,
 *   fallbacks?: array<array-key, ApiKeyConfig>
 * }
 */
class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'authentication' => $this->getAuthenticationConfig(),
            'dependencies'   => $this->getDependencies(),
        ];
    }

    /**
     * @return array{api-key: ApiAuthenticationConfig}
     */
    public function getAuthenticationConfig(): array
    {
        return [
            'api-key' => [
                'header-name' => 'X-API-Key',
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                ApiKeyAuthentication::class   => ApiKeyAuthenticationFactory::class,
                RequestParserInterface::class => HeaderRequestParserFactory::class,
                KeyGeneratorInterface::class  => KeyGeneratorFactory::class,
            ],
        ];
    }
}
