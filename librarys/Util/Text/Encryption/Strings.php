<?php

    namespace Librarys\Util\Text\Encryption;

    use Librarys\Exception\RuntimeException;

    class Strings
    {

        const METHOD_DIGEST_OPENSSL_CRYPT = 'sha256';
        const METHOD_OPENSSL_CRYPT        = 'AES-256-CBC';
        const OPTIONS_OPENSSL_CRYPT       = OPENSSL_RAW_DATA;

        /**
         * @param int $length
         * @return string
         * @throws RuntimeException
         */
        public static function randomToken($length = 32)
        {
            if ($length == null || $length <= 0)
                $length = 32;

            $token = null;

            if (function_exists('random_bytes'))
                $token = random_bytes($length);
            else if (function_exists('openssl_random_pseudo_bytes'))
                $token = openssl_random_pseudo_bytes($length);
            else if (function_exists('mcrypt_create_iv'))
                $token = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            else
                throw new RuntimeException('Not support random token');

            return bin2hex($token);
        }

        /**
         * @return string
         */
        public static function randomSalt()
        {
            $token      = self::randomToken();
            $token      = hex2bin($token);
            $saltBuffer = base64_encode($token);
            $saltBuffer = strtr($saltBuffer, '+', '.');

            // $2y$ is the blowfish algorithm
            $saltBuffer = sprintf("$2y$%02d$", 10) . $saltBuffer;

            return $saltBuffer;
        }

        /**
         * @param string      $string
         * @param null|string $salt
         * @return string
         */
        public static function createCrypt($string, $salt = null)
        {
            if ($salt == null)
                $salt = self::randomSalt();

            return @crypt($string, $salt);
        }

        /**
         * @param string $stringSalt
         * @param string $string
         * @return bool
         */
        public static function hashEqualsString($stringSalt, $string)
        {
            $hashed = crypt($string, $stringSalt);

            if (function_exists('hash_equals')) {
                return hash_equals($hashed, $stringSalt);
            } else {
                if (strlen($hashed) != strlen($stringSalt)) {
                    return false;
                } else {
                    $res = $stringSalt ^ $hashed;
                    $ret = 0;

                    for ($i = strlen($res) - 1; $i >= 0; --$i)
                        $ret |= ord($res[$i]);

                    return $ret == false;
                }
            }
        }

        /**
         * @param string $string
         * @param string $key
         * @return bool|string
         */
        public static function encodeCrypt($string, $key)
        {
            if (function_exists('openssl_digest'))
                $key = openssl_digest($key, self::METHOD_DIGEST_OPENSSL_CRYPT);
            else if (function_exists('hash'))
                $key = hash(self::METHOD_DIGEST_OPENSSL_CRYPT, $key);
            else
                return $string;

            $ivSize = openssl_cipher_iv_length(self::METHOD_OPENSSL_CRYPT);
            $iv     = openssl_random_pseudo_bytes($ivSize);
            $data   = openssl_encrypt($string, self::METHOD_OPENSSL_CRYPT, $key, self::OPTIONS_OPENSSL_CRYPT, $iv);

            if ($data == false)
                return false;

            return base64_encode($iv . $data);
        }

        /**
         * @param string $string
         * @param string $key
         * @return bool|string
         */
        public static function decodeCrypt($string, $key)
        {
            if (function_exists('openssl_digest'))
                $key = openssl_digest($key, self::METHOD_DIGEST_OPENSSL_CRYPT);
            else if (function_exists('hash'))
                $key = hash(self::METHOD_DIGEST_OPENSSL_CRYPT, $key);
            else
                return $string;

            $string = base64_decode($string);
            $ivSize = openssl_cipher_iv_length(self::METHOD_OPENSSL_CRYPT);
            $iv     = substr($string, 0, $ivSize);
            $string = substr($string, $ivSize);
            $string = openssl_decrypt($string, self::METHOD_OPENSSL_CRYPT, $key, self::OPTIONS_OPENSSL_CRYPT, $iv);

            return $string;
        }

    }
