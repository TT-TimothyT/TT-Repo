<?php
/*
 * Plugin Name: Netsuite API Integration
 * Description: Handles Netsuite API requests.
 */

class NetsuiteRESTAPI {

	public $headers;
	public $restNetsuiteConsumerkey;
	public $restNetsuiteConsumerSecret;
	public $restNetsuiteAccessToken;
	public $restNetsuiteAccessTokenSecret;
	public $restNetsuiteAccountID;
	public $restNetsuiteAPIMethod;
	public $restNetsuiteAPICallUrl;
	public $apiHost;
	public $url = '';
	public $isQueryEnable = false;
	public $message = '';

	public $apiEndPoint = '';
	public $apiPath = 'services/rest/query/';
	public $apiVersion = 'v1';
	public $signatureMethod = 'HMAC-SHA256';
	public $signatureHashKey = 'sha256';

	public static $REST_API_URL_VALID = true;
	public static $REST_API_EXCEPTION_ERROR = false;

	public function __construct() {
		global $TMWNI_OPTIONS;        
		$this->apiHost = $TMWNI_OPTIONS['ns_host'];
		$this->restNetsuiteAccountID = $TMWNI_OPTIONS['ns_account'];
		$this->restNetsuiteConsumerkey = $TMWNI_OPTIONS['ns_consumer_key'];
		$this->restNetsuiteConsumerSecret = $TMWNI_OPTIONS['ns_consumer_secret'];
		$this->restNetsuiteAccessToken = $TMWNI_OPTIONS['ns_token_id'];
		$this->restNetsuiteAccessTokenSecret = $TMWNI_OPTIONS['ns_token_secret'];
		$this->restNetsuiteAPIMethod = '';
	}

	public function getNSRequestUrl() {

		$this->url = trim($this->apiHost . $this->apiPath . $this->apiVersion . $this->apiEndPoint);
		if (filter_var($this->url, FILTER_VALIDATE_URL) === false) {
			self::$REST_API_URL_VALID = false;
		}
	}

	protected function parseRequestQuery($query) {
		if (is_null($query)) {
			return '';
		} elseif (is_array($query)) {
			return json_encode($query);
		} else {
			return '';
		}
	}

	public function addRequestHeaderData($body) {
		$urlComponents = parse_url($this->url);
		$limit=null;
		$offset=null;
		$queryParams = [];
		if (array_key_exists('query', $urlComponents)) {
		  parse_str($urlComponents['query'], $queryParams);
			if (count($queryParams) > 0) {
			  $limit = isset($queryParams['limit']) ? $queryParams['limit'] : null;
			  $offset = isset($queryParams['offset']) ? $queryParams['offset'] : null;
			}
		}
		$accountID = $this->apiHost;

		$accountID = rtrim($accountID, '/');

		if (strpos($accountID, '.com/') !== false) {
			$accountID = strstr($accountID, '.com', true) . '.com';
		}

		$realm = $this->restNetsuiteAccountID;
		$baseUrl = $accountID . '/services/rest/query/v1/suiteql';
		$ckey = $this->restNetsuiteConsumerkey; //Consumer Key
		$csecret = $this->restNetsuiteConsumerSecret; //Consumer Secret
		$tkey = $this->restNetsuiteAccessToken ; //Token ID
		$tsecret = $this->restNetsuiteAccessTokenSecret; //Token Secret
		$timestamp = time();
		$nonce = md5(mt_rand());
		$signatureMethod = 'HMAC-SHA256';
		if (null==$limit) {
			$baseString = $this->restNetsuiteAPIMethod . '&' . rawurlencode($baseUrl) . '&'
			. rawurlencode(
	//APPLY PARAMETERS IN ALPHABETICAL ORDER, URL ENCODED IN HERE
			   'oauth_consumer_key=' . rawurlencode($ckey)
			   . '&oauth_nonce=' . rawurlencode($nonce)
			   . '&oauth_signature_method=' . rawurlencode($signatureMethod)
			   . '&oauth_timestamp=' . rawurlencode($timestamp)
			   . '&oauth_token=' . rawurlencode($tkey)
			   . '&oauth_version=1.0'
			   . ( isset($offset) ? '&offset=' . rawurlencode($offset) : null )
		   );
		} else {
			$baseString = $this->restNetsuiteAPIMethod . '&' . rawurlencode($baseUrl) . '&'
			. rawurlencode(
	//APPLY PARAMETERS IN ALPHABETICAL ORDER, URL ENCODED IN HERE
			   ( isset($limit) ? 'limit=' . rawurlencode($limit) : null )
			   . '&oauth_consumer_key=' . rawurlencode($ckey)
			   . '&oauth_nonce=' . rawurlencode($nonce)
			   . '&oauth_signature_method=' . rawurlencode($signatureMethod)
			   . '&oauth_timestamp=' . rawurlencode($timestamp)
			   . '&oauth_token=' . rawurlencode($tkey)
			   . '&oauth_version=1.0'
			   . ( isset($offset) ? '&offset=' . rawurlencode($offset) : null )
		   );
		}
		$key = rawurlencode($csecret) . '&' . rawurlencode($tsecret);
		$signature = base64_encode(hash_hmac('sha256', $baseString, $key, true));
		$signature = rawurlencode($signature);

		$this->headers = array(
			"Authorization: OAuth realm=\"$realm\", oauth_consumer_key=\"$ckey\", oauth_token=\"$tkey\", oauth_nonce=\"$nonce\", oauth_timestamp=\"$timestamp\", oauth_signature_method=\"$signatureMethod\", oauth_version=\"1.0\", oauth_signature=\"$signature\"",
			'Cookie: NS_ROUTING_VERSION=LAGGING',
			'Prefer: transient',
			'Connection: keep-alive',
			'Accept: application/json',
			'Content-Type: application/json',
			'Content-length: ' . strlen($body),
		);
	}


