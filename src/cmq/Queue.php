<?php
/**
 * Created by PhpStorm.
 * User: kylinwang@dashenw.com
 * Date: 2018/08/10
 * Time: 14:35
 */

namespace src\cmq;


class Queue
{
	private $queue_name;
	private $cmq_client;
	private $encoding;

	public function __construct($queue_name, $cmq_client, $encoding = false)
	{
		$this->queue_name = $queue_name;
		$this->cmq_client = $cmq_client;
		$this->encoding = $encoding;
	}

	/**
	 * 设置是否对消息体进行base64编码
	 *
	 * @param $encoding:是否对消息体进行base64编码
	 */
	public function set_encoding($encoding)
	{
		$this->encoding = $encoding;
	}

	/**
	 * 创建队列
	 *
	 * @param $queue_meta:QueueMeta对象，设置队列的属性
	 * @api https://cloud.tencent.com/document/product/406/5832
	 */
	public function create($queue_meta)
	{
		$params = array(
			'queueName' => $this->queue_name,
			'pollingWaitSeconds' => $queue_meta->pollingWaitSeconds,
			'visibilityTimeout' => $queue_meta->visibilityTimeout,
			'maxMsgSize' => $queue_meta->maxMsgSize,
			'msgRetentionSeconds' => $queue_meta->msgRetentionSeconds,
			'rewindSeconds' => $queue_meta->rewindSeconds,
		);
		if ($queue_meta->maxMsgHeapNum > 0) {
			$params['maxMsgHeapNum'] = $queue_meta->maxMsgHeapNum;
		}
		$this->cmq_client->create_queue($params);
	}

	/**
	 * 获取队列属性
	 *
	 * @return QueueMeta:队列的属性
	 * @api https://cloud.tencent.com/document/product/406/5834
	 */
	public function get_attributes()
	{
		$params = array(
			'queueName' => $this->queue_name
		);
		$resp = $this->cmq_client->get_queue_attributes($params);
		$queue_meta = new QueueMeta();
		$queue_meta->queueName = $this->queue_name;
		$this->__resp2meta__($queue_meta, $resp);
		return $queue_meta;
	}

	/**
	 * 设置队列属性
	 *
	 * @param $queue_meta:QueueMeta对象，设置队列的属性
	 * @api https://cloud.tencent.com/document/product/406/5835
	 */
	public function set_attributes($queue_meta)
	{
		$params = array(
			'queueName' => $this->queue_name,
			'pollingWaitSeconds' => $queue_meta->pollingWaitSeconds,
			'visibilityTimeout' => $queue_meta->visibilityTimeout,
			'maxMsgSize' => $queue_meta->maxMsgSize,
			'msgRetentionSeconds' => $queue_meta->msgRetentionSeconds,
			'rewindSeconds' => $queue_meta->rewindSeconds
		);
		if ($queue_meta->maxMsgHeapNum > 0) {
			$params['maxMsgHeapNum'] = $queue_meta->maxMsgHeapNum;
		}

		$this->cmq_client->set_queue_attributes($params);
	}

	/**
	 * 回溯队列
	 *
	 * @param $backTrackingTime
	 * @api https://cloud.tencent.com/document/product/406/8407
	 */
	public function rewindQueue($backTrackingTime)
	{
		$params = array(
			'queueName' => $this->queue_name,
			'startConsumeTime' => $backTrackingTime
		);
		$this->cmq_client->rewindQueue($params);
	}

	/**
	 * 删除队列
	 *
	 * @api https://cloud.tencent.com/document/product/406/5836
	 */
	public function delete()
	{
		$params = array('queueName' => $this->queue_name);
		$this->cmq_client->delete_queue($params);
	}

