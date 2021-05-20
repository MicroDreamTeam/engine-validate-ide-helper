## 说明
此扩展用于生成提示[验证器](https://v.neww7.com)位置的注释

如果你使用了[验证器中间件](https://v.neww7.com/2/Middleware.html),那么所有的验证过程将会在中间件部分完成，传递给控制器的时候已经是处理好的数据。

此扩展将根据 [ValidateMiddlewareConfig](https://v.neww7.com/2/Middleware.html) 中的配置生成如下注释，方便定位验证器
```php
/**
 * @validate {@see UserValidate::sceneLogin()}
 */
```
## 安装
```shell
composer require itwmw/engine-validate-ide-helper --dev
```
## 使用
为了更方便的定位验证器以及字段，可使用
```shell
vendor/bin/validate-ide-helper make:validate-ide [完整命名空间或者完整文件名] --dir [文件目录]
```
如
```shell
vendor/bin/validate-ide-helper make:validate-ide W7\App\Controller\Account\AccountController
```
也可以添加目录，多个目录或者命名空间，使用空格分割
```shell
php validate-ide-helper.php make:validate-ide --dir app/Controller
```
### 软擎
如果你使用的软擎框架，在安装本扩展后，可直接使用命令
```shell
bin/gerent make:validate-ide [完整命名空间或者完整文件名] --dir [文件目录]
```
### Laravel
如果你使用的Laravel框架，在安装本扩展后，可直接使用命令
```shell
php artisan make:validate-ide [完整命名空间或者完整文件名] --dir [文件目录]
```
## WebStorm集成
文件->设置->工具->外部工具->添加
- 名称：`Validate-Ide`或者随意
- 程序:`vendor\bin\validate-ide-helper` Windows选择`vendor\bin\validate-ide-helper.bat`
- 参数:`make:validate-ide $FilePath$`
- 工作目录:`$ProjectFileDir$`

为了更方便的使用，可以给此工具设置一个快捷键

文件->设置->键盘映射->外部工具->Validate-Ide-Helper->右键点击->添加键盘快捷键，作者这里是`ALT+G`