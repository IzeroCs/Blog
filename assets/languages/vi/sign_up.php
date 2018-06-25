<?php

    return [
        'title' => 'Đăng ký',

        'input' => [
            'placeholder' => [
                'input_username'   => 'Tên đăng nhập',
                'input_name'       => 'Họ và tên',
                'input_email'      => 'Địa chỉ email',
                'input_password'   => 'Mật khẩu',
                'input_repassword' => 'Mật khẩu lặp lại'
            ]
        ],

        'button' => [
            'sign_in' => 'Đăng nhập',
            'sign_up' => 'Đăng ký'
        ],

        'alert' => [
            'not_input_all_field'        => 'Bạn chưa nhập đầy đủ thông tin',
            'username_is_small_or_large' => 'Tên đăng nhập phải lớn hoặc bằng {$min} và nhỏ hoặc bằng {$max} ký tự',
            'name_is_small_or_large'     => 'Tên phải lớn hoặc bằng {$min} và nhỏ hoặc bằng {$max} ký tự',
            'password_is_small'          => 'Mật khẩu phải lớn hơn {$min} ký tự',
            'username_not_validate'      => 'Tên đăng nhập không hợp lệ',
            'email_not_validate'         => 'Địa chỉ email không hợp lệ',
            'password_not_equals'        => 'Hai mật khẩu không giống nhau',
            'username_exists'            => 'Tên đăng nhập đã tồn tại',
            'email_exists'               => 'Địa chỉ email đã tồn tại',
            'sign_up_failed'             => 'Đăng ký thất bại, hãy thử lại',
            'sign_up_success'            => 'Đăng ký thành công, bạn hãy đăng nhập',
            'sign_up_is_disable'         => 'Đăng ký đã bị khóa, không thể thực hiện thao tác'
        ]
    ];