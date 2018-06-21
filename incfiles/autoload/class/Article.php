<?php

    use Librarys\Util\Text\Strings;
    use Librarys\File\FileSystem;
    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\Image\Optimizer;

    class Article
    {

        /**
         * @var string $title
         */
        private static $title;

        public static function processQuillContentPost($contents, $title)
        {
            self::$title = $title;

            return preg_replace_callback('/<img.+?filename="(.+?)".*?>/si', function($matches) {
                $filename = trim($matches[1]);
                $filetmp = env('app.document_root') . SP .
                            'assets' . SP .
                            'uploads' . SP .
                            'tmp' . SP .
                            $filename;

                $filedest = env('app.document_root') . SP .
                            'assets' . SP .
                            'uploads' . SP .
                            'images' . SP .
                            $filename;

                FileSystem::copy($filetmp, $filedest, true, true);

                return '<img src="' . $filename . '" alt="' . Strings::enhtml(self::$title) . '"/>';
            }, $contents);
        }

        public static function processContentGet($contents)
        {
            return preg_replace_callback('/(<img.+?src=")(.+?)(".*?>)/si', function($matches) {
                $path  = env('app.http_host') . '/resource/' .
                         cfsrTokenValue() . '/uploads/images/' . $matches[2];

                return $matches[1] . $path . $matches[3];
            }, $contents);
        }

        public static function processContentDetail($contents)
        {
            $cutter = 200;
            $ellipsis = '...';
            $contents = strip_tags($contents);
            $length = strlen($contents);

            if ($length > $cutter) {
                $lastSpace = strpos($contents, ' ', $cutter);

                if ($lastSpace != false && $lastSpace < $length) {
                    $contents = substr($contents, 0, $lastSpace);
                    $contents = trim($contents);
                    $contents = $contents . $ellipsis;
                }
            }

            return $contents;
        }

        public static function removeThumb($id)
        {
            $query = QueryFactory::createInstance(env('database.tables.article'));
            $query->setCommand(QueryAbstract::COMMAND_SELECT);
            $query->addSelect('id');
            $query->addSelect('thumb');
            $query->addWhere('id', QueryAbstract::escape(intval($id)));
            $query->setLimit(1);

            if ($query->execute() !== false && $query->rows() > 0) {
                $assoc = $query->assoc();

                if (empty($assoc['thumb']) == false) {
                    $remove = false;

                    if (empty($assoc['thumb']) == false) {
                        $path = env('app.document_root') . SP .
                            'assets' . SP .
                            'uploads' . SP .
                            'thumbs' . SP .
                            $assoc['thumb'];

                        if (FileSystem::isTypeFile($path))
                            $remove = FileSystem::unlink($path);
                        else
                            $remove = true;
                    } else {
                        $remove = true;
                    }

                    if ($remove) {
                        $query = QueryFactory::createInstance(env('database.tables.article'));
                        $query->setCommand(QueryAbstract::COMMAND_UPDATE);
                        $query->addSelect('id');
                        $query->addData('thumb', '');
                        $query->addWhere('id', QueryAbstract::escape(intval($id)));
                        $query->setLimit(1);

                        return $query->execute(true);
                    }
                }
            }

            return false;
        }

        public static function updateThumb($id, $thumbSrc, $thumbName)
        {
            $query = QueryFactory::createInstance(env('database.tables.article'));
            $query->setCommand(QueryAbstract::COMMAND_SELECT);
            $query->addSelect('id');
            $query->addSelect('thumb');
            $query->addWhere('id', QueryAbstract::escape(intval($id)));
            $query->setLimit(1);

            if ($query->execute() !== false && $query->rows() > 0) {
                $assoc = $query->assoc();
                $remove = false;

                if (empty($assoc['thumb']) == false) {
                    $remove = false;
                    $path   = env('app.document_root') . SP .
                        'assets' . SP .
                        'uploads' . SP .
                        'thumbs' . SP .
                        $assoc['thumb'];

                    if (FileSystem::isTypeFile($path))
                        $remove = FileSystem::unlink($path);
                    else
                        $remove = true;
                } else {
                    $remove = true;
                }

                if ($remove) {
                    $thumbDest = env('app.document_root') . SP .
                        'assets' . SP  .
                        'uploads' . SP .
                        'thumbs' . SP .
                        $thumbName;

                    if (FileSystem::isTypeFile($thumbSrc)) {
                        $optimizer = new Optimizer($thumbSrc, 50);

                        if ($optimizer->optimize($thumbDest)) {
                            $query = QueryFactory::createInstance(env('database.tables.article'));
                            $query->setCommand(QueryAbstract::COMMAND_UPDATE);
                            $query->addSelect('id');
                            $query->addData('thumb', $thumbName);
                            $query->addWhere('id', QueryAbstract::escape(intval($id)));
                            $query->setLimit(1);

                            return $query->execute(true);
                        }
                    }
                }
            }

            return false;
        }

        public static function generatorThumbName($str)
        {
            return md5(md5($str . time()) . time()) . '.' . FileSystem::mime($str);
        }

    }
