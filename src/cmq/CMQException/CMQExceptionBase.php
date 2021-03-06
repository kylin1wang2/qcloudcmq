<?php
/**
 * Created by PhpStorm.
 * User: kylinwang@dashenw.com
 * Date: 2018/08/10
 * Time: 14:17
 */

namespace src\cmq\CMQException;


class CMQExceptionBase extends \RuntimeException
{
	/*
    @type code: int
    @param code: 错误类型

    @type message: string
    @param message: 错误描述

    @type data: array
    @param data: 错误数据
    */

	public $code;
	public $message;
	public $data;

	public function __construct($message, $code = -1, $data = array())
	{
		parent::__construct($message, $code, $previousException);
		$this->code = $code;
		$this->message = $message;
		$this->data = $data;
	}

	public function __toString()
	{
		return "CMQExceptionBase  " . $this->get_info();
	}

	public function get_info()
	{
		$info = array("code" => $this->code,
			"data" => json_encode($this->data),
			"message" => $this->message);
		return json_encode($info);
	}

}