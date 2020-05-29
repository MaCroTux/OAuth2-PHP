<?php
declare(strict_types=1);
use App\Infrastructure\Persistence\AccessToken\JsonFileAccessTokenRepository;
use App\Infrastructure\Persistence\AuthCode\JsonFileAuthCodeRepository;
use App\Infrastructure\Persistence\Client\JsonFileClientRepository;
use App\Infrastructure\Persistence\RefreshToken\JsonFileRefreshTokenRepository;
use App\Infrastructure\Persistence\Scope\JsonFileScopeRepository;
use App\Infrastructure\Persistence\User\JsonFileUserRepository;
use DI\ContainerBuilder;
use Jajo\JSONDB;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return static function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => static function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        ClientRepositoryInterface::class => static function (ContainerInterface $c) {
            /** @var JSONDB $db */
            $db = $c->get(JSONDB::class);

            return new JsonFileClientRepository($db);
        },
        ScopeRepositoryInterface::class => static function (ContainerInterface $c) {
            /** @var JSONDB $db */
            $db = $c->get(JSONDB::class);

            return new JsonFileScopeRepository($db);
        },
        AccessTokenRepositoryInterface::class => static function (ContainerInterface $c) {
            /** @var JSONDB $db */
            $db = $c->get(JSONDB::class);

            return new JsonFileAccessTokenRepository($db);
        },
        AuthCodeRepositoryInterface::class => static function (ContainerInterface $c) {
            /** @var JSONDB $db */
            $db = $c->get(JSONDB::class);

            return new JsonFileAuthCodeRepository($db);
        },
        RefreshTokenRepositoryInterface::class => static function (ContainerInterface $c) {
            /** @var JSONDB $db */
            $db = $c->get(JSONDB::class);

            return new JsonFileRefreshTokenRepository($db);
        },
        UserRepositoryInterface::class => static function (ContainerInterface $c) {
            /** @var JSONDB $db */
            $db = $c->get(JSONDB::class);

            return new JsonFileUserRepository($db);
        },
        JSONDB::class => static function (ContainerInterface $c) {
            return new JSONDB( '/tmp' );
        }
    ]);
};
