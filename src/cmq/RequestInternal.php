<?php
/**
 * Created by PhpStorm.
 * User: kylinwang@dashenw.com
 * Date: 2018/08/10
 * Time: 15:13
 */

namespace src\cmq;


class RequestInternal
{
	public $header;
	public $method;
	public $uri;
	public $data;

	public function __construct($method = "", $uri = "", $header = NULL, $data = "")
	{
		if ($header == NULL) {
			$header = array();
		}
		$this->method = $method;
		$this->uri = $uri;
		$this->header = $header;
		$this->data = $data;
	}

	public function __toString()
	{
		$info = array("method" => $this->method,
			"uri" => $this->uri,
			"header" => json_encode($this->header),
			"data" => $this->data);
		return json_encode($info);
	}
}