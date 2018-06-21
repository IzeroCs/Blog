<?php

    use Librarys\Util\Text\Strings;
    use Librarys\UI\Alert;
    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;

    define('LOADED', 1);
    require_once('global.php');

    $typeResult = "html";
    $jsonResult = [
        'datas' => [
            'article' => []
        ],

        'alert' => [
            'msg' => null,
            'type' => null
        ]
    ];

    if (isset($_POST['search']) && isset($_POST['type']))
        $typeResult = trim(strtolower($_POST['type']));

    $isHtml = Strings::equals($typeResult, "html");

    if ($isHtml) {
        require_header(lng('home.title'), ALERT_SEARCH);
    }

    function alert($msg)
    {
        global $isHtml, $jsonResult;

        if ($isHtml) {
            Alert::danger($msg);
        } else {
            $jsonResult['alert']['msg'] = $msg;
            $jsonResult['alert']['type'] = Alert::DANGER;
        }
    }

    $keyword = null;

    if (isset($_POST['search'])) {
        $keyword = Strings::escape($_POST['keyword']);

        if (empty($keyword)) {
            alert(lng('search.alert.not_input_keyword'));
        } else {
            $query = QueryFactory::createInstance(env('database.tables.article'));
            $query->setCommand(QueryAbstract::COMMAND_SELECT);
            $query->addSelect('id');
            $query->addSelect('seo');
            $query->addSelect('title');
            $query->addWhere('title', '%' . $keyword . '%', QueryAbstract::OPERATOR_LIKE, QueryAbstract::WHERE_OR);
            $query->addWhere('content', '%' . $keyword . '%', QueryAbstract::OPERATOR_LIKE, QueryAbstract::WHERE_OR);
            $query->setOrderBy('title', QueryAbstract::ORDER_ASC);

            if ($query->execute() !== false && $query->rows() > 0) {
                while (($assoc = $query->assoc()) != null)
                    $jsonResult['datas']['article'][] = $assoc;
            }
        }
    } else {
        Alert::danger(lng('search.alert.not_support_feature'), ALERT_HOME, env('app.http_host'));
    }

    echo json_encode($jsonResult);
?>

<?php if ($isHtml) require_footer(); ?>
