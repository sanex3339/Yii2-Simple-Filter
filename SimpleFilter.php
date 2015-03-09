<?php

namespace sanex\simplefilter;

use Yii;
use yii\base\UnknownPropertyException;
use yii\helpers\Url;
use yii\web\Session;

class SimpleFilter extends \yii\base\Module
{
    public  $controllerNamespace = 'sanex\simplefilter\controllers',

            //init properties
            $ajax = true,
            $model,
            $query,
            $useDataProvider = false,

            //renderAjaxView() properties
            $ajaxViewFile,
            $ajaxViewParams = [],        

            //setFilter() properties
            $filter,

            //other properties
            $availableParams = ['property', 'caption', 'values', 'class'],
            $controllerRoute,
            $session,
            $tempSessionData;

    public function init()
    {
        parent::init();
        $this->session = new Session;
        $this->session->open();
    }    

    public function setParams(Array $params = []) 
    {
        if ($params) {
            foreach ($params as $key => $value) {
                $this->{$key} = $value; 
            }
        } else {
            throw new UnknownPropertyException("Filter parameters must be set", 1);
        }

        //get route for controller in which called this module
        $this->getControllerRoute();
        
        $this->tempSessionData['model'] = $this->model;
        $this->tempSessionData['query'] = $this->query;
        $this->tempSessionData['useDataProvider'] = $this->useDataProvider;
        $this->session['SanexFilter'] = $this->tempSessionData;
    }

    public function setFilter(Array $filter = [])
    {
        if ($filter) {
            foreach ($filter as $filterGroup) {
                foreach ($filterGroup as $key => $value) {
                    if (in_array($key, $this->availableParams)) {
                        $this->filter = $filter;
                    } else {
                        throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $key);
                    } 
                }
            }
        } else {
            throw new UnknownPropertyException("Filter parameters must be set", 1);
        }
        
        return $this->runAction('filter/set-filter');
    }

    public function renderAjaxView($ajaxViewFile, Array $ajaxViewParams = [])
    {   
        $this->tempSessionData['ajaxViewFile'] = $this->ajaxViewFile = $ajaxViewFile;
        $this->tempSessionData['ajaxViewParams'] = $this->ajaxViewParams = $ajaxViewParams;
        $this->session['SanexFilter'] = $this->tempSessionData;

        return $this->runAction('filter/show-data-get');
    }

    private function getControllerRoute()
    {
        $url = str_replace("index.php", "", Url::to(['/'.Yii::$app->controller->getRoute()]));
        $this->tempSessionData['controllerRoute'] = $this->controllerRoute = $url;
    }
}
