## 简介

iowp-setting 是一个 WordPress Settings API 的集成库，它简化了创建设置页面、部分和字段的过程。这个项目减少了实现 WordPress Settings API 所需的代码量，使得为 WordPress 插件或主题设置自定义选项变得更容易和快捷。

## 安装

你可以通过 Composer 将 iowp-setting 轻松包含到你的项目中。请在项目的根目录下运行以下命令：

```bash
composer require owen0o0/iowp-setting
```

也可以直接下载源码，将 iowp-setting 文件夹复制到你的项目中，通过 `include_once 'src/iset.php';` 引入。

## 使用方法

请参考 `iset-demo.php` 文件。此文件演示了如何使用 iowp-setting 库来创建设置页面、部分和字段。

## 示例

将 iowp-setting 文件夹放入 wordpress 的 plugins 目录下，并启用插件，可以在 wordpress 的设置页面中看到一个名为 "ISET DEMO" 的设置页面。

## 许可

GPLv3 or later