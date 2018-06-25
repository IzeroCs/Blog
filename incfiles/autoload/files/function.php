<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\Http\Rewrite;
    use Librarys\UI\Alert;
    use Librarys\UI\Paging;
    use Librarys\Util\Text\Strings;

    /**
     * @param string|null $title
     * @param string|null $idAlert
     */
    function require_header($title = null, $idAlert = null)
    {
        if ($title != null)
            $GLOBALS['titleHeader'] = $title;

        require_once(env('app.app_root') . DIRECTORY_SEPARATOR . 'incfiles' . DIRECTORY_SEPARATOR . 'header.php');

        if ($idAlert != null)
            Alert::setID($idAlert);
    }

    /**
     *
     */
    function require_footer()
    {
        require_once(env('app.app_root') . DIRECTORY_SEPARATOR . 'incfiles' . DIRECTORY_SEPARATOR . 'footer.php');
    }

    /**
     * @param null|string $urlIgone
     * @param bool        $print
     * @return null|string
     */
    function get_control_sidebar_list_action($urlIgone = null, $print = true)
    {
        SidebarControl::init();

        $datas    = SidebarControl::getDatas();
        $buffer   = null;
        $urlIgone = Rewrite::removeParameterTag($urlIgone);

        foreach ($datas AS $title => $list) {
            $listBuffer = null;

            foreach ($list AS $label => $args) {
                $url = $args['uri'];
                $loaded = $args['loaded'];

                if ($urlIgone == null || strpos($url, $urlIgone) !== 0) {
                    $listBuffer .= '<li>';

                    if ($loaded)
                        $listBuffer .= '<a href="' . $url . '">';
                    else
                        $listBuffer .= '<a href="' . $url . '" class="not-loaded">';

                    $listBuffer .= '<span class="icomoon icon-rectange"></span>';
                    $listBuffer .= '<span>' . $label . '</span>';
                    $listBuffer .= '</a>';
                    $listBuffer .= '</li>';
                }
            }

            if ($listBuffer !== null) {
                $buffer .= '<div class="entry">';
                $buffer .= '<ul class="list-action">';
                $buffer .= '<li><span>' . $title . '</span></li>';
                $buffer .= $listBuffer;
                $buffer .= '</ul>';
                $buffer .= '</div>';
            }
        }

        if ($print == false)
            return $buffer;
        else
            echo $buffer;

        return $buffer;
    }

    /**
     * @param bool $print
     * @return null|string
     */
    function get_sidebar_about_development($print = true)
    {
        if (SettingSystem::isShowAboutDev() == false)
            return null;

        $query = QueryFactory::createInstance(env('database.tables.about_development'));
        $query->setCommand(QueryAbstract::COMMAND_SELECT);
        $query->setLimit(1);

        if ($query->rows() > 0) {
            $buffer = null;
            $assoc  = $query->assoc();

            if (empty($assoc['wallpaper']))
                $assoc['wallpaper'] = 'images/about-dev/wallpaper.jpg';

            if (empty($assoc['avatar']))
                $assoc['avatar'] = 'images/about-dev/avatar.png';

            $wallpaper = env('app.http_host') . '/resource/' . cfsrTokenValue() . '/' . $assoc['wallpaper'];
            $avatar    = env('app.http_host') . '/resource/' . cfsrTokenValue() . '/' . $assoc['avatar'];

            $buffer .= '<li><div class="about-dev">';

            $buffer .= '<div class="wallpaper"><img src="' . $wallpaper . '"/></div>';
            $buffer .= '<div class="avatar"><img src="' . $avatar . '"/></div>';

            $buffer .= '<div class="detail">';
            $buffer .= '<span class="title">' . Strings::enhtml($assoc['title']) . '</span>';
            $buffer .= '<div class="divider"></div>';
            $buffer .= '<span class="content">' . Strings::enhtml($assoc['content']) . '</span>';
            $buffer .= '</div>';

            if (empty($assoc['social']) == false) {
                $socials = json_decode($assoc['social'], true);

                if (is_array($socials)) {
                    $buffer .= '<ul class="social">';

                    foreach ($socials AS $icon => $url)
                        $buffer .= '<li><a href="' . $url . '" target="_blank"><span class="icomoon icon-' . $icon . '"></span></a></li>';

                    $buffer .= '</ul>';
                }
            }

            $buffer .= '</div></li>';

            if ($print == false)
                return $buffer;
            else
                echo $buffer;

            return $buffer;
        }

        return null;
    }

    function get_sidebar_info($print = true)
    {
        $buffer = '<div class="entry">';
        $buffer .= '<div class="info">';
        $buffer .= '<span>' . env('author') . ' - ' . env('version', '1.0') . '</span>';
        $buffer .= '</div>';
        $buffer .= '</div>';

        if ($print == false)
            return $buffer;
        else
            echo $buffer;

        return $buffer;
    }

    function alert_ui_display_callback($lists)
    {
        $buffer = '<ul class="alert">';

        foreach ($lists AS $index => $alert) {
            if (is_object($alert['message']))
                $alert['message'] = 'Object';
            else if (is_array($alert['message']))
                $alert['message'] = 'Array';

            $buffer .= '<li class="' . $alert['type'] . '">';

            if ($alert['type'] != Alert::NONE)
                $buffer .= '<span><span class="icomoon icon-' . $alert['type'] . '"></span>';

            $buffer .= '<span>' . $alert['message'] . '</span>';
            $buffer .= '</li>';
        }

        $buffer .= '</ul>';

        return $buffer;
    }

    function paging_ui_display_callback($arrays)
    {
        if (is_array($arrays) == false || count($arrays) <= 0)
            return null;

        $buffer = '<ul class="paging">';

        foreach ($arrays AS $items) {
            if ($items['type'] === Paging::TYPE_CURRENT) {
                $buffer .= '<li class="current">';
                $buffer .= '<span>' . $items['number'] . '</span>';
                $buffer .= '</li>';
            } else if ($items['type'] === Paging::TYPE_JUMP) {
                $buffer .= '<li class="jump">';
                $buffer .= '<a href="' . $items['link'] . '">';
                $buffer .= '<span>...</span>';
                $buffer .= '</a>';
                $buffer .= '</li>';
            } else if ($items['type'] === Paging::TYPE_LINK) {
                $buffer .= '<li class="link">';
                $buffer .= '<a href="' . $items['link'] . '">';
                $buffer .= '<span>' . $items['number'] . '</span>';
                $buffer .= '</a>';
                $buffer .= '</li>';
            }
        }

        $buffer .= '</ul>';

        return $buffer;
    }