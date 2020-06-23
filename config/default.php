<?php

/*
     * UserFrosting (http://www.userfrosting.com)
     *
     * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/*
 * Core configuration file for UserFrosting.  You must override/extend this in your site's configuration file.
 *
 * Sensitive credentials should be stored in an environment variable or your .env file.
 * Database password: DB_PASSWORD
 * SMTP server password: SMTP_PASSWORD
 */
return [
    /*
    * ----------------------------------------------------------------------
    * Logfile Config
    * ----------------------------------------------------------------------
    * 
    */
    'logfiles' => [
        'default' => 'userfrosting_' . date('Ym') . '.log'
        /** 
     * Uncomment this to Customize Logs for Each type or just use the default above
     * */
        /*
        ,'custom' => [
            'auth' => 'auth_userfrosting_' . date('Ym') . '.log',
            'debug' => 'debug_userfrosting_' . date('Ym') . '.log',
            'error' => 'error_userfrosting_' . date('Ym') . '.log',
            'mail' => 'mail_userfrosting_' . date('Ym') . '.log',
            'query' => 'query_userfrosting_' . date('Ym') . '.log'
        ]
        */
    ],
    'ufhacks' => [
        'assets' => ['with_url' => false]
    ] //,
    //    'site' => ['uri' => ['public' => '']]

];
