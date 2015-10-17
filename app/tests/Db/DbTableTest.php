<?php

namespace SlimApp\Test\Db;

//class DbTableTest extends \PHPUnit_Framework_Testcase
class DbTableTest extends SlimApp_Tests_DatabaseTestCase
{

    /**
     * @test
     * @covers SlimApp\Db\DbTable::setConfig
     * @uses SlimApp\HasRequiredParamsTrait * 
     * @expectedException \DomainException
     * @expectedExceptionMessage Missing required parameter(s)
     */
    public function setConfig_throwns_DomainException_if_config_not_containing_all_required_params()
    {
        $table = $this->getMockForAbstractClass('SlimApp\Db\DbTable');
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        $dbTable->setRequiredParams(['driver', 'host', 'charset', 'dbname', 'username', 'password']);
        $config = ['no', 'dsn', 'data', 'in', 'this', 'config', 'array'];

        $dbTable->setConfig($config);
    }

    /**
     * @test
     * @covers SlimApp\Db\DbTable::getConfig
     */
    public function getConfig_returns_empty_array_if_not_set()
    {
        $table = $this->getMockForAbstractClass('SlimApp\Db\DbTable');
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);

        $this->assertEmpty($dbTable->getConfig());
    }

    /**
     * @test
     * @covers SlimApp\Db\DbTable::setConfig
     * @covers SlimApp\Db\DbTable::getConfig
     * @uses SlimApp\HasRequiredParamsTrait
     */
    public function setConfig_sets_config_and_returns_DbTable_instance_if_config_containing_all_required_params()
    {
        $table = $this->getMockForAbstractClass('SlimApp\Db\DbTable');
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        $dbTable->setRequiredParams(['driver', 'host', 'charset', 'dbname', 'username', 'password']);
        $config = [
            'driver' => 'whatever',
            'host' => 'whatever',
            'charset' => 'whatever',
            'dbname' => 'whatever',
            'username' => 'whatever',
            'password' => 'whatever'
        ];

        $result = $dbTable->setConfig($config);

        $this->assertEquals(new \ArrayObject($config, \ArrayObject::ARRAY_AS_PROPS), $dbTable->getConfig());
        $this->assertInstanceOf('\SlimApp\Db\DbTable', $result);
    }

    /**
     * @test
     * @covers SlimApp\Db\DbTable::__construct
     * @uses SlimApp\HasRequiredParamsTrait
     */
    public function constructor_sets_required_params()
    {
        $table = $this->getMockForAbstractClass('SlimApp\Db\DbTable');
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        $requiredParams = ['driver', 'host', 'charset', 'dbname', 'username', 'password'];

        $this->assertEquals($requiredParams, $dbTable->getRequiredParams());
    }

    /**
     * @test
     * @covers SlimApp\Db\DbTable::__construct
     * @covers SlimApp\Db\DbTable::setConfig
     * @covers SlimApp\Db\DbTable::getConfig
     * @uses SlimApp\HasRequiredParamsTrait
     * @param array $config The database configuration
     * @param array $returnedConfig The database configuration returned by getConfig()
     * @dataProvider provider_constructor_sets_config_if_config_given
     */
    public function constructor_sets_config_if_config_given($config, $returnedConfig)
    {
        $requiredParams = ['driver', 'host', 'charset', 'dbname', 'username', 'password'];
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                     ->setConstructorArgs([$config])
                     ->getMockForAbstractClass();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);

        $this->assertEquals($returnedConfig, $dbTable->getConfig());
    }

    public function provider_constructor_sets_config_if_config_given()
    {
        return [
            // config, returnedConfig
            [null, null],
            [
                [
                    'driver' => 'whatever',
                    'host' => 'whatever',
                    'charset' => 'whatever',
                    'dbname' => 'whatever',
                    'username' => 'whatever',
                    'password' => 'whatever'
                ],
                new \ArrayObject([
                    'driver' => 'whatever',
                    'host' => 'whatever',
                    'charset' => 'whatever',
                    'dbname' => 'whatever',
                    'username' => 'whatever',
                    'password' => 'whatever'
                ], \ArrayObject::ARRAY_AS_PROPS)
            ]
        ];
    }

    /**
     * @test
     * @covers SlimApp\Db\DbTable::setConnection
     * @uses SlimApp\HasRequiredParamsTrait
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Connection details not set yet
     */
    public function setConnection_throws_RuntimeException_when_config_not_set()
    {
        $table = $this->getMockForAbstractClass('SlimApp\Db\DbTable');
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);

        $dbTable->setConnection();
    }

    /**
     * @test
     * @covers SlimApp\Db\DbTable::setConnection
     * @uses SlimApp\HasRequiredParamsTrait
     * @expectedException \PDOException
     */
    public function setConnection_throws_PDOException_when_no_db_for_config_data()
    {
        $config = [
            'driver' => 'whatever',
            'host' => 'whatever',
            'charset' => 'whatever',
            'dbname' => 'whatever',
            'username' => 'whatever',
            'password' => 'whatever'
        ];
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                     ->setConstructorArgs([$config])
                     ->getMockForAbstractClass();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);

        $dbTable->setConnection();
    }

    /**
     * @test
     * @covers SlimApp\Db\DbTable::setConnection
     * @covers SlimApp\Db\DbTable::getConnection
     * @uses SlimApp\HasRequiredParamsTrait
     */
    public function setConnection_sets_dbAdapter_and_returns_DbTable_instance_if_config_set_and_correct()
    {
        $config = require __DIR__ . '/database-config-for-dbunit.php';
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                     ->setConstructorArgs([$config])
                     ->getMockForAbstractClass();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);

        $table = $dbTable->setConnection();
        $dbAdapter = $dbTable->getConnection();

        $this->assertInstanceOf('\SlimApp\Db\DbTable', $table);
        $this->assertInstanceOf('\PDO', $dbAdapter);
    }

    /**
     * @test
     * @covers SlimApp\Db\DbTable::getConnection
     * @uses SlimApp\HasRequiredParamsTrait
     */
    public function getConnection_sets_connection_if_connection_was_not_set()
    {
        $config = require __DIR__ . '/database-config-for-dbunit.php';
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                     ->getMockForAbstractClass();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        $dbTable->setConfig($config);

        $dbAdapter = $dbTable->getConnection();

        $this->assertInstanceOf('\PDO', $dbAdapter);
    }

    /**
     * @test
     * @covers SlimApp\Db\DbTable::getTableName
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Table name not set
     */
    public function getTableName_throws_RuntimeException_if_tableName_not_set()
    {
        $table = $this->getMockForAbstractClass('SlimApp\Db\DbTable');
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);

        $dbTable->getTableName();
    }

    /**
     * @test
     * @covers SlimApp\Db\DbTable::getTableName
     */
    public function getTableName_returns_tableName_if_set()
    {
        $table = $this->getMockForAbstractClass('SlimApp\Db\DbTable');
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        $tableName = 'TableName';

        $dbTable->tableName = $tableName;
        $resultTableNameSet = $dbTable->getTableName();

        $this->assertEquals($tableName, $resultTableNameSet);
    }

    /**
     * @test
     * @covers SlimApp\Db\DbTable::find
     * @uses SlimApp\HasRequiredParamsTrait
     * @param string $tableName
     * @param array $keyOperatorValueValueType
     * @param string $findResults 'noResults' if no results expected, 'results' otherwise
     * @param string $tableName
     * @param integer|string $value
     * @param null|string $key
     * @dataProvider provider_find_returns_false_when_data_not_present_in_database_data_otherwise
     */
    public function find_returns_false_when_data_not_present_in_database_data_otherwise($findResults, $tableName, $value, $key)
    {
        $config = require __DIR__ . '/database-config-for-dbunit.php';
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                     ->setConstructorArgs([$config])
                     ->getMockForAbstractClass();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        
        // Set tablename manually (set in subclasses, not in abstract class...)
        $dbTable->tableName = $tableName;
        $results = $dbTable->find($value, $key);

        // Get data as array from dataset (xml seed file)
        $resources = $this->getTablesFromDataSet()[$tableName]; // ie. users

        // Query array using a php linq
        $expected = \YaLinqo\Enumerable::from($resources)
            ->where('$resources ==> $resources["' . $key . '"] == "' . (string) $value . '"')
            ->toArray();

        $expected = array_values($expected);

        //die(var_dump($results, $expected));

        $this->assertEquals($expected, $results);

        if ('noResults' === $findResults) {
            $this->assertEmpty($results);
        }
    }

    public function provider_find_returns_false_when_data_not_present_in_database_data_otherwise()
    {
        return [
            // findResults, tableName, value, key
            ['noResults', 'Users', 100, 'UserId'],
            ['noResults', 'Users', 'asdfasdf', 'username'],
            ['noResults', 'Users', 'asdfasdf@asdf.df', 'email'],
            ['results', 'Users', 11, 'UserId'],
            ['results', 'Users', 'leo', 'username'],
            ['results', 'Users', 'gil@asdf.df', 'email'],
        ];
    }
    
    /**
     * @test
     * @covers SlimApp\Db\DbTable::findRow
     * @uses SlimApp\HasRequiredParamsTrait
     * @param string $tableName
     * @param array $keyOperatorValueValueType
     * @param string $findRowResults 'noResults' if no results expected, 'results' otherwise
     * @param string $tableName
     * @param null|string $where
     * @param null|string $whereLinq The corresponding string to create where clause with YaLinqo
     * @param null|string $order
     * @param null|string $orderLinq The corresponding string to create the order clause with YaLinqo
     * @dataProvider provider_findRow_returns_false_when_data_not_present_in_database_data_otherwise
     */
    public function findRow_returns_false_when_data_not_present_in_database_data_otherwise($findRowResults, $tableName, $where, $whereLinq, $order, $orderLinq)
    {
        $config = require __DIR__ . '/database-config-for-dbunit.php';
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                     ->setConstructorArgs([$config])
                     ->getMockForAbstractClass();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        
        // Set tablename manually (set in subclasses, not in abstract class...)
        $dbTable->tableName = $tableName;
        $results = $dbTable->findRow($where, $order);

        // Get data as array from dataset (xml seed file)
        $resources = $this->getTablesFromDataSet()[$tableName]; // ie. users

        // Query array using a php linq
        $expected = \YaLinqo\Enumerable::from($resources)
            ->where('$resources ==> $resources' . $whereLinq);

        if (null !== $orderLinq) {
            $expected = $expected->orderBy('$resources ==> $resources' . $orderLinq);
        }

        $expected = array_values($expected->toArray());

        //die(var_dump($results, $expected));

        if ('noResults' === $findRowResults) {
            $this->assertFalse($results);
            //$this->assertfalse($results);
            $this->assertEmpty($expected);
        } else {
            $expected = $expected[0];

            $this->assertEquals($expected, $results);
        }
    }

    public function provider_findRow_returns_false_when_data_not_present_in_database_data_otherwise()
    {
        return [
            // findRowResults, tableName, where, whereLinq, order, orderLinq
            ['noResults', 'Users', '`UserId` > 100', '["UserId"] > 100', null, null],
            ['noResults', 'Users', '`UserId` > 100', '["UserId"] < 10', null, null],
            ['noResults', 'Users', '`UserId` > 100', '["UserId"] > 100', 'username', '["username"]'],
            ['noResults', 'Users', '`UserId` < 10', '["UserId"] < 10', 'username', '["username"]'],
            ['noResults', 'Users', '`UserId` > 100', '["UserId"] > 100', 'username', '["username"]'],
            ['noResults', 'Users', '`UserId` < 10', '["UserId"] < 10', 'username', '["username"]'],
            ['noResults', 'Users', '`UserId` > 100', '["UserId"] > 100', 'username', '["username"]'],
            ['noResults', 'Users', '`UserId` < 10', '["UserId"] < 10', 'username', '["username"]'],
            ['results', 'Users', '`UserId` > 10', '["UserId"] > 10', null, null],
            ['results', 'Users', '`UserId` > 10', '["UserId"] > 10', null, null],
            ['results', 'Users', '`UserId` > 10', '["UserId"] > 10', null, null],
            ['results', 'Users', '`UserId` > 10', '["UserId"] > 10', 'UserId', '["UserId"]'],
            ['results', 'Users', '`UserId` > 10', '["UserId"] > 10', 'username', '["username"]'],
            ['results', 'Users', '`UserId` > 10', '["UserId"] > 10', 'email', '["email"]'],
        ];
    }

    /**
     * @test
     * @covers SlimApp\Db\DbTable::findAll
     * @uses SlimApp\HasRequiredParamsTrait
     * @param string $tableName
     * @param array $keyOperatorValueValueType
     * @param string $findAllResults 'noResults' if no results expected, 'results' otherwise
     * @param string $tableName
     * @param null|string $where
     * @param null|string $whereLinq The corresponding string to create where clause with YaLinqo
     * @param null|string $order
     * @param null|string $orderLinq The corresponding string to create the order clause with YaLinqo
     * @param null|integer $limit
     * @param null|integer $offset
     * @dataProvider provider_findAll_returns_false_when_data_not_present_in_database_data_otherwise
     */
    public function findAll_returns_false_when_data_not_present_in_database_data_otherwise($findAllResults, $tableName, $where, $whereLinq, $order, $orderLinq, $limit, $offset)
    {
        $config = require __DIR__ . '/database-config-for-dbunit.php';
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                     ->setConstructorArgs([$config])
                     ->getMockForAbstractClass();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        
        // Set tablename manually (set in subclasses, not in abstract class...)
        $dbTable->tableName = $tableName;
        $results = $dbTable->findAll($where, $order, $limit, $offset);

        // Get data as array from dataset (xml seed file)
        $resources = $this->getTablesFromDataSet()[$tableName]; // ie. users

        // Query array using a php linq
        $expected = \YaLinqo\Enumerable::from($resources)
            ->where('$resources ==> $resources' . $whereLinq);

        if (null !== $orderLinq) {
            $expected = $expected->orderBy('$resources ==> $resources' . $orderLinq);
        }

        // Get expected values as single dimension / one-dimensional array
        $expected = array_values($expected->toArray());

        if (null !== $limit) {
            if (null === $offset) {
               $offset = 0;
            } 

            $expected = array_values(array_slice($expected, $offset, $limit));
        }

        //die(var_dump($results, $expected));

        $this->assertEquals($expected, $results);

        if ('noResults' === $findAllResults) {
            $this->assertEmpty($results);
        }
    }

    public function provider_findAll_returns_false_when_data_not_present_in_database_data_otherwise()
    {
        return [
            // findAllResults, tableName, where, whereLinq, order, orderLinq, limit, offset
            ['noResults', 'Users', '`UserId` > 100', '["UserId"] > 100', null, null, null, null],
            ['noResults', 'Users', '`UserId` > 100', '["UserId"] < 10', null, null, null, null],
            ['noResults', 'Users', '`UserId` > 100', '["UserId"] > 100', 'username', '["username"]', null, null],
            ['noResults', 'Users', '`UserId` < 10', '["UserId"] < 10', 'username', '["username"]', null, null],
            ['noResults', 'Users', '`UserId` > 100', '["UserId"] > 100', 'username', '["username"]', 2, null],
            ['noResults', 'Users', '`UserId` < 10', '["UserId"] < 10', 'username', '["username"]', 2, null],
            ['noResults', 'Users', '`UserId` > 100', '["UserId"] > 100', 'username', '["username"]', 2, 1],
            ['noResults', 'Users', '`UserId` < 10', '["UserId"] < 10', 'username', '["username"]', 2, 1],
            ['results', 'Users', '`UserId` > 10', '["UserId"] > 10', null, null, null, null],
            ['results', 'Users', '`UserId` > 10', '["UserId"] > 10', 'username', '["username"]', null, null],
            ['results', 'Users', '`UserId` > 10', '["UserId"] > 10', 'email', '["email"]', null, null],
            ['results', 'Users', '`UserId` > 10', '["UserId"] > 10', 'username', '["username"]', 2, null],
            ['results', 'Users', '`UserId` > 10', '["UserId"] > 10', 'username', '["username"]', 2, 1],
            ['results', 'Users', '`UserId` > 10', '["UserId"] > 10', 'email', '["email"]', 3, 1],
        ];
    }
    
    /**
     * @test
     * @param string $tableName
     * @param array $setColumnNamesValues
     * @param null|string $where
     * @dataProvider provider_update_throws_DomainException_if_no_values_to_update
     * @expectedException \DomainException
     * @expectedExceptionMessage Missing column names and values to be updated
     */
    public function update_throws_DomainException_if_no_values_to_update($tableName, $setColumnNamesValues, $where)
    {
        $config = require __DIR__ . '/database-config-for-dbunit.php';
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                     ->setConstructorArgs([$config])
                     ->getMockForAbstractClass();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        
        // Set tablename manually (set in subclasses, not in abstract class...)
        $dbTable->tableName = $tableName;
        $results = $dbTable->update($setColumnNamesValues, $where);
    }

    public function provider_update_throws_DomainException_if_no_values_to_update()
    {
        return [
            // tableName, setColumnNamesValues, where
            ['Users', [], null],
            ['Users', [], 'whatever'],
        ];
    }
    
    /**
     * @test
     * @param string $tableName
     * @param array $setColumnNamesValues
     * @param null|string $where
     * @param null|string $whereLinq The corresponding string to create where clause with YaLinqo
     * @uses SlimApp\Db\DbTable::findRow
     * @dataProvider provider_update_updates_data_correctly
     */
    public function update_updates_data_correctly($tableName, $setColumnNamesValues, $where, $whereLinq)
    {
        $config = require __DIR__ . '/database-config-for-dbunit.php';
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                     ->setConstructorArgs([$config])
                     ->getMockForAbstractClass();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        
        // Set tablename manually (set in subclasses, not in abstract class...)
        $dbTable->tableName = $tableName;
        $dbTable->update($setColumnNamesValues, $where);

        $result = $dbTable->findRow($where);

        // Get data as array from dataset (xml seed file)
        $resources = $this->getTablesFromDataSet()[$tableName]; // ie. users

        // Query array using a php linq
        $expected = \YaLinqo\Enumerable::from($resources)
            ->where('$resources ==> $resources' . $whereLinq)
            ->toArray();

        // Substitute data in array
        $expected = array_merge($expected[0], $setColumnNamesValues);

        //die(var_dump($result, $expected));

        $this->assertEquals($expected, $result);

    }

    public function provider_update_updates_data_correctly()
    {
        return [
            // tableName, setColumnNamesValues, where, whereLinq
            ['Users', ['password' => 'leopwNEW'], '`UserId` = 11', '["UserId"] == 11'],
            ['Users', ['password' => 'leopwNEWNEW', 'email' => 'leoEMAILNEW@asdf.df' ], '`username` = "leo"', '["username"] == "leo"'],
        ];
    }
    
    /**
     * @test
     * @param string $tableName
     * @param array $columnNames
     * @param array $values
     * @dataProvider provider_insert_throws_Exception_if_no_columnNames
     * @expectedException \Exception
     * @expectedExceptionMessage Missing column names
     */
    public function insert_throws_Exception_if_no_columnNames($tableName, $columnNames, $values)
    {
        $config = require __DIR__ . '/database-config-for-dbunit.php';
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                     ->setConstructorArgs([$config])
                     ->getMockForAbstractClass();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        
        // Set tablename manually (set in subclasses, not in abstract class...)
        $dbTable->tableName = $tableName;
        $results = $dbTable->insert($columnNames, $values);
    }

    public function provider_insert_throws_Exception_if_no_columnNames()
    {
        return [
            // tableName, columnNames, values
            ['Users', [], []],
            ['Users', [], ['whatever', 'the', 'weather']],
        ];
    }
    
    /**
     * @test
     * @param string $tableName
     * @param array $columnNames
     * @param array $values
     * @dataProvider provider_insert_throws_Exception_if_columnNames_count_and_values_count_do_not_match
     * @expectedException \Exception
     * @expectedExceptionMessage Column names count and values count do not match
     */
    public function insert_throws_Exception_if_columnNames_count_and_values_count_do_not_match($tableName, $columnNames, $values)
    {
        $config = require __DIR__ . '/database-config-for-dbunit.php';
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                     ->setConstructorArgs([$config])
                     ->getMockForAbstractClass();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        
        // Set tablename manually (set in subclasses, not in abstract class...)
        $dbTable->tableName = $tableName;
        $results = $dbTable->insert($columnNames, $values);
    }

    public function provider_insert_throws_Exception_if_columnNames_count_and_values_count_do_not_match()
    {
        return [
            // tableName, columnNames, values
            ['Users', ['columnName1', 'columnName2'], ['value1', 'value2', 'value3']],
            ['Users', ['columnName1', 'columnName2', 'columnName3'], ['value1', 'value2']],
        ];
    }

    /**
     * @test
     * @param string $tableName
     * @param array $columnNames
     * @param array $values
     * @param string $where
     * @uses SlimApp\Db\DbTable::findRow
     * @dataProvider provider_insert_inserts_data
     */
    public function insert_inserts_data($tableName, $columnNames, $values, $where)
    {
        $config = require __DIR__ . '/database-config-for-dbunit.php';
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                     ->setConstructorArgs([$config])
                     ->getMockForAbstractClass();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        
        // Set tablename manually (set in subclasses, not in abstract class...)
        $dbTable->tableName = $tableName;
        $dbTable->insert($columnNames, $values);

        // Check if row inserted
        $result = $dbTable->findRow($where);

        // Recreate the result using the columnNames and values
        $expected = [];

        for ($i = 0; $i < count($columnNames); $i++) {
            $expected[$columnNames[$i]] = $values[$i];
        }

        //die(var_dump($result, $expected));
        
        $this->assertEquals($expected, $result);
    }

    public function provider_insert_inserts_data()
    {
        return [
            // tableName, columnNames, values, where
            ['Users', ['UserId', 'username', 'password', 'email'], [100, 'anna', 'annapw', 'anna@asdf.df'], '`UserId` = 100'],
            ['Users', ['UserId', 'username', 'password', 'email'], [222, 'lily', 'lilypw', 'lily@asdf.df'], '`username` = "lily"'],
        ];
    }

    /**
     * @test
     * @param string $tableName
     * @param array $columnNames
     * @param array $values
     * @param string $where
     * @uses SlimApp\Db\DbTable::insert
     * @uses SlimApp\Db\DbTable::findRow
     * @dataProvider provider_delete_deletes_data
     */
    public function delete_deletes_data($tableName, $columnNames, $values, $where)
    {
        $config = require __DIR__ . '/database-config-for-dbunit.php';
        $table = $this->getMockBuilder('SlimApp\Db\DbTable')
                     ->setConstructorArgs([$config])
                     ->getMockForAbstractClass();
        $dbTable = new \SebastianBergmann\PeekAndPoke\Proxy($table);
        
        // Set tablename manually (set in subclasses, not in abstract class...)
        $dbTable->tableName = $tableName;

        // Insert row and then delete it
        $dbTable->insert($columnNames, $values);

        $dbTable->delete($where);

        // Check if row deleted
        $result = $dbTable->findRow($where);

        //die(var_dump($result));
        
        $this->assertFalse($result);
    }

    public function provider_delete_deletes_data()
    {
        return [
            // tableName, columnNames, values, where
            ['Users', ['UserId', 'username', 'password', 'email'], [100, 'fay', 'faypw', 'fay@asdf.df'], '`UserId` = 200'],
            ['Users', ['username', 'password', 'email'], ['phil', 'philpw', 'phil@asdf.df'], '`username` = "phil"'],
            ['Users', ['username', 'password', 'email'], ['siggy', 'siggypw', 'siggy@asdf.df'], '`username` = "siggy"'],
        ];
    }
}

