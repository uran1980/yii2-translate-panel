<?php

namespace uran1980\yii\modules\i18n\models\query;

use Yii;
use yii\db\ActiveQuery;
use Zelenin\yii\modules\I18n\models\Message;

class SourceMessageQuery extends ActiveQuery
{
    /**
     * @return \uran1980\yii\modules\i18n\models\query\SourceMessageQuery
     */
    public function notTranslated()
    {
        $table = Message::tableName();
        $query = Message::find()->select("{$table}.id");

        $i = 0;
        foreach (Yii::$app->getI18n()->languages as $language) {
            if ($i === 0) {
                $query->andWhere("{$table}.language = :language and {$table}.translation is not null", [
                    ':language' => $language,
                ]);
            } else {
                $query->innerJoin("{$table} t{$i}", "t{$i}.id = {$table}.id and t{$i}.language = :language and t{$i}.translation is not null", [
                    ':language' => $language,
                ]);
            }
            $i++;
        }

        $ids = $query->indexBy('id')->all();
        $this
            ->andWhere(['not in', "{$table}.id", array_keys($ids)])
            ->andWhere(['not like', 'message', '@@'])
        ;

        return $this;
    }

    /**
     * @return \uran1980\yii\modules\i18n\models\query\SourceMessageQuery
     */
    public function translated()
    {
        $table = Message::tableName();
        $query = Message::find()->select("{$table}.id");
        $i = 0;
        foreach (Yii::$app->getI18n()->languages as $language) {
            if ($i === 0) {
                $query->andWhere("{$table}.language = :language and {$table}.translation is not null", [
                    ':language' => $language,
                ]);
            } else {
                $query->innerJoin("{$table} t{$i}", "t{$i}.id = {$table}.id and t{$i}.language = :language and t{$i}.translation is not null", [
                    ':language' => $language,
                ]);
            }
            $i++;
        }
        $ids = $query->indexBy('id')->all();
        $this
            ->andWhere(['in', "{$table}.id", array_keys($ids)])
            ->andWhere(['not like', 'message', '@@'])
        ;

        return $this;
    }

    /**
     * @return \uran1980\yii\modules\i18n\models\query\SourceMessageQuery
     */
    public function deleted()
    {
        $this->andWhere(['like', 'message', '@@']);

        return $this;
    }
}
