<?php
/**
 *  Dowmload class
 */
 
require_once 'config.php';

class googleDocDownloader
{
    protected $uploadDir = APPLICATION_UPLOAD_DIR;
    
    protected $googleKenshooKey = APPLICATION_GOOGLE_KENSHOO_KEY;
    protected $googleKenshooGid = APPLICATION_GOOGLE_KENSHOO_GID;
    
    public function dowmloadKenshooCsv()
    {
        $url = 'docs.google.com/feeds/download/spreadsheets/Export?key=' . $this->googleKenshooKey . '&exportFormat=csv&gid=' . $this->googleKenshooGid;
        $output = $this->downloadGoogleCurl($url);
        $this->parserCsv($output);
        return;
    }
    
    protected function parserCsv($data)
    {
        $output = array();
        $lines = explode(PHP_EOL, $data);
        $output = array();
        foreach ($lines as $line) {
            $output[] = str_getcsv($line);
        }
        
        $file = $this->uploadDir . '/upload.csv';
        $fp = fopen($file, 'w');
        foreach($output as $data){
            fputcsv($fp, $data);
        }
        fclose($fp);
    }
    
    protected function downloadGoogleCurl($url)
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