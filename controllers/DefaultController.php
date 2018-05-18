<?php

namespace uran1980\yii\modules\i18n\controllers;

use Yii;
use yii\base\Model;
use uran1980\yii\modules\i18n\Module;
use uran1980\yii\modules\i18n\models\SourceMessage;
use uran1980\yii\modules\i18n\models\search\SourceMessageSearch;
use uran1980\yii\modules\i18n\helpers\AppHelper;
use yii\helpers\Html;

class DefaultController extends \Zelenin\yii\modules\I18n\controllers\DefaultController
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionRescan()
    {
        // ------------------------- RESCAN MESSAGES ---------------------------
        $result = SourceMessageSearch::getInstance()->extract();

        // ----------------------- SHOW RESCAN RESULT --------------------------
        $message  = Module::t('Rescan successfully completed.') . '<br />';
        $message .= Html::ul([
            Module::t('New messages:') . ' ' . (isset($result['new']) ? $result['new'] : 0),
            Module::t('Obsolete messages:') . ' ' . (isset($result['obsolete']) ? $result['obsolete'] : 0),
        ]);
        AppHelper::showSuccessMessage($message);

        // ---------------------------- REDIRECT -------------------------------
        if ( ($referrer = Yii::$app->getRequest()->referrer) ) {
            return $this->redirect($referrer);
        } else {
            return $this->redirect(['/translations']);
        }
    }

    public function actionClearDeleted()
    {
        SourceMessage::deleteAll(['id' => SourceMessageSearch::getDeletedIds()]);

        // ---------------------------- REDIRECT -------------------------------
        if ( ($referrer = Yii::$app->getRequest()->referrer) ) {
            return $this->redirect($referrer);
        } else {
        return $this->redirect(['/translations']);
    }
    }

    public function actionClearCache()
    {
        // ---------------------- CHECK IS AJAX REQUEST ------------------------
        if ( !Yii::$app->getRequest()->isAjax ) {
            return $this->redirect(['/translations']);
        }

        // ------------------ SET JSON FORMAT FOR RESPONSE ---------------------
        // @see https://github.com/samdark/yii2-cookbook/blob/master/book/response-formats.md
        Yii::$app->getResponse()->format = \yii\web\Response::FORMAT_JSON;

        // ---------------------- SET DEFAULT RESPONSE -------------------------
        $response = array(
            'status'  => 'error',
            'message' => Module::t('An unexpected error occured!'),
        );

        // -------------------------- CLEAR CACHE ------------------------------
        if ( SourceMessageSearch::cacheFlush() ) {
            $response['status']  = 'success';
            $response['message'] = Module::t('Translations cache successfully cleared.');
        }

        return $response;
    }

    public function actionSave($id)
    {
        // ---------------------- CHECK IS AJAX REQUEST ------------------------
        if ( !Yii::$app->getRequest()->isAjax ) {
            return $this->redirect(['/translations']);
        }

        // ------------------ SET JSON FORMAT FOR RESPONSE ---------------------
        // @see https://github.com/samdark/yii2-cookbook/blob/master/book/response-formats.md
        Yii::$app->getResponse()->format = \yii\web\Response::FORMAT_JSON;

        // --------------------- SET DEFAULT RESPONSE --------------------------
        $response = array(
            'status'  => 'error',
            'message' => Module::t('An unexpected error occured!'),
        );

        // --------------------- SAVE TRANSLATION BY ID ------------------------
        // @see vendor\zelenin\yii2-i18n-module\controllers\DefaultController::actionUpdate
        $model = $this->findModel($id);
        $model->initMessages();

        if ( Model::loadMultiple($model->messages, Yii::$app->getRequest()->post())
             && Model::validateMultiple($model->messages) )
        {
            $model->saveMessages();

            // clear translation cache
            if ( ($categories = AppHelper::getRequestParam('categories')) ) {
                foreach ( $categories as $language => $category ) {
                    Yii::$app->cache->delete([
                        'yii\i18n\DbMessageSource',
                        $category,
                        $language,
                    ]);
                }
            }

            $response['status']  = 'success';
            $response['message'] = 'Translation successfuly saved.';
            $response['params']  = AppHelper::getRequestParams();
        }

        return $response;
    }

    public function actionDelete($id)
    {
        // ---------------------- CHECK IS AJAX REQUEST ------------------------
        if ( !Yii::$app->getRequest()->isAjax ) {
            return $this->redirect(['/translations']);
        }

        // ------------------ SET JSON FORMAT FOR RESPONSE ---------------------
        // @see https://github.com/samdark/yii2-cookbook/blob/master/book/response-formats.md
        Yii::$app->getResponse()->format = \yii\web\Response::FORMAT_JSON;

        // --------------------- SET DEFAULT RESPONSE --------------------------
        $response = array(
            'status'  => 'error',
            'message' => Module::t('An unexpected error occured!'),
        );

        // -------------------- DELETE TRANSLATION BY ID -----------------------
        $model = parent::findModel($id);
        $model->message = '@@' . $model->message . '@@';
        if ( $model->save() ) {
            // clear cache
            foreach ( Yii::$app->i18n->languages as $language ) {
                Yii::$app->cache->delete([
                    'yii\i18n\DbMessageSource',
                    $model->category,
                    $language,
                ]);
            }

            // set response
            $response['status']   = 'success';
            $response['message']  = 'Translation successfully deleted.';
        }

        return $response;
    }

    public function actionRestore($id)
    {
        // ---------------------- CHECK IS AJAX REQUEST ------------------------
        if ( !Yii::$app->getRequest()->isAjax ) {
            return $this->redirect(['/translations']);
        }

        // ------------------ SET JSON FORMAT FOR RESPONSE ---------------------
        // @see https://github.com/samdark/yii2-cookbook/blob/master/book/response-formats.md
        Yii::$app->getResponse()->format = \yii\web\Response::FORMAT_JSON;

        // --------------------- SET DEFAULT RESPONSE --------------------------
        $response = array(
            'status'  => 'error',
            'message' => Module::t('An unexpected error occured!'),
        );

        // -------------------- RESTORE TRANSLATION BY ID ----------------------
        $model = parent::findModel($id);
        $model->message = trim($model->message, '@@');
        if ( $model->save() ) {
            // clear cache
            foreach ( Yii::$app->i18n->languages as $language ) {
                Yii::$app->cache->delete([
                    'yii\i18n\DbMessageSource',
                    $model->category,
                    $language,
                ]);
            }

            // set response
            $response['status']   = 'success';
            $response['message']  = 'Translation successfully restored.';
        }

        return $response;
    }

    /**
     * @param array|integer $id
     * @return SourceMessage|SourceMessage[]
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $query = SourceMessage::find()->where('id = :id', [':id' => $id]);
        $models = is_array($id)
        ? $query->all()
        : $query->one();
        if (!empty($models)) {
            return $models;
        } else {
            throw new NotFoundHttpException(Module::t('The requested page does not exist'));
        }
    }
}
