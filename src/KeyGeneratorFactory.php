<?php

declare(strict_types=1);

namespace Kynx\Mezzio\Authentication\ApiKey;

use Kynx\ApiKey\KeyGenerator;
use Kynx\ApiKey\KeyGeneratorChain;
use Kynx\ApiKey\KeyGeneratorInterface;
use Kynx\ApiKey\RandomString;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Psr\Container\ContainerInterface;

use function array_map;

/**
 * @psalm-import-type ApiAuthenticationConfig from ConfigProvider
 * @psalm-import-type ApiKeyConfig from ConfigProvider
 */
final class KeyGeneratorFactory
{
    public function __invoke(ContainerInterface $container): KeyGeneratorInterface
    {
        /** @var ApiAuthenticationConfig $config */
        $config          = $container->get('config')['authentication']['api-key'] ?? [];
        $primaryConfig   = $config['primary'] ?? null;
        $fallbackConfigs = $config['fallbacks'] ?? [];

        if ($primaryConfig === null) {
            throw new InvalidConfigException(
                "Primary API key configuration not present in authentication config"
            );
        }

        $primary = $this->getKeyGenerator($primaryConfig);
        if ($fallbackConfigs === []) {
            return $primary;
        }

        $fallbacks = array_map(fn (array $config): KeyGenerator => $this->getKeyGenerator($config), $fallbackConfigs);

        return new KeyGeneratorChain($primary, ...$fallbacks);
    }

    /**
     * @param ApiKeyConfig $config
     */
    private function getKeyGenerator(array $config): KeyGenerator
    {
        $prefix = $config['prefix'] ?? null;

        if ($prefix === null) {
            throw new InvalidConfigException("Prefix not present in authentication config");
        }

        return new KeyGenerator(
            $prefix,
            $config['identifier-length'] ?? KeyGenerator::DEFAULT_IDENTIFIER_LENGTH,
            $config['secret-length'] ?? KeyGenerator::DEFAULT_SECRET_LENGTH,
            new RandomString($config['characters'] ?? RandomString::DEFAULT_CHARACTERS)
        );
    }
}
