<?php

// +----------------------------------------------------------------------
// | 日志设置
// +----------------------------------------------------------------------
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
            'access_key_id'     => 'LTAItlZKSCPZaGKr',
            // 使用你的阿里云访问秘钥 AccessKeySecret
            'access_key_secret' => 'hGyA6o02V24Ar3hzxo6AKZayzWiOAq',
            // 创建的项目名称
            'project'           => 'moviecard',
            // 选择与创建 project 所属区域匹配的 Endpoint
            'endpoint'          => 'cn-beijing.log.aliyuncs.com',
            // 创建的日志库名称
            'logstore'          => 'moviecard-dev',
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
