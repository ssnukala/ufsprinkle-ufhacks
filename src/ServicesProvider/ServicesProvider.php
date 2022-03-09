<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\UfHacks\ServicesProvider;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use UserFrosting\Sprinkle\Account\Log\UserActivityDatabaseHandler;
use UserFrosting\Sprinkle\Core\Log\MixedFormatter;
use UserFrosting\Sprinkle\UfHacks\Log\UserActivityProcessor;

/**
 * UserFrosting core services provider.
 *
 * Registers core services for UserFrosting, such as config, database, asset manager, translator, etc.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ServicesProvider
{
    /**
     * Register UserFrosting's core services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and psr-container.
     */
    public function register(ContainerInterface $container)
    {
        /*
         * Debug logging with Monolog.
         *
         * Extend this service to push additional handlers onto the 'debug' log stack.
         *
         * @return \Monolog\Logger
         */
        $container['debugLogger'] = function ($c) {
            $logger = new Logger('debug');
            $hostserver = env('HOST_SERVER', 'localhost');
            if ($hostserver == 'fargate') {
                $logFile = 'php://stdout'; // 'php://stderr'; 
            } else {
                $filename = $c->config['logfiles.custom.debug'] ? $c->config['logfiles.custom.debug'] : $c->config['logfiles.default'];
                $logFile = $c->locator->findResource('log://' . $filename, true, true);
            }
            $handler = new StreamHandler($logFile);

            $formatter = new MixedFormatter(null, null, true);

            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);

            return $logger;
        };

        /*
         * Error logging with Monolog.
         *
         * Extend this service to push additional handlers onto the 'error' log stack.
         *
         * @return \Monolog\Logger
         */
        $container['errorLogger'] = function ($c) {
            $log = new Logger('errors');

            $hostserver = env('HOST_SERVER', 'localhost');
            if ($hostserver == 'fargate') {
                $logFile = 'php://stdout'; // 'php://stderr'; 
            } else {
                $filename = $c->config['logfiles.custom.error'] ? $c->config['logfiles.custom.error'] : $c->config['logfiles.default'];
                $logFile = $c->locator->findResource('log://' . $filename, true, true);
            }
            $handler = new StreamHandler($logFile, Logger::WARNING);

            $formatter = new LineFormatter(null, null, true);

            $handler->setFormatter($formatter);
            $log->pushHandler($handler);

            return $log;
        };

        /*
         * Mail logging service.
         *
         * PHPMailer will use this to log SMTP activity.
         * Extend this service to push additional handlers onto the 'mail' log stack.
         *
         * @return \Monolog\Logger
         */
        $container['mailLogger'] = function ($c) {
            $log = new Logger('mail');

            $hostserver = env('HOST_SERVER', 'localhost');
            if ($hostserver == 'fargate') {
                $logFile = 'php://stdout'; // 'php://stderr'; 
            } else {
                $filename = $c->config['logfiles.custom.mail'] ? $c->config['logfiles.custom.mail'] : $c->config['logfiles.default'];
                $logFile = $c->locator->findResource('log://' . $filename, true, true);
            }
            $handler = new StreamHandler($logFile);
            $formatter = new LineFormatter(null, null, true);

            $handler->setFormatter($formatter);
            $log->pushHandler($handler);

            return $log;
        };

        /*
         * Laravel query logging with Monolog.
         *
         * Extend this service to push additional handlers onto the 'query' log stack.
         *
         * @return \Monolog\Logger
         */
        $container['queryLogger'] = function ($c) {
            $logger = new Logger('query');

            $hostserver = env('HOST_SERVER', 'localhost');
            if ($hostserver == 'fargate') {
                $logFile = 'php://stdout'; // 'php://stderr'; 
            } else {
                $filename = $c->config['logfiles.custom.query'] ? $c->config['logfiles.custom.query'] : $c->config['logfiles.default'];
                $logFile = $c->locator->findResource('log://' . $filename, true, true);
            }
            $handler = new StreamHandler($logFile);

            $formatter = new MixedFormatter(null, null, true);

            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);

            return $logger;
        };

        /*
         * Authorization check logging with Monolog.
         *
         * Extend this service to push additional handlers onto the 'auth' log stack.
         *
         * @return \Monolog\Logger
         */
        $container['authLogger'] = function ($c) {
            $logger = new Logger('auth');

            $hostserver = env('HOST_SERVER', 'localhost');
            if ($hostserver == 'fargate') {
                $logFile = 'php://stdout'; // 'php://stderr'; 
            } else {
                $filename = $c->config['logfiles.custom.auth'] ? $c->config['logfiles.custom.auth'] : $c->config['logfiles.default'];
                $logFile = $c->get('locator')->findResource('log://' . $filename, true, true);
            }
            $handler = new StreamHandler($logFile);

            $formatter = new MixedFormatter(null, null, true);

            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);

            return $logger;
        };

        /*
         * Logger for logging the current user's activities to the database.
         *
         * Extend this service to push additional handlers onto the 'userActivity' log stack.
         *
         * @return \Monolog\Logger
         */
        $container['userActivityLogger'] = function ($c) {
            $classMapper = $c->classMapper;
            $config = $c->config;
            $session = $c->session;

            $logger = new Logger('userActivity');

            $handler = new UserActivityDatabaseHandler($classMapper, 'activity');

            // Note that we get the user id from the session, not the currentUser service.
            // This is because the currentUser service may not reflect the actual user during login/logout requests.
            $currentUserIdKey = $config['session.keys.current_user_id'];
            $userId = isset($session[$currentUserIdKey]) ? $session[$currentUserIdKey] : $config['reserved_user_ids.guest'];
            $processor = new UserActivityProcessor($userId);

            $logger->pushProcessor($processor);
            $logger->pushHandler($handler);

            return $logger;
        };

        /*
         * Site config service (separate from Slim settings).
         * Will attempt to automatically determine which config file(s) to use based on the value of the UF_MODE environment variable.
         * Srinivas : Modified this to pick up a different .env file based on the subdomaion
         * will not use this for now.
         * @return \UserFrosting\Support\Repository\Repository
         */
        /*
    $container['config'] = function ($c) {
    // Grab any relevant dotenv variables from the .env file
    // check if env file exists for the subdomain if so load that
    try {

    if (!isset($_SERVER['HTTP_HOST'])) {
    $dotenv = Dotenv::create(\UserFrosting\APP_DIR);
    $dotenv->load();
    }
    $pos = mb_strpos($_SERVER['HTTP_HOST'], '.');
    $prefix = '';
    if ($pos) {
    $prefix = strtolower(mb_substr($_SERVER['HTTP_HOST'], 0, $pos));
    }
    $file = '.env' . '.' . $prefix;

    if (!file_exists(\UserFrosting\APP_DIR . '/' . $file)) {
    $file = '.env';
    }
    //Dotenv::load(\UserFrosting\APP_DIR, $file);
    //Debug::debug("Line 187 the env file is $file");
    $dotenv = Dotenv::create(\UserFrosting\APP_DIR, $file);
    $dotenv->load();
    } catch (InvalidPathException $e) {
    // Skip loading the environment config file if it doesn't exist.
    }

    // Get configuration mode from environment
    $mode = env('UF_MODE') ?: '';

    // Construct and load config repository
    $builder = new ConfigPathBuilder($c->locator, 'config://');
    $loader = new ArrayFileLoader($builder->buildPaths($mode));
    $config = new Repository($loader->load());

    // Construct base url from components, if not explicitly specified
    if (!isset($config['site.uri.public'])) {
    $uri = $c->request->getUri();

    // Slim\Http\Uri likes to add trailing slashes when the path is empty, so this fixes that.
    $config['site.uri.public'] = trim($uri->getBaseUrl(), '/');
    }

    // Hacky fix to prevent sessions from being hit too much: ignore CSRF middleware for requests for raw assets ;-)
    // See https://github.com/laravel/framework/issues/8172#issuecomment-99112012 for more information on why it's bad to hit Laravel sessions multiple times in rapid succession.
    $csrfBlacklist = $config['csrf.blacklist'];
    $csrfBlacklist['^/' . $config['assets.raw.path']] = [
    'GET',
    ];

    $config->set('csrf.blacklist', $csrfBlacklist);

    return $config;
    };
     */
    }
}