<?php

namespace uran1980\yii\modules\i18n\helpers;

use Yii;
use yii\helpers\ArrayHelper;

class AppHelper
{
    /**
     * @param string $key
     * @return mixed
     */
    public static function getConfigParam($key = null)
    {
        $output = '';                                                           // default

        $params = Yii::$app->params;
        if ( isset($params[$key]) )
            $output = $params[$key];

        return $output;
    }

    /**
     * @return string
     */
    public static function getModuleName()
    {
        return Yii::$app->controller->module->getUniqueId();
    }

    /**
     * @return string
     */
    public static function getControllerName()
    {
        return Yii::$app->controller->getUniqueId();
    }

    /**
     * @return string
     */
    public static function getActionName()
    {
        return Yii::$app->controller->action->getUniqueId();
    }

    /**
     * Returns the request component.
     *
     * @return \yii\web\Request the request component.
     */
    public static function getRequest()
    {
        return Yii::$app->getRequest();
    }

    /**
     * Returns the request Params
     *
     * @param string $type - all|get|post (default all)
     * @return array
     */
    public static function getRequestParams($type = 'all')
    {
        $request = Yii::$app->getRequest();
        switch ($type) {
            case 'get':
                $params = $request->get();
                break;

            case 'post':
                $params = $request->post();
                break;

            case 'all':
            default:
                $params = ArrayHelper::merge($request->get(), $request->post());
                break;
        }

        return $params;
    }

    /**
     * Returns GET|POST parameter with a given name. If name isn't specified,
     * returns an array of all Request parameters.
     *
     * @param string $name the parameter name
     * @param mixed $defaultValue the default parameter value if the parameter does not exist.
     * @return array|mixed
     */
    public static function getRequestParam($name = null, $defaultValue = null)
    {
        $params = self::getRequestParams();
        if ( isset($params[$name]) ) {
            return $params[$name];
        } else {
            return $defaultValue;
        }
    }

    /**
     * @return string
     */
    public static function getRoute()
    {
        return Yii::$app->controller->getRoute();
    }

    /**
     * @param string $message
     * @param boolean $removeAfterAccess
     */
    public static function showErrorMessage($message, $removeAfterAccess = true)
    {
        Yii::$app->getSession()->setFlash('error', $message, $removeAfterAccess);
    }

    /**
     * @param string $message
     * @param boolean $removeAfterAccess
     */
    public static function showNoticeMessage($message, $removeAfterAccess = true)
    {
        return self::showWarningMessage($message, $removeAfterAccess);
    }

    /**
     * @param string $message
     * @param boolean $removeAfterAccess
     */
    public static function showWarningMessage($message, $removeAfterAccess = true)
    {
        Yii::$app->getSession()->setFlash('warning', $message, $removeAfterAccess);
    }

    /**
     * @param string $message
     * @param boolean $removeAfterAccess
     */
    public static function showSuccessMessage($message, $removeAfterAccess = true)
    {
        Yii::$app->getSession()->setFlash('success', $message, $removeAfterAccess);
    }


}
