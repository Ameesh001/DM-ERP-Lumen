<?php
namespace App\Response;

class BasicInfoFieldResponse
{

    private $basicInfoField;

    /**
     *
     * @return array
     */
    public function getBasicInfoField()
    {
        return $this->basicInfoField;
    }

    /**
     *
     * @param array $BasicInfoField
     */
    public function setBasicInfoField($basicInfoField)
    {
        $this->basicInfoField = $basicInfoField;
    }
}
