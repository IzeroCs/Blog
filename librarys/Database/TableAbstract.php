<?php

    namespace Librarys\Database;

    use Librarys\File\FileSystem;

    abstract class TableAbstract
    {

        /**
         * @var string $table
         */
        protected $table;

        /**
         * @var SchemaAbstract $schema
         */
        protected $schema;

        /**
         * TableAbstract constructor.
         * @param string $table
         */
        public function __construct($table)
        {
            $this->table   = $table;
        }

        /**
         *
         */
        public function execute()
        {
            $this->schema = SchemaFactory::createInstance($this);

            $this->schema->clear();
            $this->drop($this->schema);
            $this->schema->execute();

            $this->schema->clear();
            $this->create($this->schema);
            $this->schema->execute();

            $this->insert(QueryFactory::createInstance($this->getTable()));

            echo('<strong style="color: green">Execute table table "' . $this->table . '" success</strong><br/>');
        }

        /**
         * @return string
         */
        public function getTable()
        {
            return $this->table;
        }

        /**
         * @param QueryAbstract $query
         */
        public function insert(QueryAbstract $query)
        {

        }

        /**
         * @param SchemaAbstract $schema
         * @return mixed
         */
        public abstract function create(SchemaAbstract $schema);

        /**
         * @param SchemaAbstract $schema
         * @return mixed
         */
        public abstract function drop(SchemaAbstract $schema);

        public static function runTableClassInDirectory($path)
        {
            $handle = FileSystem::scanDirectory($path);

            if (is_array($handle) == false)
                return false;

            foreach ($handle AS $filename) {
                if (FileSystem::isFileOrDirectory($filename)) {
                    require_once($path . DIRECTORY_SEPARATOR . $filename);

                    $lastSymbol = strpos($filename, '.');
                    $className  = substr($filename, 0, $lastSymbol);

                    /**
                     * @var TableAbstract $classInstance
                     */
                    $classInstance = new $className();
                    $classInstance->execute();
                }
            }

            return true;
        }

    }