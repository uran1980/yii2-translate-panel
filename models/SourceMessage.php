<?php

namespace uran1980\yii\modules\i18n\models;

use yii\base\InvalidConfigException;
use Yii;
use yii\db\ActiveRecord;
use uran1980\yii\modules\i18n\Module;
use uran1980\yii\modules\i18n\models\query\SourceMessageQuery;
use Zelenin\yii\modules\I18n\models\Message;

/**
 * SourceMessage model
 *
 * @property integer    $id
 * @property string     $hash
 * @property string     $category
 * @property string     $message
 * @property string     $location
 */
class SourceMessage extends ActiveRecord
{
    /**
     * @return string
     * @throws InvalidConfigException
     */
    public static function tableName()
    {
        $i18n = Yii::$app->getI18n();
        if (!isset($i18n->sourceMessageTable)) {
            throw new InvalidConfigException('You should configure i18n component');
        }
        return $i18n->sourceMessageTable;
    }

    public static function find()
    {
        return new SourceMessageQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['message', 'string'],
            ['translation', 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => Module::t('ID'),
            'category'      => Module::t('Category'),
            'message'       => Module::t('Source Messages'),
            'translation'   => Module::t('Message Translations'),
            'status'        => Module::t('Translation status')
        ];
    }

    /**
     * ActiveRelation
     * @see http://www.yiiframework.com/wiki/621/filter-sort-by-calculated-related-fields-in-gridview-yii-2-0/#hh11
     * @see http://www.ramirezcobos.com/2014/04/16/displaying-sorting-and-filtering-model-relations-on-a-gridview-yii2/
     */
    public function getMessages()
    {
        return $this
            ->hasMany(Message::className(), ['id' => 'id'])
            ->indexBy('language')
        ;
    }

    /**
     * @return array|SourceMessage[]
     */
    public static function getCategories()
    {
        return SourceMessage::find()
            ->select('category')
            ->distinct('category')
            ->asArray()
            ->all()
        ;
    }

    public function initMessages()
    {
        $messages = [];
        foreach (Yii::$app->i18n->languages as $language) {
            if (!isset($this->messages[$language])) {
                $message             = new Message;
                $message->language   = $language;
                $messages[$language] = $message;
            } else {
                $messages[$language] = $this->messages[$language];
            }
        }
        $this->populateRelation('messages', $messages);
    }

    public function saveMessages()
    {
        /** @var Message $message */
        foreach ($this->messages as $message) {
            $this->link('messages', $message);
            $message->save();
        }
    }
}
