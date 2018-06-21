<?php

    return [
        'title' => 'Sửa danh mục',

        'input' => [
            'label' => [
                'title'       => 'Tiêu đề danh mục:',
                'description' => 'Mô tả danh mục (tùy chọn):',
                'seo'         => 'SEO danh mục (tùy chọn):',
                'url'         => 'URL chuyển hướng (tùy chọn):',
            ],

            'checkbox' => [
                'options' => [
                    'title'             => 'Tùy chọn:',
                    'hidden_category'   => 'Ẩn danh mục',
                    'auto_seo_category' => 'Tự động seo tiêu đề'
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
            'save'   => 'Lưu',
            'cancel' => 'Quay lại'
        ],

        'alert' => [
            'category_not_exists'   => 'Danh mục không tồn tại',
            'not_input_title'       => 'Chưa nhập tiêu đề',
            'title_category_exists' => 'Tiêu đề danh mục đã tồn tại',
            'seo_category_exists'   => 'SEO danh mục đã tồn tại',
            'not_changed'           => 'Không có gì thay đổi',
            'edit_category_failed'  => 'Sửa danh mục thất bại',
            'edit_category_success' => 'Sửa danh mục <strong>{$name}</strong> thành công'
        ]
    ];
