<?php

    use Librarys\Database\QueryFactory;

    class SettingSystem
    {

        private static $showAboutDev;
        private static $subtitle;
        private static $description;
        private static $keyword;
        private static $maxSizeThumbUpload;
        private static $fileMimeThumbUpload;
        private static $socialShare;
        private static $createAt;
        private static $modifyAt;

        public static function init()
        {
            if (Librarys\Database\Connect::isConnect()) {
                $query = QueryFactory::createInstance(env('database.tables.setting_system'));
                $query->setLimit(1);

                if ($query->execute() !== false && $query->rows() > 0) {
                    $assoc = $query->assoc();

                    self::$showAboutDev        = boolval($assoc['show_about_dev']);
                    self::$subtitle            = $assoc['subtitle'];
                    self::$description         = $assoc['description'];
                    self::$keyword             = $assoc['keyword'];
                    self::$maxSizeThumbUpload  = intval($assoc['max_size_thumb_upload']);
                    self::$fileMimeThumbUpload = $assoc['file_mime_thumb_upload'];
                    self::$socialShare         = $assoc['social_share'];
                    self::$createAt            = intval($assoc['create_at']);
                    self::$modifyAt            = intval($assoc['modify_at']);

                    if (empty(self::$socialShare) == false && self::$socialShare !== null)
                        self::$socialShare = json_decode(self::$socialShare, true);
                    else
                        self::$socialShare = [];

                    if (empty(self::$fileMimeThumbUpload) == false && self::$fileMimeThumbUpload !== null)
                        self::$fileMimeThumbUpload = json_decode(self::$fileMimeThumbUpload, true);
                    else
                        self::$fileMimeThumbUpload = [];
                }
            }
        }

        public static function isShowAboutDev()
        {
            return self::$showAboutDev;
        }

        public static function getSubTitle()
        {
            return self::$subtitle;
        }

        public static function getDescription()
        {
            return self::$description;
        }

        public static function getKeyword()
        {
            return self::$keyword;
        }

        public static function getMaxSizeThumbUpload()
        {
            return self::$maxSizeThumbUpload;
        }

        public static function getFileMimeThumbUpload()
        {
            return self::$fileMimeThumbUpload;
        }

        public static function getSocialShare()
        {
            return self::$socialShare;
        }

        public static function getCreateAt()
        {
            return self::$createAt;
        }

        public static function getModifyAt()
        {
            return self::$modifyAt;
        }

    }