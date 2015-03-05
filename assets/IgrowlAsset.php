<?php

namespace uran1980\yii\modules\i18n\assets;

/**
 * @see http://catc.github.io/iGrowl/
 */
class IgrowlAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@uran1980/yii/modules/i18n/web/bower/iGrowl/dist';
    public $css = [
        'css/igrowl.min.css',
//        'css/fonts/vicons.css',
        'css/fonts/steadysets.css',
//        'css/fonts/linecons.css',
//        'css/fonts/feather.css',
    ];
    public $js = [
        'js/igrowl.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'uran1980\yii\modules\i18n\assets\AnimateCssAsset',
    ];
}
