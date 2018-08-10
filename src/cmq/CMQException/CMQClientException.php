<?php
/**
 * Created by PhpStorm.
 * User: kylinwang@dashenw.com
 * Date: 2018/08/10
 * Time: 14:22
 */

namespace src\cmq\CMQException;


class CMQClientException extends CMQExceptionBase
{
	public function __construct($message, $code = -1, $data = array())
	{
		parent::__construct($message, $code, $data);
	}

	public function __toString()
	{
		return "CMQClientException  " . $this->get_info();
	}
}