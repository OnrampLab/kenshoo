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
        $this->entries = $worksheet->getListFeed()->getEntries();
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
        if ( isset($this->entries[$index]) && is_object($this->entries[$index]) ) {
            return $this->entries[$index]->getValues();
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

        $entry = $this->entries[$index];
        return $entry->update($row);
    }

    /**
     *  不包含標題的資料數量
     *
     *  @return int
     */
    public function getCount()
    {
        return (int) count($this->entries);
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
