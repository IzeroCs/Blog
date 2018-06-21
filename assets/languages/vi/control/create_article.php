<?php

    return [
        'title'              => 'Tạo bài viết',
        'category_container' => 'Danh mục chứa:',

        'input' => [
            'label' => [
                'title' => 'Tiêu đề bài viết:',
                'seo'   => 'SEO bài viết (tùy chọn):',
                'url'   => 'URL chuyển hướng (tùy chọn):'
            ],

            'checkbox' => [
                'options' => [
                    'title'                => 'Tùy chọn:',
                    'hidden_article'       => 'Ẩn bài viết',
                    'get_thumb_in_article' => 'Lấy ảnh bài viết làm mô tả'
                ]
            ],

            'placeholder' => [
                'input_title' => 'Nhập tiêu đề bài biết',
                'input_seo'   => 'Nhập seo bài viết (tùy chọn)',
                'input_url'   => 'Nhập url chuyển hướng bài viết (tùy chọn)'
            ]
        ],

        'button' => [
            'create' => 'Tạo bài',
            'cancel' => 'Quay lại'
        ],

        'alert' => [
            'not_input_title'               => 'Chưa nhập tiêu đề',
            'not_input_content'             => 'Chưa nhập nội dung',
            'title_article_exists'          => 'Tiêu đề bài viết đã tồn tại',
            'seo_article_exists'            => 'SEO bài viết đã tồn tại',
            'category_container_not_exists' => 'Danh mục chứa không tồn tại',
            'create_article_failed'         => 'Tạo bài viết thất bại',
            'create_article_success'        => 'Tạo bài viết thành công'
        ]
    ];