<?php
/**
 *  Upload class training3pw:yc78v4b81gaz
 */
 
require_once 'config.php';

class Upload
{
    protected $ftpServer = APPLICATION_FTP_SERVER;
    protected $ftpUserName = APPLICATION_FTP_USERNAME;
    protected $ftpUserPass = APPLICATION_FTP_USERPASS;
    
    protected $uploadDir = APPLICATION_UPLOAD_DIR;
    protected $backupDir = APPLICATION_BACKUP_DIR;
    
    public function ftpUpload($uploadFile)
    {
        $fileName = explode('/', $uploadFile);
        
        // set up basic connection
        $connId = ftp_connect($this->ftpServer);
        
        // login with username and password
        $loginResult = ftp_login($connId, $this->ftpUserName, $this->ftpUserPass);
        
        if ((!$connId) || (!$loginResult)) { 
            ;
        } else {
            if (ftp_put($connId, $fileName[count($fileName) - 1], $uploadFile, FTP_ASCII)) {
                //FTP success
                ftp_close($connId);
                return true;
            } else {
                ;
            }
        }
        //FTP error
        ftp_close($connId);
        return false;
    }
    
    public function getFile($dir)
    {
        $fileName = false;
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if(strpos($entry, '.csv') !== false || strpos($entry, '.CSV') !== false)
                    $fileName = $entry;
            }
            closedir($handle);
        }
        $this->updateUploadCsv($fileName);
        return $fileName;
    }
    
    /*
    * New procedure. Copy a new file and upload it. Left the original csv file in this folder.
    */
    public function renameFile($fileName, $dir)
    {
        $dateFormat = date('Y-m-d',time());
        $newName = 'SimplyBridal-UC_File-' . $dateFormat . '.csv';
        copy($dir . '/' . $fileName, $dir . '/' . $newName);
        echo $newName . ' ';
        //rename($dir . '/' . $fileName, $dir . '/' . $newName);
        
        return $dir . '/' . $newName;
    }
    
    public function backupFile($filePath, $dir)
    {
        $fileName = explode('/', $filePath);
        rename($filePath, $dir . '/' . $fileName[count($fileName) - 1]);
        return ;
    }
    
    protected function updateUploadCsv($fileName)
    {
        $file = $this->uploadDir . '/' . $fileName;
        $output = array();
        if (($handle = fopen($file, 'r')) !== FALSE) {
            $tableIndex = array();
            while (($rows = fgetcsv($handle, 20000, ",")) !== FALSE) {
                if($rows[0] != 'date'){
                    $rows[0] = date("n/j/Y", time());
                }
                array_push($output, $rows);
            }
            fclose($handle);
        }
        $fp = fopen($file, 'w');
        foreach($output as $data){
            fputcsv($fp, $data);
        }
        fclose($fp);
    }
}