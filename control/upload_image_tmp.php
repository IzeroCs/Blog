<?php

    use Librarys\UI\Alert;
    use Librarys\File\FileSystem;

    define('LOADED', 1);
    require_once('global.php');
    Alert::setID(ALERT_CONTROL_UPLOAD_IMAGE_TMP);

    $results = [
        'message'  => null,
        'type'     => null,
        'filename' => null,
        'fileurl'  => null,
        'success'  => false
    ];

    if (isset($_POST['upload']) && isset($_POST['contents']) && empty($_POST['contents']) === false) {
        $contents = trim($_POST['contents']);
        $process  = function($contents) {
            $find  = 'data:';
            $index = strpos($contents, $find);

            if ($index !== 0)
                return false;

            $last  = $index + strlen($find);
            $find  = ';';
            $index = strpos($contents, $find, $last);

            if ($index === false)
                return false;

            $type  = substr($contents, $last, $index - $last);
            $mime = explode('/', $type);

            if (isset($mime[1]))
                $mime = $mime[1];

            $last  = $index + strlen($find);
            $find  = 'base64';
            $index = strpos($contents, $find, $last);

            if ($index === false)
                return false;

            $last  = $index + strlen($find);
            $find  = ',';
            $index = strpos($contents, $find, $last);

            if ($index === false)
                return false;

            $last = $index + strlen($find);
            $data = substr($contents, $last);
            $data = trim($data);

            return [
                'type' => $type,
                'mime' => $mime,
                'data' => $data
            ];
        };

        $res = $process($contents);

        if ($res === false) {
            $results['message'] = lng('control.upload_image_tmp.alert.contents_not_validate');
            $results['type']    = Alert::DANGER;
        } else {
            $imageName = md5(
                time() . '-' .
                strlen($res['data']) . '-' .
                trim($res['type']) . '-' .
                User::getAssocId() . '-' .
                User::getAssocUsername() . '-'
            ) . '.' . $res['mime'];

            FileSystem::fileWriteContents(
                env('app.document_root') . SP .
                'assets' . SP .
                'uploads' . SP .
                'tmp' . SP .
                $imageName,
                base64_decode($res['data'])
            );

            $results['message']  = lng('control.upload_image_tmp.alert.upload_success');
            $results['type']     = Alert::SUCCESS;
            $results['success']  = true;
            $results['filename'] = $imageName;
            $results['fileurl']  = env('app.http_host') . '/resource/' . cfsrTokenValue() . '/uploads/tmp/' . $imageName;
        }
    } else {
        $results['message'] = lng('control.upload_image_tmp.alert.action_not_validate');
        $results['type']    = Alert::DANGER;
    }

    echo json_encode($results);