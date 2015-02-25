<?php

namespace sanex\filter;

use sanex\filter\controllers\FilterController;
use yii\web\Session;

class SanexFilter extends \yii\base\Module
{
    public $controllerNamespace = 'sanex\filter\controllers',
           $filter, 
           $model, 
           $session,
           $setDataProvider = false,
           $query,
           $tempSessionData,
           $viewFile,
           $viewParams;

    public function init() 
    {
        parent::init();

        $this->session = new Session;
        $this->session->open();
    }

    public function setFilter($filter = [])
    {
        $this->filter = $filter;
        return $this->runAction('filter/set-filter');
    }

    public function renderDataView($viewFile, $model, $setDataProvider = false, $viewParams = [])
    {
        $this->viewFile = $this->tempSessionData['viewFile'] = $viewFile;
        $this->model = $this->tempSessionData['model'] = $model;
        $this->setDataProvider = $this->tempSessionData['setDataProvider'] = $setDataProvider;
        $this->viewParams = $this->tempSessionData['viewParams'] = $viewParams;
        $this->session['SanexFilter'] = $this->tempSessionData;

        return $this->runAction('filter/show-data-get');
    }

    public function setQuery($query)
    {
        $this->query = $this->tempSessionData['query'] = $query;
        $this->session['SanexFilter'] = $this->tempSessionData; 
    }
}
