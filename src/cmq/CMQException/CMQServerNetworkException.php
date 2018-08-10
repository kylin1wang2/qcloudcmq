<?php
/**
 * Created by PhpStorm.
 * User: kylinwang@dashenw.com
 * Date: 2018/08/10
 * Time: 14:29
 */

namespace src\cmq\CMQException;


class CMQServerNetworkException extends CMQExceptionBase
{
	public $status;
	public $header;
	public $data;

	/**
	 * 服务器网络异常
	 * @param int $status
	 * @param null $header
	 * @param string $data
	 */
	public function __construct($status = 200, $header = NULL, $data = "")
	{
		if ($header == NULL) {
			$header = array();
		}
		$this->status = $status;
		$this->header = $header;
		$this->data = $data;
	}

	public function __toString()
	{
		$info = array("status" => $this->status,
			"header" => json_encode($this->header),
			"data" => $this->data);

		return "CMQServerNetworkException  " . json_encode($info);
	}
}