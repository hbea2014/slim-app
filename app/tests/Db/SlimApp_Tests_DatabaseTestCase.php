<?php

namespace SlimApp\Test\Db;

abstract class SlimApp_Tests_DatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * @var \PDO 
     */
    static private $pdo = null;

    /**
     * @var \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    private $connection = null;

    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $config = require __DIR__ . '/database-config-for-dbunit.php';

        if (null === $this->connection) {
            // Only instantiate connection once per test

            if (null === self::$pdo) {
                // Only instantiate PDO once for test clean-up / fixture load
                $dsn = sprintf('%s:host=%s;dbname=%s;charset=%s',
                    $config['driver'],
                    $config['host'],
                    $config['dbname'],
                    $config['charset']
                );

                self::$pdo = new \PDO($dsn, $config['username'], $config['password']);
            }

            $this->connection = $this->createDefaultDBConnection(self::$pdo, $config['dbname']);
        }

        return $this->connection;
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return $this->createXMLDataSet(__DIR__ . '/seed.xml');
    }

    /**
     * Parses the XML seed file and stores data into a multidimensional array.
     *
     * @return array The tables contained in the seed file
     */
    public function getTablesFromDataSet()
    {
        // Get dataset from seed file
        $simplexml = simplexml_load_file(__DIR__ . '/seed.xml');

        $tables = [];

        foreach($simplexml->xpath('/dataset/table') as $table) {
            $tableName = (string) $table['name'];

            $tables[$tableName] = [];

            foreach ($table->row as $row) {
                $data = [];

                for($j = 0; $j < count($row->value); $j++) {
                    $columnName = (string) $table->column[$j];
                    $value = (string) $row->value[$j];
                    $data[$columnName] = $value;
                }

                $tables[$tableName][] = $data;
            }
        }

        return $tables;
    }

}

