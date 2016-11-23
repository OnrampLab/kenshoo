<?php
/**
 *  Upload class training3pw:yc78v4b81gaz
 */

class Upload
{
    protected $ftpServer = APPLICATION_FTP_SERVER;
    protected $ftpUserName = APPLICATION_FTP_USERNAME;
    protected $ftpUserPass = APPLICATION_FTP_USERPASS;
    
    protected $uploadDir = APPLICATION_UPLOAD_DIR;
    protected $backupDir = APPLICATION_BACKUP_DIR;
    
    public function ftpUpload($uploadFile)
    {
        $fileName = basename($uploadFile);

        // set up basic connection
        $connId = ftp_connect($this->ftpServer);
        
        // login with username and password
        $loginResult = ftp_login($connId, $this->ftpUserName, $this->ftpUserPass);
        
        if ((!$connId) || (!$loginResult)) { 
            ;
        } else {
            if (ftp_put($connId, $fileName, $uploadFile, FTP_ASCII)) {
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
        $this->updateCsvByFacebook($fileName);
        return $fileName;
    }
    
    /*
    * New procedure. Copy a new file and upload it. Left the original csv file in this folder.
    */
    public function renameFile($fileName, $dir)
    {
        $dateFormat = date('Y-m-d',time());
        $dateFormat = date('Y-m-d', strtotime($dateFormat . ' - 1 day'));
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
                    $rows[0] = date('n/j/Y', strtotime($rows[0] . ' - 1 day'));
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

    protected function updateCsvByFacebook($fileName)
    {
        $csvFile = $this->uploadDir . '/' . $fileName;
        $fb = new Fb(APPLICATION_FACEBOOK_ID, APPLICATION_FACEBOOK_SECRET);
        $result = $fb->get();

        // create new cvs contents
        ArrayIndex::set($result['cost']);
        $contents = array();
        if (($handle = fopen($csvFile, 'r')) !== false) {

            CsvManager::init();
            CsvManager::setHeader(fgetcsv($handle));
            CsvManager::setFilter(array(
                'cost' => 'float',
                'FB-Objective' => 'int'
            ));

            while (($line = fgetcsv($handle)) !== false) {
                $item = CsvManager::map($line);
                $index = ArrayIndex::getIndex('group_name', $item['campaign']);
                if ( null !== $index ) {
                    $item['cost']        = ArrayIndex::get($index, 'spend');
                    $item['impressions'] = ArrayIndex::get($index, 'reach');
                    $item['clicks']      = ArrayIndex::get($index, 'clicks');
                }
                $contents[] = $item;
            }

            fclose($handle);
        }

        CsvManager::save($csvFile, $contents, true);


        /*
        TODO: 找不到相同的 account_id 值, 所以暫時註解起來, 確定無法使用時, 可以刪除此區塊
        
        // create new cvs contents
        ArrayIndex::set($result['objective']);
        $contents = array();
        if (($handle = fopen($csvFile, 'r')) !== false) {

            CsvManager::setHeader(fgetcsv($handle));

            while (($line = fgetcsv($handle)) !== false) {
                $item = CsvManager::map($line);
                $index = ArrayIndex::getIndex('account_id', $item['FB-ID']);
                if ( null !== $index ) {
                    $item['FB-Objective']  = ArrayIndex::get($index, 'objective');
                }
                $contents[] = $item;
            }

            fclose($handle);
        }

        CsvManager::save($csvFile, $contents, true);
         */
    }


}