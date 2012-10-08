<?php

require_once "lib/RemoteResourceServer.php";

$config = parse_ini_file("config/rs.ini");

if(array_key_exists("protected", $_GET) && $_GET['protected'] === "1") {
    $rs = new RemoteResourceServer($config);

    $headers = apache_request_headers();
    $ah = array_key_exists('Authorization', $headers) ? $headers['Authorization'] : NULL;
    $rs->verifyAuthorizationHeader($ah);
    $data = array(
        "authorized" => TRUE, 
        "id" => $rs->getResourceOwnerId(), 
        "attributes" => $rs->getAttributes(),
        "resource_owner_scope" => $rs->getScope(),
    );
    $output = json_encode($data);  
} else {
    $data = array(
        "authorized" => FALSE, 
        "message" => "Hello Unauthorized World!",
    );
    $output = json_encode($data);
}

echo $output;
