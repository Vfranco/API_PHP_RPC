<?php

namespace Core;

class Upload
{
    private static $server = FTP_SERVER;
    private static $ftpuser = FTP_USER;
    private static $ftppass = FTP_PASS;

    /**
     * Upload the file by Folder
     */
    static function upload($file, $path, $temp)
    {
        try {
            return self::checkStatus($file, $path, $temp);
        } catch(\Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * 
     */
    static function uploadToFtp($file, $remoteFile)
    {
        try{
            return self::startUploading($file, $remoteFile);
        } catch(\Exception $e){
            print $e->getMessage();
        }
    }

    /**
     * 
     */
    static function startUploading($file, $remoteFile)
    {
        if(!isset($file) || empty($file) || !isset($remoteFile))
            throw new Exception("Error : no se definio el archivo");
        
        return self::uploading($file, $remoteFile);
    }

    /**
     * 
     */
    static function uploading($file, $remoteFile)
    {
        $status = false;

        $ftp_conn = ftp_connect(self::$server) or die ('No se logro conectar!');
        $ftp_login = ftp_login($ftp_conn, self::$ftpuser, self::$ftppass);
        
        if($ftp_login)
        {
            if(ftp_put($ftp_conn, $remoteFile, $file, FTP_BINARY))
                $status = true;            
        }

        ftp_close($ftp_conn);

        return $status;
    }

    /**
     * check the arguments
     */
    static function checkStatus($file, $path, $temp)
    {
        if(!isset($file) || !isset($path) || !isset($temp))
            throw new \Exception('Error: defina el argumento File');
        else if(!isset($path) || empty($path))
            throw new \Exception('Error: defina el argumento Path a guardar');
        else
            return self::processUpload($file, $path, $temp);
    }

    /**
     * start process to upload
     */
    static function processUpload($file, $path, $temp)
    {
        $status = true;

        if(self::folderExist($path))
        {
            if(self::fileExist($file, $path))
            {            
                if (!self::moveFileToPath($file, $path, $temp))
                    $status = false;
                else
                    $status = true;
            }
            else
            {
                if (!self::moveFileToPath($file, $path, $temp))
                    $status = false;
                else
                    $status = true;
            }
        }
        else
        {
            if(self::createPath($path))
                self::moveFileToPath($file, $path, $temp);            
        }

        return $status;
    }

    /**
     * check if folder exist
     */
    private static function folderExist($path)
    {
        $basePath = dirname(APP_PATH) . '/' . $path;

        if(realpath($basePath) != false AND is_dir($basePath))
            return true;

        return false;
    }

    /**
     * Check if the file exist
     */
    private static function fileExist($file, $path)
    {
        $path_to_file = $path . $file;

        if(file_exists($path_to_file))
            return unlink($path_to_file);

        return false;
    }

    /**
     * create the path to save the file
     */
    private static function createPath($path)
    {
        if(!self::folderExist($path))
            return mkdir($path);

        return false;
    }

    /**
     *  move the file to destination folder
     */
    private static function moveFileToPath($file, $path, $temp)
    {
        $targetFile = $path . $file;

        if(move_uploaded_file($temp, $targetFile))
            return true;

        return false;
    }
}