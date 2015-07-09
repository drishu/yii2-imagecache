Yii2 Imagecache
===============
the better image cache

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist drishu/yii2-imagecache "*"
```

or add

```
"drishu/yii2-imagecache": "*"
```

to the require section of your `composer.json` file.

### _imageCache_ component config
You should add _imageCache_ component in your application configuration :
```php
$config = [
    'components' => [
      ...
      'imagecache' => [
        'class' => 'drishu\yii2imagecache\ImageCache',
        // the below paths depend very much on your image upload setup
        'sourcePath' => Yii::getAlias('@base'), // base path to your uploads dir
        'cachePath' => '/data', // relative path to your uploads dir
      ],
      ...
    ],
];
```


Usage
-----

In your view, controller, component, etc. just call  :

```php
<?= Html::img(Yii::$app->imagecache->get($image->path, '0x160'))?>```