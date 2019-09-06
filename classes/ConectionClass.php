<?php
require_once(dirname(__FILE__).'/../../../config/config.inc.php');

class ConectionClass {
	
	/**
     * Configuration for CURL
     */
    public static $CURL_OPTS = array(
        CURLOPT_USERAGENT => "HN24-PHP-SDK-1.1.0", 
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_CONNECTTIMEOUT => 360, 
        CURLOPT_RETURNTRANSFER => 1, 
        CURLOPT_TIMEOUT => 360,
    );
	
	public static $authorization = "";
	
	protected static $API_ROOT_URL = 'https://hinet.com.ar/hn24/api/v1/';
	
	public static $URLS_SERVICE = array(
		"producto" => 'https://hinet.com.ar/hn24/api/v1/productos', // PRODUCTOS
		"movinventario" => 'https://hinet.com.ar/hn24/api/v1/stock', // MOVIMIENTO INVENTARIO
	);
	
	public static function setAuthorization($authorization) {
		self::$authorization = $authorization;
	}
	
	public static function getAuthorization() {
		return $this->authorization;
	}
	
	/**
     * Execute a GET Request
     * 
     * @param string $path
     * @param array $params
     * @param boolean $assoc
     * @return mixed
     */
    public static function get($path, $params = null, $assoc = false) {
		$opts = array(
			CURLOPT_HTTPHEADER => array('Authorization: '.self::$authorization),
		);
        $exec = self::execute($path, $opts, $params, $assoc);

        return $exec;
    }

    /**
     * Execute a POST Request
     * 
     * @param string $body
     * @param array $params
     * @return mixed
     */
    public static function post($path, $body = null, $params = array()) {
        $body = json_encode($body);
        $opts = array(
            CURLOPT_HTTPHEADER => array('Content-Type: application/json','Authorization: '.self::$authorization),
            CURLOPT_POST => true, 
            CURLOPT_POSTFIELDS => $body
        );
        
        $exec = self::execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a PUT Request
     * 
     * @param string $path
     * @param string $body
     * @param array $params
     * @return mixed
     */
    public static function put($path, $body = null, $params = array()) {
        $body = json_encode($body);
        $opts = array(
            CURLOPT_HTTPHEADER => array('Content-Type: application/json','Authorization: '.self::$authorization),
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $body
        );
        
        $exec = self::execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a DELETE Request
     * 
     * @param string $path
     * @param array $params
     * @return mixed
     */
    public static function delete($path, $params) {
        $opts = array(
			CURLOPT_HTTPHEADER => array('Authorization: '.self::$authorization),
            CURLOPT_CUSTOMREQUEST => "DELETE"
        );
        
        $exec = self::execute($path, $opts, $params);
        
        return $exec;
    }

    /**
     * Execute a OPTION Request
     * 
     * @param string $path
     * @param array $params
     * @return mixed
     */
    public static function options($path, $params = null) {
        $opts = array(
			CURLOPT_HTTPHEADER => array('Authorization: '.self::$authorization),
            CURLOPT_CUSTOMREQUEST => "OPTIONS"
        );
        
        $exec = self::execute($path, $opts, $params);

        return $exec;
    }
	
	/**
     * Execute all requests and returns the json body and headers
     * 
     * @param string $path
     * @param array $opts
     * @param array $params
     * @param boolean $assoc
     * @return mixed
     */
    public static function execute($path, $opts = array(), $params = array(), $assoc = false) {
        $uri = self::make_path($path, $params);
		
        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt_array($ch, self::$CURL_OPTS);

        if(!empty($opts)){
            curl_setopt_array($ch, $opts);
		}
		
		$result = curl_exec($ch);
		$info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

        $return["body"] = json_decode($result, $assoc);
        $return["httpCode"] = $info;

        return $return;
    }
	
	/**
     * Check and construct an real URL to make request
     * 
     * @param string $path
     * @param array $params
     * @return string
     */
    public static function make_path($path, $params = array()) {
        if (!preg_match("/^\//", $path)) {
            $path = '/' . $path;
        }

        $uri = self::$API_ROOT_URL . $path;
        
        if(!empty($params)) {
            $paramsJoined = array();

            foreach($params as $param => $value) {
               $paramsJoined[] = "$param=$value";
            }
            $params = '?'.implode('&', $paramsJoined);
            $uri = $uri.$params;
        }

        return $uri;
    }
}