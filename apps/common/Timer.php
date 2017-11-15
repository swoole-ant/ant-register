<?php
/**
 * Created by PhpStorm.
 * User: shenzhe
 * Date: 2016/11/17
 * Time: 10:51
 */

namespace common;

use sdk\HttpClient;
use sdk\TcpClient;
use sdk\UdpClient;
use ZPHP\Socket\Adapter\Swoole;
use ZPHP\Core\Config as ZConfig;

class Timer
{

    public static function checkPing()
    {
        /**
         * @var \service\ServiceList
         * @desc 定时检测在线服务的状态
         */

        $service = LoadClass::getDao('ServiceList');
        //@TODO 需要优化，如果机器比较多，检测会比较慢
        $host = ZConfig::getField('socket', 'host');
        if ($host == '0.0.0.0') {
            $host = Utils::getLocalIp();
        }
        $key = $host . ':' . ZConfig::getField('socket', 'port');
        Log::info(['start', $key], 'ping');
        $allService = $service->fetchAll(['registerKey=' => "'$key'"]);
        if (!empty($allService)) {
            foreach ($allService as $item) {
                try {
                    if ($item->ip . ':' . $item->port == $key) {
                        continue;
                    }
                    $rpc = null;
                    switch ($item->serverType) {
                        case Swoole::TYPE_TCP:
                            $rpc = new TcpClient($item->ip, $item->port);
                            break;
                        case Swoole::TYPE_UDP:
                            $rpc = new UdpClient($item->ip, $item->port);
                            break;
                        case Swoole::TYPE_HTTP:
                        case Swoole::TYPE_HTTPS:
                        case Swoole::TYPE_WEBSOCKET:
                        case Swoole::TYPE_WEBSOCKETS:
                            $rpc = new HttpClient($item->ip, $item->port);
                            break;
                    }
                    if (!$rpc) {
                        continue;
                    }
                    $result = $rpc->ping(); //发送ping包
                    if (false === $result) { //超时
                        Log::info(['false', $rpc->isConnected(), $item->name, $item->ip, $item->port, $item->status], 'ping');
                        if (1 == $item->status) { //在线状态设置为离线状态
                            $service->update(['status' => 0], ['id=' => $item->id]);
                            LoadClass::getService('Subscriber')->sync($item);
                        }
                        continue;
                    }
                    Log::info(['success', $rpc->isConnected(), $item->name, $item->ip, $item->port, $item->status, $result], 'ping');
                    if ('ant-pong' == $result) {
                        if (0 == $item->status) { //离线状态设置为在线状态
                            //@TODO 可以不单条更新，改为批量更新
                            $service->update(['status' => 1], ['id=' => $item->id]);
                            LoadClass::getService('Subscriber')->sync($item);
                        }
                        continue;
                    }
                } catch (\Exception $e) {
                    //心跳回复失败,设置离线状态
                    Log::info(['fail', $e->getMessage(), $e->getCode(), isset($rpc) ? $rpc->isConnected() : '', $item->name, $item->ip, $item->port, $item->status], 'ping');
                    if (1 == $item->status) {
                        //@TODO 可以不单条更新，改为批量更新
                        $service->update(['status' => 0], ['id=' => $item->id]);
                        LoadClass::getService('Subscriber')->sync($item);
                    }
                }
            }
        }
    }
}