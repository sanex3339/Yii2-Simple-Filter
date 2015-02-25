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
        $this->viewFile = $tempSessionData['viewFile'] = $viewFile;
        $this->model = $tempSessionData['model'] = $model;
        $this->setDataProvider = $tempSessionData['setDataProvider'] = $setDataProvider;
        $this->viewParams = $tempSessionData['viewParams'] = $viewParams;

        $this->session['SanexFilter'] = $tempSessionData;

        return $this->runAction('filter/show-data-get');
    }
}
