<?php

namespace Ouarea\WxCorp;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

class Service
{
    const GET_ACCESS_TOKEN_URL = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken';
    const SEND_MESSAGE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/message/send';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * corp id
     *
     * @var string
     */
    private $corpId;

    public function __construct($corpId, ClientInterface $client = null)
    {
        $this->corpId = $corpId;

        $this->client = $client ?: $this->createDefaultHttpClient();
    }

    /**
     *
     * Get access token from wxcorp
     *
     * @param $corpAppSecret
     *
     * @return array  [accessToken. expiresIn]
     * @throws \Exception
     */
    public function getAccessToken($corpAppSecret)
    {
        $responseObj = $this->sendGetRequestAndDecode($this->buildRequestUrlForGetAccessToken($corpAppSecret));

        if (0 != $responseObj['errcode']) {
            throw new \Exception($responseObj['errmsg']);
        }

        return [$responseObj['access_token'], $responseObj['expires_in']];
    }

    /**
     * Send text message
     *
     * @param $accessToken
     * @param int $agentId
     * @param array|null $touser
     * @param $content
     * @param int $safe
     * @throws \Exception
     */
    public function sendTextMessage($accessToken, $agentId, array $touser = null, $content, $safe = 0)
    {
        list($url, $params) = $this->buildRequestUrlAndParamsForSendTextMessage($accessToken, $agentId, $touser, $content, $safe);
        $responseObj = $this->sendPostRequestAndDecode($url, $params);

        if (0 != $responseObj['errcode']) {
            throw new \Exception($responseObj['errmsg']);
        }
    }

    /**
     * Send text message
     *
     * @param $accessToken
     * @param int $agentId
     * @param array|null $touser
     * @param $title
     * @param $description
     * @param $url
     * @param int $safe
     * @throws \Exception
     */
    public function sendTextCardMessage($accessToken, $agentId, array $touser = null, $title, $description, $url, $safe = 0)
    {
        list($url, $params) = $this->buildRequestUrlAndParamsForSendTextCardMessage($accessToken, $agentId, $touser, $title, $description, $url, $safe);
        $responseObj = $this->sendPostRequestAndDecode($url, $params);

        if (0 != $responseObj['errcode']) {
            throw new \Exception($responseObj['errmsg']);
        }
    }

    private function buildRequestUrlForGetAccessToken($corpAppSecret)
    {
        return self::GET_ACCESS_TOKEN_URL . '?' . http_build_query([
            'corpid'     => $this->corpId,
            'corpsecret' => $corpAppSecret,
        ]);
    }

    /**
     * @param $accesToken
     * @param $agentId
     * @param array|null $touser
     * @param $content
     * @param int $safe
     * @return array  [url, params]
     */
    private function buildRequestUrlAndParamsForSendTextMessage($accesToken, $agentId, array $touser = null, $content, $safe = 0)
    {
        $touser  = empty($touser) ? '@all' : implode('|', $touser);
        $toparty = null;
        $totag   = null;
        $msgType = 'text';

        return [
            self::SEND_MESSAGE_URL . '?access_token=' . $accesToken,
            [
                'touser'  => $touser,
                'toparty' => null,
                'totag'   => null,
                'msgtype' => $msgType,
                'agentid' => $agentId,
                'text'    => ['content' => $content],
                'safe'    => $safe,
            ]
        ];
    }

    /**
     * @param $accesToken
     * @param $agentId
     * @param array|null $touser
     * @param $title
     * @param $description
     * @param $url
     * @param int $safe
     * @return array  [url, params]
     */
    private function buildRequestUrlAndParamsForSendTextCardMessage($accesToken, $agentId, array $touser = null, $title, $description, $url, $safe = 0)
    {
        $touser  = empty($touser) ? '@all' : implode('|', $touser);
        $toparty = null;
        $totag   = null;
        $msgType = 'textcard';

        return [
            self::SEND_MESSAGE_URL . '?access_token=' . $accesToken,
            [
                'touser'   => $touser,
                'toparty'  => null,
                'totag'    => null,
                'msgtype'  => $msgType,
                'agentid'  => $agentId,
                'textcard' => ['title' => $title, 'description' => $description, 'url' => $url],
                'safe'     => $safe,
            ]
        ];
    }

    // issue request and decode the response
    private function sendGetRequestAndDecode($url)
    {
        $options = [
            RequestOptions::TIMEOUT => 500,
            RequestOptions::VERIFY  => false,
        ];

        $response = $this->client->request('GET', $url, $options);
        $responseBody = $response->getBody()->getContents();
        if ($response->getStatusCode() != 200) {
            throw new \Exception(sprintf('微信服务异常: %s', $responseBody));
        }

        if (false === ($response = json_decode($responseBody, true))) {
            throw new \Exception(sprintf('响应异常: %s', $responseBody));
        }

        return $response;
    }

    private function sendPostRequestAndDecode($url, $params)
    {
        $options = [
            RequestOptions::TIMEOUT => 500,
            RequestOptions::VERIFY  => false,
            RequestOptions::BODY    => json_encode($params),
        ];

        $response = $this->client->request('POST', $url, $options);
        $responseBody = $response->getBody()->getContents();
        if ($response->getStatusCode() != 200) {
            throw new \Exception(sprintf('微信服务异常: %s', $responseBody));
        }

        if (false === ($response = json_decode($responseBody, true))) {
            throw new \Exception(sprintf('响应异常: %s', $responseBody));
        }

        return $response;
    }

    /**
     * create default http client
     *
     * @return Client
     */
    private function createDefaultHttpClient()
    {
        return new Client();
    }
}