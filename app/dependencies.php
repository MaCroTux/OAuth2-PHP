<?php
declare(strict_types=1);
use App\Infrastructure\Persistence\AccessToken\InMemoryAccessTokenRepository;
use App\Infrastructure\Persistence\AuthCode\InMemoryAuthCodeRepository;
use App\Infrastructure\Persistence\Client\InMemoryClientRepository;
use App\Infrastructure\Persistence\RefreshToken\InMemoryRefreshTokenRepository;
use App\Infrastructure\Persistence\Scope\InMemoryScopeRepository;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use DI\ContainerBuilder;
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

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        ClientRepositoryInterface::class => function (ContainerInterface $c) {
            return new InMemoryClientRepository();
        },
        ScopeRepositoryInterface::class => function (ContainerInterface $c) {
            return new InMemoryScopeRepository();
        },
        AccessTokenRepositoryInterface::class => function (ContainerInterface $c) {
            return new InMemoryAccessTokenRepository();
        },
        AuthCodeRepositoryInterface::class => function (ContainerInterface $c) {
            return new InMemoryAuthCodeRepository();
        },
        RefreshTokenRepositoryInterface::class => function (ContainerInterface $c) {
            return new InMemoryRefreshTokenRepository();
        },
        UserRepositoryInterface::class => function (ContainerInterface $c) {
            return new InMemoryUserRepository();
        },
    ]);
};
