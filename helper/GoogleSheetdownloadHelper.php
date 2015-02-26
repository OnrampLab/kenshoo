<?php

class GoogleSheetdownloadHelper
{
    /**
     *
     */
    public static function download( $pathFile, $key, $gid )
    {
        $url = 'docs.google.com/feeds/download/spreadsheets/Export?key=' . $key . '&exportFormat=csv&gid=' . $gid;
        $data = self::downloadGoogleCurl($url);

        //
        $output = array();
        $lines = explode(PHP_EOL, $data);
        $output = array();
        foreach ($lines as $line) {
            $output[] = str_getcsv($line);
        }

        $fp = fopen($pathFile, 'w');
        foreach($output as $content){
            fputcsv($fp, $content);
        }
        fclose($fp);
        
        return true;
    }

    /**
     *
     */
    private static function downloadGoogleCurl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSLVERSION,3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,3);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:19.0) Gecko/20100101 Firefox/19.0 FirePHP/0.4");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}