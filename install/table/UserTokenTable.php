<?php

    use Librarys\Database\SchemaAbstract;
    use Librarys\Database\TableAbstract;

    class UserTokenTable extends TableAbstract
    {

        /**
         * UserTable constructor.
         */
        public function __construct()
        {
            parent::__construct(env('database.tables.user_token'));
        }

        /**
         * @param SchemaAbstract $schema
         */
        public function create(SchemaAbstract $schema)
        {
            $schema->mediumint('id_user', '8')->notnull()->unsigned();
            $schema->varchar('token', 100)->notnull();
            $schema->varchar('agent', 1000)->notnull();
            $schema->varchar('ip', 30)->notnull();
            $schema->int('create_at', 20)->unsigned();
            $schema->int('modify_at', 20)->unsigned();
        }

        /**
         * @param SchemaAbstract $schema
         */
        public function drop(SchemaAbstract $schema)
        {
            $schema->drop();
        }

    }
