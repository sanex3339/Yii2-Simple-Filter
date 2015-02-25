<?php

namespace sanex\filter;

use ReflectionMethod;
use sanex\filter\controllers\FilterController;
use yii\web\Session;

class SanexFilter extends \yii\base\Module
{
    public $controllerNamespace = 'sanex\filter\controllers',
           $filter, 
           $modelClass, 
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

    public function renderDataView($viewFile, $modelClass, $setDataProvider = false, $viewParams = [])
    {
        $this->viewFile = $tempSessionData['viewFile'] = $viewFile;
        $this->modelClass = $tempSessionData['modelClass'] = $modelClass;
        $this->setDataProvider = $tempSessionData['setDataProvider'] = $setDataProvider;
        $this->viewParams = $tempSessionData['viewParams'] = $viewParams;

        $this->session['SanexFilter'] = $tempSessionData;

        return $this->runAction('filter/show-data-get');
    }
}
