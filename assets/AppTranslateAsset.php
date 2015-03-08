<?php

namespace uran1980\yii\modules\i18n\assets;

class AppTranslateAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@uran1980/yii/modules/i18n/web';
    public $css = [
        'css/translate.css',
    ];
    public $js = [
        'js/translate.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'uran1980\yii\modules\i18n\assets\JqueryFormAsset',
        'uran1980\yii\modules\i18n\assets\AppAjaxButtonsAsset',
        'uran1980\yii\modules\i18n\assets\AppChosenSelectAsset',
    ];
}
