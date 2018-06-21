<?php

    use Librarys\File\FileSystem;
    use Librarys\Http\Secure\CFSRToken;
    use Librarys\Util\Text\Strings;

    define('LOADED', 1);
    define('DISABLE_GZHANDLER', 1);
    define('DISABLE_HEADER_SYSTEM', 1);
    require_once('global.php');

    $cfsrToken = null;
    $path      = null;

    if (isset($_GET['cfsr_token']))
        $cfsrToken = Strings::urldecode($_GET['cfsr_token']);

    if (isset($_GET['path']))
        $path = Strings::urldecode($_GET['path']);

    if (empty($cfsrToken) || empty($path))
        die('CFSRToken or path not validate');

    if (($tokenCheck = CFSRToken::getInstance()->validateGet($cfsrToken)) !== true)
        die('CFSRToken not validate');

    $path    = FileSystem::filter($path);
    $splits  = str_split($path, 1);
    $indexOf = 0;

    foreach ($splits AS $index => $char) {
        $indexOf = $index;

        if ($char != '.' && $char != '..' && $char != '/' && $char != '\\')
            break;
    }

    $path = substr($path, $indexOf);
    $mime = strtolower(FileSystem::mime($path));

    $formats = [
        'css' => 'text/css',
        'js'  => 'text/javascript',

        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'bmp'  => 'image/bmp',
        'ico'  => 'image/x-icon',

        'woff'  => 'application/font-woff',
        'woff2' => 'application/font-woff2',
        'ttf'   => 'application/font-ttf',
        'eot'   => 'application/vnd.ms-fontobject',
        'otf'   => 'application/font-otf',
        'svg'   => 'image/svg+xm'
    ];

    if (array_key_exists($mime, $formats) === false)
        die('Format not support');

    $pathRelative = FileSystem::filter(env('app.document_root') . SP . 'assets' . SP . $path);

    if (FileSystem::isTypeFile($pathRelative) == false)
        die('File not found');

    $contents = file_get_contents($pathRelative);
    $encoding = 'gzip';

    if ($formats[$mime] !== null)
        header('Content-Type: ' . $formats[$mime]);

    if (in_array($mime, [
            'eot',
            'ttf',
            'woff',
            'woff2'
        ]) == false) {
        header('Content-Encoding: gzip');
        echo("\x1f\x8b\x08\x00\x00\x00\x00\x00");

        if (in_array($mime, [
            'css',
            'js'
        ])) {
            $contents = Librarys\Language\Loader::langMatchesString($contents);
        }

        $size     = strlen($contents);
        $contents = gzcompress($contents, 9);
        $contents = substr($contents, 0, $size);
    }

    echo($contents);
    exit();
