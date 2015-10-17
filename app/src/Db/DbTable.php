<?php

namespace SlimApp\Db;

abstract class DbTable
{

    use \SlimApp\HasRequiredParamsTrait;
    
    /**
     * @var array The database configuration
     */
    protected $config;

    /**
     * @var \PDO The db adapter
     */
    protected $dbAdapter;

    /**
     * @var string The name of the table
     */
    protected $tableName;

    /**
     * Constructor
     *
     * @param null|array The database configuration
     */
    public function __construct($config = null)
    {
        // Set parameters required for database connection
        $this->setRequiredParams(['driver', 'host', 'charset', 'dbname', 'username', 'password']);

        if (null !== $config) {
            $this->setConfig($config);
        }
    }

    /**
     * Sets the database configuration if correct configuration passed, throws exception otherwise
     *
     * @param array $config The database configuration
     * @throws \DomainException
     * @return $this
     */
    public function setConfig(array $config)
    {
        if ( ! $this->hasRequiredParams($config) ) {
            throw new \DomainException('Missing required parameter(s)');
        }

        $this->config = new \ArrayObject($config, \ArrayObject::ARRAY_AS_PROPS);

        return $this;
    }

    /**
     * Gets database configuration
     *
     * @return array
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * Sets up the connection
     *
     * @return $this
     * @throws \RuntimeException
     * @throws \PDOException
     */
    protected function setConnection()
    {
        if (null === $this->getConfig()) {
            throw new \RuntimeException('Connection details not set yet');
        }

        $dsn = sprintf('%s:dbname=%s;host=%s;charset=%s',
            $this->getConfig()->driver,
            $this->getConfig()->dbname,
            $this->getConfig()->host,
            $this->getConfig()->charset
        );

        $pdo = new \PDO($dsn, $this->getConfig()->username, $this->getConfig()->password);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->dbAdapter = $pdo;

        return $this;
    }

    /**
     * Returns the connection ressource
     *
     * @return \PDO
     */
    private function getConnection()
    {
        if (null === $this->dbAdapter) {
            $this->setConnection();
        }

        return $this->dbAdapter;
    }

    /**
     * Returns the name of the table
     *
     * @return null|string
     * @throws \RuntimeException
     */
    public function getTableName()
    {
        if (null === $this->tableName) {
            throw new \RuntimeException('Table name not set');
        }

        return $this->tableName;
    }

    /**
     * Finds results matching a given primary key
     *
     * @param integer|string $value
     * @param null|string $primaryKey
     * @return false|array Returns the result on success, false on failure
     */
    public function find($value, $primaryKey = 'id')
    {
        $sql = sprintf('SELECT * FROM %s WHERE %s = ?', $this->getTableName(), $primaryKey);
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([$value]);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * Finds a single occurance
     *
     * @param null|string $where
     * @param null|string $order
     * @return false|array Returns the result on success, false otherwise
     */
    public function findRow($where = null, $order = null)
    {
        $sql = sprintf('SELECT * FROM %s', $this->getTableName());

        if (null !== $where) {
            $sql .= ' WHERE ' . $where;
        }

        if (null !== $order) {
            $sql .= ' ORDER BY ' . $order;
        }

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * Finds all results matching given optional conditions
     *
     * @param null|string $where
     * @param null|string $order
     * @param null|integer $limit
     * @param null|integer $offset
     * @return false|array Returns the result on success, false otherwise
     */
    public function findAll($where = null, $order = null, $limit = null, $offset = null)
    {
        $sql = sprintf('SELECT * FROM %s', $this->getTableName());

        if (null !== $where) {
            $sql .= ' WHERE ' . $where;
        }

        if (null !== $order) {
            $sql .= ' ORDER BY ' . $order;
        }

        if (null !== $limit) {
            if (null === $offset) {
                $offset = 0;
            }

            //$sql .= ' LIMIT ' . $limit . ', ' .$offset;
            $sql .= ' LIMIT ' . $limit . ' OFFSET ' .$offset;
        }

        $stmt = $this->getConnection()->prepare($sql);

        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * Updates one or more rows
     *
     * @param array $setColumnNamesValues Column names and their new values
     * $param string $where
     * @throws \DomainException
     */
    public function update(array $setColumnNamesValues, $where)
    {
        $sql = sprintf('UPDATE %s SET ', $this->getTableName());

        if (count($setColumnNamesValues) < 1) {
            throw new \DomainException('Missing column names and values to be updated');
        }

        $columnNames = array_keys($setColumnNamesValues);
        $values = array_values($setColumnNamesValues);

        foreach ($columnNames as $columnName) {
            $sql .= sprintf(' `%s` = ?,', $columnName);
        }

        // Remove the last coma
        $sql = substr($sql, 0, -1);

        if (null !== $where) {
            $sql .= ' WHERE ' . $where;
        }

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($values);
    }

    /**
     * Inserts data in the table
     *
     * @param array $columnNames
     * @param array $values
     * @throws \Exception
     */
    public function insert(array $columnNames, array $values)
    {
        $sql = sprintf('INSERT INTO %s ', $this->getTableName());

        $columnsCount = count($columnNames);
        $valuesCount = count($values);

        if (0 === $columnsCount) {
            throw new \Exception('Missing column names');
        }

        if ($columnsCount !== $valuesCount) {
            throw new \Exception('Column names count and values count do not match');
        }

        $sql .= sprintf('(`%s`) VALUES (%s)', 
            implode('`, `', $columnNames),
            substr(str_repeat('?, ', $valuesCount), 0, -2)
        );

        //die($sql);
        
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($values);
    }

    /**
     * Deletes one or more rows
     *
     * @param null|string $where
     */
    public function delete($where = null)
    {
        $sql = sprintf('DELETE FROM %s ', $this->getTableName());

        if (null !== $where) {
            $sql .= sprintf(' WHERE %s', $where);
        }

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
    }
}

