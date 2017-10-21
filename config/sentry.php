<?php

return array(
    'dsn' => env('SENTRY_DSN'),

    // capture release as git sha
    'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),

    // Capture bindings on SQL queries
    'breadcrumbs.sql_bindings' => true,

    // Capture default user context
    'user_context' => true,


    /*
     * Mongodb-sentry
     */


    'groups' => array(

        'model' => 'Jenssegers\Mongodb\Sentry\Group',

    ),

    'users' => array(

        'model' => 'Jenssegers\Mongodb\Sentry\User',

    ),

    'throttling' => array(

        'model' => 'Jenssegers\Mongodb\Sentry\Throttle',

    ),
);
