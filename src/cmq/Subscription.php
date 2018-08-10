<?php
/**
 * Created by PhpStorm.
 * User: kylinwang@dashenw.com
 * Date: 2018/08/10
 * Time: 15:36
 */

namespace src\cmq;


class Subscription
{
	private $topic_name;
	private $subscription_name;
	private $cmq_client;
	private $encoding;

	public function __construct($topic_name, $subscription_name, $cmq_client, $encoding = false)
	{
		$this->topic_name = $topic_name;
		$this->subscription_name = $subscription_name;
		$this->cmq_client = $cmq_client;
		$this->encoding = $encoding;
	}

	public function set_encoding($encoding)
	{
		$this->encoding = $encoding;
	}

	/**
	 * 创建订阅
	 *
	 * @param $subscription_meta
	 * @api https://cloud.tencent.com/document/product/406/7414
	 */
	public function create($subscription_meta)
	{
		$params = array(
			'topicName' => $this->topic_name,
			'subscriptionName' => $this->subscription_name,
			'notifyStrategy' => $subscription_meta->NotifyStrategy,
			'notifyContentFormat' => $subscription_meta->NotifyContentFormat,
		);
		if ($subscription_meta->Endpoint != "") {
			$params['endpoint'] = $subscription_meta->Endpoint;
		}
		if ($subscription_meta->Protocol != "") {
			$params['protocol'] = $subscription_meta->Protocol;
		}

		if (!$subscription_meta->bindindKey != null && is_array($subscription_meta->bindindKey) && !empty($subscription_meta->bindindKey)) {
			$n = 1;
			foreach ($subscription_meta->bindindKey as $tag) {
				$key = 'bindindKey.' . $n;
				$params[$key] = $tag;
				$n += 1;
			}
		}

		if (!$subscription_meta->FilterTag != null && is_array($subscription_meta->FilterTag) && !empty($subscription_meta->FilterTag)) {
			$n = 1;
			foreach ($subscription_meta->FilterTag as $tag) {
				$key = 'filterTag.' . $n;
				$params[$key] = $tag;
				$n += 1;
			}
		}
		$this->cmq_client->create_subscription($params);
	}

	/**
	 * 删除订阅
	 *
	 * @api https://cloud.tencent.com/document/product/406/7417
	 */
	public function delete()
	{
		$params = array(
			'topicName' => $this->topic_name,
			'subscriptionName' => $this->subscription_name
		);

		$this->cmq_client->delete_subscription($params);
	}

	/**
	 * 清空订阅标签
	 *
	 * @api https://cloud.tencent.com/document/product/406/8406
	 */
	public function clearFilterTags()
	{
		$params = array(
			'topicName' => $this->topic_name,
			'subscriptionName' => $this->subscription_name
		);

		$this->cmq_client->clear_filterTags($params);
	}

	/**
	 * 获取订阅属性
	 *
	 * @return SubscriptionMeta
	 * @api https://cloud.tencent.com/document/product/406/7418
	 */
	public function get_attributes()
	{
		$params = array(
			'topicName' => $this->topic_name,
			'subscriptionName' => $this->subscription_name
		);

		$resp = $this->cmq_client->get_subscription_attributes($params);

		$subscription_meta = new SubscriptionMeta();
		$this->__resp2meta($subscription_meta, $resp);
		return $subscription_meta;
	}

	/**
	 * 修改订阅属性
	 *
	 * @param $subscription_meta
	 * @api https://cloud.tencent.com/document/product/406/7416
	 */
	public function set_attributes($subscription_meta)
	{
		$params = array(
			'topicName' => $this->topic_name,
			'subscriptionName' => $this->subscription_name
		);
		if ($subscription_meta->NotifyStrategy != "") {
			$params['notifyStrategy'] = $subscription_meta->NotifyStrategy;
		}

		if ($subscription_meta->NotifyContentFormat != "") {
			$params['notifyContentFormat'] = $subscription_meta->NotifyContentFormat;
		}


		if ($subscription_meta->Endpoint != "") {
			$params['endpoint'] = $subscription_meta->Endpoint;
		}
		if ($subscription_meta->Protocol != "") {
			$params['protocol'] = $subscription_meta->Protocol;
		}

		if (!$subscription_meta->bindindKey != null && is_array($subscription_meta->bindindKey) && !empty($subscription_meta->bindindKey)) {
			$n = 1;
			foreach ($subscription_meta->bindindKey as $tag) {
				$key = 'bindindKey.' . $n;
				$params[$key] = $tag;
				$n += 1;
			}
		}

		if (!$subscription_meta->FilterTag != null && is_array($subscription_meta->FilterTag) && !empty($subscription_meta->FilterTag)) {
			$n = 1;
			foreach ($subscription_meta->FilterTag as $tag) {
				$key = 'filterTag.' . $n;
				$params[$key] = $tag;
				$n += 1;
			}
		}

		$this->cmq_client->set_subscription_attributes($params);

	}

	protected function __resp2meta($subscription_meta, $resp)
	{
		if (isset($resp['endpoint'])) {
			$subscription_meta->Endpoint = $resp['endpoint'];
		}
		if (isset($resp['protocol'])) {
			$subscription_meta->Protocol = $resp['protocol'];
		}
		if (isset($resp['notifyStrategy'])) {
			$subscription_meta->NotifyStrategy = $resp['notifyStrategy'];
		}
		if (isset($resp['notifyContentFormat'])) {
			$subscription_meta->NotifyContentFormat = $resp['notifyContentFormat'];
		}

		if (isset($resp['bindindKey'])) {
			foreach ($resp['bindindKey'] as $tag) {
				array_push($subscription_meta->bindindKey, $tag);
			}
		}

		if (isset($resp['filterTags'])) {
			foreach ($resp['filterTags'] as $tag) {
				array_push($subscription_meta->FilterTag, $tag);
			}
		}

	}
}