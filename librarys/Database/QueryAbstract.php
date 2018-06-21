<?php

    namespace Librarys\Database;

    use Librarys\Database\Exception\QueryCreateException;
    use Librarys\Util\Text\Strings;

    abstract class QueryAbstract
    {

        /**
         * @var resource|\mysqli_result|\PDOStatement $query
         */
        protected $query;

        /**
         * @var int $command
         */
        protected $command;

        /**
         * @var string|array|null $select
         */
        protected $select;

        /**
         * @var string $table
         */
        protected $table;

        /**
         * @var string|array|null $data
         */
        protected $data;

        /**
         * @var array $update
         */
        protected $update;

        /**
         * @var array $where
         */
        protected $where;

        /**
         * @var array $order
         */
        protected $order;

        /**
         * @var array $limit
         */
        protected $limit;

        const COMMAND_SELECT      = 'SELECT';
        const COMMAND_UPDATE      = 'UPDATE';
        const COMMAND_DELETE      = 'DELETE';
        const COMMAND_INSERT_INTO = 'INSERT INTO';

        const FUNCTION_AVG    = 'AVG';
        const FUNCTION_COUNT  = 'COUNT';
        const FUNCTION_FIRST  = 'FIRST';
        const FUNCTION_LAST   = 'LAST';
        const FUNCTION_MAX    = 'MAX';
        const FUNCTION_MIN    = 'MIN';
        const FUNCTION_UCASE  = 'UCASE';
        const FUNCTION_LCASE  = 'LCASE';
        const FUNCTION_MID    = 'MID';
        const FUNCTION_LEN    = 'LEN';
        const FUNCTION_ROUND  = 'ROUND';
        const FUNCTION_NOW    = 'NOW';
        const FUNCTION_FORMAT = 'FORMAT';

        const OPERATOR_EQUAL         = '=';
        const OPERATOR_NOT_EQUAL     = '!=';
        const OPERATOR_GREATER       = '>';
        const OPERATOR_LESS          = '<';
        const OPERATOR_GREATER_EQUAL = '>=';
        const OPERATOR_LESS_EQUAL    = '<=';
        const OPERATOR_BETWEEN       = 'BETWEEN';
        const OPERATOR_LIKE          = 'LIKE';
        const OPERATOR_NOT_LIKE      = 'NOT LIKE';
        const OPERATOR_IN            = 'IN';

        const WHERE_AND = 'AND';
        const WHERE_OR  = 'OR';

        const ORDER_ASC  = 'ASC';
        const ORDER_DESC = 'DESC';

        /**
         * @param string|null $table
         * QueryAbstract constructor.
         */
        public function __construct($table = null)
        {
            $this->clear();
            $this->setCommand(self::COMMAND_SELECT);

            if ($table != null)
                $this->setTable($table);
        }

        /**
         *
         */
        public function clear()
        {
            $this->query  = null;
            $this->select = [];
            $this->data   = [];
            $this->where  = [];
            $this->order  = [];
            $this->update = null;
            $this->limit  = null;
        }

        /**
         * @param string      $column
         * @param string|null $as
         * @param string|null $function
         * @return $this
         * @throws QueryCreateException
         */
        public function addSelect($column, $as = null, $function = null)
        {
            if ($column == null)
                throw new QueryCreateException('Column is null');

            $this->select[$column] = [
                'function' => $function,
                'as'       => $as
            ];

            return $this;
        }

        /**
         * @param string      $column
         * @param string|null $as
         * @param string|null $function
         * @return QueryAbstract
         */
        public function setSelect($column, $as = null, $function = null)
        {
            return $this->addSelect($column, $as, $function);
        }

        /**
         * @param string $column
         * @return $this
         */
        public function removeSelect($column)
        {
            if (array_key_exists($column, $this->select)) {
                $select = [];

                foreach ($this->select AS $key => $value) {
                    if (Strings::equals($column, $key) === false)
                        $select[$key] = $value;
                }

                $this->select = $select;
            }

            return $this;
        }

        /**
         * @param int $command
         */
        public function setCommand($command)
        {
            $this->command = $command;
        }

        /**
         * @return int
         */
        public function getCommand()
        {
            return $this->command;
        }

        /**
         * @param string $table
         */
        public function setTable($table)
        {
            if (($index = strpos($table, '.')) !== false) {
                $begin = substr($table, 0, $index);
                $end   = substr($table, $index + 1);

                $this->table = $begin;
                $this->table .= '`.`';
                $this->table .= Connect::getInstance()->getPrefix();
                $this->table .= $end;
            } else {
                $this->table = Connect::getInstance()->getPrefix();
                $this->table .= $table;
            }
        }

        /**
         * @return string
         */
        public function getTable()
        {
            return $this->table;
        }

        /**
         * @param string     $column
         * @param string|int $value
         * @throws QueryCreateException
         */
        public function addData($column, $value)
        {
            if ($column == null)
                throw new QueryCreateException('Column is null');

            $this->data[$column] = $value;
        }

        /**
         * @param string     $column
         * @param string|int $value
         */
        public function setData($column, $value)
        {
            $this->addData($column, $value);
        }

        /**
         * @param string $column
         */
        public function removeData($column)
        {
            if (array_key_exists($column, $this->data)) {
                $data = [];

                foreach ($this->data AS $key => $value) {
                    if (Strings::equals($column, $key) === false)
                        $data[$key] = $value;
                }

                $this->data = $data;
            }
        }

        /**
         * @param array $array
         * @throws QueryCreateException
         */
        public function addDataArray($array)
        {
            if (is_array($array) == false)
                throw new QueryCreateException('Array data is null');

            foreach ($array AS $key => $value) {
                if ($key != null && empty($key) == false)
                    $this->addData($key, $value);
            }
        }

        /**
         * @param string     $column
         * @param string|int $value
         * @param string     $operator
         * @param string     $where
         * @return $this
         * @throws QueryCreateException
         */
        public function addWhere($column, $value, $operator = self::OPERATOR_EQUAL, $where = self::WHERE_AND)
        {
            if ($column == null)
                throw new QueryCreateException('Column is null');

            $this->where[] = [
                'column'   => $column,
                'operator' => $operator,
                'value'    => $value,
                'where'    => $where
            ];

            return $this;
        }

        /**
         * @param string     $column
         * @param string|int $value
         * @param string     $operator
         * @param string     $where
         * @param int        $level
         * @return QueryAbstract
         */
        public function setWhere($column, $value, $operator = self::OPERATOR_EQUAL, $where = self::WHERE_AND, $level = -1)
        {
            foreach ($this->where AS $index => &$arrays) {
                if (Strings::equals($column, $arrays['column'])) {
                    if ($level === -1 || $level === $index) {
                        $arrays['value']    = $value;
                        $arrays['operator'] = $operator;
                        $arrays['where']    = $where;
                    }
                }
            }
        }

        /**
         * @param string $column
         */
        public function removeWhere($column, $level = -1)
        {
            $where = [];

            foreach ($this->where AS $index => $arrays) {
                if (($level === -1 || $level === $index) && Strings::equals($column, $arrays['column'])) {

                } else {
                    $where[] = $arrays;
                }
            }

            $this->where = $where;
        }

        /**
         * @param string $column
         * @param string $order
         * @throws QueryCreateException
         */
        public function addOrderBy($column, $order = self::ORDER_ASC)
        {
            if ($column == null)
                throw new QueryCreateException('Column is null');

            $this->order[$column] = $order;
        }

        /**
         * @param string $column
         * @param string $order
         */
        public function setOrderBy($column, $order = self::ORDER_ASC)
        {
            $this->addOrderBy($column, $order);
        }

        /**
         * @param string $column
         */
        public function removeOrderBy($column)
        {
            if (array_key_exists($column, $this->order)) {
                $order = [];

                foreach ($this->order AS $key => $value) {
                    if (Strings::equals($column, $key) === false)
                        $order[$key] = $value;
                }

                $this->order = $order;
            }
        }

        /**
         * @param string|int      $start
         * @param string|int|null $offset
         */
        public function setLimit($start, $offset = null)
        {
            $this->limit = [
                'start'  => $start,
                'offset' => $offset
            ];
        }

        /**
         *
         */
        public function removeLimit()
        {
            $this->limit = null;
        }

        /**
         * @param string|int $command
         * @return null|string
         */
        public static function commandToString($command)
        {
            if ($command == self::COMMAND_SELECT)
                return 'SELECT';

            if ($command == self::COMMAND_UPDATE)
                return 'UPDATE';

            if ($command == self::COMMAND_INSERT_INTO)
                return 'INSERT INTO';

            if ($command == self::COMMAND_DELETE)
                return 'DELETE';

            return null;
        }

        /**
         * @return int|string
         */
        public function toSql()
        {
            if ($this->command == null)
                throw new QueryCreateException('Command is null');

            if ($this->table == null)
                throw new QueryCreateException('Table is null');

            $cmd = self::commandToString($this->command);
            $sql = $cmd;

            if ($this->command == self::COMMAND_SELECT) {
                $sql .= ' ';

                if (count($this->select) <= 0) {
                    $sql .= '*';
                } else {
                    $index  = 0;
                    $length = count($this->select);

                    foreach ($this->select AS $key => $value) {
                        $entry = '`' . $key . '`';

                        if ($value['as'] != null)
                            $entry = '`' . $key . '` AS `' . $value['as'] . '`';

                        if ($value['function'] != null)
                            $entry = $value['function'] . '(' . $entry . ')';

                        if (++$index < $length)
                            $entry .= ', ';

                        $sql .= ' ' . $entry;
                    }
                }
            }

            if ($this->command == self::COMMAND_SELECT || $this->command == self::COMMAND_DELETE)
                $sql .= ' FROM';

            $sql .= ' `' . $this->table . '`';

            if ($this->command == self::COMMAND_UPDATE) {
                $sql .= ' SET ';

                $index  = 0;
                $length = count($this->data);

                if ($length <= 0)
                    throw new QueryCreateException('Entry insert is zero');

                foreach ($this->data AS $key => $value) {
                    $entry = '`' . $key . '`';
                    $entry .= '=';
                    $entry .= '\'' . $value . '\'';

                    if (++$index < $length)
                        $entry .= ', ';

                    $sql .= $entry;
                }
            } else if ($this->command == self::COMMAND_INSERT_INTO) {
                $index  = 0;
                $length = count($this->data);

                if ($length <= 0)
                    throw new QueryCreateException('Entry insert is zero');

                $columns = '(';
                $values  = '(';

                foreach ($this->data AS $key => $value) {
                    $columns .= '`' . $key . '`';
                    $values  .= '\'' . $value . '\'';

                    if (++$index < $length) {
                        $columns .= ', ';
                        $values  .= ', ';
                    }
                }

                $columns .= ')';
                $values  .= ')';

                $sql .= $columns . ' VALUES ' . $values;
            }

            if ($this->command != self::COMMAND_INSERT_INTO && count($this->where) > 0) {
                foreach ($this->where AS $index => $value) {
                    $entry = null;

                    if ($index == 0)
                        $entry .= ' WHERE';
                    else if ($value['where'] == null)
                        $entry .= ' ' . self::WHERE_AND;
                    else
                        $entry .= ' ' . $value['where'];

                    $entry .= ' `' . $value['column'] . '`';

                    if ($value['operator'] == null)
                        $entry .= self::OPERATOR_EQUAL;
                    else
                        $entry .= $value['operator'];

                    $entry .= ' \'' . $value['value'] . '\'';
                    $sql   .= $entry;
                }
            }

            if ($this->command == self::COMMAND_SELECT) {
                $index  = 0;
                $length = count($this->order);

                if ($length > 0) {
                    $sql .= ' ORDER BY ';

                    foreach ($this->order as $key => $value) {
                        $entry = '`' . $key . '`';

                        if ($value == null)
                            $entry .= ' ' . self::ORDER_ASC;
                        else
                            $entry .= ' ' . $value;

                        if (++$index < $length)
                            $entry .= ', ';

                        $sql .= $entry;
                    }
                }
            }

            if ($this->command != self::COMMAND_INSERT_INTO && is_array($this->limit)) {
                $sql .= ' LIMIT';
                $sql .= ' ' . $this->limit['start'];

                if ($this->limit['offset'] != null)
                    $sql .= ', ' . $this->limit['offset'];
            }

            return $sql;
        }

        /**
         * @param string $str
         * @return string
         */
        public static function escape($str)
        {
            return Strings::escape($str);
        }

        /**
         * @param string $str
         * @return string
         */
        public static function unescape($str)
        {
            return Strings::unescape($str);
        }

        /**
         * @return string
         */
        public function error()
        {
            return ModuleFactory::getInstance()->error();
        }

        /**
         * @return bool|resource|\mysqli_result|\PDOStatement
         */
        public function query($modify = true)
        {
            if ($modify == false && Connect::isResource($this->query))
                return $this->query;

            return ($this->query = ModuleFactory::getInstance()->query($this->toSql()));
        }

        /**
         * @param bool $modify
         * @return bool|int
         */
        public function rows($modify = false)
        {
            return ModuleFactory::getInstance()->rows($this->query($modify));
        }

        /**
         * @param bool $modify
         * @return array|bool
         */
        public function assoc($modify = false)
        {
            return ModuleFactory::getInstance()->assoc($this->query($modify));
        }

        /**
         * @return int
         */
        public function insertId()
        {
            return ModuleFactory::getInstance()->insertId();
        }

        /**
         * @return bool|resource|\mysqli_result|\PDOStatement
         */
        public function execute($modify = false)
        {
            return $this->query($modify);
        }

    }