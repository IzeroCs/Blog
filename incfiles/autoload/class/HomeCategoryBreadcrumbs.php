<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\UI\Breadcrumbs;
    use Librarys\Util\Text\Strings;

    class HomeCategoryBreadcrumbs extends Breadcrumbs
    {

        public static function createInstance($params, $handleError = null)
        {
            $instance = new HomeCategoryBreadcrumbs($params);
            $instance->addFirstCrumb(env('app.http_host'), '<span class="icomoon icon-home"></span>', true, false, false);
            $instance->proccessAddCrumbs($handleError);

            return $instance;
        }

        private function proccessAddCrumbs($handleError = null)
        {
            $id = intval($this->params['id_category']);

            if ($id <= 0)
                return null;

            if ($handleError === null)
                $handleError = function() {};

            $query = QueryFactory::createInstance(env('database.tables.category'));
            $query->setCommand(QueryAbstract::COMMAND_SELECT);
            $query->addSelect('id');
            $query->addSelect('id_parent');
            $query->addSelect('is_parent');
            $query->addSelect('title');
            $query->addSelect('seo');
            $query->addWhere('id', QueryAbstract::escape($id));
            $query->addWhere('is_hidden', 0);
            $query->addWhere('is_trash', 0);
            $query->setLimit(1);

            if ($query->execute() !== false && $query->rows() > 0) {
                $assoc = $query->assoc();
                $endLink = null;

                if (isset($this->params['end_link']) && $this->params['end_link']) {
                    $endLink = rewrite('url.category', [
                        'p_seo' => '?seo=',
                        'p_id'  => '&id=',

                        'seo' => Strings::urlencode($assoc['seo']),
                        'id'  => Strings::urlencode($assoc['id'])
                    ]);
                }

                if ($assoc['is_parent']) {
                    $this->addLastCrumb($endLink, $assoc['title'], $endLink !== null, true);
                } else {
                    $loopId = 0;
                    $this->addLastCrumb($endLink, $assoc['title'], $endLink !== null, true);

                    while (true) {
                        $query->setWhere('id', QueryAbstract::escape($assoc['id_parent']));

                        if ($loopId === $assoc['id_parent'])
                            break;
                        else
                            $loopId = intval($assoc['id_parent']);

                        if ($query->execute(true) !== false && $query->rows() > 0) {
                            $assoc = $query->assoc();

                            $this->addMiddleCrumb(rewrite('url.category', [
                                'p_seo' => '?seo=',
                                'p_id'  => '&id=',

                                'seo' => Strings::urlencode($assoc['seo']),
                                'id'  => Strings::urlencode($assoc['id'])
                            ]), $assoc['title'], true, true);
                        } else {
                            break;
                        }
                    }
                }
            } else {
                $handleError();
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