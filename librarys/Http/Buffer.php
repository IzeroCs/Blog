<?php

    namespace Librarys\Http;

    class Buffer
    {

        public static function startBuffer()
        {
            self::cleanLevelBuffer();

            $disableGzHandler = false;
            $disableHeader = false;

            if (defined('DISABLE_GZHANDLER')) {
                $disableGzHandler = true;
            } else if (function_exists('apache_response_headers')) {
                $headers = apache_response_headers();

                if (array_key_exists('Content-Encoding', $headers) && strcasecmp($headers['Content-Encoding'], 'gzip') === 0)
                    $disableGzHandler = true;
            }

            if ($disableGzHandler == false && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
                @ob_start('ob_gzhandler');
            else
                @ob_start();

            if (defined('DISABLE_HEADER_SYSTEM')) {
                $disableHeader = true;
            } else if (function_exists('apache_response_headers')) {
                $headers = apache_request_headers();

                if (array_key_exists('Header-System', $headers) && strcasecmp($headers['Header-System'], 'off') === 0)
                    $disableHeader = true;
            }

            if ($disableHeader == false) {
                header('Cache-Control: private, max-age=0, no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s ', time()) . 'GMT');
                header('Etag: "' . md5(time()) . '"');
            }
        }

        /**
         * Clean all buffer first run
         */
        public static function cleanLevelBuffer()
        {
            $level = @ob_get_level();

            if ($level <= 0)
                return;

            for ($i = 0; $i < $level; ++$i) {
                if (@ob_end_clean() == false && function_exists('ob_clean'))
                    @ob_clean();
            }
        }

        /**
         * Flush buffer to client
         */
        public static function flushBuffer()
        {
            @ob_flush();
        }

        /**
         * End buffer and flush buffer to client
         */
        public static function endFlushBuffer()
        {
            @ob_end_flush();
        }

        /**
         * Clear buffer and start buffer
         */
        public static function clearBuffer()
        {
            self::endCleanBuffer();
            self::startBuffer();
        }

        /**
         * End and clean buffer
         */
        public static function endCleanBuffer()
        {
            @ob_end_clean();
        }

        /**
         * Listen run shutdown and flush buffer
         */
        public static function listenEndBuffer()
        {
            if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
                return;

            register_shutdown_function(function() {
                self::flushBuffer();
                self::endFlushBuffer();
            });
        }

        /**
         * Fix magix quote if server is on magic quote
         */
        public static function fixMagicQuotesGpc()
        {
            $_SERVER = filter_var_array($_SERVER, FILTER_SANITIZE_STRING);
            $_GET    = filter_var_array($_GET,    FILTER_SANITIZE_STRING);

            if (get_magic_quotes_gpc()) {
                stripcslashesResursive($_GET);
                stripcslashesResursive($_POST);
                stripcslashesResursive($_REQUEST);
                stripcslashesResursive($_COOKIE);
            }
        }

    }