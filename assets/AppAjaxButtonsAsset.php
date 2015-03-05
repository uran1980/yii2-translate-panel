<?php

namespace uran1980\yii\modules\i18n\assets;

class AppAjaxButtonsAsset extends AssetBundle
{
    public $sourcePath = '@uran1980/yii/modules/i18n/web';
    public $js = [
        'js/app-ajax-buttons.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
        'uran1980\yii\modules\i18n\assets\AppIGrowlAsset',
    ];
}
