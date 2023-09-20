<?php
namespace TTNetSuite;

// TODO: Proper cURL error handling
// TODO: POST requests with form-data json

class NSResponse {
    public $httpStatusCode = 200;
    public $responseJson = null;
}

class NetSuiteClient {

    const DEFAULT_DEPLOY_ID = 1;

    public function __construct() {
        $this->ensureWeHaveConfiguration();
    }

    public function post($scriptId, $data=null, $queryParams=[]) {
        return $this->requestWithRetry($scriptId, $data, $queryParams, true);
    }

    public function get($scriptId, $queryParams=[]) {
        return $this->requestWithRetry($scriptId, null, $queryParams, false);
    }

    private static function log($msg)
    {
        $now = date('Y-m-d H:i:s') . ": ";
        if (PHP_SAPI === 'cli') {
            echo $now . $msg . "\n";
        } else {
            echo $now . $msg . "<br />";
        }
    }

    private function requestWithRetry($scriptId, $data, $queryParams, $isPost) {
        $responseJson = null;
        $shouldRetry = false;
        $retryNumber = 1;

        do  {
            if ($isPost) {
                $nsResponse = $this->doPost($scriptId, $data, $queryParams);
            } else {
                $nsResponse = $this->doGet($scriptId, $queryParams);
            }
            $shouldRetry = false;
            
            $responseJson = $nsResponse->responseJson;
                
            if ($nsResponse->httpStatusCode == 429 || $nsResponse->httpStatusCode == 400) {
                if (!is_null($responseJson) && !is_null($responseJson->error) && $responseJson->error->code == 'SSS_REQUEST_LIMIT_EXCEEDED') {
                    self::log("Concurrency limit exceeded. (Attempt #$retryNumber). Retrying request for script " . $scriptId . "...");
                    sleep(2);
                    $shouldRetry = true;
                    $retryNumber++;
                }
            } else if ($retryNumber > 1) {
                self::log("Retried request and got response: " . json_encode($responseJson));
            }
        } while ($shouldRetry);

        return $responseJson;
    }

    private function doPost($scriptId, $data=null, $queryParams=[]) {
        $baseParams = $this->getBaseUrlParams($scriptId);
        $allParams = array_merge($baseParams, $queryParams);
        $url = TT_NS_RESTLET_URL . '?' . http_build_query($allParams);

        $auth = new OAuthRequest('POST', $url);

        $headers = [
            'Host: ' . TT_NS_HOST,
            'Content-Type: application/json',
            'Authorization:'.$auth->getAuthorizationHeader(TT_NS_ACCOUNT_ID)
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $nsResponse = new NSResponse();
        $response = curl_exec($ch);

        $nsResponse->httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $nsResponse->responseJson = json_decode($response);

        return $nsResponse;
    }

    private function doGet($scriptId, $queryParams=[]) {

        $baseParams = $this->getBaseUrlParams($scriptId);
        $allParams = array_merge($baseParams, $queryParams);
        $url = TT_NS_RESTLET_URL . '?' . http_build_query($allParams);

        $auth = new OAuthRequest('GET', $url);

        $headers = [
            'Host: ' . TT_NS_HOST,
            'Content-Type: application/json',
            'Authorization:'.$auth->getAuthorizationHeader(TT_NS_ACCOUNT_ID)
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $nsResponse = new NSResponse();
        $response = curl_exec($ch);

        $nsResponse->httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $nsResponse->responseJson = json_decode($response);

        return $nsResponse;
    }

    protected function ensureWeHaveConfiguration() {
        if (defined('TT_NS_RESTLET_URL')
            && defined('TT_NS_ACCOUNT_ID')
            && defined('TT_NS_CONSUMER_KEY')
            && defined('TT_NS_CONSUMER_SECRET')
            && defined('TT_NS_TOKEN_ID')
            && defined('TT_NS_TOKEN_SECRET')) {
            // everything is OK
        } else {
            throw new \Exception("Missing NetSuite Configuration !");
        }
    }

    /**
     * Returns script and deploy parameters based on the script id value. Value may be {scriptId}:{deployId}, or just
     * {scriptId} and a default deploy id will be used.
     *
     * @param string|int $scriptId
     * @return array
     */
    protected function getBaseUrlParams($scriptId) {
        $values = explode(':', $scriptId);
        return [
            'script' => $values[0] ?? null,
            'deploy' => $values[1] ?? static::DEFAULT_DEPLOY_ID
        ];
    }
}
