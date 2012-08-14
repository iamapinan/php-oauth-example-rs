<?php

require_once "RemoteResourceServer.php";

$tokenEndpoint = 'http://localhost/php-oauth/token.php';

header("Content-Type: application/json");

if(array_key_exists("protected", $_GET) && $_GET['protected'] === "1") {
    // restricted call, needs authorization
    try {
        $rs = new RemoteResourceServer($tokenEndpoint);
        $headers = apache_request_headers();
        $ah = array_key_exists('Authorization', $headers) ? $headers['Authorization'] : NULL;
        $token = $rs->verify($ah);
        echo json_encode(array("authorized" => TRUE, "resource_owner_id" => $token['resource_owner_id']));    
    } catch (VerifyException $ve) {
        echo json_encode(array("error" => $ve->getMessage(), "error_description" => $ve->getDescription()));
    }
} else {
    // unrestricted call
    echo json_encode(array("authorized" => FALSE, "message" => "Hello World!"));
}  
?>
