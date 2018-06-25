<?php

    use Faker\Factory as Faker;
    use BlogArticleFaker\FakerProvider;
    use Librarys\Database\QueryAbstract;
    use Librarys\Database\SchemaAbstract;
    use Librarys\Database\TableAbstract;
    use Librarys\Http\Uri;

    class ArticleTable extends TableAbstract
    {

        /**
         * ArticleTable constructor.
         */
        public function __construct()
        {
            parent::__construct(env('database.tables.article'));
        }

        /**
         * @param SchemaAbstract $schema
         */
        public function create(SchemaAbstract $schema)
        {
            $schema->mediumint('id', 8)->primarykey()->unsigned();
            $schema->mediumint('id_category', 8)->notnull()->unsigned();
            $schema->mediumint('id_create', 8)->notnull()->unsigned();
            $schema->mediumint('id_modify', 8)->null()->unsigned();
            $schema->tinyint('is_hidden', 2, 0)->null()->unsigned();
            $schema->tinyint('is_trash', 2, 0)->null()->unsigned();
            $schema->varchar('title', 1500)->notnull();
            $schema->varchar('seo', 1500)->notnull();
            $schema->varchar('url', 1500)->null();
            $schema->varchar('thumb', 1500)->null();
            $schema->text('content')->null();
            $schema->int('view', 20, 0)->null()->unsigned();
            $schema->tinyint('robots_noindex', 2, 0)->null();
            $schema->tinyint('robots_nofollow', 2, 0)->null();
            $schema->tinyint('robots_noodp', 2, 1)->null();
            $schema->tinyint('robots_noydir', 2, 1)->null();
            $schema->int('create_at', 20)->notnull()->unsigned();
            $schema->int('modify_at', 20)->null()->unsigned();
            $schema->increment('id');
        }

        /**
         * @param SchemaAbstract $schema
         */
        public function drop(SchemaAbstract $schema)
        {
            $schema->drop();
        }

        /**
         * @param QueryAbstract $query
         */
        public function insert(QueryAbstract $query)
        {
            $faker = Faker::create();
            $faker->addProvider(new FakerProvider($faker));

            for ($i = 0; $i < 50; ++$i) {
                $query->clear();
                $query->setCommand(QueryAbstract::COMMAND_INSERT_INTO);

                $title = $faker->articleTitle;
                $seo   = Uri::seo($title);

                $query->addDataArray([
                    'id_category' => rand(1, 15),
                    'id_create'   => rand(1, 3),
                    'title'       => $title,
                    'seo'         => $seo,
                    'thumb'       => rand(1, 8) . '.jpg',
                    'content'     => $faker->articleContent,
                    'create_at'   => time()
                ]);

                $query->execute();
            }
        }

    }
