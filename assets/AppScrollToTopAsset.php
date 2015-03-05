<?php

namespace uran1980\yii\modules\i18n\assets;

class AppScrollToTopAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@uran1980/yii/modules/i18n/web';
    public $js = [
        'js/app-scroll-to-top.js',
    ];
    public $depends = [
        'uran1980\yii\modules\i18n\assets\ScrollToTopAsset',
    ];
}
