<?php

namespace llRequest;

use llRequest\Deferred;
use llRequest\Response;

/**
 * UsrRequest
 */
class Request
{

	private $headers = [];
	private $url;
	private $query;
	private $type = 'http';
	private $curlOpt = [];

	/**
	 * string type: soap || http
	 **/
	function __construct($type, $url, array $query = [])
	{
		$this->type = $type;

		$url = parse_url($url);

		if (isset($url['query'])) parse_str($url['query'], $url['query']);

		if (isset($url['query']) && !empty($url['query']) || !empty($query))
			$url['query'] = array_merge(((isset($url['query'])) ? $url['query'] : []), $query);

		if (isset($url['query'])) array_walk_recursive($url['query'], function (&$v) {
			if (is_string($v)) $v = rawurlencode($v);
		});
		if (isset($url['query'])) $url['query'] = rawurldecode(http_build_query($url['query']));

		$url = self::unparse_url($url);

		if (substr($url, -1) == '=')
			$url = substr($url, 0, -1);

		$this->url = $url;
		$this->query = $query;
	}

	public function test()
	{
		echo 'Teste de implementação tipo: ' . $this->type;
	}

	public static function http($url, array $query = [])
	{
		return new self('http', $url, $query);
	}

	public static function soap($url, array $query = [])
	{

		return new self('soap', $url, $query);
	}


	public function get()
	{
		return $this->run('get', []);
	}

	public function post($data)
	{
		return $this->run('post', $data);
	}

	public function delete($data)
	{
		return $this->run('delete', $data);
	}

	public function put($data)
	{
		return $this->run('put', $data);
	}

	public function patch($data)
	{
		return $this->run('patch', $data);
	}

	function __call($name, $arguments)
	{
		return $this->run($name, $arguments);
	}

	public function curlSetOpt(array $opts)
	{
		if (!is_array($this->curlOpt)) {
			$this->curlOpt = [];
		}

		foreach ($opts as $k => $v) {
			$this->curlOpt[$k] = $v;
		}

		return $this;
	}

	public function headers(array $headers)
	{
		$this->headers = $headers;
		return $this;
	}

	private function run($method, $data)
	{

		$dfd = new Deferred();
		$rps = new Response();

		if ($this->type == 'http') {
			$curlOpt = [];
			$method = strtoupper($method);
			$ch = curl_init();

			if (!empty($this->headers)) {
				$headers = [];
				foreach ($this->headers as $k => $v)
					$headers[] = $k . ': ' . $v;

				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			}

			if (!empty($this->curlOpt)) {
				foreach ($this->curlOpt as $k => $v) {
					curl_setopt($ch, $k, $v);
				}
			}

			curl_setopt($ch, CURLOPT_URL, $this->url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

			if ($method != 'GET') {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			}

			$return = curl_exec($ch);

			$rps->meta($this->url, curl_getinfo($ch, CURLINFO_HTTP_CODE));
			$rps->data($return);

			if ($rps->isError())
				$dfd->reject($rps);
			else
				$dfd->resolve($rps);

		} else if ($this->type == 'soap') {

			try {
				$ws = new \SoapClient($this->url, $this->headers);
				$return = $ws->{$method}($data[0]);

				$rps->data($return);
				$return = explode('|', str_ireplace(array("\n\r", "\n", "\r", " "), "|", $ws->__getLastResponseHeaders()));

				$rps->meta($this->url, $return[1]);

				if ($rps->isError())
					$dfd->reject($rps);
				else
					$dfd->resolve($rps);

				$dfd->resolve($rps);
			} catch (\Exception $e) {

				$rps->meta($this->url, 500);
				$rps->data($e->getMessage());
				$dfd->reject($rps);
			}


		}

		return $dfd->promise();
	}

	private static function unparse_url($parsed_url)
	{
		$scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
		$host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
		$port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
		$user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
		$pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
		$pass = ($user || $pass) ? "$pass@" : '';
		$path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
		$query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
		$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

		return $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
	}

}
