<?php

namespace sanex\simplefilter;

use Yii;
use yii\base\UnknownPropertyException;
use yii\helpers\Url;
use yii\web\Session;

class SimpleFilter extends \yii\base\Module
{
    /**
     * @var object model object
     */
    public $model;

    /**
     * @var object yii\db\ActiveQuery object
     */
    public $query;

    /**
     * @var bool use or not ajax for updating data
     */
    public $useAjax = true;

    /**
     * @var bool use or not cache
     */
    public $useCache = false;

    /**
     * @var bool return data as model or as dataProvider
     */
    public $useDataProvider = false;

    //renderAjaxView() properties
    /**
     * @var string alias to ajax view file
     */
    public $ajaxViewFile;

    /**
     * @var array custom parameters what was send to ajax view
     */
    public $ajaxViewParams = [];

    /**
     * @var array setFilter() properties
     */
    public $filter;

    /**
     * @var string current controller route
     */
    public $controllerRoute;

    /**
     * @var object session object
     */
    public $session;

    /**
     * @var array temporary session data
     */
    private $tempSessionData;

    /**
     * @var array available public methods parameters
     */
    private $availableParameters = [
        'setFilter' => [
            'property',
            'caption',
            'values',
            'class'
        ],
        'setParams' => [
            'model',
            'query',
            'useAjax',
            'useCache',
            'useDataProvider'
        ]
    ];

    /**
     * module init method
     */
    public function init()
    {
        parent::init();
        $this->session = new Session;
        $this->session->open();
    }

    /**
     * set class properties with given parameters
     * get current controller route in which module was called
     * put that properties into session
     *
     * @param array $params
     * @throws UnknownPropertyException
     * @return void
     */
    public function setParams(Array $params = [])
    {
        if ($params) {
            foreach ($params as $key => $value) {
                if (in_array($key, $this->availableParameters['setParams'])) {
                    $this->{$key} = $value;
                } else {
                    throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $key);
                }
            }
            if (!$this->model) {
                throw new UnknownPropertyException("The parameter `model` is required", 1);
            }
        } else {
            throw new UnknownPropertyException('Filter parameters must be set', 1);
        }
        $this->getControllerRoute();
        $this->setSessionData(['model', 'query', 'useCache', 'useDataProvider']);
    }

    /**
     * set $filter property and run action 'filter/set-filter'
     *
     * @param array $filter
     * @return mixed
     * @throws UnknownPropertyException
     */
    public function setFilter(Array $filter = [])
    {
        if ($filter) {
            foreach ($filter as $filterGroup) {
                foreach ($filterGroup as $key => $value) {
                    if (!in_array($key, $this->availableParameters['setFilter'])) {
                        throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $key);
                    }
                    if (!is_string($value) && !is_array($value)) {
                        throw new UnknownPropertyException('setFilter() values must be a string or array');
                    }
                    if (!is_array($filterGroup['values'])) {
                        throw new UnknownPropertyException('setFilter() `values` parameter must be an array');
                    }
                }
            }
            $this->filter = $filter;
        } else {
            throw new UnknownPropertyException('Filter parameters must be set', 1);
        }

        return $this->runAction('filter/set-filter');
    }

    /**
     * send $ajaxViewFile and $ajaxViewParams to module controller and run action 'filter/show-data-get'
     *
     * @param $ajaxViewFile
     * @param array $ajaxViewParams
     * @return mixed
     */
    public function renderAjaxView($ajaxViewFile, Array $ajaxViewParams = [])
    {
        if (!is_string($ajaxViewFile)) {
            throw new UnknownPropertyException('$ajaxViewFile must be a string');
        }
        $this->ajaxViewFile = $ajaxViewFile;
        $this->ajaxViewParams = $ajaxViewParams;
        $this->setSessionData(['ajaxViewFile', 'ajaxViewParams']);

        return $this->runAction('filter/show-data-get');
    }

    /**
     * encrypt data with Yii2 cookie validation key
     *
     * @param $data
     * @return string
     */
    private function encryptData($data)
    {
       return $encryptedData = Yii::$app->getSecurity()->encryptByKey(
           trim(base64_encode(serialize($data))), Yii::$app->request->cookieValidationKey
       );
    }

    /**
     * get route for controller in which was called module
     *
     * @return void
     */
    private function getControllerRoute()
    {
        $this->controllerRoute = str_replace('index.php', '', Url::to(['/' . Yii::$app->controller->getRoute()]));
        $this->setSessionData(['controllerRoute']);
    }

    /**
     * put class properties into session
     *
     * @param array $properties
     * @return void
     */
    private function setSessionData(Array $properties = [])
    {
        foreach ($properties as $property) {
            $this->tempSessionData[$property] = $this->$property;
        }
        $this->session['SimpleFilter'] = $this->encryptData($this->tempSessionData);
    }
}
