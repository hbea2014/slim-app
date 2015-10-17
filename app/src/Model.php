<?php

namespace SlimApp;

abstract class Model implements ModelInterface
{

    use \SlimApp\HasRequiredParamsTrait;

    /**
     * Constructor
     *
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null !== $data) {
            $this->populate($data);
        }
    }

    /**
     * Populates the model with row data
     *
     * @param array $row
     * @see SlimApp\ModelInterface::populate
     * @return $this
     */
    abstract public function populate(array $row);

    /**
     * Converts the model to an array
     *
     * @see SlimApp\ModelInterface::toArray
     * @return array
     */
    abstract public function toArray();
}

