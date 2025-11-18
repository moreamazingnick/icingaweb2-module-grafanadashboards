<?php

namespace Icinga\Module\Grafanadashboards;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Icinga\Application\Logger;

class GrafanaAPI
{
    private $client;
    private $apiUrl;
    private $apiKey;
    private $path ="";

    /**
     * Constructor
     *
     * @param string $apiUrl The base URL of the Grafana API (e.g., https://grafana.yourdomain.com)
     * @param string $apiKey Your Grafana API key
     * @param bool $disableSSL (Optional) Disable SSL verification. Default is false.
     */
    public function __construct($apiUrl, $apiKey, $disableSSL = false)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey;
        $parsedUrl = parse_url($apiUrl);
        if(isset($parsedUrl['path'])){
            $this->path = $parsedUrl['path'];
        }


        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'connect_timeout' => 2,
            'verify' => !$disableSSL, // Disable SSL verification if $disableSSL is true
        ]);
    }

    /* ---------------------------------------------
     * DASHBOARD METHODS
     * --------------------------------------------- */

    /**
     * Get a list of all dashboards.
     *
     * @return array|false The list of dashboards or false on failure
     */
    public function getAllDashboards()
    {
        return $this->sendRequest('GET', '/api/search?type=dash-db');
    }

    /**
     * Get a dashboard by UID.
     *
     * @param string $uid The dashboard UID
     * @return array|false The dashboard data or false on failure
     */
    public function getDashboardByUid($uid)
    {
        return $this->sendRequest('GET', '/api/dashboards/uid/' . $uid);
    }

    /**
     * Get a dashboard by UID.
     *
     * @param string $uid The dashboard UID
     * @return array|false The dashboard data or false on failure
     */
    public function getDashboardsByFolderUid($uid)
    {
        return $this->sendRequest('GET', '/api/search?folderUIDs' . $uid);
    }

    /**
     * Get a dashboard.
     *
     * @param array $dashboard The dashboard data
     * @return array|false The created/updated dashboard response or false on failure
     */
    public function getDashboard($uid)
    {
        return $this->sendRequest('GET', '/api/dashboards/uid/'.$uid);
    }


    /* ---------------------------------------------
     * FOLDER METHODS
     * --------------------------------------------- */

    /**
     * Get all folders.
     *
     * @return array|false The list of folders or false on failure
     */
    public function getAllFolders()
    {
        return $this->sendRequest('GET', '/api/folders');
    }

    /**
     * Get a folder by UID.
     *
     * @param string $uid The folder UID
     * @return array|false The folder data or false on failure
     */
    public function getFolderByUid($uid)
    {
        return $this->sendRequest('GET', '/api/folders/' . $uid);
    }


    /* ---------------------------------------------
     * HELPER METHODS
     * --------------------------------------------- */

    /**
     * Send an HTTP request to the Grafana API.
     *
     * @param string $method The HTTP method (GET, POST, DELETE, etc.)
     * @param string $uri The API endpoint (with leading `/api`)
     * @param array|null $body The request body for POST/PUT requests (optional)
     * @return array|false The response data or false on failure
     */
    private function sendRequest($method, $uri, $body = null)
    {
        try {
            $options = [];
            if ($body !== null) {
                $options['json'] = $body;
            }
            $response = $this->client->request($method, $this->path."/".ltrim($uri,"/"), $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            // Log the error or handle it as needed
            Logger::error( 'Request failed: ' . $e->getMessage());
            return false;
        }
    }
}
