<?php

class ResourceServerException extends Exception {

    private $_description;

    public function __construct($message, $description, $code = 0, Exception $previous = null) {
        $this->_description = $description;
        parent::__construct($message, $code, $previous);
    }

    public function getDescription() {
        return $this->_description;
    }

    public function getResponseCode() {
        switch($this->message) {
            case "invalid_request":
                return 400;
            case "no_token":            
            case "invalid_token":
                return 401;
            case "insufficient_scope":
            case "insufficient_entitlement":
                return 403;
            default:
                return 400;
        }
    }

    public function getLogMessage($includeTrace = FALSE) {
        $msg = 'Message    : ' . $this->getMessage() . PHP_EOL .
               'Description: ' . $this->getDescription() . PHP_EOL;
        if($includeTrace) {
            $msg .= 'Trace      : ' . PHP_EOL . $this->getTraceAsString() . PHP_EOL;
        }
        return $msg;
    }

}

class RemoteResourceServer {

    private $_config;

    private $_entitlementEnforcement;
    private $_grantedEntitlement;
    private $_grantedScope;
    private $_resourceOwnerId;

    public function __construct(array $c) {
        $this->_config = $c;

        $this->_entitlementEnforcement = TRUE;
        $this->_resourceOwnerId = NULL;
        $this->_grantedScope = NULL;
        $this->_grantedEntitlement = NULL;
    }

    public function verifyAuthorizationHeader($authorizationHeader) {
        if(NULL === $authorizationHeader) {
            throw new ResourceServerException("no_token", "no authorization header in the request");
        }
        // b64token = 1*( ALPHA / DIGIT / "-" / "." / "_" / "~" / "+" / "/" ) *"="
        $b64TokenRegExp = '(?:[[:alpha:][:digit:]-._~+/]+=*)';
        $result = preg_match('|^Bearer (?P<value>' . $b64TokenRegExp . ')$|', $authorizationHeader, $matches);
        if($result === FALSE || $result === 0) {
            throw new ResourceServerException("invalid_token", "the access token is malformed");
        }
        $accessToken = $matches['value'];

        $userPass = $this->_getRequiredConfigParameter("resourceServerId") . ":" . $this->_getRequiredConfigParameter("resourceServerSecret");

        $postParameters = array();
        $postParameters["token"] = $accessToken;
        $postParameters["grant_type"] = "urn:pingidentity.com:oauth2:grant_type:validate_bearer";

        $curlChannel = curl_init();
        curl_setopt_array($curlChannel, array (
            CURLOPT_URL => $this->_getRequiredConfigParameter("tokenEndpoint"),
            //CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($postParameters),
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERPWD => $userPass,
        ));

        $output = curl_exec($curlChannel);
        $httpCode = curl_getinfo($curlChannel, CURLINFO_HTTP_CODE);
        curl_close($curlChannel);

        if(200 !== $httpCode) {
            throw new ResourceServerException("invalid_token", "the access token is not valid");
        }

        $token = json_decode($output, TRUE);
        if(NULL === $token) {
            throw new ResourceServerException("XXX", "unable to decode the token response");
        }

        if(time() > $token['issue_time'] + $token['expires_in']) {
            throw new ResourceServerException("invalid_token", "the access token expired");
        }

        $this->_resourceOwnerId = $token['resource_owner_id'];
        $this->_grantedScope = $token['scope'];
        $this->_grantedEntitlement = $token['resource_owner_entitlement'];
    }

    public function setEntitlementEnforcement($enforce = TRUE) {
        $this->_entitlementEnforcement = $enforce;
    }

    public function getResourceOwnerId() {
        // FIXME: should we die when the resourceOwnerId is NULL?
        return $this->_resourceOwnerId;
    }

    public function getEntitlement() {
        if(NULL === $this->_grantedEntitlement) {
            return array();
        }
        return explode(" ", $this->_grantedEntitlement);
    }

    public function hasScope($scope) {
        if(NULL === $this->_grantedScope) {
            return FALSE;
        }
        $grantedScope = explode(" ", $this->_grantedScope);
        if(in_array($scope, $grantedScope)) {
            return TRUE;
        }
        return FALSE;
    }

    public function requireScope($scope) {
        if(FALSE === $this->hasScope($scope)) {
            throw new ResourceServerException("insufficient_scope", "no permission for this call with granted scope");
        }
    }

    public function hasEntitlement($entitlement) {
        if(NULL === $this->_grantedEntitlement) {
            return FALSE;
        }
        $grantedEntitlement = explode(" ", $this->_grantedEntitlement);
        if(in_array($entitlement, $grantedEntitlement)) {
            return TRUE;
        }
        return FALSE;
    }

    public function requireEntitlement($entitlement) {
        if($this->_entitlementEnforcement) {
            if(FALSE === $this->hasEntitlement($entitlement)) {
                throw new ResourceServerException("insufficient_entitlement", "no permission for this call with granted entitlement");
            }
        }
    }

    private function _getRequiredConfigParameter($key) {
        if(!array_key_exists($key, $this->_config)) {
            throw new OAuthTwoPdoCodeClientException("no config parameter '$key'");
        }
        return $this->_config[$key];
    }

}

?>
