<?php

    namespace Librarys\File;

    class FileSystem
    {

        private $filepath;
        private $filemime;

        const FILENAME_VALIDATE = '\\/:*?"<>|';

        /**
         * FileSystem constructor.
         * @param string $filepath
         */
        public function __construct($filepath)
        {
            $this->setFilepath($filepath);
        }

        /**
         * @return bool
         */
        public function isFile()
        {
            return self::isTypeFile($this->filepath);
        }

        /**
         * @return bool
         */
        public function isDirectory()
        {
            return self::isTypeDirectory($this->filepath);
        }

        /**
         * @return false|int
         */
        public function readFile()
        {
            return @readfile($this->filepath);
        }

        /**
         * @param string $filepath
         */
        public function setFilepath($filepath)
        {
            $this->filepath = separator($filepath, SP);
            $this->filemime  = self::mime($this->getFileName());

            if (empty($this->filemime))
                $this->filemime = null;
        }

        /**
         * @return mixed
         */
        public function getFilepath()
        {
            return $this->filepath;
        }

        /**
         * @return string
         */
        public function getFileName()
        {
            return basename($this->filepath);
        }

        /**
         * @return mixed
         */
        public function getFileMime()
        {
            return $this->filemime;
        }

        /**
         * @return int|string
         */
        public function getFileSize()
        {
            return self::fileSize($this->filepath);
        }

        /**
         * @param string $name
         * @return bool
         */
        public static function isNameValidate($name)
        {
            return strpbrk($name, self::FILENAME_VALIDATE) == false;
        }

        /**
         * @param string $name
         * @return null|string
         */
        public static function fileNameFix($name)
        {
            if ($name == null || self::isNameValidate($name))
                return $name;

            $chars = str_split($name, 1);

            if (is_array($chars) == false)
                return $name;

            $buffer = null;

            foreach ($chars AS $char) {
                if (self::isNameValidate($char))
                    $buffer .= $char;
                else
                    $buffer .= '_';
            }

            return $buffer;
        }

        /**
         * @param string|FileSystem $file
         * @return null|string
         */
        public static function mime($file)
        {
            if ($file instanceof FileSystem)
                $file = $file->getFileName();
            else
                $file = basename($file);

            $lastIndex = strrpos($file, '.');

            if ($lastIndex !== false)
                return strtolower(substr($file, $lastIndex + 1));

            return null;
        }

        /**
         * @param string $path
         * @param string|int|null $chmod
         * @return bool|int|string
         */
        public static function chmod($path, $chmod = null)
        {
            if ($chmod == null) {
                $perms = @fileperms($path);

                if ($perms !== false) {
                    $perms = decoct($perms);
                    $perms = substr($perms, strlen($perms) == 5 ? 2 : 3, 3);
                } else {
                    $perms = "000";
                }

                return $perms;
            } else {
                if (is_numeric($chmod) === false)
                    $chmod = strval($chmod);

                if (@chmod($path, intval("0" . $chmod, 8)))
                    return true;
            }

            return false;
        }

        /**
         * @param string $path
         * @param bool $superParentCreate
         * @return bool
         */
        public static function mkdir($path, $superParentCreate = false)
        {
            if ($superParentCreate == false)
                return @mkdir($path);

            $rootPath        = env('server.document_root');
            $pathAbsolute    = $path;
            $isRootPathFirst = false;
            $separator       = SP;

            if (stripos($path, $rootPath) === 0) {
                $pathAbsolute    = substr($path, strlen($rootPath));
                $isRootPathFirst = true;

                if (strpos($pathAbsolute, $separator) === 0)
                    $pathAbsolute = substr($pathAbsolute, 1);
            }

            $pathSplits  = explode($separator, $pathAbsolute);
            $countSplits = count($pathSplits);

            if (is_array($pathSplits) == false && $countSplits > 0)
                return @mkdir($path);

            $pathBuffer         = null;
            $isFirstIndexBuffer = false;

            if ($separator === '/' && $isRootPathFirst == false)
                $pathBuffer = $separator;
            else if ($isRootPathFirst)
                $pathBuffer = $rootPath;

            for ($i = 0; $i < $countSplits; ++$i) {
                $entry = $pathSplits[$i];

                if (empty($entry) == false) {
                    if ($isFirstIndexBuffer == false) {
                        if ($isRootPathFirst == false && $separator === '/')
                            $pathBuffer .= $entry;
                        else if ($isRootPathFirst)
                            $pathBuffer .= $separator . $entry;
                        else
                            $pathBuffer .= $entry;

                        $isFirstIndexBuffer = true;
                    } else {
                        $pathBuffer = self::filter($pathBuffer . $separator . $entry);
                    }

                    if (self::exists($pathBuffer) == false && @mkdir($pathBuffer) == false)
                        return false;
                }
            }

            return true;
        }

        /**
         * @param string|array $src
         * @param string $dest
         * @param bool $parent
         * @param bool $move
         * @param null $callbackIsFileExists
         * @return bool
         */
        public static function copy(
            $src,
            $dest,
            $parent = true,
            $move = false,
            &$callbackIsFileExists = null
        ) {
            if ($callbackIsFileExists == null) {
                $callbackIsFileExists = function($directory, $filename, $isDirectory) {
                    return $directory . SP . $filename;
                };
            }

            if (is_array($src)) {
                foreach ($src AS $entry) {
                    $path = $dest . SP . $entry;

                    if (self::isTypeFile($path)) {
                        $file = $parent . SP . $entry;

                        if (is_file($file))
                            $file = $callbackIsFileExists($parent, $entry, false);

                        // If file is null skip file
                        if ($file == null)
                            return true;

                        if (@copy($path, $file) == false)
                            return false;

                        if ($move)
                            self::unlink($path);
                    } else if (self::isTypeDirectory($path)) {
                        $file = $parent . SP . $entry;

                        if (self::isTypeDirectory($file))
                            $file = $callbackIsFileExists($parent, $entry, true);

                        // If file is null skip file
                        if ($file == null)
                            return true;

                        if (self::copy($path, $file, $move) == false)
                            return false;
                    } else {
                        return false;
                    }
                }

                return true;
            } else if (self::isTypeFile($src)) {
                if (self::isTypeFile($dest)) {
                    $separatorLastIndex = strrpos($dest, SP);

                    if ($separatorLastIndex === false)
                        return false;

                    $directory = substr($dest, 0, $separatorLastIndex);
                    $filename  = substr($dest, $separatorLastIndex + 1);

                    $dest = $callbackIsFileExists($directory, $filename, false);
                }

                if ($dest == null)
                    return true;

                if (@copy($src, $dest) == false)
                    return false;

                if ($move)
                    self::unlink($src);

                return true;
            } else if (self::isTypeDirectory($src)) {
                $handle = self::scanDirectory($src);

                if ($handle !== false) {
                    if (($parent && $src != SP) || $parent == false) {
                        if (self::isTypeFile($dest))
                            return false;

                        if (self::isTypeDirectory($dest)) {
                            $separatorLastIndex = strrpos($dest, SP);

                            if ($separatorLastIndex === false)
                                return false;

                            $directory = substr($dest, 0, $separatorLastIndex);
                            $filename  = substr($dest, $separatorLastIndex + 1);
                            $dest       = $callbackIsFileExists($directory, $filename, true);
                        }

                        if ($dest == null)
                            return true;

                        if (self::isTypeDirectory($dest) == false && self::mkdir($dest) == false)
                            return false;
                    }

                    foreach ($handle AS $entry) {
                        if ($entry != '.' && $entry != '..') {
                            $entrySrc  = $src  . SP . $entry;
                            $entryDest = $dest . SP . $entry;

                            if (self::isTypeFile($entrySrc)) {
                                if (self::isTypeFile($entryDest))
                                    $entryDest = $callbackIsFileExists($dest, $entry, false);

                                if ($entryDest == null)
                                    return true;

                                if (@copy($entrySrc, $entryDest) == false)
                                    return false;

                                if ($move)
                                    self::unlink($entrySrc);
                            } else if (self::isTypeDirectory($entrySrc)) {
                                if (self::isTypeDirectory($entryDest))
                                    $entryDest = $callbackIsFileExists($dest, $entry, true);

                                if ($entryDest == null)
                                    return true;

                                if (self::copy($entrySrc, $entryDest, false, $move, $callbackIsFileExists) == false)
                                    return false;
                            } else {
                                return false;
                            }
                        }
                    }

                    if ($move)
                        return self::rrmdir($src, null);
                    else
                        return true;
                }

                return true;
            }

            return false;
        }

        /**
         * @param string $name
         * @return bool
         */
        public static function invalid($name)
        {
            return strpbrk($name, "\\/?%*:|\"<>") == false;
        }

        /**
         * @param string $src
         * @param string $dest
         * @return bool
         */
        public static function rename($src, $dest)
        {
            return rename($src, $dest);
        }

        /**
         * @param string $path
         * @return bool
         */
        public static function rmdir($path)
        {
            return @rmdir($path);
        }

        /**
         * @param string|array $path
         * @param string|null $directory
         * @return bool
         */
        public static function rrmdir($path, $directory = null)
        {
            if (is_array($path)) {
                foreach ($path AS $entry) {
                    $filename = $directory . SP . $entry;

                    if (self::isTypeFile($filename)) {
                        if (self::unlink($filename) == false)
                            return false;
                    } else if (self::isTypeDirectory($filename)) {
                        if (self::rrmdir($filename, null) == false)
                            return false;
                    } else {
                        return false;
                    }
                }

                return true;
            } else if (self::isReadable($path) && self::isTypeFile($path)) {
                return self::unlink($path);
            } else {
                $handle = self::scanDirectory($path);

                if ($handle !== false) {
                    foreach ($handle AS $entry) {
                        if ($entry != '.' && $entry != '..') {
                            $filename = $path . SP . $entry;

                            if (self::isTypeFile($filename)) {
                                if (self::unlink($filename) == false)
                                    return false;
                            } else if (self::isTypeDirectory($filename)) {
                                if (self::rrmdir($filename, null) == false)
                                    return false;
                            } else {
                                return false;
                            }
                        }
                    }

                    return self::rmdir($path);
                }
            }

            return false;
        }

        /**
         * @param string|int $size
         * @return int|string
         */
        public static function sizeToString($size)
        {
            $size = @intval($size);

            if ($size < 1024)
                $size = $size . 'B';
            else if ($size < 1048576)
                $size = round($size / 1024, 2) . 'KB';
            else if ($size < 1073741824)
                $size = round($size / 1048576, 2) . 'MB';
            else
                $size = round($size / 1073741824, 2) . 'GB';

            return $size;
        }

        /**
         * @param string $path
         * @return bool
         */
        public static function unlink($path)
        {
            return @unlink($path);
        }

        /**
         * @param string $path
         * @param bool $isPathZIP
         * @return mixed
         */
        public static function filter($path, $isPathZIP = false)
        {
            $SP = SP;

            if ($SP == '\\')
                $SP = '\\\\';

            $path = str_replace('\\', $SP, $path);
            $path = str_replace('/',  $SP, $path);

            $path = preg_replace('#\\{1,}#', $SP, $path);
            $path = preg_replace('#/{1,}#',  $SP, $path);

            $path = preg_replace('#' . $SP . '\.'   . $SP . '#', $SP . $SP, $path);
            $path = preg_replace('#' . $SP . '\.\.' . $SP . '#', $SP . $SP, $path);

            $path = preg_replace('#' . $SP . '\.{1,2}$#', $SP . $SP, $path);
            $path = preg_replace('#' . $SP . '{2,}#',     $SP,       $path);

            if ($isPathZIP)
                $path = preg_replace('#' . $SP . '?(.+?)' . $SP . '?$#', '$1', $path);
            else
                $path = preg_replace('#(.+?)' . $SP . '$#', '$1', $path);

            return $path;
        }

        /**
         * @param string $path
         * @return bool
         */
        public static function exists($path)
        {
            return @file_exists($path);
        }

        /**
         * @param string $path
         * @return bool|int|string
         */
        public static function perms($path)
        {
            $perms = fileperms($path);

            if ($perms !== false) {
                $perms = decoct($perms);
                $perms = substr($perms, strlen($perms) == 5 ? 2 : 3, 3);
            } else {
                $perms = 0;
            }

            return $perms;
        }

        /**
         * @param string $path
         * @param bool $convertToString
         * @return int|string
         */
        public static function fileSize($path, $convertToString = false)
        {
            if ($convertToString == false)
                return @filesize($path);

            return self::sizeToString(@filesize($path));
        }

        /**
         * @param string $directory
         * @return array|bool
         */
        public static function scanDirectory($directory)
        {
            if (self::exists($directory))
                return @scandir($directory);

            return false;
        }

        /**
         * @param string $pattern
         * @param int $flag
         * @return array
         */
        public static function globDirectory($pattern, $flag = 0)
        {
            return @glob($pattern, $flag);
        }

        /**
         * @param string $path
         * @return bool
         */
        public static function isTypeFile($path)
        {
            return @is_file($path);
        }

        /**
         * @param string $path
         * @return bool
         */
        public static function isTypeDirectory($path)
        {
            return @is_dir($path);
        }

        /**
         * @param string $path
         * @return bool
         */
        public static function isLink($path)
        {
            return @is_link($path);
        }

        /**
         * @param string $path
         * @return bool
         */
        public static function isReadable($path)
        {
            return @is_readable($path);
        }

        /**
         * @param string $path
         * @return bool
         */
        public static function isWriteable($path)
        {
            return @is_writeable($path);
        }

        /**
         * @param string $path
         * @return bool
         */
        public static function isExecutable($path)
        {
            return @is_executable($path);
        }

        /**
         * @param string $filename
         * @return bool
         */
        public static function isFileOrDirectory($filename)
        {
            return $filename != '.' && $filename != '..';
        }

        /**
         * @param string $path
         * @param string $mode
         * @return bool|resource
         */
        public static function fileOpen($path, $mode)
        {
            return @fopen($path, $mode);
        }

        /**
         * @param string $host
         * @param int $port
         * @param null $errno
         * @param null $errstr
         * @param int $timeout
         * @return resource
         */
        public static function fileSockOpen($host, $port = -1, &$errno = null, &$errstr = null, $timeout = 30)
        {
            return @fsockopen($host, $port, $errno, $errstr, $timeout);
        }

        /**
         * @param resource $handle
         * @return bool
         */
        public static function fileClose($handle)
        {
            return @fclose($handle);
        }

        /**
         * @param resource $handle
         * @param int $length
         * @return bool|null|string
         */
        public static function fileRead($handle, $length)
        {
            if ($length <= 0)
                return null;

            return @fread($handle, $length);
        }

        /**
         * @param resource $handle
         * @param int $offet
         * @param int $whence
         * @return int
         */
        public static function fileSeek($handle, $offet, $whence = SEEK_SET)
        {
            return @fseek($handle, $offet, $whence);
        }

        /**
         * @param resource $handle
         * @return bool
         */
        public static function fileEndOfFile($handle)
        {
            return @feof($handle);
        }

        /**
         * @param resource $handle
         * @param string $buffer
         * @param int|null $length
         * @return bool
         */
        public static function fileWrite($handle, $buffer, $length = null)
        {
            if ($buffer == null)
                $buffer = '';

            if ($length == null)
                $length = strlen($buffer);

            return @fwrite($handle, $buffer, $length) && @fflush($handle);
        }

        /**
         * @param resource $handle
         * @return bool
         */
        public static function fileFlush($handle)
        {
            return @fflush($handle);
        }

        /**
         * @param string $path
         * @return bool|null|string
         */
        public static function fileReadContents($path)
        {
            $handle = self::fileOpen($path, 'ra');

            if ($handle == false)
                return false;

            $data = self::fileRead($handle, self::fileSize($path));

            if ($data == false) {
                self::fileClose($handle);
                return false;
            } else {
                self::fileClose($handle);
            }

            return $data;
        }

        /**
         * @param string $path
         * @param string $buffer
         * @return bool
         */
        public static function fileWriteContents($path, $buffer)
        {
            $handle = self::fileOpen($path, 'wa+');

            if ($handle == false || $handle === 0 || $handle === false)
                return false;

            if (self::fileWrite($handle, $buffer) == false) {
                self::fileClose($handle);

                return false;
            } else {
                self::fileFlush($handle);
                self::fileClose($handle);
            }

            return true;
        }

        /**
         * @param resource $handle
         * @param int $length
         * @return bool|string
         */
        public static function fileGetsLine($handle, $length = 1024)
        {
            return @fgets($handle, $length);
        }

        /**
         * @param string $filename
         * @param int $flag
         * @param resource|null $context
         * @return array|bool
         */
        public static function fileReadsToArray($filename, $flag = 0, $context = null)
        {
            return @file($filename, $flag, $context);
        }

        /**
         * @param string $path
         * @return bool|int
         */
        public static function fileMTime($path)
        {
            return @filemtime($path);
        }

        /**
         * @param string $path
         * @return bool|int
         */
        public static function fileOwner($path)
        {
            return @fileowner($path);
        }

        /**
         * @param string $path
         * @return bool|int
         */
        public static function fileGroup($path)
        {
            return @filegroup($path);
        }

    }