	/**
	 * 发送消息
	 *
	 * @param $message:发送的Message object
	 * @param int $delayTime:单位为秒，表示该消息发送到队列后，需要延时多久用户才可见该消息。
	 * @return Message:消息发送成功的返回属性，包含MessageId
	 * @api https://cloud.tencent.com/document/product/406/5837
	 */
	public function send_message($message, $delayTime = 0)
	{
		if ($this->encoding) {
			$msgBody = base64_encode($message->msgBody);
		} else {
			$msgBody = $message->msgBody;
		}
		$params = array(
			'queueName' => $this->queue_name,
			'msgBody' => $msgBody,
			'delaySeconds' => $delayTime
		);
		$msgId = $this->cmq_client->send_message($params);
		$retmsg = new Message();
		$retmsg->msgId = $msgId;
		return $retmsg;
	}

	/**
	 * 批量发送消息
	 *
	 * @param $messages:发送的Message object list
	 * @param int $delayTime:单位为秒，表示该消息发送到队列后，需要延时多久用户才可见。（该延时对一批消息有效，不支持多对多映射）
	 * @return array:多条消息发送成功的返回属性，包含MessageId
	 * @api https://cloud.tencent.com/document/product/406/5838
	 */
	public function batch_send_message($messages, $delayTime = 0)
	{
		$params = array(
			'queueName' => $this->queue_name,
			'delaySeconds' => $delayTime
		);
		$n = 1;
		foreach ($messages as $message) {
			$key = 'msgBody.' . $n;
			if ($this->encoding) {
				$params[$key] = base64_encode($message->msgBody);
			} else {
				$params[$key] = $message->msgBody;
			}
			$n += 1;
		}
		$msgList = $this->cmq_client->batch_send_message($params);
		$retMessageList = array();
		foreach ($msgList as $msg) {
			$retmsg = new Message();
			$retmsg->msgId = $msg['msgId'];
			$retMessageList [] = $retmsg;
		}
		return $retMessageList;
	}

	/**
	 * 消费消息
	 *
	 * @param null $polling_wait_seconds:本次请求的长轮询时间，单位：秒
	 * @return Message:Message object中包含基本属性、临时句柄
	 * @api https://cloud.tencent.com/document/product/406/5839
	 */
	public function receive_message($polling_wait_seconds = NULL)
	{

		$params = array('queueName' => $this->queue_name);
		if ($polling_wait_seconds != NULL) {
			$params['UserpollingWaitSeconds'] = $polling_wait_seconds;
			$params['pollingWaitSeconds'] = $polling_wait_seconds;
		} else {
			$params['UserpollingWaitSeconds'] = 30;
		}
		$resp = $this->cmq_client->receive_message($params);
		$msg = new Message();
		if ($this->encoding) {
			$msg->msgBody = base64_decode($resp['msgBody']);
		} else {
			$msg->msgBody = $resp['msgBody'];
		}
		$msg->msgId = $resp['msgId'];
		$msg->receiptHandle = $resp['receiptHandle'];
		$msg->enqueueTime = $resp['enqueueTime'];
		$msg->nextVisibleTime = $resp['nextVisibleTime'];
		$msg->dequeueCount = $resp['dequeueCount'];
		$msg->firstDequeueTime = $resp['firstDequeueTime'];
		return $msg;
	}

