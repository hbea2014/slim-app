<?php

namespace SlimApp\Test;

abstract class SlimApp_Tests_ModelTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * The name of the class, eg. 'SlimApp\User'
     *
     * @var string
     */
    protected $className;

    /**
     * The required params for the class, eg. ['UserId', 'username', 'password', 'email']
     *
     * @var array
     */
    protected $requiredParams;

    /**
     * The names of the class getter methods
     *
     * @array
     */
    protected $getters;

    /**
     * The names of the class setter methods
     *
     * @array
     */
    protected $setters;

    /**
     * Construtor
     *
     * Gets and sets data (className, requiredParams, getters and setters) about the subclass
     */
    public function __construct()
    {
        $this->setClassName();
        $this->setRequiredParams();
        $this->setGettersAndSetters();
    }

    /**
     * Sets the class name of the tested class
     */
    protected function setClassName()
    {
        $testingClassName = get_class($this);

        // Remove 'Test' at the end of the className, and '\Test' in namespace
        $this->className = str_replace(['\Test', 'Test'], '', $testingClassName);
    }

    /**
     * Instantiates the class
     *
     * @param array $data
     */
    protected function getInstance($data = null)
    {
        return new $this->className($data);
    }

    /**
     * Sets requiredParams for the class
     */
    protected function setRequiredParams()
    {
        $this->requiredParams = $this->getInstance()->getRequiredParams();
    }

    /**
     * Gets requiredParams for the class
     *
     * @return array
     */
    protected function getRequiredParams()
    {
        return $this->requiredParams;
    }

    /**
     * Sets getters and setters
     */
    public function setGettersAndSetters()
    {
        $classMethods = get_class_Methods($this->className);

        array_walk($classMethods, function ($v) {
            if ('get' === substr($v, 0, 3) && 'getRequiredParams' !== $v) { 
                $this->getters[] = $v;
            } else if ('set' === substr($v, 0, 3)) { 
                $this->setters[] = $v;
            }
        });
    }

    /**
     * Gets the name of the getter method to retrieve the property of name propertyName
     *
     * @param $string $propertyName
     */
    public function getGetterFromPropertyName($propertyName)
    {
        return 'get' . ucfirst($propertyName);
    }

    /**
     * Gets the name of the setter method to retrieve the property of name propertyName
     *
     * @param $string $propertyName
     */
    public function getSetterFromPropertyName($propertyName)
    {
        return 'set' . ucfirst($propertyName);
    }

    /**
     * @test
     * @covers ModelSubClass::__construct
     */
    public function constructor_sets_required_parameters()
    {
        $object = $this->getInstance();

        $expectedRequiredParams = $this->getRequiredParams();
        $requiredParams = $object->getRequiredParams();

        $this->assertEquals($expectedRequiredParams, $requiredParams);
    }

    /**
     * @test
     * @covers ModelSubClass::getPropertyName
     * @covers ModelSubClass::setPropertyName
     */
    public function getter_and_setters_correctly_getting_and_setting()
   {
        $object = $this->getInstance();

        foreach ($this->provider_getter_and_setters_correctly_getting_and_setting as $data) {
            foreach ($data as $propertyName => $value) {
                // Set property
                $setterName = $this->getSetterFromPropertyName($propertyName);
                $object->$setterName($value);

                // Get property
                $getterName = $this->getGetterFromPropertyName($propertyName);
                $resultValue = $object->$getterName($propertyName);

                // Assert
                $this->assertEquals($value, $resultValue);
            }
        }
    }

    /**
     * @test
     * @covers ModelSubClass::populate
     */
    public function populate_populates_object_and_returns_self_if_correct_row_data()
    {
        $object = $this->getInstance();
        $result = $object->populate($this->provider_populate_and_toArray_tests);

        foreach ($this->provider_populate_and_toArray_tests as $propertyName => $value) {
            // Get property
            $getterName = $this->getGetterFromPropertyName($propertyName);
            $resultValue = $object->$getterName($propertyName);

            // Assert that property correctly set by populate()
            $this->assertEquals($value, $resultValue);
        }

        // Assert that populate returns the object
        $this->assertInstanceOf($this->className, $object);
        $this->assertSame($result, $object);
    }

    /**
     * @test
     * @covers ModelSubClass::toArray
     */
    public function toArray_returns_object_properties_as_array()
    {
        $object = $this->getInstance($this->provider_populate_and_toArray_tests);
        $result = $object->toArray();

        $this->assertEquals($this->provider_populate_and_toArray_tests, $result);
    }
}
