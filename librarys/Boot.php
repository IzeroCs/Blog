<?php

    namespace Librarys;

    use Librarys\Database\Connect as DatabaseConnect;
    use Librarys\Exception\RuntimeException;
    use Librarys\File\FileSystem;
    use Librarys\Http\Buffer;
    use Librarys\Http\Error\Handler;
    use Librarys\Http\Request;
    use Librarys\Http\Rewrite;
    use Librarys\Http\Secure\CFSRToken;
    use Librarys\Language\Loader as Language;
    use Librarys\UI\Alert;

    class Boot
    {

        /**
         * @var \Librarys\Boot $instance
         */
        private static $instance;

        const PHP_VERSION_SUPPORT = '5.0.0';

        /**
         * Boot constructor.
         */
        protected function __construct()
        {
            $this->initBuffer();
            $this->initErrorReported();
            $this->compareVersionPHP();
            $this->initDate();
            $this->initMakeDirectorys();
            $this->initDatabaseConnect();
            $this->initLanguage();
            $this->initCFSRToken();
            $this->initRewrite();
            $this->initAlert();
            $this->initRunTasks();
            $this->initErrorHttp();
        }

        protected function __wakeup()
        {
            // TODO: Implement __wakeup() method.
        }

        protected function __clone()
        {
            // TODO: Implement __clone() method.
        }

        /**
         * @return \Librarys\Boot
         */
        public static function getInstance()
        {
            if (self::$instance == null)
                self::$instance = new Boot();

            return self::$instance;
        }

        private function initBuffer()
        {
            if (env('boot.run_ob_buffer', true)) {
                Buffer::startBuffer();
                Buffer::listenEndBuffer();
            }

            if (env('boot.run_fix_magic_quote', true))
                Buffer::fixMagicQuotesGpc();
        }

        private function initErrorReported()
        {
            $enable  = env('boot.error_reported.enable', false);
            $product = env('boot.error_reported.product', false);

            if (($enable && Request::isLocal()) || $product)
                Handler::listenError(env('boot.error_reported.level'));
            else
                Handler::disError();
        }

        private function compareVersionPHP()
        {
            if (version_compare(PHP_VERSION, self::PHP_VERSION_SUPPORT, '<'))
                throw new RuntimeException('Your php version is less than ' . self::PHP_VERSION_SUPPORT . ', source code could not support you install the php version or greater than or ' . self::PHP_VERSION_SUPPORT . ' for use');
        }

        private function initDate()
        {
            $timezone = env('date.timezone', 'Asia/Ho_Chi_Minh');

            if ($timezone != null && empty($timezone) == false)
                @date_default_timezone_set($timezone);
        }

        private function initMakeDirectorys()
        {
            $directorys = env('boot.make_directorys', []);

            if (is_array($directorys) == false)
                return;

            foreach ($directorys AS $pathname => $chmod) {
                if (FileSystem::exists($pathname) == false)
                    FileSystem::mkdir($pathname, true);

                if (FileSystem::isTypeDirectory($pathname))
                    FileSystem::chmod($pathname, $chmod);
            }
        }

        private function initDatabaseConnect()
        {
            $array = [];

            if (Request::isLocal())
                $array = env('database', []);
            else
                $array = env('database.product', []);

            $connect = DatabaseConnect::createInstanceConnectArray($array);

            if ($connect !== false)
                $connect->connect();
        }

        private function initLanguage()
        {
            Language::getInstance()->execute();
        }

        private function initCFSRToken()
        {
            if (CFSRToken::getInstance()->validatePost() !== true)
                throw new RuntimeException('CFSR Token validate failed');
        }

        private function initRewrite()
        {
            Rewrite::getInstance(env('rewrite.config.path'));
        }

        private function initAlert()
        {
            if (env('alert.enable'))
                Alert::execute();
        }

        private function initRunTasks()
        {
            $tasks = env('boot.run_tasks', function() {

            });

            if (is_object($tasks))
                $tasks();
        }

        private function initErrorHttp()
        {
            if (env('boot.error_http.enable', false) && function_exists('http_response_code')) {
                register_shutdown_function(function() {
                    $handle = env('boot.error_http.handle');

                    if ($handle !== null) {
                        $httpCode = http_response_code();
                        $httpString = Request::httpResponseCodeToString($httpCode);

                        $handle($httpCode, $httpString);
                    }
                });
            }
        }

    }
