<?php
interface GitHubHttpClient {
    
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_POST = 'POST';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD = 'HEAD';

    const API_URL = 'https://api.github.com';

    public function request($requestType, $url, $params);


}

?>