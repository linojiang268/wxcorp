<?php
/**
 * Created by PhpStorm.
 * User: ouarea
 * Date: 2017/7/6
 * Time: 22:39
 */
require_once __DIR__.'/../vendor/autoload.php';

$testCorpId = '';
$testCorpAppSecret = '';
$testAgentId = '';
$toUsers = null;
$content = 'test message';
$title = '测试卡片';
$description = '恭喜你,获取一张..........卡片.';
$url = '';

$service = new \Ouarea\WxCorp\Service($testCorpId);
list($accessToken, $expiresIn) = $service->getAccessToken($testCorpAppSecret);

echo $accessToken . "\n" . $expiresIn;

$service->sendTextMessage($accessToken, $testAgentId, $toUsers, 'test message');
$service->sendTextCardMessage($accessToken, $testAgentId, $toUsers, $title, $description, $url);
