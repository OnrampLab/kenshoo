<?php

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;

class GoogleWorksheetManager
{
    /**
     *
     */
    public function __construct( $worksheet )
    {
        $this->worksheet = $worksheet;
    }

    /**
     *
     */
    public function getEntries()
    {
        return $this->worksheet->getListFeed()->getEntries();
    }


    /**
     *  return google worksheet gid
     */
    public function getGid()
    {
        return $this->worksheet->getGid();
    }


    /**
     *  @return array
     */
    public function getHeader()
    {
        $row = $this->getRow(0);
        if ( !$row ) {
            return array();
        }

        $result = array();
        foreach ( $row as $title => $value ) {
            $result[] = $title;
        }

        return $result;
    }

    /**
     *  @return array or false
     */
    public function getRow($index)
    {
        $entries = $this->getEntries();
        if ( isset($entries[$index]) && is_object($entries[$index]) ) {
            return $entries[$index]->getValues();
        }
        return false;
    }

    /**
     *  @return boolean
     */
    public function setRow($index, $row)
    {
        $oldRow = $this->getRow($index);
        if ( !$oldRow ) {
            return false;
        }

        $entries = $this->getEntries();
        $entry = $entries[$index];
        return $entry->update($row);
    }

    /**
     *  @return boolean
     */
    public function addRow($row)
    {
        $listFeed = $this->worksheet->getListFeed();
        return $listFeed->insert($row);
    }

    /**
     *  不包含標題的資料數量
     *
     *  @return int
     */
    public function getCount()
    {
        return (int) count( $this->getEntries() );
    }

    /**
     *  build empty row
     */
    public function buildRow()
    {
        // 因為不確定過濾方式 是否完全符合 google sheet library
        // 所以這裡 $header 的程式並未放置在 getHeader() 裡面
        $headers = [];
        $entries = $this->worksheet->getCellFeed()->getEntries();
        foreach ($entries as $entry) {
            $key = $this->keyConvert( $entry->getContent() );
            $headers[$key] = null;
        }
        return $headers;
    }

    /**
     *  create sheet title fields
     *  @param Array $headers
     */
    public function createHeaders($headers)
    {
        $cellFeed = $this->worksheet->getCellFeed();
        $index = 0;
        foreach ($headers as $fieldName) {
            $index++;
            $cellFeed->editCell(1, $index, $fieldName);
        }
    }

    /**
     *  將一個字串轉換成 可以作為 key 使用的字串
     *
     *  為了符合 google sheet library 的方式
     *  這裡有過濾 '_' 符號
     */
    public function keyConvert($key)
    {
        return strtolower(
            preg_replace('/[^a-zA-Z0-9\-]+/', '', $key)
        );
    }

}

/*
    Example:

        name,age,like
        kevin,15,eat
        vivian,18,eat/sport
        old man,85,mountain
        Chris,45,game

        $sheet = new GoogleWorksheetManager( $googleWorksheet );
        $count = $sheet->getCount();
        for ( $i=0; $i<$count; $i++ ) {
            $row = $sheet->getRow($i);
            $row['age']++;
            $sheet->setRow($i, $row);
        }

*/
