<?php

    use Librarys\Http\Buffer;
    use Librarys\Http\Request;

    if (defined('SP') == false)
        define('SP', DIRECTORY_SEPARATOR);

    return [
        'author'  => 'IzeroCs',
        'version' => '1.0',

        'boot' => [
            'run_ob_buffer'       => true,
            'run_fix_magic_quote' => true,

            'error_reported' => [
                'enable'  => true,
                'product' => true,
                'level'   => Librarys\Http\Error\Handler::EU_ALL
            ],

            'make_directorys' => [
                __DIR__ . SP . 'assets' . SP . 'tmp'                     => 777,
                __DIR__ . SP . 'assets' . SP . 'uploads'                 => 777,
                __DIR__ . SP . 'assets' . SP . 'uploads' . SP . 'thumbs' => 777,
                __DIR__ . SP . 'assets' . SP . 'uploads' . SP . 'images' => 777,
                __DIR__ . SP . 'assets' . SP . 'uploads' . SP . 'tmp'    => 777
            ],

            'run_tasks' => function() {
                User::execute();
            },

            'error_http' => [
                'enable' => true,

                'handle' => function($responseCode, $responseCodeString) {
                    if (defined('ERROR') == false && $responseCode !== Request::HTTP_CODE_OK) {
                        Buffer::clearBuffer();
                        require_once(env('app.document_root') . SP . 'error.php');
                    }
                }
            ]
        ],

        'app' => [
            'ip_localhost' => [
                'localhost',
                '127.0.0.1',
                '192.168.31.50',
                '127.0.0.1:8080',
                '192.168.31.50:8080'
            ],

            'document_root'  => $_SERVER['DOCUMENT_ROOT'],
            'app_root'       => $_SERVER['DOCUMENT_ROOT'],
            'request_scheme' => Librarys\Http\Request::scheme(),
            'http_host'      => Librarys\Http\Request::scheme() . '://' . $_SERVER['HTTP_HOST']
        ],

        'alert' => [
            'enable'           => true,
            'session_prefix'   => '__alert_',
            'display_callback' => 'alert_ui_display_callback'
        ],

        'paging' => [
            'number_on_page'   => 5,
            'display_callback' => 'paging_ui_display_callback'
        ],

        'language' => [
            'path'   => __DIR__ . SP . 'assets' . SP . 'languages',
            'mime'   => 'php',
            'locale' => 'vi'
        ],

        'session' => [
            'init'            => true,
            'name'            => 'BlogSess',
            'cookie_lifetime' => 86400 * 7,
            'cookie_path'     => ini_get('session.cookie_path'),
            'cookie_domain'   => ini_get('session.cookie_domain'),
            'cookie_secure'   => ini_get('session.cookie_secure'),
            'cookie_httponly' => ini_get('session.cookie_httponly'),
            'cache_limiter'   => 'private',
            'cache_expire'    => 180
        ],

        'database' => [
            'uri'     => 'mysql',
            'host'    => 'localhost',
            'user'    => 'root',
            'pass'    => '',
            'name'    => 'blog',
            'port'    => '3306',
            'prefix'  => 'blog_',
            'charset' => 'utf8',

            'modules' => [
                \Librarys\Database\Module\PDO::class,
                \Librarys\Database\Module\Mysqli::class,
                \Librarys\Database\Module\Mysql::class
            ],

            'product' => [
                'uri'     => 'mysql',
                'host'    => 'localhost',
                'user'    => 'izerocsn_blog',
                'pass'    => 'blogpassword',
                'name'    => 'izerocsn_blog',
                'port'    => 3306,
                'prefix'  => 'blog_',
                'charset' => 'latin1'
            ],

            'tables' => [
                'about_development' => 'about_development',
                'article'           => 'article',
                'category'          => 'category',
                'image'             => 'image',
                'user'              => 'user',
                'user_token'        => 'user_token',
                'setting_system'    => 'setting_system'
            ]
        ],

        'cfsr' => [
            'enable' => true,
            'name'   => '__cfsr_token',

            'cookie' => [
                'time' => 60000,
                'path' => '/'
            ],

            'validate' => [
                'post' => true,
                'get'  => true
            ]
        ],

        'rewrite' => [
            'enable' => true,

            'config' => [
                'baseurl' => '${app.http_host}/',
                'path'    => __DIR__ . SP . '.rewrite.php'
            ]
        ],

        'date' => [
            'timezone' => 'Asia/Ho_Chi_Minh',
            'format'   => 'd.m.y - H:i'
        ]
    ];
