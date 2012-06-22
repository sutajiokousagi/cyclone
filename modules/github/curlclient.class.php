<?php
class GitHubCurl implements GitHubHttpClient {

    public $user;
    public $pass;
    public $authType;

    public $response;
    public $headers;
    public $errorNumber;
    public $errorMessage;
    public $httpCode;

    public function request($requestType, $url, $params) {
        $query = utf8_encode(http_build_query($params, '', '&'));

        $set = array();

        if($requestType == 'GET') {
            $url = $url . '?' . $query;
        } else {
            $set += array(CURLOPT_POSTFIELDS => json_encode($params));
        }

        $set += array(
            CURLOPT_URL => self::API_URL . $url,
            CURLOPT_USERAGENT => ':)))',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CUSTOMREQUEST => $requestType,
        );

        switch($this->authType) {
            case GitHubApi::AUTH_HTTP:
                $set += array(CURLOPT_USERPWD => $this -> user . ':' . $this -> pass);
                break;
            case GitHubApi::AUTH_OAUTH:
                $set += array(CURLOPT_HTTPHEADER => array('Authorization: token ' . $this -> user));
                break;
        }

        $curl = curl_init();

        curl_setopt_array($curl, $set);

        $this -> response = json_decode(curl_exec($curl));
        $this -> headers = curl_getinfo($curl);
        $this -> errorNumber = curl_errno($curl);
        $this -> errorMessage = curl_error($curl);
        $this -> httpCode = $this -> headers['http_code'];

        curl_close($curl);

        switch($this -> httpCode) {
            case 200:
            case 201:
                return $this -> response;
                break;
            case 401:
                throw new GitHubCommonException('Bad credentials');
                break;
            case 422:
            default:
                throw new GitHubCommonException(json_encode($this -> response));
                break;
        }
    }

}

?>