<?php

use yii\helpers\Html;
use yii\bootstrap\Tabs;
use uran1980\yii\modules\i18n\Module;

$items = [];

$languages = Yii::$app->i18n->languages;
foreach ( $languages as $lang ) {
    $message = Yii::t($model->category, $model->message, [], $lang);
    $message = ($model->message == $message && Yii::$app->sourceLanguage == $lang)
        ? $model->message : $message;
    $parameters = [
        'id'    => 'message-' . $lang . '-translation',
        'class' => 'translation-textarea form-control',
        'rel'   => $lang,
        'dir'   => (in_array($lang, ['ar', 'fa']) ? 'rtl' : 'ltr'),
        'rows'  => 3,
    ];
    if (isset(Yii::$app->i18n->translations[$model->category]) && !Yii::$app->i18n->translations[$model->category]->forceTranslation && Yii::$app->sourceLanguage == $lang) {
        $parameters['disabled'] = 'disabled';
        $parameters['title']    = Module::t('Please set [forceTranslation] to true to be able to edit this field');
    }
    $items[] = [
        'label' => '<b>' . strtoupper($lang) . '</b>',
        'content' => Html::textarea('Message[' . $lang . '][translation]', $message, $parameters) . Html::hiddenInput('categories[' . $lang . ']', $model->category),
        'active' => ($lang == Yii::$app->language) ? true : false,
    ];
}

echo '<form method="POST" class="translation-save-form">' . Tabs::widget([
    'encodeLabels' => false,
    'items' => $items,
]) . '</form>';
