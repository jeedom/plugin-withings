<?php

/**
 * WithingsPHP v.0.73. Basic Withings API wrapper for PHP using OAuth
 *
 * Note: Library is in beta and provided as-is. We hope to add features as API grows, however
 *       feel free to fork, extend and send pull requests to us.
 *
 * - https://github.com/heyitspavel/withingsphp
 *
 *
 * Date: 2014/09/23
 * Requires OAuth 1.0.0, SimpleXML
 * @version 0.73 ($Id$)
 */
class WithingsPHP {

    /**
     * API Constants
     *
     */
    private $authHost = 'oauth.withings.com';
    private $apiHost = 'wbsapi.withings.net';
    private $baseApiUrl;
    private $authUrl;
    private $requestTokenUrl;
    private $accessTokenUrl;

    /**
     * Class Variables
     *
     */
    protected $oauth;
    protected $oauthToken, $oauthSecret;
    protected $metric = 0;

    /**
     * @param string $consumer_key Application consumer key for Withings API
     * @param string $consumer_secret Application secret
     * @param int $debug Debug mode (0/1) enables OAuth internal debug
     * @param string $user_agent User-agent to use in API calls
     * @param string $response_format Response format (json or xml) to use in API calls
     */
    public function __construct($consumer_key, $consumer_secret) {
        $this->initUrls();

        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->oauth = new OAuth($consumer_key, $consumer_secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
    }

    /**
     * @param string $consumer_key Application consumer key for Withings API
     * @param string $consumer_secret Application secret
     */
    public function reinit($consumer_key, $consumer_secret) {
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->oauth = new OAuth($consumer_key, $consumer_secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
    }

    /**
     * @param string $apiHost API host, i.e. api.withings.com (do you know any others?)
     * @param string $authHost Auth host, i.e. www.withings.com
     */
    public function setEndpointBase($apiHost, $authHost, $https = true, $httpsApi = true) {
        $this->apiHost = $apiHost;
        $this->authHost = $authHost;

        $this->initUrls($https, $httpsApi);
    }

    private function initUrls($https = true, $httpsApi = true) {

        if ($httpsApi)
            $this->baseApiUrl = 'https://' . $this->apiHost . '/';
        else
            $this->baseApiUrl = 'http://' . $this->apiHost . '/';

        if ($https) {
            $this->authUrl = 'https://' . $this->authHost . '/account/authorize';
            $this->requestTokenUrl = 'https://' . $this->authHost . '/account/request_token';
            $this->accessTokenUrl = 'https://' . $this->authHost . '/account/access_token';
        } else {
            $this->authUrl = 'http://' . $this->authHost . '/account/authorize';
            $this->requestTokenUrl = 'http://' . $this->authHost . '/account/request_token';
            $this->accessTokenUrl = 'http://' . $this->authHost . '/account/access_token';
        }
    }

    /**
     * Returns Withings session status for frontend (i.e. 'Sign in with Withings' implementations)
     *
     * @return int (0 - no session, 1 - just after successful authorization, 2 - session exist)
     */
    public static function sessionStatus() {
        $session = session_id();
        if (empty($session)) {
            session_start();
        }
        if (empty($_SESSION['withings_Session']))
            $_SESSION['withings_Session'] = 0;

        return (int) $_SESSION['withings_Session'];
    }

    /**
     * Initialize session. Inits OAuth session, handles redirects to Withings login/authorization if needed
     *
     * @param  $callbackUrl Callback for 'Sign in with Withings'
     * @param  $cookie Use persistent cookie for authorization, or session cookie only
     * @return int (1 - just after successful authorization, 2 - if session already exist)
     */
    public function initSession($callbackUrl, $cookie = true) {

        $session = session_id();
        session_start();

        if (empty($_SESSION['withings_Session']))
            $_SESSION['withings_Session'] = 0;


        if (!isset($_GET['oauth_token']) && $_SESSION['withings_Session'] == 1) {
            $_SESSION['withings_Session'] = 0;
        }

        if ($_SESSION['withings_Session'] == 0) {

            $request_token_info = $this->oauth->getRequestToken($this->requestTokenUrl, $callbackUrl);

            $_SESSION['withings_Secret'] = $request_token_info['oauth_token_secret'];
            $_SESSION['withings_Session'] = 1;
            @session_write_close();
            return $this->authUrl . '?oauth_token=' . $request_token_info['oauth_token'];
        } else if ($_SESSION['withings_Session'] == 1) {

            $this->oauth->setToken($_GET['oauth_token'], $_SESSION['withings_Secret']);
            $access_token_info = $this->oauth->getAccessToken($this->accessTokenUrl);

            $_SESSION['withings_Session'] = 2;
            $_SESSION['withings_Token'] = $access_token_info['oauth_token'];
            $_SESSION['withings_Secret'] = $access_token_info['oauth_token_secret'];

            $this->setOAuthDetails($_SESSION['withings_Token'], $_SESSION['withings_Secret']);
            @session_write_close();
            return 1;
        } else if ($_SESSION['withings_Session'] == 2) {
            $this->setOAuthDetails($_SESSION['withings_Token'], $_SESSION['withings_Secret']);
            @session_write_close();
            return 2;
        }
    }

    /**
     * Reset session
     *
     * @return void
     */
    public function resetSession() {
        $_SESSION['withings_Session'] = 0;
    }

    /**
     * Sets OAuth token/secret. Use if library used in internal calls without session handling
     *
     * @param  $token
     * @param  $secret
     * @return void
     */
    public function setOAuthDetails($token, $secret) {
        $this->oauthToken = $token;
        $this->oauthSecret = $secret;

        $this->oauth->setToken($this->oauthToken, $this->oauthSecret);
    }

    /**
     * Get OAuth token
     *
     * @return string
     */
    public function getOAuthToken() {
        return $this->oauthToken;
    }

    /**
     * Get OAuth secret
     *
     * @return string
     */
    public function getOAuthSecret() {
        return $this->oauthSecret;
    }

    /**
     * Set Unit System for all future calls (see http://wiki.withings.com/display/API/API-Unit-System)
     * 0 (Metric), 1 (en_US), 2 (en_GB)
     *
     * @param int $metric
     * @return void
     */
    public function setMetric($metric) {
        $this->metric = $metric;
    }

    public function doRequest($url) {
        try {
            $this->oauth->fetch($this->baseApiUrl . $url, null, OAUTH_HTTP_METHOD_GET);
        } catch (Exception $E) {
            
        }
        $response = $this->oauth->getLastResponse();
        $responseInfo = $this->oauth->getLastResponseInfo();
        if (!strcmp($responseInfo['http_code'], '200')) {
            $response = substr($response, strpos($response, '{'));
            $response = $this->parseResponse($response);

            if ($response)
                return $response;
            else
                throw new WithingsException($responseInfo['http_code'], 'Withings request failed. Code: ' . $responseInfo['http_code']);
        } else {
            throw new WithingsException($responseInfo['http_code'], 'Withings request failed. Code: ' . $responseInfo['http_code']);
        }
    }

    /**
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    private function parseResponse($response) {
        return (isset($response->errors)) ? $response->errors : json_decode($response, true);
    }

}

/**
 * Withings API communication exception
 *
 */
class WithingsException extends Exception {

    public $fbMessage = '';
    public $httpcode;

    public function __construct($code, $fbMessage = null, $message = null) {

        $this->fbMessage = $fbMessage;
        $this->httpcode = $code;

        if (isset($fbMessage) && !isset($message))
            $message = $fbMessage;

        try {
            $code = (int) $code;
        } catch (Exception $E) {
            $code = 0;
        }

        parent::__construct($message, $code);
    }

}

/**
 * Basic response wrapper for customCall
 *
 */
class WithingsResponse {

    public $response;
    public $code;

    /**
     * @param  $response string
     * @param  $code string
     */
    public function __construct($response, $code) {
        $this->response = $response;
        $this->code = $code;
    }

}

/**
 * Wrapper for rate limiting quota
 *
 */
class WithingsRateLimiting {

    public $viewer;
    public $viewerReset;
    public $viewerQuota;
    public $client;
    public $clientReset;
    public $clientQuota;

    public function __construct($viewer, $client, $viewerReset = null, $clientReset = null, $viewerQuota = null, $clientQuota = null) {
        $this->viewer = $viewer;
        $this->viewerReset = $viewerReset;
        $this->viewerQuota = $viewerQuota;
        $this->client = $client;
        $this->clientReset = $clientReset;
        $this->clientQuota = $clientQuota;
    }

}
