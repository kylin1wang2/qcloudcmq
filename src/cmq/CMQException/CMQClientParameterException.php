<?php
/**
 * Created by PhpStorm.
 * User: kylinwang@dashenw.com
 * Date: 2018/08/10
 * Time: 14:27
 */

namespace src\cmq\CMQException;


class CMQClientParameterException extends CMQClientException
{
	/**
	 * 参数格式错误
	 * @note: 请根据提示修改对应参数;
	 * @param $message
	 * @param int $code
	 * @param array $data
	 */
	public function __construct($message, $code = -1, $data = array())
	{
		parent::__construct($message, $code, $data);
	}

	public function __toString()
	{
		return "CMQClientParameterException  " . $this->get_info();
	}
}