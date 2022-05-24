# Filesite.io core lib

FSC is the core lib of filesite.io, a small PHP Framework.


## Controllers and Actions

Please put controller files in the directory **controller/**,
which are extends from the base **Controller**.

Please name public actions like **actionIndex** in your controller file,
which are using the prefix **action**.

Examples:
```
controller/SiteController.php
controller/CommandController.php
```


## Layout and Views

Please put layout files in the directory **views/layout/**,
and create diretories for your views,
such as **views/site/**.


## App config

Please set configs in the file **conf/app.php**.

You can use ```FSC::$app['config']``` in controller and views.

Example in views:

```
print_r(FSC::$app['config']);
```


## View data

You can pass parameters to view with an array, like:

```
$viewName = 'index';
$params = compact('foo', 'bar');
return $this->render($viewName, $params);
```

And you can visit parameters with the variable ```$viewData``` in views.

Example in view:

```
echo "I'm foo {$viewData['foo']}.";
print_r($viewData);
```


## Commands

You can add functions in the command controller ```controller/CommandController.php```,
and run the command to execute it:

```
php bin/command.php
php bin/command.php "test" "foo=bar"
```


## Nginx config example

Please check the file [Nginx.conf.md](./Nginx.conf.md)
