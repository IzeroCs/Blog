<?php

    namespace Librarys\Image;

    use Librarys\File\FileSystem;
    use Librarys\Util\Text\Strings;

    class Optimizer
    {

        protected $filepath;
        protected $fileinfo;
        protected $quality;
        protected $img;
        protected $error;

        private static $formats = [
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png'  => 'imagecreatefrompng',
            'image/gif'  => 'imagecreatefromjpeg',
            'image/bmp'  => 'imagecreatefrombmp'
        ];

        const ERROR_NONE            = 0;
        const ERROR_FILE_NOT_EXISTS = 1;
        const ERROR_NOT_IS_IMAGE    = 2;

        const IMAGE_JPEG = 'image/jpeg';
        const IMAGE_PNG  = 'image/png';
        const IMAGE_GIF  = 'image/gif';

        public function __construct($filepath, $quality = 75)
        {
            $this->error = self::ERROR_NONE;

            if (FileSystem::isTypeFile($filepath) == false) {
                $this->error = self::ERROR_FILE_NOT_EXISTS;

                return;
            }

            $fileinfo = getimagesize($filepath);
            $mime     = trim(strtolower($fileinfo['mime']));
            $img      = null;

            foreach (self::$formats AS $m => $function) {
                if (Strings::equals($mime, $m)) {
                    $img = $function($filepath);
                    break;
                }
            }

            if ($img == null)
                $this->error = self::ERROR_NOT_IS_IMAGE;

            $this->filepath = $filepath;
            $this->fileinfo = $fileinfo;
            $this->quality  = $quality;
            $this->img      = $img;
        }

        public function getErrorCode()
        {
            return $this->error;
        }

        public function optimize($destpath)
        {
            if ($this->error !== self::ERROR_NONE)
                return false;

            if (imagejpeg($this->img, $destpath, $this->quality))
                return true;

            return false;
        }

    }