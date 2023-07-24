# kynx/mezzio-authentication-apikey

[![Continuous Integration](https://github.com/kynx/mezzo-authentication-apikey/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/kynx/mezzo-authentication-apikey/actions/workflows/continuous-integration.yml)

This package provides a [Mezzio Authentication] adapter for logging in users with a well-structured API key.

For an overview of using authentication middleware in your Mezzio application, see the `mezzio/mezzio-authentication`
[Introduction].

To use this package to authenticate users you will need two things:

* A [User Repository] that handles checking the user credentials against your persistent storage
* Your API key prefix. This string is prepended to the keys you generate to make them easy to identify

Choose your prefix carefully. It can be used by tools like [GitHub's Secret Scanner] to find leaked keys. It should also
provide enough information to help users and support identify the correct key to use. In the examples here we use
`myco_sandbox` so show both the origin (`myco`) and the environment (`sandbox`) the key is for. If you've ever
integrated with Stripe, this pattern will be familiar.

Mezzio Authentication provides two user repositories out-the-box: `Htpasswd` and `PdoDatabase`. In the examples here we
will use `PdoDatabase`. See the [PDO Configuration] documentation for the additional steps needed to set that up.

## Installation

```commandline
composer require kynx/mezzio-authentication-apikey
```

## Configuration

The configuration either can be stored in a file under the `/config/autoload/` folder or in your application or module's `ConfigProvider`.

Create a `config/autoload/api.global.php` and add the following:

```php
<?php

use Kynx\Mezzio\Authentication\ApiKey\ApiKeyAuthentication;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserRepository\PdoDatabase;
use Mezzio\Authentication\UserRepositoryInterface;

return [
    'authentication' => [
        'api-key' => [
            'primary' => [
                'prefix' => 'myco_sandbox',
            ],
        ],
    ],
    'dependencies'   => [
        'factories' => [
            AuthenticationInterface::class => ApiKeyAuthentication::class,
            UserRepositoryInterface::class => PdoDatabase::class,
        ],
    ],
];
```

Alternately, if your application already has a `ConfigProvider` class, add the configuration above to that.

## Piping vs Routing

If your entire application needs to be protected by API keys, add the authentication middleware to your
`config/pipeline.php`, before the `DispatchMiddleware` is piped:

```php
$app->pipe(Mezzio\Authentication\AuthenticationMiddleware::class);
```

If only some routes need protecting, add the middleware to individual route pipelines in `config/routes.php`:

```php
$app->get('/api/users', [
    Mezzio\Authentication\AuthenticationMiddleware::class,
    Api\Action\Users::class
], 'api.users');
```

## Changing the API Key Header

By default this package expects to find the API key in an `X-API-Key` request header. To use a different header name,
add it to the configuration:

```php
return [
    'authentication' => [
        'api-key' => [
            'header-name' => 'X-MyCo-Key',
            'primary' => [
                'prefix' => 'myco_sandbox',
            ],
        ],
    ],
    // rest of config
];
```

## Advanced Configuration

This package uses [kynx/api-key-generator] to parse and generate API keys. That provides a number of options for setting
the lengths of the keys and the characters used - see that package's documentation for full details. The defaults should
be fine for the majority of use cases, but if needed you can change them in your configuration:

```php
use Kynx\ApiKey\RandomString;

return [
    'authentication' => [
        'api-key' => [
            'primary' => [
                'prefix'            => 'myco_sandbox',
                'identifier-length' => 8,
                'secret-length'     => 36,
                'characters'        => RandomString::DEFAULT_CHARACTERS,
            ],
        ],
    ],
    // rest of config
];
```

Please note that it is **not** recommended to change the characters used! See the notes on the [default configuration]
for the rationale.

## Fallback configurations

If you have already issued keys to your users and make changes to the `primary` key configuration, existing keys will
stop working. To continue supporting the old keys, add the old settings to the `fallbacks` configuration:

```php
use Kynx\ApiKey\RandomString;

return [
    'authentication' => [
        'api-key' => [
            'primary'   => [
                'prefix' => 'newco_sandbox',
            ],
            'fallbacks' => [
                [
                    'prefix' => 'myco_sandbox',
                ],
            ],
        ],
    ],
    // rest of config
];
```

## Working with keys

[kynx/api-key-generator] provides a `KeyGenerator` for generating well-formed API keys. Only keys generated by the same
key generator as used when parsing the header will be able to authenticate.

### Generating a key

```php

use Kynx\ApiKey\KeyGeneratorInterface;

require 'vendor/autoload.php'

$container    = require 'config/container.php';
$keyGenerator = $container->get(KeyGeneratorInterface::class);
$apiKey       = $keyGenerator->generate(); 
echo $apiKey->getKey();
```

This will output an API key to share with your users. Something like:

```text
myco_sandbox_Ez2FJvSAeRbLmLXYTyIzi8zSqxky6IXJ0VKxpqC8_69e51b54
```

### Storing a key

The generated key contains methods to extract the content you will need to persist in your user repository:

* `ApiKey::getIdentifier()` returns the random string of characters used to look up the user. It's a bit like a
  username.
* `ApiKey::getSecret()` returns the long "password" for the key

The identifier should be stored un-hashed and in a case sensitive manner. For instance, in MySQL / MariaDB you would
store it in a `VARBINARY` column with a unique index.

The secret **must** be hashed before storage. Use PHP's [password_hash()] for this.

To support rolling over API keys, you should store the keys in a separate table with a foreign key linking them to the
user record. That way a user can have multiple keys. An example for MySQL might be:

```sql
CREATE TABLE apikeys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    identifier VARBINARY(255) NOT NULL,
    hash VARCHAR(255) NOT NULL,
    created DATETIME NOT NULL,
    expires DATETIME NOT NULL,
    UNIQUE INDEX apikeys_identifier_udx (identifier),
    CONSTRAINT apikeys_user_fk FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE 
);
```

[Mezzio Authentication]: https://docs.mezzio.dev/mezzio-authentication/
[Introduction]: https://docs.mezzio.dev/mezzio-authentication/v1/intro/
[User Repository]: https://docs.mezzio.dev/mezzio-authentication/v1/user-repository/
[GitHub's Secret Scanner]: https://docs.github.com/en/code-security/secret-scanning/about-secret-scanning
[PDO Configuration]: https://docs.mezzio.dev/mezzio-authentication/v1/user-repository/#pdo-configuration
[kynx/api-key-generator]: https://github.com/kynx/api-key-generator
[default configuration]: https://github.com/kynx/api-key-generator#defaults
[password_hash()]: https://www.php.net/password_hash
