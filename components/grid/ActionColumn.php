<?php

namespace uran1980\yii\modules\i18n\components\grid;

use uran1980\yii\modules\i18n\Module;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class ActionColumn extends \yii\grid\ActionColumn
{
    public function init()
    {
        $this->header = $this->header ?: Module::t('Actions');
        $this->footer = $this->footer ?: Module::t('Actions');
        $this->headerOptions = ArrayHelper::merge([
            'class' => 'text-align-center',
            'width' => '100',
        ], $this->headerOptions);
        $this->footerOptions = ArrayHelper::merge([
            'class' => 'text-align-center font-weight-bold th',
        ], $this->footerOptions);
        $this->contentOptions = ArrayHelper::merge([
            'class' => 'text-align-center nowrap',
        ], $this->contentOptions);

        parent::init();
    }

    /**
     * Initializes the default button rendering callbacks.
     */
    protected function initDefaultButtons()
    {
        if (!isset($this->buttons['view'])) {
            $this->buttons['view'] = function ($url, $model, $key) {
                return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, [
                    'class'     => 'btn btn-xs btn-default',
                    'title'     => Module::t('View'),
                    'data-pjax' => '0',
                ]);
            };
        }
        if (!isset($this->buttons['update'])) {
            $this->buttons['update'] = function ($url, $model, $key) {
                return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                    'class'     => 'btn btn-xs btn-default',
                    'title'     => Module::t('Update'),
                    'data-pjax' => '0',
                ]);
            };
        }
        if ( !isset($this->buttons['save']) ) {
            $this->buttons['save'] = function ($url, $model, $key) {
                return Html::a('<span class="glyphicon glyphicon-download"></span> ' . Module::t('Save'), $url, [
                    'class'     => 'btn btn-xs btn-success',
                    'title'     => Module::t('Save'),
                    'data-pjax' => '0',
                ]);
            };
        }
        if (!isset($this->buttons['delete'])) {
            $this->buttons['delete'] = function ($url, $model, $key) {
                return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                    'class'         => 'btn btn-xs btn-danger margin-left-10px',
                    'title'         => Module::t('Delete'),
                    'data-confirm'  => Module::t('Are you sure you want to delete this item?'),
                    'data-method'   => 'post',
                    'data-pjax'     => '0',
                ]);
            };
        }

    }
}
