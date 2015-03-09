<?php

namespace sanex\simplefilter\assets;

use yii\web\AssetBundle;

/**
 * Theme main asset bundle.
 */
class FilterAsset extends AssetBundle
{
    public $sourcePath = '@sanex/simplefilter';
    public $baseUrl = '@web/assets/';

    public $css = [
        'css/simple-filter.css'
    ];

    public $js = [
        'js/jquery.query-object.js',
        'js/sanex.simple-filter.js'
    ];

    public $depends = [
        'yii\web\YiiAsset'
    ];
}
