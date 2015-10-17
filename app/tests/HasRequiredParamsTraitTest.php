<?php

namespace SlimApp\Test;

class HasRequiredParamsTraitTest extends \PHPUnit_Framework_Testcase
{

    /**
     * @test
     * @covers SlimApp\HasRequiredParamsTrait::hasRequiredParams
     * @param array $requiredParams The required parameters
     * @param array $data The data to be checked
     * @param boolean $returnValue The value returned by hasRequiredParams()
     * @dataProvider provider_hasRequiredParams_returns_true_if_data_contains_required_parameters
     * @see https://sandro-keil.de/blog/2015/08/31/phpunit-with-peek-and-poke-no-more-reflection/
     */
    function hasRequiredParams_returns_true_if_data_contains_required_parameters(array $requiredParams, array $data, $returnValue)
    {
        // Get mock for our trait
        $trait = $this->getMockForTrait('\SlimApp\HasRequiredParamsTrait');

        // Proxy the protected properties and methods
        $hasRequiredParamsTrait = new \SebastianBergmann\PeekAndPoke\Proxy($trait);

        // Now we can set the protected params...
        $hasRequiredParamsTrait->setRequiredParams($requiredParams);

        // ... and run the protected method
        $result = $hasRequiredParamsTrait->hasRequiredParams($data);

        $this->assertEquals($returnValue, $result);
    }

    function provider_hasRequiredParams_returns_true_if_data_contains_required_parameters()
    {
        return [
            // requiredParams, row, return value
            [
                ['one', 'two', 'three'],
                ['one' => 'ForTheMoney', 'two' => 'ForTheShow'],
                false
            ],
            [
                ['one', 'two', 'three'],
                ['one' => 'ForTheMoney', 'two' => 'ForTheShow', 'three' => 'ToGetReady'],
                true
            ]
        ];
    }
}
