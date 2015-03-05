<?php

namespace uran1980\yii\modules\i18n\assets;

class JqueryFormAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/jquery-form';
    public $js = [
        'jquery.form.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
