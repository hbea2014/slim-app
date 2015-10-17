<?php

namespace SlimApp\Test;

class ModelTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers SlimApp\Model::__construct
     * @dataProvider provider_construct_calls_populate_only_if_data_submitted
     */
    public function construct_calls_populate_only_if_data_submitted($data)
    {

        $model = $this->getMockBuilder('\SlimApp\Model')
                      ->disableOriginalConstructor()
                      ->getMockForAbstractClass();

        if (null !== $data) {
            $model->expects($this->once())
                  ->method('populate')
                  ->with($data)
                  ->willReturnSelf();
        }

        // Call the constructor
        $reflectedModel = new \ReflectionClass('\SlimApp\Model');
        $constructor = $reflectedModel->getConstructor();
        $constructor->invoke($model, $data);
    }

    public function provider_construct_calls_populate_only_if_data_submitted()
    {
        return [
            [['propertyName1' => 'value1', 'propertyName2' => 'value2']],
            [null]
        ];
    }
}

