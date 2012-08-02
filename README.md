# Introduction
This is a simple resource server that is protected using OAuth. The
`RemoteResourceServer` class takes care of verifying the Bearer token
received through the HTTP header.

The API is straightforward:

    $rs = new RemoteResourceServer('http://localhost/php-oauth/token.php');
    $rs->verify("Bearer xyz");

The parameter to the constructor is the full URL to the OAuth "token" 
endpoint. The parameter to the `verify` method is the content of the HTTP
`Authorization` header. In PHP this header can be obtained through the
`apache_request_headers()` function when using Apache. For other web servers 
a different approach may be required to obtain the `Authorization` header.

