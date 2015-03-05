<?php

namespace uran1980\yii\modules\i18n\assets;

class AppSpoilersAsset extends AssetBundle
{
    public $sourcePath = '@uran1980/yii/modules/i18n/web';
    public $js = [
        'js/app-spoilers.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
