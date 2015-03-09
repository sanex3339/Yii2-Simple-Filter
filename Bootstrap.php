<?php

namespace sanex\simplefilter;

use Yii;
use yii\base\BootstrapInterface;

/**
 * SimpleFilter module bootstrap class.
 */
class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        //automatic add module to application
        Yii::$app->setModule('SimpleFilter', [
           'class' => 'sanex\simplefilter\SimpleFilter',
        ]);
        Yii::$app->getModule('SimpleFilter');

        // Add module URL rules.
        $app->getUrlManager()->addRules(
            [
                'simple-filter-ajax' => 'SimpleFilter/filter/show-data-post-ajax',
            ]
        );
    }
}