<?php
namespace App\Response;

class BaseResponse
{

    protected $code;

    protected $message;

    /**
     *
     * @return number
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     *
     * @param number $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     *
     * @return string:
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Group of Array Index Map
     * *************************
     * Desc:
     *
     * Suppose: [['name' => 'Desk', 'price' => 100], ['name' => 'Table', 'price' => 200], ['name' => 'Desk', 'price' => 300]]
     * call: object_index_map( exists_arr, 'name');
     * Output:
     *     [Desk] =>
     *     [0]
     *         [
     *             'name' => 'Desk',
     *             'price' => 100
     *        ],
     *   [1] =>
     *       [
     *         'name' => 'Desk',
     *         'price' => 300
     *       ]
     *   ]
     *   [Table] =>
     *   [
     *       'name' => 'Table',
     *       'price' => 200
     *  ]
     *
     */
    public function group_array_index_map($array, $index, $remove_index = false)
    {
        $newArray = array();

        foreach ($array as $key => $arr)
        {
            $arr = (array) $arr;

            if ( isset($arr[$index]) )
            {
                $_tmp = $arr[$index];

                if ( $remove_index )
                    unset($arr[$index]);

                $newArray[ $_tmp ][] = $arr;
            }
        }

        return $newArray;
    }
}
