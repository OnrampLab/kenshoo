<?php

class DownloadHelper
{

    /**
     *
     */
    public static function getByUrl($url)
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

    /**
     *
     */
    public static function contentToFile($content, $pathFile)
    {
        file_put_contents($pathFile, $content);
    }

    /**
     *
     */
    public static function contentToCsv($content, $pathFile)
    {
        $output = array();
        $lines = explode(PHP_EOL, $content);
        foreach ($lines as $line) {
            $output[] = str_getcsv($line);
        }

        $fp = fopen($pathFile, 'w');
        foreach($output as $data){
            fputcsv($fp, $data);
        }
        fclose($fp);
    }

}