	public function curlRestRequest($body) {

		$curl = curl_init();

		$opts = array(
			CURLOPT_URL => $this->url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $this->restNetsuiteAPIMethod,
			CURLOPT_HTTPHEADER => $this->headers,
			CURLOPT_POSTFIELDS => $body
		);

		curl_setopt_array($curl, $opts);
		$response = curl_exec($curl);


		if (curl_errno($curl)) {
			$error_message = curl_error($curl);
			$this->message = "\n cURL error: $error_message";
		}

		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($http_status >= 400) {
		// Retrieve error message from response body
			$error_message = json_decode($response, true);
			if (isset($error_message['o:errorDetails'][0]['detail'])) {
				$this->message = "\n HTTP error ($http_status): " . $error_message['o:errorDetails'][0]['detail'];
			} else {
			// Handle or log the error without a specific error message
				$this->message = "\n HTTP error: $http_status";
				self::$REST_API_EXCEPTION_ERROR = true;
			}
		}

		curl_close($curl);
		$data = json_decode($response, true);
		return $data;
	}

	private function _request($body) {

		//Prepare the request arguments
		$args = array(
			'body'        => $body,
			'method'     => 'POST',
			'headers'     => $this->headers,
			'timeout'     => 0,
			'redirection' => 10,
			'httpversion' => CURL_HTTP_VERSION_1_1,
			'sslverify'   => false, // Adjust this as needed
			// 'user-agent' => 'PostmanRuntime/7.37.3',
			'blocking'    => true, // added

		);

		// Make the request
		$response = wp_remote_post($this->url, $args);

		if (is_wp_error($response)) {
			$this->message = 'HTTP error: ' . $response->get_error_message();
			return null;
		}

		return $response;
	}

	public function nsRESTRequest($method,$endPoint,$queryEnable,$requestBody=null) {

		if (empty($method)) {
			return;
		}
		$this->restNetsuiteAPIMethod = strtoupper($method);

		$body = $this->parseRequestQuery($requestBody);

		$this->apiEndPoint = $endPoint;
		$this->isQueryEnable = $queryEnable;

		$this->getNSRequestUrl();

		$this->addRequestHeaderData($body);
		 $response = $this->curlRestRequest($body);

		 return $response;
		
		return null;
	}   
}
