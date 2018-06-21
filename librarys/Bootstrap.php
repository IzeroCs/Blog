<?php

    namespace Librarys;

    use Librarys\Environment\Loader;

    require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Global.php');

    class Bootstrap
    {

        /**
         * Run
         */
        public static function run()
        {
            Loader::getInstance(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env.php')->execute();
            Boot::getInstance();
        }

    }
