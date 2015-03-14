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
        'uran1980\yii\assets\jQueryEssential\JqueryEssentialAsset',
        'uran1980\yii\modules\i18n\assets\AppAjaxButtonsAsset',
        'uran1980\yii\widgets\chosen\ChosenSelectAsset',
    ];
}
