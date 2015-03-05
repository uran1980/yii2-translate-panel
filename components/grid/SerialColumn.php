<?php

namespace uran1980\yii\modules\i18n\components\grid;

use yii\helpers\ArrayHelper;

class SerialColumn extends \yii\grid\SerialColumn
{
    public function init()
    {
        $this->header = 'ID';
        $this->footer = 'ID';
        $this->headerOptions = ArrayHelper::merge([
            'class' => 'text-align-center',
            'width' => '30',
        ], $this->headerOptions);
        $this->footerOptions = ArrayHelper::merge([
            'class' => 'text-align-center font-weight-bold th',
        ], $this->footerOptions);
        $this->contentOptions = ArrayHelper::merge([
            'class' => 'text-align-center',
        ], $this->contentOptions);
    }
}
