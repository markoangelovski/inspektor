<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? \Pdo\Mysql::ATTR_SSL_CA : \PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? \Pdo\Mysql::ATTR_SSL_CA : \PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    // 'redis' => [

    //     'client' => env('REDIS_CLIENT', 'phpredis'),

    //     'options' => [
    //         'cluster' => env('REDIS_CLUSTER', 'redis'),
    //         'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
    //         'persistent' => env('REDIS_PERSISTENT', false),
    //     ],

    //     'default' => [
    //         'url' => env('REDIS_URL'),
    //         'host' => env('REDIS_HOST', '127.0.0.1'),
    //         'username' => env('REDIS_USERNAME'),
    //         'password' => env('REDIS_PASSWORD'),
    //         'port' => env('REDIS_PORT', '6379'),
    //         'database' => env('REDIS_DB', '0'),
    //         'max_retries' => env('REDIS_MAX_RETRIES', 3),
    //         'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
    //         'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
    //         'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
    //     ],

    //     'cache' => [
    //         'url' => env('REDIS_URL'),
    //         'host' => env('REDIS_HOST', '127.0.0.1'),
    //         'username' => env('REDIS_USERNAME'),
    //         'password' => env('REDIS_PASSWORD'),
    //         'port' => env('REDIS_PORT', '6379'),
    //         'database' => env('REDIS_CACHE_DB', '1'),
    //         'max_retries' => env('REDIS_MAX_RETRIES', 3),
    //         'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
    //         'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
    //         'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
    //     ],

    // ],

    // Suggested by Claude.ai:
    /**
     * Required config file changes in your Laravel app
     * The .env variables for Redis DB separation only work if config/database.php defines the corresponding named connections. 
     * This is not done by default — Laravel ships with default and cache connections only, not session. You must add the session connection:
     */
    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            // Global key prefix — keeps your keys namespaced if you ever share
            // a Redis instance with another app.
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_'),
        ],

        // Used by the Redis facade and by Horizon for queue jobs.
        'default' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        // Used by the cache store (Cache::put, Cache::remember, etc.).
        // Safe to flush independently of sessions and queues.
        'cache' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

        // Used exclusively for user sessions.
        // SESSION_CONNECTION=session in .env routes session reads/writes here.
        'session' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_SESSION_DB', '2'),
        ],
    ],
    /**
 * Session driver decision rationale
 * Redis sessions are great for horizontally scaled setups with multiple app servers, where session data must be shared across nodes. 
 * For a standard Laravel app on a single VM, database sessions are often safer and simpler since they have ACID guarantees and sidestep 
 * the occasional CSRF token mismatch that can occur under high concurrency with Redis sessions. Mintlify
 * For this stack the recommendation is Redis for sessions for a specific reason: you are already running Redis for Horizon queues. 
 * Adding database sessions would introduce a Postgres read+write on every single HTTP request purely for session data — a cost you're 
 * already paying Redis for. Redis sessions are the fastest option — fully in-memory with built-in TTL/expiry — and are the right call 
 * for high-traffic apps that already have Redis in the stack. Traefik The SESSION_CONNECTION=session variable pointing to a dedicated 
 * DB index keeps them completely isolated from both cache and queue data.
 * The key risk — a cache:clear wiping sessions — is fully neutralised by the separate database index approach described above. 
 * Typically the solution to preventing app cache flushes from destroying session and queue data is to set the Redis database index to 
 * different values for your application cache versus everything else.
 */
];
