<?php

namespace SlimApp\Test\Db;

class MapperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers SlimApp\Db\Mapper::setDbTable
     */
    public function setDbTable_returns_Mapper()
    {
        $dbTable = $this->getMockForAbstractClass('\SlimApp\Db\DbTable');
        $mapper = new \SlimApp\Db\Mapper();
        $result = $mapper->setDbTable($dbTable);

        $this->assertInstanceOf('\SlimApp\Db\Mapper', $result);
    }

    /**
     * @test
     * @covers SlimApp\Db\Mapper::getDbTable
     * @expectedException \RuntimeException
     * @expectedExceptionMessage DbTable was not set
     */
    public function getDbTable_throws_RuntimeException_when_DbTable_not_previously_set()
    {
        $mapper = new \SlimApp\Db\Mapper();
        $dbTable = $mapper->getDbTable();
    }

    /**
     * @test
     * @covers SlimApp\Db\Mapper::getDbTable
     * @covers SlimApp\Db\Mapper::setDbTable
     */
    public function setDbTable_sets_DbTable_and_getDbTable_returns_DbTable_when_DbTable_previously_set()
    {
        $dbTable = $this->getMockForAbstractClass('\SlimApp\Db\DbTable');
        $mapper = new \SlimApp\Db\Mapper($dbTable);
        $dbTable = $mapper->getDbTable();

        $this->assertInstanceOf('\SlimApp\Db\DbTable', $dbTable);
    }

    /**
     * @test
     * @covers SlimApp\Mapper::setModel
     */
    public function setModel_returns_Mapper()
    {
        $model = $this->getMockForAbstractClass('\SlimApp\Model');
        $mapper = new \SlimApp\Db\Mapper();
        $result = $mapper->setModel($model);

        $this->assertInstanceOf('\SlimApp\Db\Mapper', $result);
    }

    /**
     * @test
     * @covers SlimApp\Mapper::getModel
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Model was not set
     */
    public function getModel_throws_RuntimeException_when_Model_not_previously_set()
    {
        $mapper = new \SlimApp\Db\Mapper();
        $model = $mapper->getModel();
    }

    /**
     * @test
     * @covers SlimApp\Mapper::getModel
     * @covers SlimApp\Mapper::setModel
     */
    public function setModel_sets_Model_and_getModel_returns_Model_when_Model_previously_set()
    {
        $dbTable = $this->getMockForAbstractClass('\SlimApp\Db\DbTable');
        $model = $this->getMockForAbstractClass('\SlimApp\Model');
        $mapper = new \SlimApp\Db\Mapper($dbTable, $model);
        $model = $mapper->getModel();

        $this->assertInstanceOf('\SlimApp\Model', $model);
    }

    /**
     * @test
     * @covers SlimApp\Db\Mapper::find
     * @param boolean|array $findResultsDbTable
     * @param integer|string $value
     * @param null|string $primaryKey
     * @dataProvider provider_find_returns_an_array_of_objects_if_success_false_instead
     */
    public function find_returns_an_array_of_objects_if_success_false_instead($findResultsDbTable, $value, $primaryKey)
    {
        $dbTable = $this->getMockBuilder('\SlimApp\Db\DbTable')
                        ->setMethods(['find'])
                        ->getMockForAbstractClass();
        $model = $this->getMockBuilder('\SlimApp\Model')
                      ->setMethods(['populate'])
                      ->getMockForAbstractClass();
        $mapper = new \SlimApp\Db\Mapper($dbTable, $model);

        $dbTable->method('find')
             ->with($value, $primaryKey)
             ->willReturn($findResultsDbTable);

        if (false !== $findResultsDbTable) {
            $model->method('populate')
                  // Return a model
                  ->willReturnSelf();
        }

        $results = $mapper->find($value, $primaryKey);

        if (false === $findResultsDbTable) {
            $this->assertFalse($results);
        } else {
            foreach ($results as $result) {
                $this->assertInstanceOf('\SlimApp\Model', $result);
            }
        }
    }

    public function provider_find_returns_an_array_of_objects_if_success_false_instead()
    {
        return [
            // findResultsDbTable, value, primaryKey
            [false, 'whateverValue', null],
            [false, 'whateverValue', 'whateverKey'],
            [
                [
                    ['columnName1' => 'value1', 'columnName2' => 'value2'],
                    ['columnName1' => 'value1', 'columnName2' => 'value2']
                ], 
                'whateverValue', 
                null
            ],
            [
                [
                    ['columnName1' => 'value1', 'columnName2' => 'value2'],
                    ['columnName1' => 'value1', 'columnName2' => 'value2']
                ], 
                'whateverValue', 
                'whateverKey'
            ],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Db\Mapper::findRow
     * @param boolean|array $findRowResultDbTable
     * @param integer|string $where
     * @param null|string $order
     * @dataProvider provider_findRow_returns_a_populated_object_if_success_false_instead
     */
    public function findRow_returns_a_populated_object_if_success_false_instead($findRowResultDbTable, $where, $order)
    {
        $dbTable = $this->getMockBuilder('\SlimApp\Db\DbTable')
                        ->setMethods(['findRow'])
                        ->getMockForAbstractClass();
        $model = $this->getMockBuilder('\SlimApp\Model')
                      ->setMethods(['populate'])
                      ->getMockForAbstractClass();
        $mapper = new \SlimApp\Db\Mapper($dbTable, $model);

        $dbTable->method('findRow')
             ->with($where, $order)
             ->willReturn($findRowResultDbTable);

        if (false !== $findRowResultDbTable) {
            $model->method('populate')
                  // Return a model
                  ->willReturnSelf();
        }

        $result = $mapper->findRow($where, $order);

        if (false === $findRowResultDbTable) {
            $this->assertFalse($result);
        } else {
            $this->assertInstanceOf('\SlimApp\Model', $result);
        }
    }

    public function provider_findRow_returns_a_populated_object_if_success_false_instead()
    {
        return [
            // findRowResultDbTable, where, order
            [false, 'whateverValue', null],
            [false, 'whateverValue', 'whateverKey'],
            [['columnName1' => 'where1', 'columnName2' => 'where2'] , 'whateverValue', null],
            [['columnName1' => 'where1', 'columnName2' => 'where2'] , 'whateverValue', 'whateverKey'],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Db\Mapper::findAll
     * @param boolean|array $findAllResultsDbTable
     * @param null|string $where
     * @param null|string $order
     * @param null|string $limit
     * @param null|string $offset
     * @dataProvider provider_findAll_returns_an_array_of_objects_if_success_false_instead
     */
    public function findAll_returns_an_array_of_objects_if_success_false_instead($findAllResultsDbTable, $where, $order, $limit, $offset)
    {
        $dbTable = $this->getMockBuilder('\SlimApp\Db\DbTable')
                        ->setMethods(['findAll'])
                        ->getMockForAbstractClass();
        $model = $this->getMockBuilder('\SlimApp\Model')
                      ->setMethods(['populate'])
                      ->getMockForAbstractClass();
        $mapper = new \SlimApp\Db\Mapper($dbTable, $model);

        $dbTable->method('findAll')
             ->with($where, $order, $limit, $offset)
             ->willReturn($findAllResultsDbTable);

        if (false !== $findAllResultsDbTable) {
            $model->method('populate')
                  // Return a model
                  ->willReturnSelf();
        }

        $results = $mapper->findAll($where, $order, $limit, $offset);

        if (false === $findAllResultsDbTable) {
            $this->assertFalse($results);
        } else {
            foreach ($results as $result) {
                $this->assertInstanceOf('\SlimApp\Model', $result);
            }
        }
    }

    public function provider_findAll_returns_an_array_of_objects_if_success_false_instead()
    {
        return [
            // findAllResultsDbTable, where, order, limit, offset
            [false, null, null, null, null],
            [false, '`columnName` = value', null, null, null],
            [false, '`columnName` = value', 'columnname', null, null],
            [false, '`columnName` = value', 'columnname', 10, null],
            [false, '`columnName` = value', 'columnname', 10, 0],

            [
                [
                    ['columnName1' => 'value1', 'columnName2' => 'value2'],
                    ['columnName1' => 'value1', 'columnName2' => 'value2']
                ], 
                null, null, null, null
            ],
            [
                [
                    ['columnName1' => 'value1', 'columnName2' => 'value2'],
                    ['columnName1' => 'value1', 'columnName2' => 'value2']
                ], 
                '`columnName` = value', null, null, null
            ],
            [
                [
                    ['columnName1' => 'value1', 'columnName2' => 'value2'],
                    ['columnName1' => 'value1', 'columnName2' => 'value2']
                ],
                '`columnName` = value', 'columnname', null, null
            ],
            [
                [
                    ['columnName1' => 'value1', 'columnName2' => 'value2'],
                    ['columnName1' => 'value1', 'columnName2' => 'value2']
                ], 
                '`columnName` = value', 'columnname', 10, null
            ],
            [
                [
                    ['columnName1' => 'value1', 'columnName2' => 'value2'],
                    ['columnName1' => 'value1', 'columnName2' => 'value2']
                ], 
                '`columnName` = value', 'columnname', 10, 0
            ],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Db\Mapper::update
     */
    public function update_calls_update_function_on_DbTable()
    {
        $setColumnNamesValues = ['columnName1' => 'value1', 'columnName2' => 'value2'];
        $where = '`columnName3` = value3';
        $dbTable = $this->getMockBuilder('\SlimApp\Db\DbTable')
                        ->setMethods(['update'])
                        ->getMockForAbstractClass();
        $mapper = new \SlimApp\Db\Mapper($dbTable);

        $dbTable->expects($this->once())
             ->method('update')
             ->with($setColumnNamesValues, $where);

        $mapper->update($setColumnNamesValues, $where);
    }

    /**
     * @test
     * @covers SlimApp\Db\Mapper::insert
     */
    public function insert_calls_insert_function_on_DbTable()
    {
        $columnNames = ['columnName1', 'columnName2'];
        $values = ['value1', 'value2'];
        $dbTable = $this->getMockBuilder('\SlimApp\Db\DbTable')
                        ->setMethods(['insert'])
                        ->getMockForAbstractClass();
        $mapper = new \SlimApp\Db\Mapper($dbTable);

        $dbTable->expects($this->once())
             ->method('insert')
             ->with($columnNames, $values);

        $mapper->insert($columnNames, $values);
    }

    /**
     * @test
     * @covers SlimApp\Db\Mapper::delete
     */
    public function delete_calls_delete_function_on_DbTable()
    {
        $where = '`columnName3` = value3';
        $dbTable = $this->getMockBuilder('\SlimApp\Db\DbTable')
                        ->setMethods(['delete'])
                        ->getMockForAbstractClass();
        $mapper = new \SlimApp\Db\Mapper($dbTable);

        $dbTable->expects($this->once())
             ->method('delete')
             ->with($where);

        $mapper->delete($where);
    }
}

