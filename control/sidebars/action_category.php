<?php

    use Librarys\Util\Text\Strings;

    $parameter_category = [
        'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
        'id'        => intval(Strings::urlencode($_GET[PARAMETER_CONTROL_LIST_CATEGORY_ID]))
    ];

    SidebarControl::addArray(lng('control.global.sidebar.action_category.title'), [
        lng('control.global.sidebar.action_category.list.edit')   => rewrite('url.control.edit_category', $parameter_category),
        lng('control.global.sidebar.action_category.list.move')   => rewrite('url.control.move_category', $parameter_category),
        lng('control.global.sidebar.action_category.list.remove') => rewrite('url.control.remove_category', $parameter_category)
    ], 1);
