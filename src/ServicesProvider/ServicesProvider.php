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
use UserFrosting\Sprinkle\Core\Log\MixedFormatter;
use UserFrosting\Sprinkle\Core\Util\RawAssetBundles;
use UserFrosting\Assets\AssetBundles\GulpBundleAssetsCompiledBundles as CompiledAssetBundles;
use UserFrosting\Assets\Assets;
use UserFrosting\Sprinkle\Core\Facades\Debug;

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
            $filename = $c->config['logfiles.custom.debug'] ? $c->config['logfiles.custom.debug'] : $c->config['logfiles.default'];
            $logFile = $c->locator->findResource('log://' . $filename, true, true);

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

            $filename = $c->config['logfiles.custom.error'] ? $c->config['logfiles.custom.error'] : $c->config['logfiles.default'];
            $logFile = $c->locator->findResource('log://' . $filename, true, true);

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

            $filename = $c->config['logfiles.custom.mail'] ? $c->config['logfiles.custom.mail'] : $c->config['logfiles.default'];
            $logFile = $c->locator->findResource('log://' . $filename, true, true);

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

            $filename = $c->config['logfiles.custom.query'] ? $c->config['logfiles.custom.query'] : $c->config['logfiles.default'];
            $logFile = $c->locator->findResource('log://' . $filename, true, true);

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

            $filename = $c->config['logfiles.custom.auth'] ? $c->config['logfiles.custom.auth'] : $c->config['logfiles.default'];
            $logFile = $c->get('locator')->findResource('log://' . $filename, true, true);

            $handler = new StreamHandler($logFile);

            $formatter = new MixedFormatter(null, null, true);

            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);

            return $logger;
        };
        /*
        $container['assets'] = function ($c) {
            $config = $c->config;
            $locator = $c->locator;

            // Load asset schema
            if ($config['assets.use_raw']) {

                // Register sprinkle assets stream, plus vendor assets in shared streams
                $locator->registerStream('assets', 'vendor', \UserFrosting\NPM_ASSET_DIR, true);
                $locator->registerStream('assets', 'vendor', \UserFrosting\BROWSERIFIED_ASSET_DIR, true);
                $locator->registerStream('assets', 'vendor', \UserFrosting\BOWER_ASSET_DIR, true);
                $locator->registerStream('assets', '', \UserFrosting\ASSET_DIR_NAME);

                if ($config['ufhacks.assets.with_url']) {
                    $baseUrl1 = $config['site.uri.public'];
                } else {
                    $baseUrl1 = '';
                }
                $baseUrl = $baseUrl1 . '/' . $config['assets.compiled.path'];
                //$baseUrl = $config['site.uri.public'] . '/' . $config['assets.raw.path'];

                $assets = new Assets($locator, 'assets', $baseUrl);

                // Load raw asset bundles for each Sprinkle.

                // Retrieve locations of raw asset bundle schemas that exist.
                $bundleSchemas = array_reverse($locator->findResources('sprinkles://' . $config['assets.raw.schema']));

                // Load asset bundle schemas that exist.
                if (array_key_exists(0, $bundleSchemas)) {
                    $bundles = new RawAssetBundles(array_shift($bundleSchemas));

                    foreach ($bundleSchemas as $bundleSchema) {
                        $bundles->extend($bundleSchema);
                    }

                    // Add bundles to asset manager.
                    $assets->addAssetBundles($bundles);
                }
            } else {

                // Register compiled assets stream in public folder + alias for vendor ones + build stream for CompiledAssetBundles
                $locator->registerStream('assets', '', \UserFrosting\PUBLIC_DIR_NAME . '/' . \UserFrosting\ASSET_DIR_NAME, true);
                $locator->registerStream('assets', 'vendor', \UserFrosting\PUBLIC_DIR_NAME . '/' . \UserFrosting\ASSET_DIR_NAME, true);
                $locator->registerStream('build', '', \UserFrosting\BUILD_DIR_NAME, true);

                if ($config['ufhacks.assets.with_url']) {
                    $baseUrl1 = $config['site.uri.public'];
                } else {
                    $baseUrl1 = '';
                }
                $baseUrl = $baseUrl1 . '/' . $config['assets.compiled.path'];
                $assets = new Assets($locator, 'assets', $baseUrl);

                // Load compiled asset bundle.
                $path = $locator->findResource('build://' . $config['assets.compiled.schema'], true, true);
                $bundles = new CompiledAssetBundles($path);
                $assets->addAssetBundles($bundles);
            }

            return $assets;
        };
        */
    }
}
