<?php

namespace sanex\filter\assets;

use yii\web\AssetBundle;

/**
 * Theme main asset bundle.
 */
class FilterAsset extends AssetBundle
{
    public $sourcePath = '@sanex/filter';
    public $baseUrl = '@web/assets/';
    //public $basePath = '@webroot/assets/';
    

    public $css = [
        'css/sanexFilter.css'
    ];

    public $js = [
        'js/jquery.query-object.js',
        'js/sanex.sanex-filter.js'
    ];

    public $depends = [
        'yii\web\YiiAsset'
    ];
}