	/**
	 * 批量消费消息
	 *
	 * @param $num_of_msg:本次请求最多获取的消息条数
	 * @param null $polling_wait_seconds:本次请求的长轮询时间，单位：秒
	 * @return array:多条消息的属性，包含消息的基本属性、临时句柄
	 * @api https://cloud.tencent.com/document/product/406/5924
	 */
	public function batch_receive_message($num_of_msg, $polling_wait_seconds = NULL)
	{
		$params = array('queueName' => $this->queue_name, 'numOfMsg' => $num_of_msg);
		if ($polling_wait_seconds != NULL) {
			$params['UserpollingWaitSeconds'] = $polling_wait_seconds;
			$params['pollingWaitSeconds'] = $polling_wait_seconds;
		} else {
			$params['UserpollingWaitSeconds'] = 30;
		}
		$msgInfoList = $this->cmq_client->batch_receive_message($params);
		$retMessageList = array();
		foreach ($msgInfoList as $msg) {
			$retmsg = new Message();
			if ($this->encoding) {
				$retmsg->msgBody = base64_decode($msg['msgBody']);
			} else {
				$retmsg->msgBody = $msg['msgBody'];
			}
			$retmsg->msgId = $msg['msgId'];
			$retmsg->receiptHandle = $msg['receiptHandle'];
			$retmsg->enqueueTime = $msg['enqueueTime'];
			$retmsg->nextVisibleTime = $msg['nextVisibleTime'];
			$retmsg->dequeueCount = $msg['dequeueCount'];
			$retmsg->firstDequeueTime = $msg['firstDequeueTime'];
			$retMessageList [] = $retmsg;
		}
		return $retMessageList;
	}

	/**
	 * 删除消息
	 *
	 * @param $receipt_handle:最近一次操作该消息返回的临时句柄
	 * @api https://cloud.tencent.com/document/product/406/5840
	 */
	public function delete_message($receipt_handle)
	{
		$params = array('queueName' => $this->queue_name, 'receiptHandle' => $receipt_handle);
		$this->cmq_client->delete_message($params);
	}

	/**
	 * 批量删除消息
	 *
	 * @param $receipt_handle_list:batch_receive_message返回的多条消息的临时句柄
	 * @api https://cloud.tencent.com/document/product/406/5841
	 */
	public function batch_delete_message($receipt_handle_list)
	{
		$params = array('queueName' => $this->queue_name);
		$n = 1;
		foreach ($receipt_handle_list as $receipt_handle) {
			$key = 'receiptHandle.' . $n;
			$params[$key] = $receipt_handle;
			$n += 1;
		}
		$this->cmq_client->batch_delete_message($params);
	}

	protected function __resp2meta__($queue_meta, $resp)
	{
		if (isset($resp['queueName'])) {
			$queue_meta->queueName = $resp['queueName'];
		}
		if (isset($resp['maxMsgHeapNum'])) {
			$queue_meta->maxMsgHeapNum = $resp['maxMsgHeapNum'];
		}
		if (isset($resp['pollingWaitSeconds'])) {
			$queue_meta->pollingWaitSeconds = $resp['pollingWaitSeconds'];
		}
		if (isset($resp['visibilityTimeout'])) {
			$queue_meta->visibilityTimeout = $resp['visibilityTimeout'];
		}
		if (isset($resp['maxMsgSize'])) {
			$queue_meta->maxMsgSize = $resp['maxMsgSize'];
		}
		if (isset($resp['msgRetentionSeconds'])) {
			$queue_meta->msgRetentionSeconds = $resp['msgRetentionSeconds'];
		}
		if (isset($resp['createTime'])) {
			$queue_meta->createTime = $resp['createTime'];
		}
		if (isset($resp['lastModifyTime'])) {
			$queue_meta->lastModifyTime = $resp['lastModifyTime'];
		}
		if (isset($resp['activeMsgNum'])) {
			$queue_meta->activeMsgNum = $resp['activeMsgNum'];
		}
		if (isset($resp['rewindSeconds'])) {
			$queue_meta->rewindSeconds = $resp['rewindSeconds'];
		}
		if (isset($resp['inactiveMsgNum'])) {
			$queue_meta->inactiveMsgNum = $resp['inactiveMsgNum'];
		}
		if (isset($resp['rewindmsgNum'])) {
			$queue_meta->rewindmsgNum = $resp['rewindmsgNum'];
		}
		if (isset($resp['minMsgTime'])) {
			$queue_meta->minMsgTime = $resp['minMsgTime'];
		}
		if (isset($resp['delayMsgNum'])) {
			$queue_meta->delayMsgNum = $resp['delayMsgNum'];
		}
	}
}