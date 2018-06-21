<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\Http\Request;
    use Librarys\Http\Secure\CFSRToken;
    use Librarys\Util\Text\Encryption\Strings as StringEncryption;
    use Librarys\Util\Text\Strings;

    class User
    {

        /**
         * @var bool $isSignIn
         */
        protected static $isSignIn;

        /**
         * @var string $userAssoc
         */
        protected static $userAssoc;

        /**
         * @var array $tokenAssoc
         */
        protected static $tokenAssoc;

        const SESSION_ID_NAME    = 'id_user';
        const SESSION_TOKEN_NAME = 'token_user';

        const MIN_USERNAME = 3;
        const MIN_NAME     = 3;
        const MIN_PASSWORD = 5;

        const MAX_USERNAME = 30;
        const MAX_NAME     = 50;
        const MAX_EMAIL    = 200;

        const PERMS_ADMINSTRATOR = 16;
        const PERMS_ADMIN        = 8;
        const PERMS_BOT          = 4;
        const PERMS_USER         = 2;
        const PERMS_BAND         = 1;

        public static function execute()
        {
            self::$isSignIn = self::checkSession(
                self::$tokenAssoc,
                self::$userAssoc
            );
        }

        /**
         * @return bool|string
         */
        protected static function generatorToken()
        {
            $query = QueryFactory::createInstance(env('database.tables.user_token'));

            for ($i = 0; $i < 10; ++$i) {
                $token = CFSRToken::generator();

                $query->clear();
                $query->setCommand(QueryAbstract::COMMAND_SELECT);
                $query->addSelect('token');
                $query->addWhere('token', QueryAbstract::escape($token));
                $query->setLimit(1);

                if ($query->execute() != false && $query->rows() <= 0)
                    return $token;
            }

            return false;
        }

        /**
         * @param array $assoc
         * @return bool
         */
        public static function createSession($assoc)
        {
            $time  = intval($_SERVER['REQUEST_TIME']);
            $token = self::generatorToken();
            $query = QueryFactory::createInstance(env('database.tables.user_token'));
            $query->setCommand(QueryAbstract::COMMAND_INSERT_INTO);
            $query->setLimit(1);

            $query->addDataArray([
                'id_user'   => QueryAbstract::escape($assoc['id']),
                'token'     => QueryAbstract::escape($token),
                'agent'     => QueryAbstract::escape(Request::useragent()),
                'ip'        => QueryAbstract::escape(Request::ip()),
                'create_at' => $time,
                'modify_at' => $time
            ]);

            if ($query->execute() == false)
                return false;

            $query->clear();
            $query->setTable(env('database.tables.user'));
            $query->setCommand(QueryAbstract::COMMAND_UPDATE);
            $query->addData('sign_at', QueryAbstract::escape($time));
            $query->addWhere('id', QueryAbstract::escape($assoc['id']));
            $query->setLimit(1);

            if ($query->execute() == false)
                return false;

            $query->clear();
            $query->setTable(env('database.tables.user'));
            $query->setCommand(QueryAbstract::COMMAND_SELECT);
            $query->addWhere('id', QueryAbstract::escape($assoc['id']));
            $query->setLimit(1);

            if ($query->execute() == false)
                return false;

            self::$isSignIn  = true;
            self::$userAssoc = $query->assoc();

            Request::session()->put(self::SESSION_ID_NAME, StringEncryption::encodeCrypt($assoc['id'], $time));
            Request::session()->put(self::SESSION_TOKEN_NAME, $token);

            return true;
        }

        /**
         * @param array|null $tokenAssoc
         * @param array|null $userAssoc
         * @return bool
         */
        public static function checkSession(&$tokenAssoc = null, &$userAssoc = null)
        {
            $id    = Request::session()->get(self::SESSION_ID_NAME);
            $token = Request::session()->get(self::SESSION_TOKEN_NAME);

            if ($id == null || $token == null)
                return false;

            $query = QueryFactory::createInstance(env('database.tables.user_token'));
            $query->setCommand(QueryAbstract::COMMAND_SELECT);
            $query->addWhere('token', QueryAbstract::escape($token));
            $query->setLimit(1);

            if ($query->execute() == false || $query->rows() <= 0)
                return false;

            $assoc = $query->assoc();
            $id    = StringEncryption::decodeCrypt($id, $assoc['create_at']);

            if (intval($id) !== intval($assoc['id_user']))
                return false;

            if (strcasecmp($assoc['agent'], Request::useragent()) !== 0)
                return false;

            if (Strings::equals($assoc['ip'], Request::ip()) === false)
                return false;

            $tokenAssoc = $assoc;
            $timeNow    = QueryAbstract::escape($_SERVER['REQUEST_TIME']);

            $query->setCommand(QueryAbstract::COMMAND_UPDATE);
            $query->addData('modify_at', $timeNow);

            if ($query->execute(true) == false)
                return false;

            $query->clear();
            $query->setTable(env('database.tables.user'));
            $query->setCommand(QueryAbstract::COMMAND_UPDATE);
            $query->addData('sign_at', $timeNow);
            $query->addWhere('id', QueryAbstract::escape($assoc['id_user']));
            $query->setLimit(1);

            if ($query->execute() == false)
                return false;

            $query->clear();
            $query->setTable(env('database.tables.user'));
            $query->setCommand(QueryAbstract::COMMAND_SELECT);
            $query->addWhere('id', QueryAbstract::escape($assoc['id_user']));
            $query->setLimit(1);

            if ($query->execute() == false || $query->rows() <= 0)
                return false;
            else
                $userAssoc = $query->assoc();

            return true;
        }

        /**
         * @return bool
         */
        public static function closeSession()
        {
            if (is_array(self::$tokenAssoc)) {
                $id    = null;
                $token = null;

                if (isset(self::$tokenAssoc['id_user']))
                    $id = intval(self::$tokenAssoc['id_user']);

                if (isset(self::$tokenAssoc['token']))
                    $token = self::$tokenAssoc['token'];

                if ($id == null || $token == null)
                    return false;

                $query = QueryFactory::createInstance(env('database.tables.user_token'));
                $query->setCommand(QueryAbstract::COMMAND_DELETE);
                $query->addWhere('id_user', QueryAbstract::escape($id));
                $query->addWhere('token', QueryAbstract::escape($token));
                $query->setLimit(1);

                if ($query->execute() == false)
                    return false;
                else
                    self::$isSignIn = false;

                Request::session()->remove(self::SESSION_ID_NAME);
                Request::session()->remove(self::SESSION_TOKEN_NAME);

                return true;
            }

            return false;
        }

        /**
         * @return bool
         */
        public static function isSignIn()
        {
            return self::$isSignIn;
        }

        /**
         * @param string $password
         * @return string
         */
        public static function createPassword($password)
        {
            return StringEncryption::createCrypt($password);
        }

        public static function createUser($username, $name, $email, $password)
        {
            $query = QueryFactory::createInstance(env('database.tables.user'));
            $query->setCommand(QueryAbstract::COMMAND_INSERT_INTO);

            $query->addDataArray([
                'username'  => $username,
                'name'      => $name,
                'email'     => $email,
                'password'  => QueryAbstract::escape(self::createPassword($password)),
                'birthday'  => 0,
                'perms'     => self::PERMS_USER,
                'create_at' => time()
            ]);

            if ($query->execute() == false)
                return false;

            return true;
        }

        /**
         * @param string $password
         * @param string $passwordSalt
         * @return bool
         */
        public static function equalsPassword($password, $passwordSalt)
        {
            return StringEncryption::hashEqualsString($password, $passwordSalt);
        }

        /**
         * @param string $username
         * @return bool
         */
        public static function isUsernameValidate($username)
        {
            return preg_match('/[^A-Za-z0-9]/i', $username) == false;
        }

        /**
         * @param string $key
         * @return null|string
         */
        public static function getAssoc($key)
        {
            if (is_array(self::$userAssoc) && array_key_exists($key, self::$userAssoc))
                return self::$userAssoc[$key];

            return null;
        }

        /**
         * @return null|string
         */
        public static function getAssocId()
        {
            return self::getAssoc('id');
        }

        /**
         * @return null|string
         */
        public static function getAssocUsername()
        {
            return self::getAssoc('username');
        }

        /**
         * @return null|string
         */
        public static function getAssocName()
        {
            return self::getAssoc('name');
        }

        /**
         * @return null|string
         */
        public static function getAssocBirthday()
        {
            return self::getAssoc('birthday');
        }

        /**
         * @return null|string
         */
        public static function getAssocPerms()
        {
            return self::getAssoc('perms');
        }

        /**
         * @return null|string
         */
        public static function getAssocCreateAt()
        {
            return self::getAssoc('create_at');
        }

        /**
         * @return null|string
         */
        public static function getAssocModifyAt()
        {
            return self::getAssoc('modify_at');
        }

        /**
         * @return null|string
         */
        public static function getAssocSignAt()
        {
            return self::getAssoc('sign_at');
        }

    }

