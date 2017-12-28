<?php

/**
 * @var View $this
 * @var SourceMessage $model
 */

use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use uran1980\yii\modules\i18n\Module;
use uran1980\yii\modules\i18n\models\SourceMessage;

$this->title = Module::t('Update') . ': ' . $model->message;
$this->params['breadcrumbs'][] = ['label' => Module::t('Translations'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="message-update">
    <div class="message-form">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo Module::t('Source message'); ?></div>
            <div class="panel-body"><?php echo Html::encode($model->message); ?></div>
        </div>
        <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <?php foreach ($model->messages as $language => $message):
            $message->translation = ($model->message == $message && Yii::$app->sourceLanguage == $lang
                ? $model->message : $message->translation);
            echo $form->field($message, '[' . $language . ']translation', ['options' => ['class' => 'form-group col-sm-6']])
                    ->textInput(
                        (isset(Yii::$app->i18n->translations[$model->category]['forceTranslation']) && !Yii::$app->i18n->translations[$model->category]['forceTranslation'] && Yii::$app->sourceLanguage == $language)
                            ? ['disabled' => 'disabled', 'title' => Module::t('Please set [forceTranslation] to true to be able to edit this field')]
                            : []
                    )->label(strtoupper($language));
            endforeach; ?>
        </div>
        <div class="form-group">
            <?php echo
            Html::submitButton(
                $model->getIsNewRecord() ? Module::t('Create') : Module::t('Update'),
                ['class' => $model->getIsNewRecord() ? 'btn btn-success' : 'btn btn-primary']
            ); ?>
        </div>
        <?php $form::end(); ?>
    </div>
</div>
