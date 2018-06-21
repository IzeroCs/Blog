<?php

    use Librarys\Environment\Loader;
    use Librarys\Http\Rewrite;
    use Librarys\Http\Secure\CFSRToken;
    use Librarys\Http\Validate;

    /**
     * Dump variable debug
     * @param $var
     * @return null
     */
    function dump($var)
    {
        echo('<pre>');
        var_dump($var);
        echo('</pre>');

        return null;
    }

    if (function_exists('boolval') == false) {
        /**
         * @param mixed $val
         * @return bool
         */
        function boolval($val)
        {
            return !!$val;
        }
    }

    /**
     * Get env loader and system
     * @param string $key
     * @param        string null $default
     * @return array|false|null|string
     */
    function env($key, $default = null)
    {
        return Loader::env($key, $default);
    }

    /**
     * @param string $name
     * @return array|bool|mixed|null|string
     */
    function lng($name)
    {
        $params = null;

        if (is_array($params) == false) {
            $nums = func_num_args() - 1;
            $args = func_get_args();

            if ($nums >= 1 && is_array($args[1]))
                $params = $args[1];
            else if ($nums > 0 && $nums % 2 == 0)
                $params = array_splice($args, 1, $nums);
        }

        return Librarys\Language\Loader::lng($name, $params);
    }

    /**
     * @param string $name
     * @param array  $params
     * @return array|bool|mixed|null
     */
    function rewrite($name, $params = [], $removeParamsNotProcess = true)
    {
        return Rewrite::getInstance()->get($name, $params, null, $removeParamsNotProcess);
    }

    /**
     * @param string $str
     * @param string $separator
     * @return mixed
     */
    function separator($str, $separator = SP)
    {
        $str = str_replace('/', $separator, $str);
        $str = str_replace('\\', $separator, $str);

        return $str;
    }

    /**
     * @param string|array $value
     */
    function stripcslashesResursive(&$value)
    {
        if (is_array($value) == false)
            $value = stripslashes($value);
        else
            array_walk_recursive($value, __FUNCTION__);
    }

    /**
     * @param string $str
     * @return mixed
     */
    function urlSeparatorMatches($str)
    {
        if (Validate::ip($str))
            $str = separator($str, '/');

        return $str;
    }

    /**
     * @return string
     */
    function cfsrTokenName()
    {
        return CFSRToken::getInstance()->getName();
    }

    /**
     * @return string
     */
    function cfsrTokenValue()
    {
        return CFSRToken::getInstance()->getToken();
    }

