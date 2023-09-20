<?php
namespace TTNetSuite;

class OAuthRequest {

    protected $verb;
    protected $url;
    protected $params;

    protected $baseString;

    public function __construct($verb, $url) {
        $this->verb = $verb;
        $this->url = $url;

        $nonce = md5(microtime() . mt_rand());
        $timestamp = time();
        $version = "1.0";
        $signatureMethod = "HMAC-SHA256";

        $oauthParams = [
            'oauth_nonce' => $nonce,
            'oauth_timestamp' => $timestamp,
            'oauth_version' => $version,
            'oauth_token' => TT_NS_TOKEN_ID,
            'oauth_consumer_key' => TT_NS_CONSUMER_KEY,
            'oauth_signature_method' => $signatureMethod,
        ];

        $this->params = array_merge(
            $this->parseParams(parse_url($url, PHP_URL_QUERY)),
            $oauthParams
        );

        $this->setParam('oauth_signature', $this->buildSignature(TT_NS_CONSUMER_SECRET, TT_NS_TOKEN_SECRET));

        $this->setParam('realm', TT_NS_ACCOUNT_ID);
    }

    public function getAuthorizationHeader($realm) {
        $output = 'OAuth realm="' . $this->urlencode_rfc3986($realm) . '"';
        foreach ($this->params as $k => $v) {
            if (substr($k, 0, 5) != "oauth") continue;
            $output .= ',' . $this->urlencode_rfc3986($k) . '="' . $this->urlencode_rfc3986($v) . '"';
        }

        return $output;
    }

    protected function getSignatureBaseString() {
        $parts = [
            $this->getNormalizedVerb(),
            $this->getNormalizedUrl(),
            $this->getSignableParameters()
        ];
        $parts = $this->urlencode_rfc3986($parts);
        return implode('&', $parts);
    }

    protected function buildSignature($consumerSecret, $tokenSecret) {
        $baseString = $this->getSignatureBaseString();
        $this->baseString = $baseString;

        $keyParts = [$consumerSecret, $tokenSecret];
        $keyParts = $this->urlencode_rfc3986($keyParts);
        $key = implode('&', $keyParts);

        return base64_encode(hash_hmac('sha256', $baseString, $key, true));
    }

    public function setParam($name, $value, $allowDuplicates = true) {
        if ($allowDuplicates && isset($this->params[$name])) {
            if (is_scalar($this->params[$name])) {
               $this->params[$param] = [$this->params[$param]]; // convert to array
            }
            $this->params[$param][] = $value;
        } else {
            $this->params[$name] = $value;
        }
    }

    private function parseParams($input) {
        if (!isset($input) || !$input) return [];
        $pairs = explode('&', $input);
        $parsedParams = [];
        foreach ($pairs as $pair) {
            $split = explode('=', $pair, 2);
            $param = urldecode($split[0]);
            $value = isset($split[1]) ? urldecode($split[1]) : '';
            if (isset($parsedParams[$param])) {
                if (is_scalar($parsedParams[$param])) {
                    $parsedParams[$param] = [$parsedParams[$param]]; // convert to array
                }
                $parsedParams[$param][] = $value;
            } else {
                $parsedParams[$param] = $value;
            }
        }
        return $parsedParams;
    }

    protected function getNormalizedVerb() {
        return strtoupper($this->verb);
    }

    protected function getNormalizedUrl() {
        $parts = parse_url($this->url);
        $scheme = (isset($parts['scheme'])) ? $parts['scheme'] : 'http';
        $port = (isset($parts['port'])) ? $parts['port'] : (($scheme == 'https') ? '443' : '80');
        $host = (isset($parts['host'])) ? strtolower($parts['host']) : '';
        $path = (isset($parts['path'])) ? $parts['path'] : '';
        if (($scheme == 'https' && $port != '443')
            || ($scheme == 'http' && $port != '80')) {
            $host = "$host:$port";
        }
        return "$scheme://$host$path";
    }

    protected function getSignableParameters() {
        $params = $this->params;

        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }

        return $this->buildHttpQuery($params);
    }

    protected function buildHttpQuery($params) {
         if (!$params) return '';

        $keys = $this->urlencode_rfc3986(array_keys($params));
        $values = $this->urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);

        uksort($params, 'strcmp');

        $pairs = array();
        foreach ($params as $parameter => $value) {
            if (is_array($value)) {
                sort($value, SORT_STRING);
                foreach ($value as $duplicate_value) {
                    $pairs[] = $parameter . '=' . $duplicate_value;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }
        }

        return implode('&', $pairs);
    }

    private function urlencode_rfc3986($input) {
        if (is_array($input)) {
            return array_map([$this, 'urlencode_rfc3986'], $input);
        } else if (is_scalar($input)) {
            return str_replace(
                '+',
                ' ',
                str_replace('%7E', '~', rawurlencode($input))
            );
        } else {
            return '';
        }
    }

}
