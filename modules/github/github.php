<?php

//Spell it out, no complicated autoloader
require_once ("exception.class.php");
require_once ("httpclient.interface.php");
require_once ("curlclient.class.php");

/*
 * GitHub Api v3 connector (not wrapper)
 * Original version by Nandor Sivok
 * https://github.com/dominis/GitHub-API-PHP
 *
 * Modified by Torin Nguyen
 * Removed autoloader, more intuitive constructor & authentication functions
 * Lots of minor bug fixes
 *
 * Example usage:
 		$githubApi = new GitHubApi();
	 	try {
	 	    $aUser = $githubApi->get( '/users/:user', array('user' => 'dominis') );
	 	    var_dump($aUser);
	 	} catch(Exception $e) {
	 	    print_r(json_decode($e->getMessage()));
	 	}
 */
class GitHubApi
{
    const AUTH_HTTP = 'BASIC';
    const AUTH_OAUTH = 'OAUTH';
    private $httpClient = null;

    public function __construct()
		{
        $this->httpClient = new GitHubCurl();
    }

		/*
 		 * Authenticate with username & password.
		 * Not recommended for web application
 		 */
    public function setUsernamePassword($username, $password, $type = self::AUTH_OAUTH)
 		{
        $this->httpClient->user = $username;
        $this->httpClient->pass = $password;
        $this->httpClient->authType = self::AUTH_HTTP;
    }

		/*
 		 * Authenticate with OAuth tokem.
		 * You have to provide your own OAuth flow implementation & pass the final access token into here
 		 */
    public function setAcessToken($token)
 		{
        $this->httpClient->user = $token;
        $this->httpClient->authType = self::AUTH_OAUTH;
    }

		//-------------------------------------------------------------------------
		// API Wrapper

		public function getUser()
		{
			$result = null;
			try {
			  $result = $this->get( '/user', null );
			} catch(Exception $e) {
				$result = null;
		    var_dump($e->getMessage());
			}
			return $result;
		}
		
		public function getUserOrganizations()
		{
			$result = null;
			try {
			  $result = $this->get( '/user/orgs', null );
			} catch(Exception $e) {
				$result = null;
		    var_dump($e->getMessage());
			}
			return $result;
		}

		public function getUserRepos($type, $sort, $direction)
		{
			$param = array();
			if ($type != null)				$param['type'] = $type;
			if ($sort != null)				$param['sort'] = $sort;
			if ($direction != null)		$param['direction'] = $direction;

			$result = null;
			try {
			  $result = $this->get( '/user/repos', $param );
			} catch(Exception $e) {
				$result = null;
		    var_dump($e->getMessage());
			}
			return $result;
		}
		
		public function getOrganizationRepos($organization, $type)
		{
			$param = array();
			$param['org'] = $organization;
			if ($type != null)				$param['type'] = $type;

			$result = null;
			try {
			  $result = $this->get( '/orgs/:org/repos', $param );
			} catch(Exception $e) {
				$result = null;
		    var_dump($e->getMessage());
			}
			return $result;
		}

		//-------------------------------------------------------------------------
		// Dirty stuffs

		/*
		 * Map function call to HTTP Verbs
		 */
    public function __call($method, $arg)
 		{
				//Some API doesn't need arguments
        if(!isset($arg[0]))
            throw new GitHubCommonException('Missing argument');
        if(!isset($arg[1]) || $arg[1] == null)
					$arg[1] = array();

        $httpMethod = 'METHOD_' . strtoupper($method);

        switch($method)
				{
            case 'get':
            case 'head':
            case 'delete':
                return $this->doRequest(constant('GitHubHttpClient::'. $httpMethod), $arg[0], $arg[1]);
                break;

            case 'post':
            case 'put':
            case 'patch':
                if(!isset($arg[2])) { throw new GitHubCommonException('Missing argument for write operation'); }
                return $this->doRequest(constant('GitHubHttpClient::'. $httpMethod), $arg[0], $arg[1], $arg[2]);
                break;
        }
    }

    private function prepareUrl($url, array $params)
		{
        $hasMatch = preg_match_all('/(:(\w+))/', $url, $matches);

        ksort($params);
        $paramKeys = array_keys($params);
        $paramValues = array_values($params);

        $urlKeys = array_keys(array_flip($matches[2]));
        sort($urlKeys);

        $urlParams = array_keys(array_flip($matches[1]));
        sort($urlParams);

        if($paramKeys === $urlKeys)
            return str_replace($urlParams, $paramValues, $url);

        return false;
    }

    private function doRequest($method, $url, array $params, array $input = null)
		{
        if($input === null)
 					$input = array();
        
        $remoteUrl = $this->prepareUrl($url, $params);
        return $this->httpClient->request($method, $remoteUrl, $input);
    }
}

?>