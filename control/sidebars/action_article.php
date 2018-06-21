<?php

    use Librarys\Util\Text\Strings;

    $parameter_category = [
        'p' => '?' . PARAMETER_CONTROL_ARTICLE_ID . '=',
        'id'        => intval(Strings::urlencode($_GET[PARAMETER_CONTROL_ARTICLE_ID]))
    ];

    SidebarControl::addArray(lng('control.global.sidebar.action_article.title'), [
        lng('control.global.sidebar.action_article.list.info')         => rewrite('url.control.info_article', $parameter_category),
        lng('control.global.sidebar.action_article.list.edit')         => rewrite('url.control.edit_article', $parameter_category),
        lng('control.global.sidebar.action_article.list.move')         => rewrite('url.control.move_article', $parameter_category),
        lng('control.global.sidebar.action_article.list.remove')       => rewrite('url.control.remove_article', $parameter_category),
        lng('control.global.sidebar.action_article.list.upload_thumb') => rewrite('url.control.upload_thumb_article', $parameter_category)
    ], 1);
