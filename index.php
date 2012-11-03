<?php

require_once 'extlib/php-lib-remote-rs/lib/OAuth/RemoteResourceServer.php';

use \OAuth\RemoteResourceServer as RemoteResourceServer;

$config = parse_ini_file("config/rs.ini");

if (array_key_exists("protected", $_GET) && $_GET['protected'] === "1") {
    $rs = new RemoteResourceServer($config);
    $rs->verifyRequest();
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
