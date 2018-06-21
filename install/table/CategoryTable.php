<?php

    use Faker\Factory as Faker;
    use BlogArticleFaker\FakerProvider;
    use Librarys\Database\QueryAbstract;
    use Librarys\Database\SchemaAbstract;
    use Librarys\Database\TableAbstract;
    use Librarys\Http\Uri;

    class CategoryTable extends TableAbstract
    {

        /**
         * CategoryTable constructor.
         */
        public function __construct()
        {
            parent::__construct(env('database.tables.category'));
        }

        /**
         * @param SchemaAbstract $schema
         */
        public function create(SchemaAbstract $schema)
        {
            $schema->mediumint('id', 8)->primarykey()->unsigned();
            $schema->mediumint('id_parent', 8)->null()->unsigned();
            $schema->mediumint('id_create', 8)->notnull()->unsigned();
            $schema->mediumint('id_modify', 8)->null()->unsigned();
            $schema->tinyint('is_parent', 1, 0)->null();
            $schema->tinyint('is_hidden', 1, 0)->null();
            $schema->tinyint('is_trash', 1, 0)->null();
            $schema->varchar('title', 1500)->notnull();
            $schema->varchar('description', 1500)->null();
            $schema->varchar('seo', 1500)->notnull();
            $schema->varchar('url', 1500)->null();
            $schema->tinyint('robots_noindex', 2, 0)->null();
            $schema->tinyint('robots_nofollow', 2, 0)->null();
            $schema->tinyint('robots_noodp', 2, 1)->null();
            $schema->tinyint('robots_noydir', 2, 1)->null();
            $schema->int('access', 20, 0)->null();
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

            for ($i = 0; $i < rand(8, 20); $i++) {
                $title = $faker->articleTitle;
                $seo   = Uri::seo($title);

                $query->clear();
                $query->setCommand(QueryAbstract::COMMAND_INSERT_INTO);
                $query->addDataArray([
                    'id_create' => rand(1, 3),
                    'is_parent' => true,
                    'title'     => $title,
                    'seo'       => $seo,
                    'create_at' => time()
                ]);

                $query->execute();
                $id = $query->insertId();

                for ($c = 0; $c < rand(3, 10); ++$c) {
                    $title = $faker->articleTitle;
                    $des   = $faker->articleTitle;
                    $seo   = Uri::seo($title);

                    if (rand(0, 1) === 1)
                        $des = null;

                    $query->clear();
                    $query->setCommand(QueryAbstract::COMMAND_INSERT_INTO);
                    $query->addDataArray([
                        'id_parent'   => $id,
                        'id_create'   => rand(1, 3),
                        'is_parent'   => false,
                        'title'       => $title,
                        'description' => $des,
                        'seo'         => $seo,
                        'create_at'   => time()
                    ]);

                    $query->execute();
                }
            }
        }

    }