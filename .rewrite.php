<?php

    return [
        'form' => [
            'sign_up' => [
                'sign_up.php',
                'sign_up.html'
            ],

            'sign_in' => [
                'sign_in.php',
                'sign_in.html'
            ]
        ],

        'url' => [
            'index' => [
                'index.php{$p_page}{$page}',
                'page/{$page}.html'
            ],

            'sign_up' => [
                'sign_up.php',
                'sign_up.html'
            ],

            'sign_in' => [
                'sign_in.php',
                'sign_in.html'
            ],

            'sign_out' => [
                'sign_out.php',
                'sign_out.html'
            ],

            'category' => [
                'category.php{$p_seo}{$seo}{$p_id}{$id}',
                'category/{$id}-{$seo}.html'
            ],

            'category_page' => [
                'category.php{$p_seo}{$seo}{$p_id}{$id}{$p_page}{$page}',
                'category/{$page}/{$id}-{$seo}.html'
            ],

            'article' => [
                'article.php{$p_seo}{$seo}{$p_id}{$id}',
                'article/{$id}-{$seo}.html'
            ],

            'profile' => [
                'user/profile.php{$p_user}{$user}',
                'profile/{$user}'
            ],

            'control' => [
                'home'          => 'control/index.php',
                'list_category' => 'control/list_category.php{$p}{$id}',
                'list_article'  => 'control/list_article.php',
                'list_trash'    => 'control/list_trash.php',

                'create_category' => 'control/create_category.php{$p}{$id}',
                'create_article'  => 'control/create_article.php{$p}{$id}',

                'setting_system'  => 'control/setting_system.php',
                'setting_account' => 'control/setting_account.php',

                'edit_category'   => 'control/edit_category.php{$p}{$id}',
                'move_category'   => 'control/move_category.php{$p}{$id}',
                'remove_category' => 'control/remove_category.php{$p}{$id}',

                'info_article'         => 'control/info_article.php{$p}{$id}',
                'edit_article'         => 'control/edit_article.php{$p}{$id}',
                'move_article'         => 'control/move_article.php{$p}{$id}',
                'remove_article'       => 'control/remove_article.php{$p}{$id}',
                'upload_thumb_article' => 'control/upload_thumb_article.php{$p}{$id}'
            ]
        ]
    ];