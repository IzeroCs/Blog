<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\SchemaAbstract;
    use Librarys\Database\TableAbstract;
    use Librarys\Http\Uri;

    class SettingSystemTable extends TableAbstract
    {

        /**
         * ArticleTable constructor.
         */
        public function __construct()
        {
            parent::__construct(env('database.tables.setting_system'));
        }

        /**
         * @param SchemaAbstract $schema
         */
        public function create(SchemaAbstract $schema)
        {
            $schema->tinyint('show_about_dev', 2, 1)->null();
            $schema->varchar('subtitle', 100)->null();
            $schema->varchar('description', 1500)->null();
            $schema->varchar('keyword', 1500)->null();
            $schema->varchar('social_share', 3000)->null();
            $schema->mediumint('max_size_thumb_upload', 20)->null()->unsigned();
            $schema->varchar('file_mime_thumb_upload', 500)->null();
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
                'show_about_dev' => false,
                'subtitle'       => ' - Blog',
                'description'    => 'Blog by IzeroCs',
                'keyword'        => 'Blog, IzeroCs',
                'create_at'      => time(),
                'modify_at'      => 0,

                'max_size_thumb_upload' => (1024 * 1024) << 1,

                'file_mime_thumb_upload' => json_encode([
                    'image/jpeg',
                    'image/png',
                    'image/gif'
                ]),

                'social_share' => json_encode([
                    'facebook' => 'https://www.facebook.com/sharer/sharer.php?u={$url}',
                    'google'   => 'https://plus.google.com/share?url={$url}',
                    'twitter'  => 'https://twitter.com/intent/tweet?url={$url}'
                ])
            ]);

            $query->execute();
        }

    }
