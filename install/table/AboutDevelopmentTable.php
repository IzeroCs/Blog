<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\SchemaAbstract;
    use Librarys\Database\TableAbstract;
    use Librarys\Http\Uri;

    class AboutDevelopmentTable extends TableAbstract
    {

        /**
         * ArticleTable constructor.
         */
        public function __construct()
        {
            parent::__construct(env('database.tables.about_development'));
        }

        /**
         * @param SchemaAbstract $schema
         */
        public function create(SchemaAbstract $schema)
        {
            $schema->varchar('wallpaper', 1500)->null();
            $schema->varchar('avatar', 1500)->null();
            $schema->varchar('title', 100)->notnull();
            $schema->varchar('content', 3000)->notnull();
            $schema->varchar('social', 3000)->null();
            $schema->int('create_at', 20)->notnull()->unsigned();
            $schema->int('modify_at', 20)->null()->unsigned();
            $schema->engineMyISAM();
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
            $query->clear();
            $query->setCommand(QueryAbstract::COMMAND_INSERT_INTO);

            $query->addDataArray([
                'wallpaper' => 'images/about-dev/wallpaper.jpg',
                'avatar'    => 'images/about-dev/avatar.png',
                'title'     => 'IzeroCs',
                'content'   => 'Content about development',
                'create_at' => time(),
                'modify_at' => 0,

                'social' => json_encode([
                    'facebook' => 'https://facebook.com/IzeroCs',
                    'twitter'  => 'https://twitter.com/IzeroCz',
                    'github'   => 'https://github.com/IzeroCs'
                ])
            ]);

            $query->execute();
        }

    }
