<?php

/**
 *  該程式應該修改成非靜態 class
 */
class CsvManager
{
    /**
     *
     */
    private static $header = array();

    /**
     *
     */
    private static $filterSetting = array();

    /**
     *  init
     */
    public static function init()
    {
        self::$header        = array();
        self::$filterSetting = array();
    }

    /**
     *  set csv header
     */
    public static function setHeader(Array $row)
    {
        self::$header = $row;
    }

    /**
     *  save to file
     */
    public static function save($filename, Array $contents, $isWriteHeader=false )
    {
        $fp = fopen($filename, 'w');
        if ( $isWriteHeader ) {
            fputcsv($fp, self::$header);
        }
        foreach ($contents as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);
    }

    /**
     *
     */
    public static function map($row)
    {
        $item = array_combine(self::$header, $row);
        
        foreach ( $item as $key => $value ) {
            foreach ( self::$filterSetting as $filterKey => $filterType ) {
                if ( $key===$filterKey ) {
                    $item[$key] = self::$filterType($value);
                }
            }
        }
        return $item;
    }

    /**
     *
     */
    public static function setFilter( Array $filterSetting )
    {
        self::$filterSetting = $filterSetting;
    }

    /**
     *  magic call getting and setting
     */
    static function __callStatic($name, $args)
    {
        $function = '_filter_'.$name;
        return self::$function($args[0]);
    }

    /* --------------------------------------------------------------------------------
        custom filter
    -------------------------------------------------------------------------------- */

    private static function _filter_float( $value )
    {
        return (float) $value;
    }

    private static function _filter_int( $value )
    {
        return (int) $value;
    }

}

/*
example:

$csv = 'your_file.csv';
if (($handle = fopen($csvFile, 'r')) !== false) {

    CsvManager::setHeader(fgetcsv($handle));
    CsvManager::setFilter(array(
        'money' => 'float',
        'age'   => 'int'
    ));

    while (($line = fgetcsv($handle)) !== false) {
        $item = CsvManager::map($line);
        print_r($item);
    }

    fclose($handle);
}
*/
