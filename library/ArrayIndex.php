<?php

/*
    代入一個 index => key, values 的二維陣列
    來搜尋、比對裡面的內容

    example
        Array(
            [0] => Array(
                [name] => kevin
                [age]  => 15
            [1] => Array(
                [name] => vivian
                [age] => 18
            [2] => Array(
                [name] => old man
                [age] => 85
            [3] => Array(
                [name] => Chris
                [age] => 45
        )

    ArrayIndex::set($arr);
    ArrayIndex::has('name','vivian');       // true
    ArrayIndex::hasString('name','vian');   // true (是否有任何糢糊匹配)
    ArrayIndex::getIndex('name','kevin');   // 0
    ArrayIndex::get(3, 'name');             // Chris

*/
class ArrayIndex
{

    private static $arr = array();

    /**
     *
     */
    public static function set(Array $arr)
    {
        self::$arr = $arr;
    }

    /**
     *  比對內容是否有相同的值
     *  @return boolean
    public static function has($key, $value)
    {
        foreach ( self::$arr as $index => $item ) {
            if ( $item[$key] === $value ) {
                return true;
            }
        }
        return false;
    }
    */

    /**
     *  get index by 比對內容是否有相同的值
     *  @return int (index value) or null
     */
    public static function getIndex($key, $value)
    {
        foreach ( self::$arr as $index => $item ) {
            if ( $item[$key] === $value ) {
                return $index;
            }
        }
        return null;
    }

    /**
     *  get index by 糢糊搜尋
     *  @return int (index value) or null
     */
    public static function getIndexByHasString($key, $value)
    {
        foreach ( self::$arr as $index => $item ) {
            if ( false !== stristr($value, $item[$key]) ) {
                return $index;
            }
        }
        return null;
    }

    /**
     *  @param $index - array index number
     *  @param $field - field keyword
     *  @param $defaultValue
     */
    public static function get($index, $field, $defaultValue=null)
    {
        if ( !isset(self::$arr[$index]) ) {
            return $defaultValue;
        }
        if ( !isset(self::$arr[$index][$field]) ) {
            return $defaultValue;
        }
        return self::$arr[$index][$field];
    }

}