<?php

    use Librarys\Util\Text\Strings;

    class SidebarControl
    {

        private static $filerequire;
        private static $sidebars;

        public static function init()
        {
            self::$sidebars = [];

            $rewrites = [];

            $rewrites['list.category'] = null;
            $rewrites['list.article']  = null;
            $rewrites['list.trash']    = null;

            $rewrites['create.category'] = null;
            $rewrites['create.article']  = null;

            if (isset($_GET[PARAMETER_CONTROL_LIST_CATEGORY_ID])) {
                $parameter_category = [
                    'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
                    'id'        => intval(Strings::urlencode($_GET[PARAMETER_CONTROL_LIST_CATEGORY_ID]))
                ];

                $rewrites['list.category']   = rewrite('url.control.list_category', $parameter_category);
                $rewrites['create.category'] = rewrite('url.control.create_category', $parameter_category);
                $rewrites['create.article']  = rewrite('url.control.create_article', $parameter_category);
            } else {
                $rewrites['list.category']   = rewrite('url.control.list_category');
                $rewrites['create.category'] = rewrite('url.control.create_category');
            }

            $rewrites['list.article'] = rewrite('url.control.list_article');
            $rewrites['list.trash']   = rewrite('url.control.list_trash');

            self::addArray(lng('control.global.sidebar.manager.title'), [
                lng('control.global.sidebar.manager.list.category') => $rewrites['list.category'],
                lng('control.global.sidebar.manager.list.article')  => $rewrites['list.article'],
                lng('control.global.sidebar.manager.list.trash')    => $rewrites['list.trash']
            ]);

            self::add(lng('control.global.sidebar.create.title'), lng('control.global.sidebar.create.list.category'), $rewrites['create.category']);

            if ($rewrites['create.article'] !== null)
                self::add(lng('control.global.sidebar.create.title'), lng('control.global.sidebar.create.list.article'), $rewrites['create.article'], -1, false);

//            self::addArray(lng('control.global.sidebar.setting.title'), [
//                lng('control.global.sidebar.setting.list.system')  => rewrite('url.control.setting_system'),
//                lng('control.global.sidebar.setting.list.account') => rewrite('url.control.setting_account')
//            ]);

            if (self::$filerequire !== null)
                require_once(self::$filerequire);
        }

        /**
         * @param string $title
         * @param string $label
         * @param string $uri
         * @param int    $level
         */
        public static function add($title, $label, $uri, $level = -1, $loaded = true)
        {
            if ($level !== -1 && isset(self::$sidebars[$title]) == false) {
                $sidebars = [];
                $index    = 0;

                foreach (self::$sidebars AS $titleOriginal => $datasOriginal) {
                    if ($index++ === $level) {
                        $sidebars[$title][$label] = [
                            'uri' => $uri,
                            'loaded' => $loaded
                        ];

                        $sidebars[$titleOriginal] = $datasOriginal;
                    } else {
                        $sidebars[$titleOriginal] = $datasOriginal;
                    }
                }

                self::$sidebars = $sidebars;
            } else {
                self::$sidebars[$title][$label] = [
                    'uri' => $uri,
                    'loaded' => $loaded
                ];
            }
        }

        /**
         * @param string $title
         * @param array  $datas
         * @param int    $level
         */
        public static function addArray($title, $datas, $level = -1)
        {
            foreach ($datas AS $label => $args) {
                $uri = $args;
                $loaded = true;

                if (is_array($args)) {
                    $uri = $args['uri'];

                    if (isset($args['loaded']))
                        $loaded = $args['loaded'];
                }

                self::add($title, $label, $uri, $level, $loaded);
            }
        }

        /**
         * @return array
         */
        public static function getDatas()
        {
            return self::$sidebars;
        }

        /**
         * @param string $filepath
         */
        public static function setFileRequire($filepath)
        {
            self::$filerequire = $filepath;
        }

    }