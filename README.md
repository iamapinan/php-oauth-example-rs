# Introduction
This is a simple resource server that is protected using OAuth. The
`RemoteResourceServer` class takes care of verifying the Bearer token
received through the HTTP `Authorization` header.

Using the library is straightforward:

    <?php
    require_once 'lib/RemoteResourceServer.php';

    $config = array(
        "tokenEndpoint" => "http://localhost/oauth/php-oauth/token.php",
        "resourceServerId" => "foo",
        "resourceServerSecret" => "bar",
    );

    $rs = new RemoteResourceServer($config);
    $rs->verifyRequest();

After the `verifyRequest()` some methods are available to retrieve information
about the resource owner and client.

* `getResourceOwnerId()` (the unique resource owner identifier)
* `getAttributes()` (additional attributes associated with the resource owner)
* `getScope()` (the scope granted to the client accessing this resource)
* `getEntitlement()` (the entitlement the resource owner has when accessing this 
  resource)

Some additional methods are available for your convenience, see the API 
documentation.

If the verification fails, the library will handle passing error messages back
to the client according to the OAuth Bearer specification, the execution of the
script will halt and not return after calling `verifyRequest()`.

https://tools.ietf.org/html/draft-ietf-oauth-v2-bearer
