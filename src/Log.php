<?php
/**
 * 凯拓软件 [临渊羡鱼不如退而结网,凯拓与你一同成长]
 * Project: topphp-client
 * Date: 2020/2/25 10:30
 * Author: bai <sleep@kaituocn.com>
 */
declare(strict_types=1);


namespace Topphp\TopphpLog;

use think\console\Output;
use think\facade\Config;
use think\facade\Log as Logger;
use think\helper\Str;

class Log
{

    /**
     * 扩展驱动名
     */
    const DRIVER_EXTEND = [
        "Aliyun",
        "Redis"
    ];

    /**
     * 通道
     * @var $channel
     */
    protected static $channel;

    /**
     * 通道Driver
     * @var $channelType
     */
    protected static $channelType;

    /**
     * 是否是格式化的重组数据
     * @var $isFormatData
     */
    protected static $isFormatData;

    /**
     * 错误信息
     * @var $errorLog
     */
    protected static $errorLog;

    /**
     * Swoole服务debug模式下日志同时打印到控制台
     * @param mixed $data
     * @return bool
     * @author bai
     */
    private static function consoleDump($data)
    {
        try {
            if (env('APP_DEBUG')) {
                // json中文乱码加 JSON_UNESCAPED_UNICODE
                $msg    = json_encode($data,JSON_UNESCAPED_UNICODE);
                $date   = new \DateTime();
                $output = app(Output::class);
                $level  = Str::upper(debug_backtrace()[1]['function']);
                $output->info("[{$date->format('Y-m-d H:i:s.u')}][{$level}] {$msg}");
                return true;
            }
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
        return true;
    }

    /**
     * 格式化通道Driver
     * @param string $channel
     * @author bai
     */
    private static function formatChannel($channel = "")
    {
        if (empty($channel)) {
            $channel = Config::get("log.default");
        }
        self::$channel = $channel;
        $type          = "unknow";
        if (isset(Logger::getChannelConfig($channel)['type'])) {
            $type = Logger::getChannelConfig($channel)['type'];
        }
        if (in_array($type, self::DRIVER_EXTEND)) {
            self::$channelType = "\\Topphp\\TopphpLog\\driver\\" . $type;
        } else {
            self::$channelType = $type;
        }
        $channelsConfig                         = Logger::getConfig("channels");
        $channelsConfig[self::$channel]['type'] = self::$channelType;
        Config::set(['channels' => $channelsConfig], 'log');
    }

    /**
     * 整理数据
     * @param string $service 服务 如：订单服务 order
     * @param string $operate 操作 如：支付操作 pay
     * @param mixed $data 数据 默认空数组
     * @param string $channel 通道 不传使用配置的默认通道
     * @return mixed
     * @author bai
     */
    public static function generateData(string $service, string $operate, $data = [], string $channel = "")
    {
        if (empty($channel)) {
            $channel = Config::get("log.default");
        }
        $isJson = false;
        if (isset(Logger::getChannelConfig($channel)['json'])) {
            $isJson = Logger::getChannelConfig($channel)['json'];
        }
        if (!empty($service)) {
            self::$isFormatData = true;
            $param              = empty(request()->param()) ? "" : request()->param();
            $headers            = request()->header();
            if (empty($headers)) {
                $url = "is Command";
            } else {
                $url = request()->domain() . request()->url();
            }
            $returnData = [
                "service"  => $service,
                "operate"  => $operate,
                "log-data" => $data,
                'headers'  => $headers,
                "method"   => empty(request()->method()) ? "is Command 命令行" : request()->method(),
                "param"    => $param,
                "url"      => $url,
            ];
            if ($isJson) {
                $returnData = json_encode($returnData, JSON_UNESCAPED_UNICODE);
            }
            return $returnData;
        } else {
            if ($isJson) {
                $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            }
            return $data;
        }
    }

    /**
     * 透传TP日志方法
     * @param $name
     * @param $arguments
     * @return mixed
     * @author bai
     */
    public static function __callStatic($name, $arguments)
    {
        return Logger::$name(...$arguments);
    }

    /**
     * 获取日志类内部错误信息
     * @return mixed
     * @author bai
     */
    public static function getErrorMsg()
    {
        return self::$errorLog;
    }

    //*************************************** --- 助手函数 --- *******************************************//

    /**
     * 返回TP日志原始句柄
     * @param null $channelName 日志通道名称，不传使用配置的默认通道
     * @return bool|\think\log\Channel|\think\log\ChannelSet
     * @author bai
     */
    public static function handler($channelName = null)
    {
        try {
            self::formatChannel($channelName);
            return Logger::channel(self::$channel);
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

    /**
     * * record 方式在fpm下为不是实时保存的（与tp6官方文档一致）
     *           在swoole的http环境下为实时保存的
     *
     * @param mixed $msg 日志内容，支持字符串和数组
     * @param string $level 日志级别，包含 debug, info, notice, warning, error, critical, alert, emergency
     * @param string $channel 通道（可选），相当于可配置不同级别的记录配置或者是不同业务使用的日志存储方式
     * @param string $service 服务 如：订单服务 order（可选，传入参数将会记录请求详细信息，不传默认透传日志数据）
     * @param string $operate 操作 如：支付操作 pay（可选）
     * @param array $context 上下文替换（可选，一般没用）
     * @return bool|\think\log\Channel|\think\log\ChannelSet
     * @author bai
     */
    public static function record(
        $msg,
        string $level,
        string $channel = "",
        string $service = "",
        string $operate = "",
        array $context = []
    ) {
        try {
            self::formatChannel($channel);
            $data = self::generateData($service, $operate, $msg, $channel);
            self::consoleDump($data);
            if (false !== strpos(self::$channelType, '\\')) {
                return Logger::channel(self::$channel)->write($data, $level, $context);
            }
            return Logger::channel(self::$channel)->record($data, $level, $context);
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

    /**
     * write 实时写入日志（推荐）
     * @param mixed $msg 日志内容
     * @param string $level 日志级别
     * @param string $service 服务
     * @param string $operate 操作
     * @param string $channel 通道
     * @param array $context 上下文替换
     * @return bool|\think\log\Channel|\think\log\ChannelSet
     * @author bai
     */
    public static function write(
        $msg,
        string $level,
        string $service = "",
        string $operate = "",
        string $channel = "",
        array $context = []
    ) {
        try {
            self::formatChannel($channel);
            $data = self::generateData($service, $operate, $msg, $channel);
            self::consoleDump($data);
            return Logger::channel(self::$channel)->write($data, $level, $context);
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

    /**
     * emergency 级别日志
     * @param mixed $msg 日志内容
     * @param string $service 服务
     * @param string $operate 操作
     * @param string $channel 通道
     * @param array $context 上下文替换
     * @return bool
     * @author bai
     */
    public static function emergency(
        $msg,
        string $service = "",
        string $operate = "",
        string $channel = "",
        array $context = []
    ) {
        try {
            self::formatChannel($channel);
            $data = self::generateData($service, $operate, $msg, $channel);
            self::consoleDump($data);
            Logger::channel(self::$channel)->write($data, "emergency", $context);
            return true;
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

    /**
     * alert 级别日志
     * @param mixed $msg 日志内容
     * @param string $service 服务
     * @param string $operate 操作
     * @param string $channel 通道
     * @param array $context 上下文替换
     * @return bool
     * @author bai
     */
    public static function alert(
        $msg,
        string $service = "",
        string $operate = "",
        string $channel = "",
        array $context = []
    ) {
        try {
            self::formatChannel($channel);
            $data = self::generateData($service, $operate, $msg, $channel);
            self::consoleDump($data);
            Logger::channel(self::$channel)->write($data, "alert", $context);
            return true;
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

    /**
     * critical 级别日志
     * @param mixed $msg 日志内容
     * @param string $service 服务
     * @param string $operate 操作
     * @param string $channel 通道
     * @param array $context 上下文替换
     * @return bool
     * @author bai
     */
    public static function critical(
        $msg,
        string $service = "",
        string $operate = "",
        string $channel = "",
        array $context = []
    ) {
        try {
            self::formatChannel($channel);
            $data = self::generateData($service, $operate, $msg, $channel);
            self::consoleDump($data);
            Logger::channel(self::$channel)->write($data, "critical", $context);
            return true;
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

    /**
     * error 级别日志
     * @param mixed $msg 日志内容
     * @param string $service 服务
     * @param string $operate 操作
     * @param string $channel 通道
     * @param array $context 上下文替换
     * @return bool
     * @author bai
     */
    public static function error(
        $msg,
        string $service = "",
        string $operate = "",
        string $channel = "",
        array $context = []
    ) {
        try {
            self::formatChannel($channel);
            $data = self::generateData($service, $operate, $msg, $channel);
            self::consoleDump($data);
            Logger::channel(self::$channel)->write($data, "error", $context);
            return true;
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

    /**
     * warning 级别日志
     * @param mixed $msg 日志内容
     * @param string $service 服务
     * @param string $operate 操作
     * @param string $channel 通道
     * @param array $context 上下文替换
     * @return bool
     * @author bai
     */
    public static function warning(
        $msg,
        string $service = "",
        string $operate = "",
        string $channel = "",
        array $context = []
    ) {
        try {
            self::formatChannel($channel);
            $data = self::generateData($service, $operate, $msg, $channel);
            self::consoleDump($data);
            Logger::channel(self::$channel)->write($data, "warning", $context);
            return true;
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

    /**
     * notice 级别日志
     * @param mixed $msg 日志内容
     * @param string $service 服务
     * @param string $operate 操作
     * @param string $channel 通道
     * @param array $context 上下文替换
     * @return bool
     * @author bai
     */
    public static function notice(
        $msg,
        string $service = "",
        string $operate = "",
        string $channel = "",
        array $context = []
    ) {
        try {
            self::formatChannel($channel);
            $data = self::generateData($service, $operate, $msg, $channel);
            self::consoleDump($data);
            Logger::channel(self::$channel)->write($data, "notice", $context);
            return true;
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

    /**
     * info 级别日志
     * @param mixed $msg 日志内容
     * @param string $service 服务
     * @param string $operate 操作
     * @param string $channel 通道
     * @param array $context 上下文替换
     * @return bool
     * @author bai
     */
    public static function info(
        $msg,
        string $service = "",
        string $operate = "",
        string $channel = "",
        array $context = []
    ) {
        try {
            self::formatChannel($channel);
            $data = self::generateData($service, $operate, $msg, $channel);
            self::consoleDump($data);
            Logger::channel(self::$channel)->write($data, "info", $context);
            return true;
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

    /**
     * debug 级别日志
     * @param mixed $msg 日志内容
     * @param string $service 服务
     * @param string $operate 操作
     * @param string $channel 通道
     * @param array $context 上下文替换
     * @return bool
     * @author bai
     */
    public static function debug(
        $msg,
        string $service = "",
        string $operate = "",
        string $channel = "",
        array $context = []
    ) {
        try {
            self::formatChannel($channel);
            $data = self::generateData($service, $operate, $msg, $channel);
            self::consoleDump($data);
            Logger::channel(self::$channel)->write($data, "debug", $context);
            return true;
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

    /**
     * sql 日志
     * @param mixed $msg 日志内容
     * @param string $service 服务
     * @param string $operate 操作
     * @param string $channel 通道
     * @param array $context 上下文替换
     * @return bool
     * @author bai
     */
    public static function sql(
        $msg,
        string $service = "",
        string $operate = "",
        string $channel = "",
        array $context = []
    ) {
        try {
            self::formatChannel($channel);
            $data = self::generateData($service, $operate, $msg, $channel);
            self::consoleDump($data);
            Logger::channel(self::$channel)->write($data, "sql", $context);
            return true;
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

    /**
     * 清空日志信息
     * @param string $channel 通道
     * @return bool|Logger
     * @author bai
     */
    public static function clear(string $channel = "*")
    {
        try {
            self::formatChannel($channel);
            return Logger::clear(self::$channel);
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

    /**
     * 关闭本次请求日志写入
     * @param string $channel 通道
     * @return bool|Logger
     * @author bai
     */
    public static function close(string $channel = "*")
    {
        try {
            self::formatChannel($channel);
            return Logger::close(self::$channel);
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

    /**
     * 获取日志信息（只能获取到内存中未保存的日志信息，用处不大）
     * @param string $channel 通道
     * @return bool|array
     * @author bai
     */
    public static function getLog(string $channel = null)
    {
        try {
            self::formatChannel($channel);
            return Logger::getLog(self::$channel);
        } catch (\Exception $e) {
            self::$errorLog = "[" . $e->getLine() . "]" . $e->getMessage() . " @ " . $e->getFile();
            return false;
        }
    }

}
