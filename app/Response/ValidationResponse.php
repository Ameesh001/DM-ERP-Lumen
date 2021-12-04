<?php
namespace App\Response;

class ValidationResponse extends BaseResponse
{

    private $errors;

    public function getTest()
    {
        return $this->errors;
    }
    
    /**
     *
     * @return 'App\Response\ValidationDetail' object arrary
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     *
     * @param
     *            arrary of App\Response\ValidationDetail object $detail
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }
}
