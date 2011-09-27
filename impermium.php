<?php

/**
 * Impermium-PHP
 *
 * A simple PHP wrapper for Impermium
 *
 * Usage:
 *
 *     $impermium = new ImpermiumAPI($api_key);
 *
 *     $response = $impermium->contentComment($event_id, array(
 *         'uid_ref'       => 'foo',
 *         'content'       => 'bar',
 *         'resource_url'  => 'http://www.popsugar.com/',
 *         'enduser_ip'    => '127.0.0.1',
 *     ));
 *
 *     if ($response->spam->label == 'spam') {
 *         echo "It's spam!";
 *     }
 *
 * @author      Alec Vallintine <avallintine@sugarinc.com>
 * @version     0.1
 * @copyright   Copyright (c) 2011 Sugar Inc.
 * @link        https://github.com/PopSugar/Impermium-PHP
 * @license		see LICENSE
 */

if (!function_exists('curl_init')) {
  throw new Exception('Impermium-PHP needs the CURL PHP extension.');
}

if (!function_exists('json_decode')) {
  throw new Exception('Impermium-PHP needs the JSON PHP extension.');
}

class ImpermiumAPIException extends Exception {
}

/**
 * @throws ImpermiumAPIException
 * 
 */
class ImpermiumAPI {

    /**
     * Version
     */
    const VERSION = '0.1';

    /**
     * Default Impermium API version
     */
    const API_VERSION = '2.0';

    /**
     * Default Impermium API host name
     */
    const API_HOST = 'api-test.impermium.com';

    /**
     * The API key issued to you when you signed up for the service
     *
     * @var string
     * @access private
     */
    private $_apiKey;

    /**
     * Impermium API host name
     *
     * @var string
     * @access private
     */
    private $_apiHost;

    /**
     * Impermium API version
     *
     * @var string
     * @access private
     */
    private $_apiVersion;

    /**
     * Default options for curl
     *
     * @var array
     * @access private
     */
    private $_curlOpts = array(
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_POST            => true,
        CURLOPT_CONNECTTIMEOUT  => 10,
        CURLOPT_TIMEOUT         => 60,
        CURLOPT_USERAGENT       => 'Impermium-PHP/0.1',
        CURLOPT_HTTPHEADER      => array(
            'Content-Type: application/json',
        ),
    );

    /**
     * Constructor
     *
     * @param string $api_key
     * @param string $api_host [optional]
     * @param string $api_version [optional]
     * @access public
     */
    public function __construct($api_key, $api_host=self::API_HOST, $api_version=self::API_VERSION) {
        $this->_apiKey      = $api_key;
        $this->_apiHost     = $api_host;
        $this->_apiVersion  = $api_version;
    }

    /**
     * Builds a request url
     *
     * @param string $type
     * @param string $action
     * @param string|int $event_id
     * @return string
     * @access private
     */
    private function _buildUrl($type, $action, $event_id) {
        $url = sprintf('http://%s/%s/%s/%s/%s/%s', $this->_apiHost, $type, $action, $this->_apiVersion, $this->_apiKey, $event_id);
        return $url;
    }

    /**
     * Makes a request to Impermium and returns the decoded response
     *
     * @param string $type
     * @param string $action
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access private
     * @throws ImpermiumAPIException
     */
    private function _request($type, $action, $event_id, $params) {
        $url = $this->_buildUrl($type, $action, $event_id);
        $post_data = json_encode($params);

        $ch = curl_init();

        curl_setopt_array($ch, $this->_curlOpts);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        $response = curl_exec($ch);

        $error = false;

        if ($response === false) {
            $error = curl_error($ch);
        }
        else {
            $response_data = json_decode($response);
        }

        if (!$error && curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            $error = $response_data->message;
        }

        if ($error) {
            curl_close($ch);
            throw new ImpermiumAPIException($error);
        }

        curl_close($ch);
        
        return $response_data;
    }

    /**
     * Gets the API key
     *
     * @return string
     * @access public
     */
    public function getApiKey() {
        return $this->_apiKey;
    }

    /**
     * Sets the API key
     *
     * @param string $api_key
     * @return void
     * @access public
     */
    public function setApiKey($api_key) {
        $this->_apiKey = $api_key;
    }

    /**
     * Gets the API host
     *
     * @return string
     * @access public
     */
    public function getApiHost() {
        return $this->_apiHost;
    }

    /**
     * Sets the API host
     *
     * @param string $api_host
     * @return void
     * @access public
     */
    public function setApiHost($api_host) {
        $this->_apiHost = $api_host;
    }

    /**
     * Gets the API version
     *
     * @return string
     * @access public
     */
    public function getApiVersion() {
        return $this->_apiVersion;
    }

    /**
     * Sets the API version
     *
     * @param string $api_version
     * @return void
     * @access public
     */
    public function setApiVersion($api_version) {
        $this->_apiVersion = $api_version;
    }

    /**
     * Gets the options for curl
     *
     * @return array
     * @access public
     */
    public function getCurlOpts() {
        return $this->_curlOpts;
    }

    /**
     * Sets the options for curl
     *
     * @param array $curl_opts
     * @return void
     * @access public
     */
    public function setCurlOpts($curl_opts) {
        $this->_curlOpts = $curl_opts;
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function accountLogin($event_id, $params) {
        return $this->_request('account', 'login', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function accountProfile($event_id, $params) {
        return $this->_request('account', 'profile', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function accountSignup($event_id, $params) {
        return $this->_request('account', 'signup', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function accountSignupAttempt($event_id, $params) {
        return $this->_request('account', 'signup_attempt', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function connectionInvite($event_id, $params) {
        return $this->_request('connection', 'invite', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function connectionInviteResponse($event_id, $params) {
        return $this->_request('connection', 'invite_response', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function contentBlogEntry($event_id, $params) {
        return $this->_request('content', 'blog_entry', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function contentChatMessage($event_id, $params) {
        return $this->_request('content', 'chat_message', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function contentChatroomMessage($event_id, $params) {
        return $this->_request('content', 'chatroom_message', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function contentComment($event_id, $params) {
        return $this->_request('content', 'comment', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function contentForumMessage($event_id, $params) {
        return $this->_request('content', 'forum_message', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function contentGeneric($event_id, $params) {
        return $this->_request('content', 'generic', $event_id, $params);
    }

    /**
     * 
     *
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function contentMessage($event_id, $params) {
        return $this->_request('content', 'message', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function contentRating($event_id, $params) {
        return $this->_request('content', 'rating', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function contentWallMessage($event_id, $params) {
        return $this->_request('content', 'wall_message', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function feedbackContent($event_id, $params) {
        return $this->_request('feedback', 'content', $event_id, $params);
    }

    /**
     * @param string|int $event_id
     * @param array $params
     * @return object
     * @access public
     * @link http://impermium.com/api
     */
    public function feedbackUser($event_id, $params) {
        return $this->_request('feedback', 'user', $event_id, $params);
    }
}
