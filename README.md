## 自动生成提示验证器位置的注释
如果你使用了[验证器中间件](https://v.neww7.com/2/Middleware.html),那么所有的验证过程将会在中间件部分完成，传递给控制器的时候已经是处理好的数据。

此扩展将根据[ValidateConfig](https://v.neww7.com/2/Start.html)中的配置生成如下注释，方便定位验证器
```php
/**
 * @validate {@see UserValidate::sceneLogin()}
 */
```
### 使用
为了更方便的定位验证器以及字段，可使用
```shell
php validate-ide-helper.php make:validate-ide [class full name] --dir [path]
```
如
```shell
php validate-ide-helper.php make:validate-ide W7\App\Controller\Account\AccountController
```
也可以添加目录，多个目录或者命名空间，使用空格分割
```shell
php validate-ide-helper.php make:validate-ide --dir app/Controller
```
生成注释

如果你使用的软擎框架，在安装本扩展后，可直接使用命令
```shell
bin/gerent make:validate-ide [class full name] --dir [path]
```
### 安装
```shell
composer require itwmw/engine-validate-ide-helper --dev
```