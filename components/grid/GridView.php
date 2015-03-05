<?php

namespace uran1980\yii\modules\i18n\components\grid;

use uran1980\yii\modules\i18n\components\widgets\LinkPager;
use yii\helpers\ArrayHelper;

class GridView extends \yii\grid\GridView
{
    /**
     * Initializes the grid view.
     * This method will initialize required property values and instantiate [[columns]] objects.
     */
    public function init()
    {
        $this->dataColumnClass  = DataColumn::className();
        $this->summaryOptions   = [
            'class' => 'summary well well-sm pull-left margin-bottom-10px',
        ];
        $this->layout     = '{summary}<div class="clearfix"></div>{pager}{items}{pager}';
        $this->showFooter = true;
        $this->options = ArrayHelper::merge($this->options, [
            'class' => 'grid-view table-responsive',
        ]);
        $this->pager = [
            'class' => LinkPager::className(),
        ];

        parent::init();
    }
}
