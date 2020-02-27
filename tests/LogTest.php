<?php

declare(strict_types=1);

namespace Topphp\Test;

use Topphp\TopphpLog\Log;
use Topphp\TopphpTesting\HttpTestCase;

class LogTest extends HttpTestCase
{

    /**
     * 初始化获取配置
     * @author bai
     */
    public function init()
    {
        $logConfig = $this->app->config->get("log");
        if (empty($logConfig)) {
            $ds        = DIRECTORY_SEPARATOR;
            $configDir = dirname(__DIR__) . $ds . "config" . $ds;
            $logConfig = include $configDir . "log.php";
            $this->app->config->set($logConfig, "log");
        }
    }

    /**
     * 测试写入日志
     * @author bai
     */
    public function testLog()
    {
        self::init();

        // 使用默认文件日志通道【file】
        Log::record("测试日志", "debug");// debug

        Log::write("测试日志（带请求数据）", "info", "", "订单服务", "支付操作");// info

        Log::notice("测试级别日志");// notice

        Log::warning("测试级别日志（带请求数据）", "", "订单服务", "支付操作");// warning

        // 使用阿里云日志通道【aliyun】
        Log::record("测试日志", "error", "aliyun");// error

        Log::write("测试日志（带请求数据）", "critical", "aliyun", "用户服务", "登录操作");// critical

        Log::alert("测试级别日志", "aliyun");// alert

        Log::emergency("测试级别日志（带请求数据）", "aliyun", "用户服务", "登录操作");// emergency

        $this->assertTrue(true);
    }

}
