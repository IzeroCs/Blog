<?php

    namespace Librarys\Database;

    use Librarys\Database\Exception\ConnectException;
    use Librarys\Util\Arrays;

    class Connect
    {

        /**
         * @var string $uri
         */
        private $uri;

        /**
         * @var string $host
         */
        private $host;

        /**
         * @var string $user
         */
        private $user;

        /**
         * @var string $pass
         */
        private $pass;

        /**
         * @var string $name
         */
        private $name;

        /**
         * @var string|int $port
         */
        private $port;

        /**
         * @var string $prefix
         */
        private $prefix;

        /**
         * @var string $charset
         */
        private $charset;

        /**
         * @var resource|object|\mysqli|\PDO $resource
         */
        private $resource;

        /**
         * @var array $querys
         */
        private $querys;

        /**
         * @var \Librarys\Database\ModuleAbstract $module
         */
        private $module;

        /**
         * @var \Librarys\Database\Connect $instance
         */
        private static $instance;

        /**
         * @var array $modules
         */
        private $modules;

        /**
         * @var array $moduleDefaults
         */
        private static $moduleDefaults = [
            \Librarys\Database\Module\PDO::class,
            \Librarys\Database\Module\Mysqli::class,
            \Librarys\Database\Module\Mysql::class
        ];

        /**
         * Connect constructor.
         * @param string     $uri
         * @param string     $host
         * @param string     $user
         * @param string     $pass
         * @param string     $name
         * @param string|int $port
         * @param string     $prefix
         * @param string     $charset
         * @param array      $modules
         */
        protected function __construct(
            $uri,
            $host,
            $user,
            $pass,
            $name,
            $port,
            $prefix,
            $charset,
            $modules
        ) {
            $this->setUri($uri);
            $this->setHost($host);
            $this->setUser($user);
            $this->setPass($pass);
            $this->setName($name);
            $this->setPort($port);
            $this->setPrefix($prefix);
            $this->setCharset($charset);
            $this->setModules($modules);
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
         * @return \Librarys\Database\Connect
         */
        public static function getInstance()
        {
            return self::$instance;
        }

        /**
         * @param string     $uri
         * @param string     $host
         * @param string     $user
         * @param string     $pass
         * @param string     $name
         * @param string|int $port
         * @param string     $prefix
         * @param string     $charset
         * @param array      $modules
         * @return \Librarys\Database\Connect
         */
        public static function createInstanceConnect(
            $uri,
            $host,
            $user,
            $pass,
            $name,
            $port,
            $prefix,
            $charset,
            $modules
        ) {
            if (self::$instance == null) {
                self::$instance = new Connect(
                    $uri,
                    $host,
                    $user,
                    $pass,
                    $name,
                    $port,
                    $prefix,
                    $charset,
                    $modules
                );
            }

            return self::$instance;
        }

        /**
         * @param array $config
         * @return \Librarys\Database\Connect|bool
         */
        public static function createInstanceConnectArray($config)
        {
            if (is_array($config) == false)
                return false;

            Arrays::makeEntryIfNotExists($config, 'uri', 'mysql');
            Arrays::makeEntryIfNotExists($config, 'host', 'localhost');
            Arrays::makeEntryIfNotExists($config, 'user', 'root');
            Arrays::makeEntryIfNotExists($config, 'pass', '');
            Arrays::makeEntryIfNotExists($config, 'name', 'blog');
            Arrays::makeEntryIfNotExists($config, 'port', '3306');
            Arrays::makeEntryIfNotExists($config, 'prefix', '');
            Arrays::makeEntryIfNotExists($config, 'charset', 'utf8');
            Arrays::makeEntryIfNotExists($config, 'modules', self::$moduleDefaults);

            return self::createInstanceConnect(
                $config['uri'],
                $config['host'],
                $config['user'],
                $config['pass'],
                $config['name'],
                $config['port'],
                $config['prefix'],
                $config['charset'],
                $config['modules']
            );
        }

        /**
         * @param string $uri
         */
        public function setUri($uri)
        {
            $this->uri = $uri;
        }

        /**
         * @return string
         */
        public function getUri()
        {
            return $this->uri;
        }

        /**
         * @param string $host
         */
        public function setHost($host)
        {
            $this->host = $host;
        }

        /**
         * @return string
         */
        public function getHost()
        {
            return $this->host;
        }

        /**
         * @param string $user
         */
        public function setUser($user)
        {
            $this->user = $user;
        }

        /**
         * @return string
         */
        public function getUser()
        {
            return $this->user;
        }

        /**
         * @param string $pass
         */
        public function setPass($pass)
        {
            $this->pass = $pass;
        }

        /**
         * @return string
         */
        public function getPass()
        {
            return $this->pass;
        }

        /**
         * @param string $name
         */
        public function setName($name)
        {
            $this->name = $name;
        }

        /**
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * @param string $port
         */
        public function setPort($port)
        {
            $this->port = $port;
        }

        /**
         * @return string|int
         */
        public function getPort()
        {
            return $this->port;
        }

        /**
         * @param string $prefix
         */
        public function setPrefix($prefix)
        {
            $this->prefix = $prefix;
        }

        /**
         * @return string
         */
        public function getPrefix()
        {
            return $this->prefix;
        }

        /**
         * @param string $charset
         */
        public function setCharset($charset)
        {
            $this->charset = $charset;
        }

        /**
         * @return string
         */
        public function getCharset()
        {
            return $this->charset;
        }

        /**
         * @return resource|object|\mysqli|\PDO
         */
        public function getResource()
        {
            return $this->resource;
        }

        /**
         * @param array $modules
         */
        public function setModules($modules)
        {
            $this->modules = $modules;
        }

        /**
         * @return array
         */
        public function getModules()
        {
            return $this->modules;
        }

        /**
         * @return \Librarys\Database\ModuleAbstract
         */
        public function getModule()
        {
            return $this->module;
        }

        /**
         * @return array
         */
        public function getQuerys()
        {
            return $this->querys;
        }

        public function connect()
        {
            $this->moduleConnect();
            $this->listenShutdown();
        }

        public function disconnect()
        {

        }

        public function freeconnect()
        {

        }

        protected function moduleConnect()
        {
            /**
             * @var \Librarys\Database\ModuleAbstract $namespace
             */
            foreach ($this->modules AS $namespace) {
                if ($namespace::isSupport($this->uri)) {
                    $this->module = new $namespace();

                    $this->resource = $this->module->connect(
                        $this->uri,
                        $this->host,
                        $this->user,
                        $this->pass,
                        $this->name,
                        $this->port
                    );

                    if (self::isResource($this->resource) === false)
                        throw new ConnectException($this->module->errorconnect());

                    break;
                }
            }
        }

        protected function listenShutdown()
        {
            register_shutdown_function(function() {
                $this->freeconnect();
                $this->disconnect();
            });
        }

        /**
         * @return bool
         */
        public static function isConnect()
        {
            return self::isResource(self::getInstance()->getResource());
        }

        /**
         * @param resource $resource
         * @return bool
         */
        public static function isResource($resource)
        {
            return is_resource($resource) || is_object($resource);
        }

    }
