<?php
namespace App\Response;

class ValidationReponseDetail
{

    private $type;

    private $field_name;

    private $detail;

    /**
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     *
     * @return string
     */
    public function getField_name()
    {
        return $this->field_name;
    }

    /**
     *
     * @param string $field_name
     */
    public function setField_name($field_name)
    {
        $this->field_name = $field_name;
    }

    /**
     *
     * @return array
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     *
     * @param array $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }
}