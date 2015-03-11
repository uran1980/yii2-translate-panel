<?php

namespace uran1980\yii\modules\i18n\assets;

use Yii;

class BootstrapConfirmationAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/bootstrap-confirmation2';
    public $js = [
        'bootstrap-confirmation.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];

    /**
     * Initializes the bundle.
     * If you override this method, make sure you call the parent implementation in the last.
     */
    public function init()
    {
        parent::init();

        $view = Yii::$app->view;
        $js = <<<SCRIPT
jQuery('[data-toggle="confirmation"]').confirmation();
SCRIPT;
        $view->registerJs($js, \yii\web\View::POS_READY);
    }
}
