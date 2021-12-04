<?php 

namespace App\Http\Controllers;

trait ArrayTrait {

/**
 * Array Pluck
 * *************************
 * Desc: The array_only function returns only the specified key / value pairs from the given array:
 *
 * Suppose: [['id' => 1, 'title' => 'A', 'active' => 1], 'id' => 2, 'title' => 'B', 'active' => 1s]
 * call: array_pluck( exists_arr, ['id', title']);
 * Output: [1=>A, 2=>B]
 *
 */
public function array_pluck( $array, $fields )
{
    if( (is_array($array) && count($array)) )
    {
        $_tmp = [];

        foreach ($array as $key => $value)
        {
            $value  = (array) $value;
            if( count($fields) == 2 )
            {
                $_tmp[$value[$fields[0]]] = $value[$fields[1]];
            }
        }

        return $_tmp;
    }

    return [];
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

/**
 * Object Index Map
 * *************************
 * Desc:
 *
 * Suppose: [['name' => 'Desk', 'price' => 100], ['name' => 'Table', 'price' => 200]]
 * call: object_index_map( exists_arr, 'name');
 * Output:
 *     [Desk] =>
 *     [
 *         'name' => 'Desk',
 *         'price' => 100
 *    ],
 *   [Table] =>
 *   [
 *       'name' => 'Table',
 *       'price' => 200
 *  ]
 *
 */

public function array_index_map($object, $index, $remove_index = false)
{
    $newArray = array();

    foreach ($object as $key => $obj)
    {
        if ( isset($obj[$index]) )
        {
            $_tmp = $obj[$index];

            if ( $remove_index )
                unset($obj[$index]);

            $newArray[$_tmp] = $obj;
        }
    }

    return $newArray;
}

}