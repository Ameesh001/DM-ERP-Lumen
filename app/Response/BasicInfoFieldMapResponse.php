<?php
namespace App\Response;

class BasicInfoFieldMapResponse
{

    private $basicInfoFieldMap;

    /**
     *
     * @return array
     */
    public function getBasicInfoFieldMap()
    {
        return $this->basicInfoFieldMap;
    }

    /**
     *
     * @param array $BasicInfoFieldMap
     */
    public function setBasicInfoFieldMap($basicInfoFieldMap)
    {
        $this->basicInfoFieldMap = $basicInfoFieldMap;
    }
}
