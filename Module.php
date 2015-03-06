<?php

namespace uran1980\yii\modules\i18n;

use Yii;

class Module extends \yii\base\Module
{
    public $pageSize = 25;

    public static function module()
    {
        return static::getInstance();
    }

    public static function t($message, $params = [], $language = null)
    {
        return Yii::t('uran1980\yii\modules\i18n', $message, $params, $language);
    }
}
