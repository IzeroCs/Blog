<?php

    namespace Librarys\UI;

    use Librarys\Http\Request;

    class Paging
    {

        private $itemNums;
        private $urlDefault;
        private $urlPattern;
        private $parameterMatch;

        const TYPE_CURRENT = 1;
        const TYPE_JUMP    = 2;
        const TYPE_LINK    = 3;

        public function __construct(
            $urlDefault = null,
            $urlPattern = null,
            $parameterMatch = null,
            $itemNums = null
        ) {
            $this->setUrlDefault($urlDefault);
            $this->setUrlPattern($urlPattern);
            $this->setParameterMatch($parameterMatch);
            $this->setItemNums($itemNums);
        }

        public function setUrlDefault($urlDefault)
        {
            $this->urlDefault = $urlDefault;
        }

        public function getUrlDefault()
        {
            return $this->urlDefault;
        }

        public function setUrlPattern($urlPattern)
        {
            $this->urlPattern = $urlPattern;
        }

        public function getUrlPattern()
        {
            return $this->urlPattern;
        }

        public function setParameterMatch($parameterMatch)
        {
            $this->parameterMatch = $parameterMatch;
        }

        public function getParameterMatch()
        {
            return $this->parameterMatch;
        }

        public function setItemNums($itemNums)
        {
            if ($itemNums == null || $itemNums < 1)
                $itemNums = intval(env('paging.number_on_page', 5));

            if ($itemNums % 2 === 0)
                $itemNums++;

            $this->itemNums = $itemNums;
        }

        public function getItemNums()
        {
            return $this->itemNums;
        }

        private function replaceParameter($indexPage)
        {
            if ($this->urlPattern == null || $this->parameterMatch == null)
                return null;

            if (strpos($this->urlPattern, $this->parameterMatch) === false)
                return $this->urlPattern;

            return str_replace($this->parameterMatch, $indexPage, $this->urlPattern);
        }

        public function display($current, $total, $callback = null)
        {
            if ($current > $total)
                Request::redirect($this->urlDefault);

            if ($callback == null)
                $callback = env('paging.display_callback');

            if ($callback == null)
                $callback = function($vars) {
                };

            $arrays  = [];
            $between = $this->itemNums - 2;

            if ($total <= $this->itemNums) {
                for ($i = 1; $i <= $total; ++$i) {
                    if ($current == $i) {
                        $arrays[] = [
                            'type'   => self::TYPE_CURRENT,
                            'number' => $i
                        ];
                    } else {
                        if ($i == 1) {
                            $arrays[] = [
                                'type'   => self::TYPE_LINK,
                                'number' => $i,
                                'link'   => $this->urlDefault
                            ];
                        } else {
                            $arrays[] = [
                                'type'   => self::TYPE_LINK,
                                'number' => $i,
                                'link'   => $this->replaceParameter($i)
                            ];
                        }
                    }
                }
            } else {
                if ($current == 1) {
                    $arrays[] = [
                        'type'   => self::TYPE_CURRENT,
                        'number' => 1
                    ];
                } else {
                    $arrays[] = [
                        'type'   => self::TYPE_LINK,
                        'number' => 1,
                        'link'   => $this->urlDefault,
                        'begin'  => true
                    ];
                }

                if ($current > $between) {
                    if ($current - $between < 1)
                        $index = 1;
                    else
                        $index = $current - $between;

                    if ($index == 1) {
                        $arrays[] = [
                            'type'   => self::TYPE_JUMP,
                            'number' => 1,
                            'link'   => $this->urlDefault
                        ];
                    } else {
                        $arrays[] = [
                            'type'   => self::TYPE_JUMP,
                            'number' => $index,
                            'link'   => $this->replaceParameter($index)
                        ];
                    }
                }

                $offset = [
                    'begin' => 0,
                    'end'   => 0
                ];

                if ($current <= $between) {
                    $offset['begin'] = 2;
                } else {
                    if ($current > $total - $between)
                        $offset['begin'] = $current - ($total - $between);
                    else
                        $offset['begin'] = floor($between >> 1);

                    $offset['begin'] = $current - $offset['begin'];
                }

                if ($current >= $total - $between + 1) {
                    $offset['end'] = $total - 1;
                } else {
                    if ($current <= $between)
                        $offset['end'] = ($between + 1) - $current;
                    else
                        $offset['end'] = floor($between >> 1);

                    $offset['end'] += $current;
                }

                for ($i = $offset['begin']; $i <= $offset['end']; ++$i) {
                    if ($current == $i) {
                        $arrays[] = [
                            'type'   => self::TYPE_CURRENT,
                            'number' => $i
                        ];
                    } else {
                        $arrays[] = [
                            'type'   => self::TYPE_LINK,
                            'number' => $i,
                            'link'   => $this->replaceParameter($i)
                        ];
                    }
                }

                if ($current < $total - $between + 1) {
                    if ($current + $between > $total) {
                        $arrays[] = [
                            'type'   => self::TYPE_JUMP,
                            'number' => $total,
                            'link'   => $this->replaceParameter($total)
                        ];
                    } else {
                        $arrays[] = [
                            'type'   => self::TYPE_JUMP,
                            'number' => $current + $between,
                            'link'   => $this->replaceParameter($current + $between)
                        ];
                    }
                }

                if ($current == $total) {
                    $arrays[] = [
                        'type'   => self::TYPE_CURRENT,
                        'number' => $total
                    ];
                } else {
                    $arrays[] = [
                        'type'   => self::TYPE_LINK,
                        'number' => $total,
                        'link'   => $this->replaceParameter($total),
                        'end'    => true
                    ];
                }
            }

            echo $callback($arrays);
        }

    }

