<?php

class Log
{
    /**
     *  error log
     */
    public static function getPath()
    {
        return APPLICATION_DIR .'/tmp';
    }

    /**
     *  error log
     */
    public static function record( $content )
    {
        $content = date("Y-m-d H:i:s") . ' - '. $content;
        self::write( 'log.log', $content );
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
    
        $filename = self::getPath() .'/'. $file;
        file_put_contents( $filename, $content."\n", FILE_APPEND );
    }

}
