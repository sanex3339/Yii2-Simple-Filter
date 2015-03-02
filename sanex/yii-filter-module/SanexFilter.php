<?php

namespace sanex\filter;

use Yii;
use yii\web\Session;
use yii\helpers\Url;

class SanexFilter extends \yii\base\Module
{
    public $ajax,
           $controllerNamespace = 'sanex\filter\controllers',
           $filter, 
           $model, 
           $session,
           $setDataProvider = false,
           $query,
           $tempSessionData,
           $urlForLinks,
           $viewFile,
           $viewParams;

    public function init() 
    {
        parent::init();

        $this->session = new Session;
        $this->session->open();
    }

    public function setFilter($filter = [], $ajax = true)
    {
        $this->filter = $filter;
        $this->ajax = $ajax === true ? 'true' : 'false';

        return $this->runAction('filter/set-filter');
    }

    public function renderDataView($viewFile, \yii\db\ActiveRecord $model, $setDataProvider = false, $viewParams = [])
    {
        $this->viewFile = $this->tempSessionData['viewFile'] = $viewFile;
        $this->model = $this->tempSessionData['model'] = $model;
        $this->setDataProvider = $this->tempSessionData['setDataProvider'] = $setDataProvider;
        $this->viewParams = $this->tempSessionData['viewParams'] = $viewParams;
        //set url for correct work of all links in view that rendered by showDataGet controller
        $this->urlForLinks = $this->tempSessionData['urlForLinks'] = Url::to(['/'.Yii::$app->controller->getRoute()]);
        $this->session['SanexFilter'] = $this->tempSessionData;

        return $this->runAction('filter/show-data-get');
    }

    public function setQuery(\yii\db\ActiveQuery $query)
    {
        $this->query = $this->tempSessionData['query'] = $query;
        $this->session['SanexFilter'] = $this->tempSessionData; 
    }
}
