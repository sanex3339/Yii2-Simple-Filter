<?php

namespace sanex\filter;

use sanex\filter\controllers\FilterController;
use yii\web\Session;

class SanexFilter extends \yii\base\Module
{
    public $controllerNamespace = 'sanex\filter\controllers';
    public $session;

    public function init()
    {
        parent::init();
        $this->session = new Session;
        $this->session->open();
    }

    public function setFilter($filter)
    {
        $this->params['filter'] = $filter;
        $this->session['SanexFilter'] = $filter;
    	return $this->runAction('filter/set-filter');
    }

    public function getData()
    {
        return $this->runAction('filter/show-data-get');
    }
}
