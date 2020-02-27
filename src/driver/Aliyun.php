<?php
/**
 * 凯拓软件 [临渊羡鱼不如退而结网,凯拓与你一同成长]
 * Project: topphp-client
 * Date: 2020/2/25 10:30
 * Author: bai <sleep@kaituocn.com>
 */
declare(strict_types=1);

namespace Topphp\TopphpLog\driver;

use think\contract\LogHandlerInterface;
use think\facade\Log as Logger;
use Topphp\TopphpLog\Log;

class Aliyun extends Log implements LogHandlerInterface
{

    private $logData;

    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        'time_format'       => 'c',
        'json'              => false,
        'json_options'      => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        'format'            => '[%s][%s] %s',
        'type'              => 'Aliyun',
        'access_key_id'     => '',
        'access_key_secret' => '',
        'project'           => '',
        'endpoint'          => '',
        'logstore'          => '',
    ];

    /**
     * 构造基本配置
     * Aliyun constructor.
     */
    public function __construct()
    {
        $logConfig    = Logger::getConfig();
        $aliyunConfig = $logConfig['channels'][parent::$channel];
        $this->config = array_merge($this->config, $aliyunConfig);
    }

    /**
     * 格式化数据（保持与TP文件存储一样的数据样式）
     * @param array $log
     * @return array
     * @author bai
     */
    private function formatLog(array $log)
    {
        $info = [];
        $time = date($this->config['time_format']);
        foreach ($log as $level => $item) {
            $message = [];
            foreach ($item as $k => $msg) {
                $service = "";
                if (!is_string($msg)) {
                    if (is_array($msg) && parent::$isFormatData === true) {
                        $service = $msg['service'];
                    }
                    $msg = var_export($msg, true);
                }
                // 服务
                $message[$k]['service'] = $service;
                // 日志数据
                $message[$k]['data'] = $this->config['json'] ?
                    json_encode(['time' => $time, 'type' => $level, 'msg' => $msg], $this->config['json_options']) :
                    sprintf($this->config['format'], $time, $level, $msg);
            }
            $info[$level] = $message;
        }
        return $info;
    }

    /**
     * 发送日志
     * @param array $contents
     * @return bool
     * @throws \Aliyun_Log_Exception
     * @author bai
     */
    private function sendAliyun(array $contents)
    {
        foreach ($contents as $type => $msg) {
            foreach ($msg as $item) {
                $service = $item['service'];
                // 校验数据
                if (is_string($item['data'])) {
                    if (parent::$isFormatData === true && $this->config['json'] === true) {
                        $json     = json_decode($item['data'], true);
                        $jsonMsg  = $json['msg'];
                        $logMixed = json_decode($jsonMsg, true);
                        if (is_string($logMixed)) {
                            $logData['log-data'] = $logMixed;
                        } elseif (is_array($logMixed)) {
                            foreach ($logMixed as &$child) {
                                if (is_array($child)) {
                                    $child = json_encode($child, $this->config['json_options']);
                                }
                            }
                            $logData = $logMixed;
                        } else {
                            $logData['log-data'] = var_export($logMixed, true);
                        }
                        $logData['time'] = $json['time'];
                        $logData['type'] = $json['type'];
                    } else {
                        $logData['log-data'] = $item['data'];
                    }
                } else {
                    $logData = $item['data'];
                }
                $conf = [];
                // 配置
                $accessKeyId = $conf['access_key_id'] = $this->config['access_key_id']; // 使用你的阿里云访问秘钥 AccessKeyId
                $accessKey   = $conf['access_key_secret']
                    = $this->config['access_key_secret']; // 使用你的阿里云访问秘钥 AccessKeySecret
                $project     = $conf['project'] = $this->config['project']; // 创建的项目名称
                $endpoint    = $conf['endpoint'] = $this->config['endpoint']; // 选择与创建 project 所属区域匹配的 Endpoint
                $logstore    = $conf['logstore'] = $this->config['logstore']; // 创建的日志库名称
                // 校验配置
                foreach ($conf as $key => $val) {
                    if (empty($val)) {
                        parent::$errorLog = "Log config param [ " . $key . " ] is empty";
                        return false;
                    }
                }
                // 写入数据
                $topic    = $type; // 日志级别
                $source   = empty($service) ? "app" : $service; // 通道或模块
                $client   = new \Aliyun_Log_Client($endpoint, $accessKeyId, $accessKey);
                $req      = new \Aliyun_Log_Models_ListLogstoresRequest($project);
                $res      = $client->listLogstores($req);
                $logitems = [];
                $logItem  = new \Aliyun_Log_Models_LogItem();
                $logItem->setTime(time());
                $logItem->setContents($logData);// 这里设置的数据必须是一围数组
                array_push($logitems, $logItem);
                $req2 = new \Aliyun_Log_Models_PutLogsRequest($project, $logstore, $topic, $source, $logitems);
                $res2 = $client->putLogs($req2);
                return true;
            }
        }
    }

    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @return bool
     */
    public function save(array $log): bool
    {
        try {
            $this->logData = $this->formatLog($log);
            $res           = true;
            if ($this->logData) {
                $res = $this->sendAliyun($this->logData);
            }
            return $res;
        } catch (\Exception $e) {
            parent::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }
}
