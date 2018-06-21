<?php

    namespace Librarys\Http\Secure;

    use Librarys\Http\Request;
    use Librarys\Util\Text\Encryption\Strings;

    class CFSRToken
    {

        /**
         * @var array|false|null|string $name
         */
        private $name;

        /**
         * @var string $token
         */
        private $token;

        /**
         * @var array|false|null|string $time
         */
        private $time;

        /**
         * @var array|false|null|string $path
         */
        private $path;

        /**
         * @var bool $isTokenUpdate
         */
        private $isTokenUpdate;

        /**
         * @var CFSRToken $instance
         */
        private static $instance;

        const TOKEN_NAME_NOT_FOUND = 1;
        const TOKEN_NOT_EQUAL      = 2;

        /**
         * CFSRToken constructor.
         */
        protected function __construct()
        {
            if (env('cfsr.enable') == false)
                return;

            $this->name = env('cfsr.name', '__cfsr_token');
            $this->time = env('cfsr.cookie.time', 180);
            $this->path = env('cfsr.cookie.path', '/');

            if (isset($_COOKIE[$this->name]) == false) {
                $this->token         = self::generator();
                $this->isTokenUpdate = true;
            } else {
                $this->token         = addslashes($_COOKIE[$this->name]);
                $this->isTokenUpdate = false;
            }

            setcookie($this->name, $this->token, env('SERVER.REQUEST_TIME') + $this->time, $this->path);
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
         * @return CFSRToken
         */
        public static function getInstance()
        {
            if (null === self::$instance)
                self::$instance = new CFSRToken();

            return self::$instance;
        }

        /**
         * @return string
         */
        public static function generator()
        {
            return ('token' .
                md5(
                    base64_encode(Request::ip() . Request::useragent() . time() . Strings::randomSalt())
                )
            );
        }

        public function validatePost()
        {
            if (env('cfsr.enable') == false || env('cfsr.validate.post') == false)
                return true;

            if (env('SERVER.REQUEST_METHOD') == 'POST') {
                if (isset($_POST[$this->name]) == false || isset($_COOKIE[$this->name]) == false)
                    return self::TOKEN_NAME_NOT_FOUND;
                else if ($_POST[$this->name] != $_COOKIE[$this->name])
                    return self::TOKEN_NOT_EQUAL;
            }

            return true;
        }

        public function validateGet($token = null)
        {
            if (env('cfsr.enable') == false || env('cfsr.validate.get') == false)
                return true;

            if (env('SERVER.REQUEST_METHOD') == 'GET') {
                if ($token === null) {
                    if (isset($_GET[$this->name]) == false || isset($_COOKIE[$this->name]) == false)
                        return self::TOKEN_NAME_NOT_FOUND;
                    else if ($_GET[$this->name] != $_COOKIE[$this->name])
                        return self::TOKEN_NOT_EQUAL;
                } else {
                    if (isset($_COOKIE[$this->name]) == false)
                        return self::TOKEN_NAME_NOT_FOUND;
                    else if ($token != $_COOKIE[$this->name])
                        return self::TOKEN_NOT_EQUAL;
                }
            }

            return true;
        }

        public function getName()
        {
            return $this->name;
        }

        public function getTime()
        {
            return $this->time;
        }

        public function getToken()
        {
            return $this->token;
        }

        public function isTokenUpdate()
        {
            return $this->isTokenUpdate;
        }

    }
