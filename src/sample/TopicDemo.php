<?php
/**
 * 主题模型接口调用DEMO
 *
 * Created by PhpStorm.
 * User: kylinwang@dashenw.com
 * Date: 2018/08/10
 * Time: 15:56
 */

namespace src\sample;


use src\cmq\CMQException\CMQExceptionBase;
use src\cmq\SubscriptionMeta;
use src\cmq\TopicMeta;

class TopicDemo
{
	private $secretId;
	private $secretKey;
	private $endpoint;

	public function __construct($secretId, $secretKey, $endpoint)
	{
		$this->secretId = $secretId;
		$this->secretKey = $secretKey;
		$this->endpoint = $endpoint;
	}

	public function run()
	{
		try {
			// create account and topic
			$topic_name = "Topic-test-php";
			echo "init account \n";
			$my_account = new \Account($this->endpoint, $this->secretId, $this->secretKey);
			$my_topic = $my_account->get_topic($topic_name);
			$my_topicmeta = new TopicMeta();
			$my_topic->create($my_topicmeta);

			echo "get and set topic meta \n";
			$my_topicmeta = $my_topic->get_attributes();
			$my_topicmeta->maxMsgSize = 1024;
			$my_topic->set_attributes($my_topicmeta);
			echo "set attributes\n";
			// list topic
			$topiclist = $my_account->list_topic();
			echo $topiclist;

			// publish message and batch publish message without tags
			$msg = "this is a test message ";
			$msgid = $my_topic->publish_message($msg);
			echo "publish message without tag \n";

			$vmsg = array();
			for ($i = 0; $i < 3; $i++) {
				$msg = "I am test message ";
				$vmsg [] = $msg;
			}
			$vmsgid = $my_topic->batch_publish_message($vmsg);
			echo "batch publish message without tags \n";

			// publish message  with tags
			// tag define
			$vtag = array("test", "cmq", "york");
			$msg = "this is a test message";
			$msgid = $my_topic->publish_message($msg, $vtag);
			echo " publish message with tag \n";
			$vmsg = array();
			for ($i = 0; $i < 3; $i++) {
				$msg = "I am test message " . $i;
				$vmsg [] = $msg;
			}


			$vmsgid = $my_topic->batch_publish_message($vmsg, $vtag);
			echo "batch publish message with tag \n";

			// create subscription
			$subscription_name = "subsc-test34324";
			$my_subscription = $my_account->get_subscription($topic_name, $subscription_name);
			$subscriptionmeta = new SubscriptionMeta();
			// get and set subscription meta
			// please input your endpoint and protocol
			$subscriptionmeta->Endpoint = "";
			$subscriptionmeta->Protocol = "";
			$my_subscription->create($subscriptionmeta);
			echo "create sub \n";


			$subscriptionmeta = $my_subscription->get_attributes();
			echo "get attributes\n";
			echo $subscriptionmeta;
			$my_subscription->set_attributes($subscriptionmeta);

			echo "set attributes\n";
			// list subscription
			$subscriptionlist = $my_topic->list_subscription($topic_name);
			echo $subscriptionlist;
			echo "list subscription \n";
			// delete subscription and topic
			$my_subscription->delete();
			echo "delete subscription \n";
			$my_topic->delete();
			echo "delete topic \n";
		} catch (CMQExceptionBase $e) {
			echo $e;
		}
	}


	public function test()
	{
		// 从腾讯云官网查看云api的密钥信息
		$secretId = "your-qcloud-secretId";
		$secretKey = "your-qcloud-secretKey";
		//请求域名说明：https://cloud.tencent.com/document/product/406/12667
		$endPoint = "https://cmq-topic-sh.api.qcloud.com"; //例如此endpoint为主题模型上海外网请求域名

		$instance = new TopicDemo($secretId, $secretKey, $endPoint);
		$instance->run();
	}
}