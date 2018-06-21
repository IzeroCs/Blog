<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\SchemaAbstract;
    use Librarys\Database\TableAbstract;
    use Librarys\Util\Text\Encryption\Strings as StringEncryption;

    class UserTable extends TableAbstract
    {

        /**
         * UserTable constructor.
         */
        public function __construct()
        {
            parent::__construct(env('database.tables.user'));
        }

        /**
         * @param SchemaAbstract $schema
         */
        public function create(SchemaAbstract $schema)
        {
            $schema->mediumint('id', '8')->primarykey()->unsigned();
            $schema->varchar('username', 30)->notnull();
            $schema->varchar('password', 100)->notnull();
            $schema->varchar('email', 1000)->null();
            $schema->varchar('name', 50)->null();
            $schema->varchar('wallpaper', 1500)->null();
            $schema->varchar('avatar', 1500)->null();
            $schema->int('birthday', 20)->null()->unsigned();
            $schema->tinyint('sex', 2, 0)->null()->unsigned();
            $schema->tinyint('perms', 2)->notnull()->unsigned();
            $schema->int('create_at', 20)->notnull()->unsigned();
            $schema->int('modify_at', 20)->null()->unsigned();
            $schema->int('sign_at', 20)->null()->unsigned();
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
            $query->setCommand(QueryAbstract::COMMAND_INSERT_INTO);

            $query->addDataArray([
                'username'  => 'IzeroCs',
                'password'  => StringEncryption::createCrypt('12345'),
                'email'     => 'izero.cs@gmail.com',
                'name'      => 'Nguyen Danh Nam',
                'birthday'  => strtotime('31-12-1995'),
                'sex'       => 0,
                'perms'     => 16,
                'create_at' => time()
            ]);

            $query->execute();
            $query->clear();
            $query->setCommand(QueryAbstract::COMMAND_INSERT_INTO);

            $query->addDataArray([
                'username'  => 'Admin',
                'password'  => '99999',
                'email'     => 'admin@master.com',
                'name'      => 'Admin',
                'birthday'  => time(),
                'sex'       => rand(0, 1),
                'perms'     => 8,
                'create_at' => time()
            ]);

            $query->execute();
            $query->clear();
            $query->setCommand(QueryAbstract::COMMAND_INSERT_INTO);

            $query->addDataArray([
                'username'  => 'Bot',
                'password'  => '11111',
                'email'     => 'bot@master.com',
                'name'      => 'Bot',
                'birthday'  => time(),
                'sex'       => rand(0, 1),
                'perms'     => 4,
                'create_at' => time()
            ]);

            $query->execute();
            $query->clear();
        }

    }