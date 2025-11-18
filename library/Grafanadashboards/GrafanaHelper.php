<?php

namespace Icinga\Module\Grafanadashboards;

use DateTime;

class GrafanaHelper
{
    private $apiConnection;
    private $cacheDir;
    private $live;
    /**
     * Constructor
     *
     * @param string $apiUrl The base URL of the Grafana API (e.g., https://grafana.yourdomain.com/api)
     * @param string $apiKey Your Grafana API key
     * @param bool $disableSSL (Optional) Disable SSL verification. Default is false.
     */


    public function getApiConnection() :GrafanaAPI
    {
        return $this->apiConnection;
    }

    public function __construct($name, $apiUrl, $apiKey, $disableSSL,$cacheDir,$live=false)
    {
        $apiUrl = rtrim($apiUrl, '/');
        $this->cacheDir = $cacheDir.DIRECTORY_SEPARATOR.$this->makeFilesystemSafeString($name);
        $this->live = $live;
        if(! file_exists($this->cacheDir)){
            mkdir($this->cacheDir,0755,true);
        }
        $this->cleanup();
        $this->apiConnection = new GrafanaAPI($apiUrl,$apiKey,$disableSSL);
    }

    private function makeFilesystemSafeString($string) {
        // Replace spaces with underscores
        $string = str_replace(' ', '_', $string);

        // Convert to lowercase
        $string = strtolower($string);

        // Remove any character that is not a letter, number, underscore, or hyphen
        $string = preg_replace('/[^a-z0-9-_]/', '', $string);

        // Optionally, truncate the string to a certain length (e.g., 255 characters for some filesystems)
        $string = substr($string, 0, 255);

        return $string;
    }

    public function rebuildCache(){
        $this->live =true;
        if($this->provideMenuItems() === false || $this->getDashboardsByFolder('') === false || $this->getDashboardByUid(false) ===false){
            return false;
        }
        $this->cleanup();
        return true;
    }

    public function provideMenuItems()
    {
        $today = new DateTime();
        $todayFormatted = $today->format('Y-m-d');
        $cacheFile = $this->cacheDir.DIRECTORY_SEPARATOR.$todayFormatted."_".__FUNCTION__.".json";
        $return = [];
        if($this->live || !file_exists($cacheFile)){
            $data = $this->apiConnection->getAllFolders();
            if($data !== false){
                file_put_contents($cacheFile,json_encode($data));
            }

        }else{
            $data = json_decode(file_get_contents($cacheFile),true);
        }
        if($data === false){
            return false;
        }
        $return[]='General';

        foreach ( $data as $folder){
            $return[]=$folder['title'];
        }
        return $return;

    }

    public function getDashboardsByFolder($folder)
    {
        $today = new DateTime();
        $todayFormatted = $today->format('Y-m-d');
        $cacheFile = $this->cacheDir.DIRECTORY_SEPARATOR.$todayFormatted."_".__FUNCTION__.".json";
        if($this->live || !file_exists($cacheFile)){
            $data = $this->apiConnection->getAllDashboards();
            if($data !== false){
                file_put_contents($cacheFile,json_encode($data));
            }

        }else{
            $data = json_decode(file_get_contents($cacheFile),true);
        }
        if($data === false){
            return false;
        }
        if($folder === "General"){
            $filteredData = array_filter($data, function($item) use ($folder){
                return !isset($item['folderUid']);
            });
        }else{
            $filteredData = array_filter($data, function($item) use ($folder){
                return isset($item['folderTitle']) && $item['folderTitle'] === $folder;
            });
        }

        return $filteredData;

    }
    public function getDashboardByUid($uid)
    {
        $today = new DateTime();
        $todayFormatted = $today->format('Y-m-d');
        $cacheFile = $this->cacheDir.DIRECTORY_SEPARATOR.$todayFormatted."_".__FUNCTION__.".json";
        if($this->live || !file_exists($cacheFile)){
            $data = $this->apiConnection->getAllDashboards();
            if($data !== false){
                file_put_contents($cacheFile,json_encode($data));
            }
        }else{
            $data = json_decode(file_get_contents($cacheFile),true);
        }
        if($data === false){
            return false;
        }
        if($uid === false){
            return array_pop($data);
        }

        $filteredData = array_filter($data, function($item) use ($uid){
            return isset($item['uid']) && $item['uid'] === $uid;
        });

        if(count($filteredData) === 1){
            return array_pop($filteredData);
        }else{
            return false;
        }

    }
    private function cleanup()
    {
        $today = new DateTime();
        $todayFormatted = $today->format('Y-m-d');
        $cacheFile = $this->cacheDir.DIRECTORY_SEPARATOR.$todayFormatted."_".__FUNCTION__.".json";
        if(file_exists($cacheFile)){
            return;
        }
        file_put_contents($cacheFile,"OK");
        $files = scandir($this->cacheDir);


        foreach ($files as $file) {
            // Skip the current and parent directory entries
            if ($file === '.' || $file === '..') {
                continue;
            }

            // Check if the file starts with today's date
            if (strpos($file, $todayFormatted) !== 0) {
                // Construct the full file path
                $filePath = $this->cacheDir . DIRECTORY_SEPARATOR . $file;

                // Delete the file if it's not a directory
                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }
}