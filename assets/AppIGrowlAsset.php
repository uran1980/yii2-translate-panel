<?php

namespace uran1980\yii\modules\i18n\assets;

class AppIGrowlAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@uran1980/yii/modules/i18n/web';
    public $css = [
        'css/app-igrowl.css',
    ];
    public $depends = [
        'uran1980\yii\modules\i18n\assets\IgrowlAsset',
    ];
}
