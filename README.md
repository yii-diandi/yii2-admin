Installation
------------

### Install With Composer

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require yii-diandi/yii2-admin "~1.0"
or
php composer.phar require yii-diandi/yii2-admin "~2.0"
```

or for the dev-master

```
php composer.phar require yii-diandi/yii2-admin "2.x-dev"
```

Or, you may add

```
"yii-diandi/yii2-admin": "~2.0"
```

to the require section of your `composer.json` file and execute `php composer.phar update`.

### Install From the Archive

Download the latest release from here [releases](https://github.com/yii-diandi/yii2-admin/releases), then extract it to your project.
In your application config, add the path alias for this extension.

```php
return [
    ...
    'aliases' => [
        '@diandi/admin' => 'path/to/your/extracted',
        // for example: '@diandi/admin' => '@app/extensions/mdm/yii2-admin-2.0.0',
        ...
    ]
];
```

[**More...**](docs/guide/configuration.md)

[screenshots](https://goo.gl/r8RizT)
