<?php
	use yii\web\View;
	$this->registerJs('var sanexFilterAjaxUrl = "'.\Yii::$app->getUrlManager()->createAbsoluteUrl('/sanex-filter-ajax').'";', View::POS_HEAD);
?>

<div class="fltr-data-wrapper">
	<?=$this->render($viewFile, $viewParams)?>
</div>