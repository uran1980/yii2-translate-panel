<?php

namespace uran1980\yii\modules\i18n\assets;

class AppChosenSelectAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@uran1980/yii/modules/i18n/web';
    public $js = [
        'js/app-chosen-select.js',
    ];
    public $depends = [
        'uran1980\yii\modules\i18n\assets\ChosenSelectAsset',
    ];
}
