<?php
namespace App\Response;

class SuccessResponse extends BaseResponse
{

    private $data;

    /**
     *
     * @return object
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     *
     * @param object $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
