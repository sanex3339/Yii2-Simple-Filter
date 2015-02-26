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

    public $css = [
        'css/sanex-filter.css'
    ];

    public $js = [
        'js/jquery.query-object.js',
        'js/sanex.sanex-filter.js'
    ];

    public $depends = [
        'yii\web\YiiAsset'
    ];
}
