# topphp-log

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

>这是一个日志管理工具，包含文件存储File、阿里云日志存储Aliyun、Socket日志

## 包含方法

 - 返回TP日志原始句柄 handler 方法
 - 获取日志组件类内部错误信息 getErrorMsg 方法
 - 快速生成格式化日志数据 generateData 方法
 - 非实时记录日志 record 方法
 - 实时记录日志 write 方法（推荐）
 - 八大日志级别快捷方法（debug, info, notice, warning, error, critical, alert, emergency）
 - SQl类型日志 sql 方法
 - 清空内存中的日志信息 clear 方法等

## 组件结构


```
config/     
src/     
tests/
vendor/
```


## 安装

``` bash
    composer require topphp/topphp-log
```

## 日志配置

``` bash
    return [
        // 默认日志记录通道
        'default'      => env('log.channel', 'file'),
        // 日志记录级别
        'level'        => [],
        // 日志类型记录的通道 ['error'=>'email',...]
        'type_channel' => [],
        // 关闭全局日志写入
        'close'        => false,
        // 全局日志处理 支持闭包
        'processor'    => null,
    
        // 日志通道列表
        'channels'     => [
            'file'   => [
                // 日志记录方式
                'type'           => 'File',
                // 日志保存目录（不传默认写入runtime/log内）
                'path'           => '',
                // 单文件日志写入
                'single'         => false,
                // 独立日志级别
                'apart_level'    => [],
                // 最大日志文件数量
                'max_files'      => 0,
                // 使用JSON格式记录
                'json'           => false,
                // 日志处理
                'processor'      => null,
                // 关闭通道日志写入
                'close'          => false,
                // 时间记录格式
                'time_format'    => 'c',
                // 日志输出格式化
                'format'         => '[%s][%s] %s',
                // 是否实时写入
                'realtime_write' => false,
            ],
            // 其它日志通道配置（示例Aliyun）
            'aliyun' => [
                // 日志记录方式
                'type'              => 'Aliyun',
                // 使用你的阿里云访问秘钥 AccessKeyId
                'access_key_id'     => '',
                // 使用你的阿里云访问秘钥 AccessKeySecret
                'access_key_secret' => '',
                // 创建的项目名称
                'project'           => '',
                // 选择与创建 project 所属区域匹配的 Endpoint
                'endpoint'          => '',
                // 创建的日志库名称
                'logstore'          => '',
                // 使用JSON格式记录（存储阿里云建议使用json方式，效果更好）
                'json'              => false,
                // 关闭通道日志写入
                'close'             => false,
                // 时间记录格式
                'time_format'       => 'c',
                // 日志输出格式化
                'format'            => '[%s][%s] %s',
            ],
        ],
    
    ];
```


## 用法

```php
    命名空间引用：
        use Topphp\TopphpLog\Log;
    调用方式有两种：
        1、通过原始句柄 handler 方法操作原始TP日志
            Log::handler($channel)->write("测试日志", "info");
        2、直接调用Topphp日志方法【简单的调用方式以及拥有更多拓展功能，推荐使用】
            Log::record("测试日志", "debug");
            Log::alert("测试级别日志", "aliyun");
            Log::write("测试日志（带请求数据）", "info", "", "订单服务", "支付操作");
        3、我们还可以自定义日志类型
            Log::diy("自定义一个日志类型");
            Log::handler("aliyun")->diy("自定义一个指定通道的日志类型");
    使用须知：
        1、使用多通道方式注意配置好对应的通道配置
        2、默认的日志类已经集成了大部分TP日志方法，并提供兼容保障，更多用法可以参看TP的官方文档
```

## 修改日志

有关最近更改的内容的详细信息，请参阅更改日志（[CHANGELOG](CHANGELOG.md)）。

## 测试

``` bash
    ./vendor/bin/phpunit tests/LogTest.php
```

## 贡献

详情请参阅贡献（[CONTRIBUTING](CONTRIBUTING.md)）和行为准则（[CODE_OF_CONDUCT](CODE_OF_CONDUCT.md)）。


## 安全

如果您发现任何与安全相关的问题，请发送电子邮件至sleep@kaitoocn.com，而不要使用问题跟踪器。

## 信用

- [topphp][link-author]
- [All Contributors][link-contributors]

## 许可证

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/topphp/component-builder.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/topphp/component-builder/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/topphp/component-builder.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/topphp/component-builder.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/topphp/component-builder.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/topphp/component-builder
[link-travis]: https://travis-ci.org/topphp/component-builder
[link-scrutinizer]: https://scrutinizer-ci.com/g/topphp/component-builder/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/topphp/component-builder
[link-downloads]: https://packagist.org/packages/topphp/component-builder
[link-author]: https://github.com/topphp
[link-contributors]: ../../contributors
