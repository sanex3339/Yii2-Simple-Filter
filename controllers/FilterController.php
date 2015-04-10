<?php

namespace sanex\simplefilter\controllers;

use sanex\simplefilter\components\FilterDataGetRequest;
use sanex\simplefilter\components\FilterDataPostRequest;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class FilterController extends Controller
{
    /**
     * prepare filter properties for view
     * render view `filter-list`
     *
     * @return mixed
     */
    public function actionSetFilter()
    {
        $filter = $this->module->filter;

        //make css class for checkbox
        foreach ($filter as $key => $property) {
            if (isset($property['class'])) {
                if (is_array($property['class'])) {
                    $filter[$key]['class'] = implode(' ', $property['class']);
                }
            } else {
                $filter[$key]['class'] = '';
            }
        }

        //set value for js ajax variable
        $useAjax = $this->module->useAjax ? 'true' : 'false';

        return $this->renderPartial('filter-list', ['filter' => $filter, 'useAjax' => $useAjax]);
    }

    /**
     * GET request
     * create $where array, create $getParams array
     * $where array contain all get parameters with names same as model attributes names
     * $getParams array contain all other get parameters
     *
     * @return mixed
     */
    public function actionShowDataGet()
    {
        $filterData = new FilterDataGetRequest([
            'model' => $this->module->model,
            'query' => $this->module->query,
            'useCache' => $this->module->useCache,
            'useDataProvider' => $this->module->useDataProvider,
        ]);
        $data = $filterData->getData();

        //set dynamic route for this action
        $this->setRoute($this->module->controllerRoute, 'get');

        $this->module->ajaxViewParams['simpleFilterData'] = $data;

        return $this->renderPartial('filter-data-wrapper', [
            'viewFile' => $this->module->ajaxViewFile, 'viewParams' => $this->module->ajaxViewParams
        ]);
    }

    /**
     * redirect to `actionShowDataPost` action
     *
     * @return mixed
     */
    public function actionShowDataPostAjax()
    {
        return $this->module->runAction('filter/show-data-post');
    }

    /**
     * POST AJAX request
     * create $where array, create $getParams array
     * $where array contain all get parameters with names same as model attributes names
     * $getParams array contain all other get parameters
     *
     * @throws NotFoundHttpException
     */
    public function actionShowDataPost()
    {
        if (Yii::$app->request->post('filter') && Yii::$app->request->getIsAjax()) {
            $parameters = unserialize(trim(base64_decode(
                Yii::$app->getSecurity()->decryptByKey(
                    $this->module->session['SimpleFilter'],
                    Yii::$app->request->cookieValidationKey
                )
            )));

            $filterData = new FilterDataPostRequest([
                'filter' => json_decode(Yii::$app->request->post('filter'), true),
                'model' => $parameters['model'],
                'query' => $parameters['query'],
                'useCache' => $parameters['useCache'],
                'useDataProvider' => $parameters['useDataProvider'],
            ]);
            $data = $filterData->getData();

            //set dynamic route for this action and dataProvider urls
            $this->setRoute($parameters['controllerRoute'], 'post', $data, $parameters['useDataProvider']);

            $ajaxViewParams = $parameters['ajaxViewParams'];
            $ajaxViewParams['simpleFilterData'] = $data;

            return $this->renderPartial('filter-data-wrapper', [
                'viewFile' => $parameters['ajaxViewFile'], 'viewParams' => $ajaxViewParams
            ]);
        } else {
            throw new NotFoundHttpException("Page not found.", 1);
        }
    }

    /**
     * * Setting dynamic routes depending on controller in which was created Filter object
     * On this controller route will redirect all `FilterController` get and post actions
     * As result, in all URLs inside Ajax view file $viewFile in href attribute, instead this url:
     * >> site.com/filter/filter/show-data-post/
     * ..will this url:
     * >> site.com/controller_name_where_we_create_filter_object/
     *
     * @param $url
     * @param $type
     * @param string $data
     * @param bool $useDataProvider
     */
    private function setRoute($url, $type, $data = '', $useDataProvider = false)
    {
        if ($useDataProvider) {
            $data->pagination->route = $url;
            $data->sort->route = $url;
        }

        Yii::$app->getUrlManager()->addRules(
            [$url => $type == 'get' ? 'SimpleFilter/filter/show-data-get' : 'filter/filter/show-data-post']
        );
    }
}
