<?php

namespace sanex\filter;

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

    public function setFilter($filter)
    {
        foreach($filter as $key => $value){
            $this->{$key} = $value;
        }
        $this->session['SanexFilter'] = $filter;
    	return $this->runAction('filter/set-filter');
    }

    public function renderDataView($viewParams = [])
    {
        $this->viewParams = $viewParams;
        $tempSessionData =  $this->session['SanexFilter'];
        $tempSessionData['viewParams'] = $viewParams;
        $this->session['SanexFilter'] = $tempSessionData;

        return $this->runAction('filter/show-data-get');
    }
}
