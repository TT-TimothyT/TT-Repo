<?php
ini_set("default_socket_timeout", 30);
if(!class_exists('cfx_netsuite_common')){
class cfx_netsuite_common{
public static function arrayValuesAreEmpty ($array)
{
    if (!is_array($array))
    {
        return false;
    }

    foreach ($array as $key => $value)
    {
        if ( $value === false || ( !is_null($value) && $value != "" && !cfx_netsuite_common::arrayValuesAreEmpty($value)))
        {
            return false;
        }
    }

    return true;
}

public static function array_is_associative ($array)
{
    if ( is_array($array) && ! empty($array) )
    {
        for ( $iterator = count($array) - 1; $iterator; $iterator-- )
        {
            if ( ! array_key_exists($iterator, $array) ) { return true; }
        }
        return ! array_key_exists(0, $array);
    }
    return false;
}

public static function setFields ($object, array $fieldArray=null)
{
    // helper method that allows creating objects and setting their properties based on an associative array passed as argument. Mimics functionality from PHP toolkit
    $classname = get_class($object);
    // a static map that maps class parameters to their types. needed for knowing which objects to create
    $typesmap = $classname::$paramtypesmap;

    if (!isset ($typesmap)) {
        // if the class does not have paramtypesmap, consider it empty
        $typesmap = array();
    }

    if ($fieldArray == null)
    {
        // nothign to do
        return;
    }

    foreach ($fieldArray as $fldName => $fldValue)
    {
        if (((is_null($fldValue) || $fldValue == "") && $fldValue !== false) || cfx_netsuite_common::arrayValuesAreEmpty($fldValue))
        {
            //empty param
            continue;
        }

        if (!isset($typesmap[$fldName])) {
            // the value is not a valid class atrribute
            trigger_error("SetFields error: parameter \"" .$fldName . "\" is not a valid parameter for an object of class \"" . $classname . "\", it will be omitted", E_USER_WARNING);
            continue;
        }

        if ($fldValue === 'false')
        {
            // taken from the PHP toolkit, but is it really necessary?
            $object->$fldName = FALSE;
        }
        elseif (is_object($fldValue))
        {
            $object->$fldName = $fldValue;
        }
        elseif (is_array($fldValue) && cfx_netsuite_common::array_is_associative($fldValue))
        {
            // example: 'itemList'  => array('item' => array($item1, $item2), 'replaceAll'  => false)
            if (substr($typesmap[$fldName],-2) == "[]") {
                trigger_error("Trying to assign an object into an array parameter \"" .$fldName . "\" of class \"" . $classname . "\", it will be omitted", E_USER_WARNING);
                continue;
            }
            $obj = new $typesmap[$fldName]();
            cfx_netsuite_common::setFields($obj, $fldValue);
            $object->$fldName = $obj;
        }
        elseif (is_array($fldValue) && !cfx_netsuite_common::array_is_associative($fldValue))
        {
            // array type 
            if (substr($typesmap[$fldName],-2) != "[]") {
                // the type is not an array, skipping this value
                trigger_error("Trying to assign an array value into parameter \"" .$fldName . "\" of class \"" . $classname . "\", it will be omitted", E_USER_WARNING);
                continue;
            }

            // get the base type  - the string is of type <type>[]
            $basetype = substr($typesmap[$fldName],0,-2);

            // example: 'item' => array($item1, $item2)
            foreach ($fldValue as $item)
            {
                if (is_object($item))
                {
                    // example: $item1 = new nsComplexObject('SalesOrderItem');
                    $val[] = $item;
                }
                elseif ($typesmap[$fldName] == "string")
                {
                    // handle enums
                    $val[] = $item;
                }
                else
                {
                    // example: $item2 = array( 'item'      => new nsComplexObject('RecordRef', array('internalId' => '17')),
                    //                          'quantity'  => '3')
                    $obj = new $basetype();
                    cfx_netsuite_common::setFields($obj, $item);
                    $val[] = $obj;
                }
            }

            $object->$fldName = $val;
        }
        else
        {
            $object->$fldName = $fldValue;
        }
    }
}

public static function milliseconds ()
{
    $m = explode(' ',microtime());
    return (int)round($m[0]*10000,4);
}

public static function cleanUpNamespaces ($xml_root)
{
    $xml_root = str_replace('xsi:type', 'xsitype', $xml_root);
    $record_element = new SimpleXMLElement($xml_root);

    foreach ($record_element->getDocNamespaces() as $name => $ns)
    {
        if ( $name != "" )
        {
            $xml_root = str_replace($name . ':', '', $xml_root);
        }
    }

    $record_element = new SimpleXMLElement($xml_root);

    foreach($record_element->children() as $field)
    {
        $field_element = new SimpleXMLElement($field->asXML());

        foreach ($field_element->getDocNamespaces() as $name2 => $ns2)
        {
            if ($name2 != "")
            {
                $xml_root = str_replace($name2 . ':', '', $xml_root);
            }
        }
    }

    return $xml_root;
}
}
}
if(!interface_exists('cfx_netsuite_token_gen')){
/**
 * iTokenPassportGenerator
 */
interface cfx_netsuite_token_gen {
    /**
     * returns one time Token Passport
     */
    public function generateTokenPassport();
}
class cfx_netsuite_token implements cfx_netsuite_token_gen
{
    protected $account; // NS account

    protected $consumer_key;  // Consumer Key shown once on Integration detail page
    protected $consumer_secret; // Consumer Secret shown once on Integration detail page
        // following token has to be for role having those permissions: Log in using Access Tokens, Web Services
    protected $token; // Token Id shown once on Access Token detail page
    protected $token_secret; // Token Secret shown once on Access Token detail page
    

    /**
     * Shows how to generate TokenPassport for SuiteTalk, called by PHP Toolkit before each request
     */
    public function generateTokenPassport($account = null, $consumer_key = null, $consumer_secret = null, $token = null,  $token_secret = null) {


        if (isset($account)) {
            $this->account = $account;
        } elseif (vx_netsuite_vars::$account) {
            $this->account = vx_netsuite_vars::$account;
        }
        if (isset($consumer_key)) {
            $this->consumer_key = $consumer_key;
        } elseif (vx_netsuite_vars::$key) {
            $this->consumer_key = vx_netsuite_vars::$key;
        }
        if (isset($consumer_secret)) {
            $this->consumer_secret = $consumer_secret;
        } elseif (vx_netsuite_vars::$secret) {
            $this->consumer_secret = vx_netsuite_vars::$secret;
        }
        if (isset($token)) {
            $this->token = $token;
        } elseif (vx_netsuite_vars::$token) {
            $this->token = vx_netsuite_vars::$token;
        }
        if (isset($token_secret)) {
            $this->token_secret = $token_secret;
        } elseif (vx_netsuite_vars::$token_secret) {
            $this->token_secret = vx_netsuite_vars::$token_secret;
        }

        $nonce = $this->generateRandomString();// CAUTION: this sample code does not generate cryptographically secure values
        $timestamp = time();

        $baseString = urlencode($this->account) ."&". urlencode($this->consumer_key) ."&". urlencode($this->token) ."&". urlencode($nonce) ."&". urlencode($timestamp);
        $secret = urlencode($this->consumer_secret) .'&'. urlencode($this->token_secret);
        $method = 'sha256'; 
        $signature = base64_encode(hash_hmac($method, $baseString, $secret, true));
        
        $tokenPassport = new TokenPassport();
        $tokenPassport->account = $this->account;
        $tokenPassport->consumerKey = $this->consumer_key;
        $tokenPassport->token = $this->token;
        $tokenPassport->nonce = $nonce;                                    
        $tokenPassport->timestamp = $timestamp; 
        $tokenPassport->signature = new TokenPassportSignature();
        $tokenPassport->signature->_ = $signature;
        $tokenPassport->signature->algorithm = "HMAC-SHA256";  
        
        return $tokenPassport;
    }

    // CAUTION: it does not generate cryptographically secure values
    private function generateRandomString() {
        $length = 20;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)]; // CAUTION: The rand function does not generate cryptographically secure values
            // Since PHP 7 the cryptographically secure random_int can be used
        }
        return $randomString;
    }

}
}
if(!class_exists('NSPHPClient')){
class NSPHPClient {
    private $nsversion = '2022_1';    

    public $client = null;
    public $tokenPassport = null;    
    private $soapHeaders = array();
    protected $classmap = null;
    public $generated_from_endpoint = "";
    protected $tokenGenerator = null;

    protected function __construct($wsdl=null, $options=array()) {
        
        if (!isset($wsdl)) {
           if (empty(vx_netsuite_vars::$host)) {
                throw new Exception('Webservice host must be specified');
             }
         
             //$wsdl = NS_HOST . "/wsdl/v" . $this->nsversion . "_0/netsuite.wsdl";
             $wsdl = dirname(__FILE__). "/wsdl/netsuite.wsdl";
        }



        if ( $this->generated_from_endpoint != $this->nsversion ) {
            // check for the endpoint compatibility failed, but it might still be compatible. Issue only warning
            $endpoint_warning = 'The NetSuiteService classes were generated from the '.$this->generated_from_endpoint .' endpoint but you are running against ' . $this->nsversion;
            trigger_error($endpoint_warning, E_USER_WARNING);
        }

        $options['classmap'] = $this->classmap;
        $options['trace'] = 1;
        $options['connection_timeout'] = 5;
        $options['cache_wsdl'] = WSDL_CACHE_BOTH;
        $httpheaders = "PHP-SOAP/" . phpversion() . " + NetSuite PHP Toolkit " . $this->nsversion;

       if (!empty(vx_netsuite_vars::$host)) {
            $options['location'] = vx_netsuite_vars::$host . "services/NetSuitePort_" . $this->nsversion;
        }
        $options['keep_alive'] = false; // do not maintain http connection to the server.
        $options['features'] = SOAP_SINGLE_ELEMENT_ARRAYS;

        $context = array('http' =>
            array(
                'header' => 'Authorization: dnwdjewdnwe'
            )
        );
        //$options['stream_context'] = stream_context_create($context);

        $options['user_agent'] =  $httpheaders;

        $this->client = new SoapClient($wsdl, $options);
    }

    protected function setTokenPassport($tokenPassport) {
        $this->tokenPassport = $tokenPassport;
    }

    public function setPreferences ($warningAsError = false, $disableMandatoryCustomFieldValidation = false, $disableSystemNotesForCustomFields = false,  $ignoreReadOnlyFields = false, $runServerSuiteScriptAndTriggerWorkflows = null)    
    {
        $sp = new Preferences();
        $sp->warningAsError = $warningAsError;
        $sp->disableMandatoryCustomFieldValidation = $disableMandatoryCustomFieldValidation;
        $sp->disableSystemNotesForCustomFields = $disableSystemNotesForCustomFields;
        $sp->ignoreReadOnlyFields = $ignoreReadOnlyFields;
        $sp->runServerSuiteScriptAndTriggerWorkflows = $runServerSuiteScriptAndTriggerWorkflows;          

        $this->addHeader("preferences", $sp);
    }          
                
    public function clearPreferences() {
        $this->clearHeader("preferences");
    }        

    public function setSearchPreferences ($bodyFieldsOnly = true, $pageSize = 50, $returnSearchColumns = true)
    {
        $sp = new SearchPreferences();
        $sp->bodyFieldsOnly = $bodyFieldsOnly;
        $sp->pageSize = $pageSize;
        $sp->returnSearchColumns = $returnSearchColumns;

        $this->addHeader("searchPreferences", $sp);
    }

    public function clearSearchPreferences() {
        $this->clearHeader("searchPreferences");
    }

    public function addHeader($header_name, $header) {
        $this->soapHeaders[$header_name] = new SoapHeader("ns", $header_name, $header);
    }
    public function clearHeader($header_name) {
        unset($this->soapHeaders[$header_name]);
    }

    protected function makeSoapCall($operation, $parameter) {
            if (!isset($this->tokenGenerator)) {
                $generator = new cfx_netsuite_token();
                $this->tokenGenerator = $generator;
            }    
            $token = $this->tokenGenerator->generateTokenPassport();
            $this->setTokenPassport($token);

            $this->addHeader("tokenPassport", $this->tokenPassport);

        $response = $this->client->__soapCall($operation, array($parameter), NULL, $this->soapHeaders);
        
        if ( file_exists(dirname(__FILE__) . '/nslog') ) {
            // log the request and response into the nslog directory. Code taken from PHP toolkit
            // REQUEST
            $req = dirname(__FILE__) . '/nslog' . "/" . date("Ymd.His") . "." . cfx_netsuite_common::milliseconds() . "-" . $operation . "-request.xml";
            $Handle = fopen($req, 'w');
            $Data = $this->client->__getLastRequest();

            $Data = cleanUpNamespaces($Data);

            $xml = simplexml_load_string($Data, 'SimpleXMLElement', LIBXML_NOCDATA);

            $passwordFields = $xml->xpath("//password | //password2 | //currentPassword | //newPassword | //newPassword2 | //ccNumber | //ccSecurityCode | //socialSecurityNumber");

            foreach ($passwordFields as &$pwdField) {
                (string)$pwdField[0] = "[Content Removed for Security Reasons]";
            }

            $stringCustomFields = $xml->xpath("//customField[@xsitype='StringCustomFieldRef']");

            foreach ($stringCustomFields as $field) {
                (string)$field->value = "[Content Removed for Security Reasons]";
            }

            $xml_string = str_replace('xsitype', 'xsi:type', $xml->asXML());

            fwrite($Handle, $xml_string);
            fclose($Handle);

            // RESPONSE
            $resp = dirname(__FILE__) . '/nslog' . "/" . date("Ymd.His") . "." . cfx_netsuite_common::milliseconds() . "-" . $operation . "-response.xml";
            $Handle = fopen($resp, 'w');
            $Data = $this->client->__getLastResponse();
            fwrite($Handle, $Data);
            fclose($Handle);

        }

        return $response;

    }
	
    public function setHost($hostName) {
        return $this->client->__setLocation($hostName . "/services/NetSuitePort_" . $this->nsversion);
    }
}
}


?>