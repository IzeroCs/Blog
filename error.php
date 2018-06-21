<?php

    use Librarys\File\FileSystem;
    use Librarys\Http\Request;
    use Librarys\Util\Text\Strings;

    define('LOADED', 1);
    define('ERROR', 1);
    require_once('global.php');

    $httpCode = 404;

    if (isset($_GET['code']) && empty($_GET['code']) == false)
        $httpCode = intval(Strings::escape($_GET['code']));
    else if (function_exists('http_response_code'))
        $httpCode = http_response_code();

    $httpString = Request::httpResponseCodeToString($httpCode);
    $filepath = env('app.document_root') . SP . 'error' . SP . $httpCode . '.php';

    require_header($httpString);

    if (FileSystem::isTypeFile($filepath))
        require_once($filepath);

    echo '<div id="error-document-wrapper">';
        echo '<span class="code">' . $httpCode . '</span>';
        echo '<span class="message">' . lng('error.message.' . $httpCode) . '</span>';
    echo '</div>';

require_footer();