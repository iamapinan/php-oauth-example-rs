# Introduction
This is a simple resource server that is protected using OAuth 2.0. The
`RemoteResourceServer` class takes care of verifying the Bearer token
received through the HTTP `Authorization` header.

It depends on [php-lib-remote-rs](https://github.com/fkooman/php-lib-remote-rs),
it can be installed with this command:

    $ docs/install_dependencies.sh

Some example calls:

    $ curl -i http://localhost/oauth/php-oauth-example-rs/index.php 
    HTTP/1.1 200 OK
    Date: Sat, 03 Nov 2012 15:41:16 GMT
    Server: Apache/2.2.22 (Unix) DAV/2 PHP/5.3.15 with Suhosin-Patch mod_ssl/2.2.22 OpenSSL/0.9.8r
    X-Powered-By: PHP/5.3.15
    Content-Length: 58
    Content-Type: text/html

    {"authorized":false,"message":"Hello Unauthorized World!"}

This call is "free", i.e.: you don't need to send an `Authorization` header of
the `Bearer` type. If you request the protected resource without such header
you'll get an error:

    $ curl -i http://localhost/oauth/php-oauth-example-rs/index.php?protected=1
    HTTP/1.1 401 Authorization Required
    Date: Sat, 03 Nov 2012 15:42:45 GMT
    Server: Apache/2.2.22 (Unix) DAV/2 PHP/5.3.15 with Suhosin-Patch mod_ssl/2.2.22 OpenSSL/0.9.8r
    X-Powered-By: PHP/5.3.15
    WWW-Authenticate: Bearer realm="My Example RS"
    Content-Length: 81
    Content-Type: application/json

    {"error":"no_token","error_description":"no authorization header in the request"}

Now when you specify a valid `Authorization` header all will be fine:

$ curl -H "Authorization: Bearer ABCDEF" -i http://localhost/oauth/php-oauth-example-rs/index.php?protected=1
HTTP/1.1 200 OK
Date: Sat, 03 Nov 2012 15:44:13 GMT
Server: Apache/2.2.22 (Unix) DAV/2 PHP/5.3.15 with Suhosin-Patch mod_ssl/2.2.22 OpenSSL/0.9.8r
X-Powered-By: PHP/5.3.15
Content-Length: 212
Content-Type: text/html

{"authorized":true,"id":"VWXYZ","attributes":{"uid":["admin"],"eduPersonEntitlement":["urn:x-oauth:entitlement:applications"],"displayName":["Carlos Catalano"]},"resource_owner_scope":["grades"]}

