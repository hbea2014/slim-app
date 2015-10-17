<?php

namespace SlimApp;

trait HasRequiredParamsTrait
{

    /**
     * @var array The required parameters
     */
    protected $requiredParams = [];

    /**
     * @param array $requiredParams The required parameters
     */
    protected function setRequiredParams(array $requiredParams)
    {
        $this->requiredParams = $requiredParams;
    }

    /**
     * @return array The required parameters
     */
    public function getRequiredParams()
    {
        return $this->requiredParams;
    }

    /**
     * Checks if the data contains the required parameters
     *
     * @param array $data The data to be checked
     * @return boolean Returns true if data contains the required parameters, false otherwise
     */
    protected function hasRequiredParams(array $data)
    {
        return [] === array_diff($this->getRequiredParams(), array_keys($data));
    }
}

