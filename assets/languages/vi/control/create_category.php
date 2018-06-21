<?php

    return [
        'title' => 'Tạo danh mục',

        'input' => [
            'label' => [
                'title'       => 'Tiêu đề danh mục:',
                'description' => 'Mô tả danh mục (tùy chọn):',
                'seo'         => 'SEO danh mục (tùy chọn):',
                'url'         => 'URL chuyển hướng (tùy chọn):',
            ],

            'checkbox' => [
                'options' => [
                    'title'           => 'Tùy chọn:',
                    'hidden_category' => 'Ẩn danh mục'
                ]
            ],

            'placeholder' => [
                'input_title'       => 'Nhập tiêu đề danh mục',
                'input_description' => 'Nhập mô tả danh mục (tùy chọn)',
                'input_seo'         => 'Nhập seo danh mục (tùy chọn)',
                'input_url'         => 'Nhập url chuyển hướng danh mục (tùy chọn)'
            ]
        ],

        'button' => [
            'create_continue' => 'Tiếp tục',
            'create'          => 'Tạo',
            'cancel'          => 'Quay lại'
        ],

        'alert' => [
            'category_container_not_exists' => 'Danh mục chứa không tồn tại',
            'not_input_title'               => 'Chưa nhập tiêu đề',
            'title_category_exists'         => 'Tiêu đề danh mục đã tồn tại',
            'seo_category_exists'           => 'SEO danh mục đã tồn tại',
            'create_category_failed'        => 'Tạo danh mục thất bại',
            'create_category_success'       => 'Tạo danh mục <strong>{$name}</strong> thành công'
        ]
    ];