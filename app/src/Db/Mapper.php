<?php

namespace SlimApp\Db;

use SlimApp\Db\DbTable;
use SlimApp\Model;

class Mapper
{

    /**
     * @var SlimApp\Db\DbTable
     */
    protected $dbTable;

    /**
     * @var SlimApp\Model;
     */
    protected $model;

    /**
     * Constructor
     *
     * @param null|\SlimApp\Db\DbTable
     * @param null|\SlimApp\Model
     */
    public function __construct($dbTable = null, $model = null)
    {
        if (null !== $dbTable) {
            $this->setDbTable($dbTable);
        }

        if (null !== $model) {
            $this->setModel($model);
        }
    }

    /**
     * Sets the DbTable
     *
     * @param \SlimApp\Db\DbTable
     * @returns $this
     */
    public function setDbTable(DbTable $dbTable)
    {
        $this->dbTable = $dbTable;

        return $this;
    }

    /**
     * Fetches the DbTable
     *
     * @retuns null|\SlimApp\Db\DbTable
     * @throws \RuntimeException
     */
    public function getDbTable()
    {
        if (null === $this->dbTable) {
            throw new \RuntimeException('DbTable was not set');
        }

        return $this->dbTable;
    }

    /**
     * Sets the Model
     *
     * @param \SlimApp\Model
     * @returns $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Fetches the Model
     *
     * @retuns \SlimApp\Model
     * @throws \RuntimeException
     */
    public function getModel()
    {
        if (null === $this->model) {
            throw new \RuntimeException('Model was not set');
        }

        return $this->model;
    }

    /**
     * Finds results matching a given primary key
     *
     * @param string $value
     * @param null|string $primaryKey
     * @see \SlimApp\Db\DbTable::find()
     * @return false|array The objects populated if success, false otherwise
     */
    public function find($value, $primaryKey = 'id')
    {
        $result = $this->getDbTable()->find($value, $primaryKey);

        if ($result) {
            $resultSet = [];

            foreach ($result as $row) {
                $object = clone $this->getModel();
                $resultSet[] = $object->populate($row);
            }

            return $resultSet;
        }

        return false;
    }

    /**
     * Finds a single occurance
     *
     * @param null|string $where
     * @param null|string order
     * @see \SlimApp\Db\DbTable::findRow()
     * @return false|\SlimApp\Model The object populated if success, false otherwise
     */
    public function findRow($where = null, $order = null)
    {
        $result = $this->getDbTable()->findRow($where, $order);

        if ($result) {
            $object = clone $this->getModel();
            $object->populate($result);

            return $object;
        }

        return false;
    }

    /**
     * Finds all results matching given optional conditions
     *
     * @param null|string $where
     * @param null|string $order
     * @param null|string $limit
     * @param null|string $offset
     * @see \SlimApp\Db\DbTable::findAll()
     * @return false|array The objects populated if success, false otherwise
     */
    public function findAll($where = null, $order = null, $limit = null, $offset = null)
    {
        $result = $this->getDbTable()->findAll($where, $order, $limit, $offset);

        if ($result) {
            $resultSet = [];

            foreach ($result as $row) {
                $object = clone $this->getModel();
                $resultSet[] = $object->populate($row);
            }

            return $resultSet;
        }

        return false;
    }

    /**
     * Updates data in the table
     *
     * @param array $setColumnNamesValues Column names and their new values
     * @param string $where
     * @return boolean Returns true on success, false on failure
     * @see \SlimApp\Db\DbTable::update()
     */
    public function update(array $setColumnNamesValues, $where)
    {
        return $this->getDbTable()->update($setColumnNamesValues, $where);
    }

    /**
     * Inserts data in the table
     *
     * @param array $columnNames
     * @param array $values
     * @return boolean Returns true on success, false on failure
     * @see \SlimApp\Db\DbTable::insert()
     */
    public function insert(array $columnNames, array $values)
    {
        return $this->getDbTable()->insert($columnNames, $values);
    }

    /**
     * Deletes data in the table
     *
     * @param null|string $where
     * @return boolean Returns true on success, false on failure
     * @see \SlimApp\Db\DbTable::delete()
     */
    public function delete($where = null)
    {
        return $this->getDbTable()->delete($where);
    }
}

