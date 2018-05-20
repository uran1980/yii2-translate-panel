# Yii2 Translate Panel

[Yii2](http://www.yiiframework.com) Translate Panel makes the translation of your application awesome!

This module based on [i18n (internalization) module](https://github.com/zelenin/yii2-i18n-module) with greatly improved usability in mind (see screen shots below).

![Yii2 Translate Panel screens](https://cloud.githubusercontent.com/assets/1616795/6514529/d91e4a1a-c38b-11e4-80d2-3642ccce04d0.png)


## Installation


### Install Yii2

Install and configure [Yii2 App Advanced Template](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/start-installation.md)


### Composer

The preferred way to install this extension is through [Composer](http://getcomposer.org/).

Either run

```
php composer.phar require uran1980/yii2-translate-panel "dev-master"
```

or add

```
"uran1980/yii2-translate-panel": "dev-master"
```

to the require section of your ```composer.json```


## Usage

Configure urlManager and "Yii2 Translate Panel" component in ```common/config/main.php```:

```php
return [
    ...
    'components' => [
        ...
        'urlManager' => [
            'class'             => yii\web\UrlManager::className(),
            'enablePrettyUrl'   => true,
            'showScriptName'    => false, // false - means that index.php will not be part of the URLs
        ],
        'i18n' => [
            'class'      => uran1980\yii\modules\i18n\components\I18N::className(),
            'languages'  => ['en', 'de', 'fr', 'it', 'es', 'pt', 'ru'],
            // Or, if you manage languages in database
            //'languages'  => function() {
            //    /* /!\ Make sure the result is a mere list of language codes, and the
            //     * one used in views is the first one */
            //    return \namespace\of\your\LanguageClass::find()->where(['active' => true'])->orderBy('default' => SORT_DESC])->select('code')->column();
            //},
            'format'     => 'db',
            'sourcePath' => [
                __DIR__ . '/../../frontend',
                __DIR__ . '/../../backend',
                __DIR__ . '/../../common',
            ],
            'messagePath' => __DIR__  . '/../../messages',
            // Whether database messages are to be used instead of view ones.
            // Enables editing messages in locale specified by
            // Yii::$app->sourceLanguage
            // Can be set per translation category too
            //'forceTranslation' => true,
            'translations' => [
                '*' => [
                    'class'           => yii\i18n\DbMessageSource::className(),
                    'enableCaching'   => true,
                    'cachingDuration' => 60 * 60 * 2, // cache on 2 hours
                    // Whether database messages are to be used instead of view
                    // ones. Enables editing messages in view code locale.
                    // Can be set globally too.
                    //'forceTranslation' => true,
                ],
            ],
        ],
        ...
    ],
    ...
];
```

Configure "Yii2 Translate Panel" module in ```backend/config/main.php```:

```php
return [
    ...
    'modules' => [
        ...
        'i18n' => [
            'class' => uran1980\yii\modules\i18n\Module::className(),
            'controllerMap' => [
                'default' => uran1980\yii\modules\i18n\controllers\DefaultController::className(),
            ],
            // example for set access control to module (if required):
            'as access' => [
                'class' => yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'controllers'   => ['i18n/default'],
                        'actions'       => ['index', 'save', 'update', 'rescan', 'clear-cache', 'delete', 'restore', 'clear-deleted'],
                        'allow'         => true,
                        'roles'         => ['translator'],
                    ]
                ],
            ],
        ],
        ...
    ],
    ...
]
```

Run:

```
php yii migrate --migrationPath=@uran1980/yii/modules/i18n/migrations
```

Go to ```http://backend.yourdomain.com/translations``` for translating your messages


### PHP to DB import

If you have an old project with PHP-based i18n you may migrate to DbSource via console.

Run:

```
php yii i18n/import @common/messages
```

where ```@common/messages``` is path for app translations


### DB to PHP export

Run:

```
php yii i18n/export @uran1980/yii/modules/i18n/messages uran1980/modules/i18n
```

where ```@uran1980/yii/modules/i18n/messages``` is path for app translations and ```uran1980/modules/i18n``` is translations category in DB


### Using ```yii``` category with DB

Import translations from PHP files

```
php yii i18n/import @yii/messages
```


## Info

Component uses yii\i18n\MissingTranslationEvent for auto-add of missing translations to database

See [Yii2 i18n guide](https://github.com/yiisoft/yii2/blob/master/docs/guide/tutorial-i18n.md)


## Author

[Ivan Yakovlev](https://github.com/uran1980/), e-mail: [uran1980@gmail.com](mailto:uran1980@gmail.com)
