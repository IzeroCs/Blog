<?php

    namespace Librarys\Http;

    use Librarys\Http\Exception\SessionException;

    class Session
    {

        /**
         * @var Session $instance
         */
        private static $instance;

        /**
         * @var bool $isSessionStarted
         */
        private $isSessionStarted;

        /**
         * Session constructor.
         */
        protected function __construct()
        {
            self::$instance = $this;
        }

        public function __wakeup()
        {
            // TODO: Implement __wakeup() method.
        }

        public function __clone()
        {
            // TODO: Implement __clone() method.
        }

        /**
         * @return Session
         */
        public static function getInstance()
        {
            if (null === self::$instance)
                self::$instance = new Session();

            if (self::$instance instanceof Session || self::$instance->isSessionStarted == false)
                self::$instance->start();

            return self::$instance;
        }

        /**
         * @throws SessionException
         */
        public function start()
        {
            $this->isSessionStarted = false;

            if (env('session.init', false) == false)
                throw new SessionException('Session not enable in config app', 1);

            $sessionStatus = false;

            if (version_compare(phpversion(), '5.4.0', '>='))
                $sessionStatus = session_status() === PHP_SESSION_ACTIVE;
            else
                $sessionStatus = session_id() !== '';

            if ($sessionStatus === false) {
                $name         = env('session.name',          null);
                $cacheLimiter = env('session.cache_limiter', null);
                $cacheExpire  = env('session.cache_expire',  null);

                if (empty($name))
                    throw new SessionException('Name session in config is empty', 1);

                if (is_numeric($cacheExpire) == false)
                    throw new SessionException('Cache expire session not validate', 1);

                session_name($name);
                session_cache_limiter($cacheLimiter);
                session_cache_expire($cacheExpire);

                $cookieLifetime = env('session.cookie_lifetime', ini_get('session.cookie_lifetime'));
                $cookiePath     = env('session.cookie_path', ini_get('session.cookie_path'));
                $cookieDomain   = env('session.cookie_domain', ini_get('session.cookie_domain'));
                $cookieSecure   = env('session.cookie_secure', ini_get('session.cookie_secure'));
                $cookieHttpOnly = env('session.cookie_httponly', ini_get('session.cookie_httponly'));

                if (is_numeric($cookieLifetime) == false)
                    throw new SessionException('Cookie lifetime session not validate', 1);

                session_set_cookie_params(
                    $cookieLifetime,
                    $cookiePath,
                    $cookieDomain,
                    $cookieSecure,
                    $cookieHttpOnly
                );

                if (session_start() == false)
                    throw new SessionException('Cannot start session', 1);
            }

            $this->isSessionStarted = true;
        }

        /**
         * @param string $key
         * @return string|null
         * @throws SessionException
         */
        public function get($key)
        {
            if ($this->isSessionStarted == false)
                throw new SessionException('Session not started', 1);

            if ($this->has($key))
                return $_SESSION[$key];

            return null;
        }

        /**
         * @param string $key
         * @return bool
         * @throws SessionException
         */
        public function has($key)
        {
            if ($this->isSessionStarted == false)
                throw new SessionException('Session not started', 1);

            return isset($_SESSION[$key]);
        }

        /**
         * @param string $key
         * @param mixed $value
         * @param bool $isPutArray
         * @throws SessionException
         */
        public function put($key, $value, $isPutArray = false)
        {
            if ($this->isSessionStarted == false)
                throw new SessionException('Session not started', 1);

            if ($isPutArray == false)
                $_SESSION[$key] = $value;
            else
                $_SESSION[$key][] = $value;
        }

        /**
         * @param string $key
         * @throws SessionException
         */
        public function remove($key)
        {
            if ($this->isSessionStarted == false)
                throw new SessionException('Session not started', 1);

            if (isset($_SESSION[$key]))
                unset($_SESSION[$key]);
        }

        /**
         * @return bool
         * @throws SessionException
         */
        public function destroy()
        {
            if ($this->isSessionStarted == false)
                throw new SessionException('Session not started', 1);

            if (session_destroy() == false)
                return false;
            else
                $this->isSessionStarted = false;

            return true;
        }

    }
