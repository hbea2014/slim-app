<?php 

namespace SlimApp;

interface ModelInterface
{

    /**
     * Populates the model with row data
     *
     * @param array $row
     * @return $this
     */
    public function populate(array $row);

    /**
     * Converts the model to an array
     *
     * @return array
     */
    public function toArray();
}

