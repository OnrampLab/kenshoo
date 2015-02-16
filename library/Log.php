<?php

class Log
{
    /**
     *
     */
    private static $logPath = 'tmp';

    /**
     *  error log
     */
    public static function error( $content )
    {
        $content = date("Y-m-d H:i:s") . ' - '. $content;
        self::write( 'error.log', $content );
    }

    /* --------------------------------------------------------------------------------
        private
    -------------------------------------------------------------------------------- */

    /**
     *  write file
     */
    public static function write( $file, $content )
    {
        if (!preg_match('/^[a-z0-9_\-\.]+$/i', $file)) {
            return;
        }
    
        $filename = self::$logPath .'/'. $file;
        file_put_contents( $filename, $content."\n", FILE_APPEND );
    }

}
