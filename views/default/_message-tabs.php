<?php

use yii\helpers\Html;
use yii\bootstrap\Tabs;

$items = [];
foreach ( Yii::$app->i18n->languages as $lang ) {
    $message = Yii::t($model->category, $model->message, [], $lang);
    $message = ($model->message == $message && $lang != Yii::$app->i18n->languages[0])
             ? '' : $message;
    $items[] = [
        'label' => '<b>' . strtoupper($lang) . '</b>',
        'content' => Html::textarea('Message[' . $lang . '][translation]', $message, [
            'id'    => 'message-' . $lang . '-translation',
            'class' => 'translation-textarea form-control',
            'rel'   => $lang,
            'dir'   => (in_array($lang, ['ar', 'fa']) ? 'rtl' : 'ltr'),
            'rows'  => 3,
        ]) . Html::hiddenInput('categories[' . $lang . ']', $model->category),
        'active' => ($lang == Yii::$app->language) ? true : false,
    ];
}

echo '<form method="POST" class="translation-save-form">' . Tabs::widget([
    'encodeLabels' => false,
    'items' => $items,
]) . '</form>';
