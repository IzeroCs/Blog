<?php

    namespace Librarys\UI;

    abstract class Breadcrumbs
    {

        /**
         * @var array $firstCrumbs
         */
        protected $firstCrumbs;

        /**
         * @var array $middleCrumbs
         */
        protected $middleCrumbs;

        /**
         * @var array $lastCrumbs
         */
        protected $lastCrumbs;

        /**
         * @var array $params
         */
        protected $params;

        /**
         * Breadcrumbs constructor.
         */
        protected function __construct($params)
        {
            $this->firstCrumbs  = [];
            $this->middleCrumbs = [];
            $this->lastCrumbs   = [];
            $this->params       = $params;
        }

        protected function __wakeup()
        {
            // TODO: Implement __wakeup() method.
        }

        protected function __clone()
        {
            // TODO: Implement __clone() method.
        }

        public function addFirstCrumb($url, $title, $isLink = true, $putToFirst = false, $encodeHtml = false)
        {
            $this->addCrumb($url, $title, $isLink, $putToFirst, $encodeHtml, $this->firstCrumbs);
        }

        public function addMiddleCrumb($url, $title, $isLink = true, $putToFist = false, $encodeHtml = false)
        {
            $this->addCrumb($url, $title, $isLink, $putToFist, $encodeHtml, $this->middleCrumbs);
        }

        public function addLastCrumb($url, $title, $isLink = true, $putToFist = false, $encodeHtml = false)
        {
            $this->addCrumb($url, $title, $isLink, $putToFist, $encodeHtml, $this->lastCrumbs);
        }

        private function addCrumb($url, $title, $isLink, $putToFirst, $encodeHtml, &$array)
        {
            $entry = [
                'url'     => $url,
                'title'   => $title,
                'is_link' => $isLink,
                'en_html' => $encodeHtml
            ];

            if ($putToFirst)
                array_unshift($array, $entry);
            else
                $array[] = $entry;
        }

        public abstract function createDisplayCrumbs($arrayCrumbs);

        public function mergeCrumbs()
        {
            return array_merge(
                $this->firstCrumbs,
                $this->middleCrumbs,
                $this->lastCrumbs
            );
        }

        public function display($print = true)
        {
            $buffer = $this->createDisplayCrumbs($this->mergeCrumbs());

            if ($print)
                echo $buffer;

            return $buffer;
        }

    }