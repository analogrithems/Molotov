<?php
namespace Arez\Core\Lib;

class Curl
{
	public function __construct($url)
	{
		$this->url = $url;
	}

	public function translateRequest($params)
	{
		if(array_key_exists('token', $params)) {
			$return['nonce'] = $params['token'];
			unset($params['token']);
		} else {
			$return['nonce'] = 'NEW';
		}
		$return['data'] = $params;

		return $return;
	}

	public function translateReturn($data)
	{
		$return = $data;
		if(array_key_exists('nonce', $data)) {
			$return['token'] = $data['nonce'];
			unset($return['nonce']);
		}

		if(array_key_exists('status', $data) && $data['status'] < 0) {
			$return['error'] = $data['msg'];
			unset($return['status']);
			unset($return['msg']);
		}

		return $return;
	}
	
	public function request($service, $action, $params = null)
	{
		if(isset($params)) {
			$params = $this->translateRequest($params);
		}
		$return = json_decode($this->raw($this->url, $params), 1);
		$return = $this->translateReturn($return);
		return $return;
	}
	
	public function raw($url, $params, $type = 'GET')
	{
		$ch = curl_init();
	
		if($type == 'GET') {
			if(!empty($params) && $params) {
				$url = trim($url) . '?' . http_build_query($params);
			}
			curl_setopt($ch, CURLOPT_HTTPGET, 1);
		}
		
		if($type == 'POST') {
			if(!empty($params) && $params) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
			}
			curl_setopt($ch, CURLOPT_POST, 1);
		}

		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
		curl_setopt($ch, CURLOPT_NETRC, false);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION , true);
	    curl_setopt($ch, CURLOPT_AUTOREFERER , true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	    curl_setopt($ch, CURLOPT_HEADER , true);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
		
		if(isset($_SERVER['HTTP_USER_AGENT'])) {
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		} else {
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.X.Y.Z Safari/525.13.');
		}
		
		
		$result = curl_exec($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($result, 0, $header_size);
		$body = substr($result, $header_size);
		
		curl_close($ch);
		
		return $body;
	}
}