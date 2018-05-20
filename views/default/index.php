<?php

/**
 * @var View $this
 */
use uran1980\yii\modules\i18n\Module;
use uran1980\yii\modules\i18n\components\grid\GridView;
use uran1980\yii\modules\i18n\components\grid\SerialColumn;
use uran1980\yii\modules\i18n\components\grid\ActionColumn;
use uran1980\yii\modules\i18n\components\grid\DataColumn;
use uran1980\yii\modules\i18n\models\search\SourceMessageSearch;
use uran1980\yii\modules\i18n\assets\AppTranslateAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;

$searchModel = SourceMessageSearch::getInstance();

$this->title = Module::t('Translations');
$this->params['breadcrumbs'][] = $this->title;

AppTranslateAsset::register($this);
?>

<div class="translations-index">
    <div class="row">
        <div class="col-lg-12">
            <span class="pull-left btn-group">
            <?php   foreach ( [
                        SourceMessageSearch::STATUS_ALL             => Module::t('All'),
                        SourceMessageSearch::STATUS_TRANSLATED      => Module::t('Translated'),
                        SourceMessageSearch::STATUS_NOT_TRANSLATED  => Module::t('Not Translated'),
                        SourceMessageSearch::STATUS_DELETED         => Module::t('Deleted'),
                    ] as $status => $name ) { ?>
                <a class="btn btn-default <?php
                    $params = ArrayHelper::merge(Yii::$app->request->getQueryParams(), [
                        $searchModel->formName() => ['status' => $status],
                    ]);
                    $route = ArrayHelper::merge(['/translations'], $params);
                    echo SourceMessageSearch::isActiveTranslation([
                        'url'       => $route,
                        'current'   => $status,
                    ]); ?>" href="<?php
                    echo Url::to($route); ?>"><?php
                    echo $name; ?></a>
            <?php } ?>
            </span>
        </div>
    </div>
    <h2>
        <?php echo Html::a($this->title, ['/translations']); ?>
        <span class="pull-right btn-group">
            <a class="btn btn-success" href="<?php
                echo Url::to(['/translations/rescan']); ?>"><i class="fa fa-refresh"></i> <?php
                echo Module::t('Rescan'); ?></a>
            <a class="btn btn-warning btn-ajax" action="translation-clear-cache"
               before-send-title="<?php echo Module::t('Request sent'); ?>"
               before-send-message="<?php echo Module::t('Please, wait...'); ?>"
               success-title="<?php echo Module::t('Server Response'); ?>"
               success-message="<?php echo Module::t('Cache successfully cleared.'); ?>"
               href="<?php
                    echo Url::to(['/translations/clear-cache']); ?>"><i class="fa fa-recycle"></i> <?php
                    echo Module::t('Clear Cache'); ?></a>
               <a class="btn btn-danger" href="<?php
               echo Url::to(['/translations/clear-deleted']); ?>"><i class="fa fa-trash"></i> <?php
                   echo Module::t('Clear deleted'); ?></a>
        </span>
    </h2>
    <?php
    echo GridView::widget([
        'filterModel' => $searchModel,
        'dataProvider' => $searchModel->search(Yii::$app->getRequest()->get()),
        'columns' => [
            // ----------------------------- ID --------------------------------
//            [
//                'attribute' => 'id',
//                'headerOptions' => [
//                    'width' => '30',
//                ],
//                'contentOptions' => [
//                    'class' => 'text-align-center',
//                ],
//                'value' => function ($model, $key, $index, $dataColumn) {
//                    return $model->id;
//                },
//                'filter' => false,
////                'visible' => false,
//            ],
            [
                'class' => SerialColumn::className(),
            ],
            // ----------------------- SOURCE MESSAGES -------------------------
            [
                'attribute' => 'message',
                'format' => 'raw',
                'contentOptions' => [
                    'class' => 'source-message',
                ],
                'value' => function ($model, $key, $index, $column) {
                    return $this->render('_source-message-content', [
                        'model'     => $model,
                        'key'       => $key,
                        'index'     => $index,
                        'column'    => $column,
                    ]);
                },
            ],
            // ----------------------- COPY BUTTON -----------------------------
            [
                'class'  => ActionColumn::className(),
                'header' => '<i class="fa fa-copy"></i>',
                'footer' => '<i class="fa fa-copy"></i>',
                'template' => '{copy}',
                'headerOptions' => [
                    'width' => '30',
                ],
                'buttons' => [
                    'copy' => function ($url, $model, $key) {
                        return Html::a('<i class="fa fa-arrow-right "></i>', '', [
                            'class' => 'btn btn-xs btn-default btn-translation-copy-from-source',
                            'title' => Module::t('Copy from source message'),
                        ]);
                    },
                ],
            ],
            // --------------------- MESSAGE TRANSLATIONS ----------------------
            [
                'attribute' => 'translation',
                'headerOptions' => [
                    'width' => '400',
                ],
                'contentOptions' => [
                    'class' => 'translation-tabs tabs-mini',
                ],
                'value' => function ($model, $key, $index, $column) {
                    return $this->render('_message-tabs', [
                        'model'     => $model,
                        'key'       => $key,
                        'index'     => $index,
                        'column'    => $column,
                    ]);
                },
                'format' => 'raw',
            ],
            // --------------------------- CATEGORY ----------------------------
            [
                'attribute' => 'category',
                'headerOptions' => [
                    'width' => '150',
                ],
                'contentOptions' => [
                    'class' => 'text-align-center',
                ],
                'value' => function ($model, $key, $index, $dataColumn) {
                    return $model->category;
                },
                'filter' => ArrayHelper::map($searchModel::getCategories(), 'category', 'category'),
                'filterInputOptions' => DataColumn::$filterOptionsForChosenSelect,
            ],
            // ---------------------------- STATUS -----------------------------
            [
                'attribute' => 'status',
                'headerOptions' => [
                    'width' => '150',
                ],
                'contentOptions' => [
                    'class' => 'text-align-center',
                ],
                'value' => '',
                'filter' => Html::dropDownList(
                    $searchModel->formName() . '[status]',
                    $searchModel->status,
                    $searchModel->getStatus(),
                    DataColumn::$filterOptionsForChosenSelect
                ),
                'visible' => false,
            ],
            // --------------------------- ACTIONS -----------------------------
            [
                'class' => ActionColumn::className(),
                'template' => '{save} {fullscreen} {delete}',
                'buttons' => [
                    'save' => function ($url, $model, $key) {
                        return Html::a('<i class="glyphicon glyphicon-download"></i> ' . Module::t('Save'), $url, [
                            'class'                 => 'btn btn-xs btn-success btn-translation-save',
                            'action'                => 'translation-save',
                            'title'                 => Module::t('Save'),
                            'before-send-title'     => Module::t('Request sent'),
                            'before-send-message'   => Module::t('Please, wait...'),
                            'success-title'         => Module::t('Server Response'),
                            'success-message'       => Module::t('Message successfully saved.'),
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        if ( strstr($model->message, '@@') ) {
                            return '<span class="btn-ajax-wrap">' . Html::a('<i class="glyphicon glyphicon-refresh"></i>', str_replace('delete', 'restore', $url), [
                                'class'                 => 'btn btn-xs btn-info btn-ajax',
                                'action'                => 'translation-restore',
                                'data'                  => [
                                    //'toggle'            => 'confirmation',
                                    'popout'            => true,
                                    'singleton'         => true,
                                    'placement'         => 'left',
                                    'title'             => Module::t('Are you sure you want to restore this item?'),
                                    'method'            => 'post',
                                    'btn-ok-label'      => Module::t('Yes'),
                                    'btn-ok-class'      => 'btn-xs btn-success',
                                    'btn-cancel'        => Module::t('No'),
                                    'btn-cancel-class'  => 'btn-xs btn-warning',
                                ],
                                'before-send-title'     => Module::t('Request sent'),
                                'before-send-message'   => Module::t('Please, wait...'),
                                'success-title'         => Module::t('Server Response'),
                                'success-message'       => Module::t('Message successfully restored.'),
                            ]) . '</span>';
                        } else {
                            return '<span class="btn-ajax-wrap">' . Html::a('<i class="glyphicon glyphicon-trash"></i>', $url, [
                                'class'                 => 'btn btn-xs btn-danger btn-ajax',
                                'action'                => 'translation-delete',
                                'data'                  => [
                                    //'toggle'            => 'confirmation',
                                    'popout'            => true,
                                    'singleton'         => true,
                                    'placement'         => 'left',
                                    'title'             => Module::t('Are you sure you want to restore this item?'),
                                    'method'            => 'post',
                                    'btn-ok-label'      => Module::t('Yes'),
                                    'btn-ok-class'      => 'btn-xs btn-success',
                                    'btn-cancel'        => Module::t('No'),
                                    'btn-cancel-class'  => 'btn-xs btn-warning',
                                ],
                                'before-send-title'     => Module::t('Request sent'),
                                'before-send-message'   => Module::t('Please, wait...'),
                                'success-title'         => Module::t('Server Response'),
                                'success-message'       => Module::t('Message successfully deleted.'),
                            ]) . '</span>';
                        }
                    },
                ],
            ],
            // --------------------------- LOCATIONS ---------------------------
            [
                'attribute' => 'location',
                'value' => function ($model, $key, $index, $dataColumn) {
                    return $model->location;
                },
                'enableSorting' => false,
                'visible' => false,
            ],
        ],
    ]); ?>
</div>
