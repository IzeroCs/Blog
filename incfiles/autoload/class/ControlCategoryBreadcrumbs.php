<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\UI\Breadcrumbs;
    use Librarys\Util\Text\Strings;

    class ControlCategoryBreadcrumbs extends Breadcrumbs
    {

        public static function createInstance($params)
        {
            $instance = new ControlCategoryBreadcrumbs($params);
            $instance->addFirstCrumb($params['default_url'], '<span class="icomoon icon-home"></span>', true, false, false);
            $instance->proccessAddCrumbs();

            return $instance;
        }

        private function proccessAddCrumbs()
        {
            $id = intval($this->params['id_category']);

            if ($id <= 0)
                return null;

            $query = QueryFactory::createInstance(env('database.tables.category'));
            $query->setCommand(QueryAbstract::COMMAND_SELECT);
            $query->addSelect('id');
            $query->addSelect('id_parent');
            $query->addSelect('is_parent');
            $query->addSelect('title');
            $query->addWhere('id', QueryAbstract::escape($id));
            $query->setLimit(1);

            if ($query->execute() !== false && $query->rows() > 0) {
                $assoc = $query->assoc();

                if ($assoc['is_parent']) {
                    $this->addLastCrumb(null, $assoc['title'], false, true);
                } else {
                    $loopId = 0;
                    $this->addLastCrumb(null, $assoc['title'], false, true);

                    while (true) {
                        $query->setWhere('id', QueryAbstract::escape($assoc['id_parent']));

                        if ($loopId === $assoc['id_parent'])
                            break;
                        else
                            $loopId = intval($assoc['id_parent']);

                        if ($query->execute(true) !== false && $query->rows() > 0) {
                            $assoc = $query->assoc();
                            $this->addMiddleCrumb($this->params['begin_url'] . Strings::urlencode($assoc['id']) . $this->params['end_url'], $assoc['title'], true, true);
                        } else {
                            break;
                        }
                    }
                }
            }
        }

        public function createDisplayCrumbs($arrayCrumbs)
        {
            $buffer = '<div class="breadcrumbs">';

            foreach ($arrayCrumbs AS $crumbs) {
                $buffer .= '<span class="crumb">';

                if ($crumbs['is_link'])
                    $buffer .= '<a href="' . $crumbs['url'] . '">';

                if ($crumbs['en_html'])
                    $buffer .= '<span title="' . Strings::enhtml($crumbs['title']) . '">' . Strings::enhtml($crumbs['title']) . '</span>';
                else
                    $buffer .= '<span>' . $crumbs['title'] . '</span>';

                if ($crumbs['is_link'])
                    $buffer .= '</a>';

                $buffer .= '</span>';
            }

            $buffer .= '</div>';

            return $buffer;

        }

    }