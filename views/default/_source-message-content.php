<?php

use yii\helpers\Html;
use yii\helpers\Json;

$locations = isset($model->location) ? Json::decode($model->location) : [];
?>
<div class="source-message-content"><?php
    echo Html::a($model->message, ['update', 'id' => $model->id], [
        'data'   => ['pjax' => 0],
        'target' => '_blank',
    ]); ?>
</div>
<?php
if ( is_array($locations) && !empty($locations) ) {
    echo Html::ul(array_unique($locations), [
        'class' => 'trace',
        'item' => function ($location) {
            return "<li>{$location}</li>";
        },
    ]);
}