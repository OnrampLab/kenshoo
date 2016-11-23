<?php
/**
 *  管理 csv
 *  只讀取資料, 不會反寫回 csv file
 *  使用 yield , 所以 PHP 版本必須 >= 5.5 (??????)
 *  改良自 CsvManager
 */
class CsvReadManager
{

    /**
     *
     */
    private $handle = null;

    /**
     *
     */
    private $header = array();

    /**
     *
     */
    private $filterSetting = array();

    /**
     *
     */
    public function __construct( $csvFile, $autoSetHeader=true )
    {
        if ( !file_exists($csvFile) ) {
            return;
        }

        if (($this->handle = fopen($csvFile, 'r')) === false) {
            return;
        }

        if ($autoSetHeader) {
            // 有處理全無內容的問題
            $firstRow = fgetcsv($this->handle);
            if ( !$firstRow ) {
                return false;
            }
            $this->setHeader($firstRow);
        }
    }

    /**
     *  set csv header
     *  有處理重覆名稱問題
     */
    public function setHeader(Array $row)
    {
        $index = 0;
        $allowRow = [];
        foreach ( $row as $name ) {
            $index++;
            $name = $this->normalNameCase($name);
            if ( in_array($name, $allowRow) ) {
                $name .= '_repeat_' . md5($index);
            }
            array_push( $allowRow, $name );
        }
        $this->header = $allowRow;
    }

    /**
     *
     */
    public function generator()
    {
        if ( !$this->handle ) {
            return null;
        }

        while (true) {
            $row = fgetcsv($this->handle);
            if ( !$row || !is_array($row) ) {
                break;
            }

            $item = $this->map($row);
            if (!$item) {
                // echo "<p>一筆有問題的資料, 可以略過, 這裡選擇輸出 false.</p>\n";
                yield false;
                continue;
            }
            yield $item;
        }
        fclose($this->handle);
    }

    /**
     *
     */
    public function setFilter( Array $filterSetting )
    {
        $this->filterSetting = $filterSetting;
    }

    /**
     *  magic call getting and setting
     */
    public function __call($name, $args)
    {
        // 是否需要檢查該 method 是否存在?
        $function = '_filter_'.$name;
        return $this->$function($args[0]);
    }

    /**
     *  將 header name 轉為乾淨的字串
     */
    private function normalNameCase($value)
    {
        $value = str_replace(array('-',' '), '_', $value);
        $value = preg_replace("/[^a-zA-Z0-9_]+/", "", $value );
        $value = strtolower(trim($value));
        return $value;
    }

    /**
     *  將 value array 轉換成 key-value array
     */
    private function map($row)
    {
        if ( !$row || !is_array($row) || !$row[0] ) {
            return false;
        }
        if ( count($this->header) !== count($row) ) {
            return false;
        }

        $item = array_combine($this->header, $row);

        foreach ( $item as $key => $value ) {
            foreach ( $this->filterSetting as $filterKey => $filterType ) {
                if ( $key===$filterKey ) {
                    $item[$key] = $this->$filterType($value);
                }
            }
        }
        return $item;
    }

    /* --------------------------------------------------------------------------------
        custom filter
    -------------------------------------------------------------------------------- */

    private function _filter_float( $value )
    {
        return (float) $value;
    }

    private function _filter_int( $value )
    {
        return (int) $value;
    }

}

/*

Q 欄位數量如果不符合會傳回什麼?
A 傳回 boolean -> false

example:

    $csvFile = 'file.csv';
    $csv = new CsvReadManager( $csvFile, $autoSetHeader=true );
    $csv->setFilter(array(
        'money' => 'float',
        'age'   => 'int'
    ));
    foreach ( $csv->generator() as $item ) {
        if (!$item) {
            echo "<p>一筆有問題的資料</p>\n";
            continue;
        }
        print_r($item);
    }

*/

