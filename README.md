# component-builder

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

#
前言
为了让开发者更好的为 topphp 开发组件，我们提供了本指南用于指导开发者进行组件开发，在阅读本指南前，需要您对 topphp和thinkphp 的文档进行仔细的阅读。

# 为什么要开发组件
在 PHP-FPM 架构下的开发，通常在我们需要借助第三方库来解决我们的需求时，都会通过 Composer 来直接引入一个对应的组件，但是有时候第三方库并不能满足我们的需求时,会自己进行一个组件的封装.而且topphp可以在swoole环境下持久化运行,且支持协程,导致了第三方组件会存在一些使用上的问题.
阅读本指南会教会你如何开发一个属于自己的topphp组件.

# 组件开发的环境及准备工作

##### 1.安装骨架项目(topphp/topphp-skeleton):
```shell
git clone https://github.com/topphp/topphp-skeleton.git
```
##### 2. 同级目录下添加`topphp`文件夹

##### 3.进入`topphp文件夹`,并通过组件创建器(topphp/component-builder)进行组件创建:
```shell
# 运行以下命令
$ cd topphp
$ composer create-project topphp/component-builder=dev-master 你的组件名字

composer create-project topphp/component-builder=dev-master topphp-testing
Installing topphp/component-builder (dev-master 63957ce7c3e9e8425c280c0fa4135e847a6d5ec3)
- Installing topphp/component-builder (dev-master 63957ce): Cloning 63957ce7c3 from cache
Created project in topphp-testing
> Topphp\Install\Shell::init
请对组件进行配置
请输入你的组件名称(topphp/demo):topphp/topphp-testing
填写你的组件描述:单元测试组件
请设置你的软件许可证(MIT):
请填写应用命名空间 (Topphp\TopphpTesting):
需要安装 topthink/framework 组件吗? [n]:y
正在删除安装脚本命名空间...
正在删除安装脚本相关composer配置...
删除安装脚本目录
Loading composer repositories with package information
Updating dependencies (including require-dev)
Package operations: 29 installs, 0 updates, 0 removals
- Installing sebastian/version (2.0.1): Loading from cache
- Installing sebastian/resource-operations (2.0.1): Loading from cache
- Installing sebastian/recursion-context (3.0.0): Loading from cache
- Installing sebastian/object-reflector (1.1.1): Loading from cache
- Installing sebastian/object-enumerator (3.0.3): Loading from cache
- Installing sebastian/global-state (2.0.0): Loading from cache
- Installing sebastian/exporter (3.1.2): Loading from cache
- Installing sebastian/environment (4.2.3): Loading from cache
- Installing sebastian/diff (3.0.2): Loading from cache
- Installing sebastian/comparator (3.0.2): Loading from cache
- Installing phpunit/php-timer (2.1.2): Loading from cache
- Installing phpunit/php-text-template (1.2.1): Loading from cache
- Installing phpunit/php-file-iterator (2.0.2): Loading from cache
- Installing theseer/tokenizer (1.1.3): Loading from cache
- Installing sebastian/code-unit-reverse-lookup (1.0.1): Loading from cache
- Installing phpunit/php-token-stream (3.1.1): Loading from cache
- Installing phpunit/php-code-coverage (6.1.4): Loading from cache
- Installing doctrine/instantiator (1.3.0): Loading from cache
- Installing symfony/polyfill-ctype (v1.13.1): Loading from cache
- Installing webmozart/assert (1.6.0): Loading from cache
- Installing phpdocumentor/reflection-common (2.0.0): Loading from cache
- Installing phpdocumentor/type-resolver (1.0.1): Loading from cache
- Installing phpdocumentor/reflection-docblock (5.0.0): Loading from cache
- Installing phpspec/prophecy (v1.10.2): Loading from cache
- Installing phar-io/version (2.0.1): Loading from cache
- Installing phar-io/manifest (1.0.3): Loading from cache
- Installing myclabs/deep-copy (1.9.5): Loading from cache
- Installing phpunit/phpunit (7.5.20): Loading from cache
- Installing squizlabs/php_codesniffer (3.5.4): Loading from cache
sebastian/global-state suggests installing ext-uopz (*)
phpunit/php-code-coverage suggests installing ext-xdebug (^2.6.0)
phpunit/phpunit suggests installing phpunit/php-invoker (^2.0)
phpunit/phpunit suggests installing ext-xdebug (*)
Writing lock file
Generating autoload files
Do you want to remove the existing VCS (.git, .svn..) history? [Y,n]? Y
```
##### 4.这样做的目的是为了让 `topphp-skeleton` 项目可以直接通过 `path` 的形式，让 Composer 直接通过 `topphp` 文件夹内的项目作为依赖项被加载到 `topphp-skelton` 项目的 `vendor` 目录中，我们对 `topphp-skelton` 内的 `composer.json` 文件增加一个 repositories 项，如下：
```json
{
    "repositories": {
        "hyperf": {
            "type": "path",
            "url": "../topphp/*"
        },
        "packagist": {
            "type": "composer",
            "url": "https://mirrors.aliyun.com/composer"
        }
    }
}
```
##### 5.然后删除 `topphp-skeleton` 项目内的 `composer.lock` 文件和 `vendor` 目录，再执行 `composer require 你刚刚安装的组件=dev-master`,或者直接在 `composer.json` 增加如下代码:
```json
{
   "require":{
      "你的组件名字": "dev-master"
  }
}
```
> 然后就可以对组件进行开发了.

#### 注意
> 交互输入必须使用英文半角输入法,否则会出现字符确实.

现代的PHP组件都使用语义版本方案(http://semver.org), 版本号由三个点(.)分数字组成(例如:1.13.2).第一个数字是主版本号,如果PHP组件更新破坏了向后兼容性,会提升主版本号.
第二个数字是次版本号,如果PHP组件小幅更新了功能,而且没有破坏向后兼容性,会提升次版本号.
第三个数字(即最后一个数字)是修订版本号,如果PHP组件修正了向后兼容的缺陷,会提升修订版本号.

## Structure
> 组件结构

```
bin/        
build/
docs/
config/
src/
tests/
vendor/
```


## Install

Via Composer

``` bash
$ composer create-project topphp/component-builder 你的组件名称
```

## Usage

``` php
$skeleton = new topphp\componentBuilder();
echo $skeleton->echoPhrase('Hello, TOPPHP!');
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email sleep@kaituocn.com instead of using the issue tracker.

## Credits

- [topphp][link-author]
- [All Contributors][link-contributors]

## License

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
