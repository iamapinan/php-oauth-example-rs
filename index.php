<?php

require_once "RemoteResourceServer.php";

$tokenEndpoint = 'http://localhost/php-oauth/token.php';

if(array_key_exists("action", $_GET) && $_GET['action'] === 'protected') {
    // restricted call
    try {
        $rs = new RemoteResourceServer($tokenEndpoint);
	$headers = apache_request_headers();
	$ah = array_key_exists('Authorization', $headers) ? $headers['Authorization'] : NULL;
        $v = $rs->verify($ah);
        echo "Welcome to the protected resource " . $v['resource_owner_id'] . "!";
    } catch (VerifyException $ve) {
        echo $ve->getMessage() . ": " . $ve->getDescription();
    }
} else {
    // unrestricted call
    echo "Hello World!";
}
?>
