<?php
    use sanex\filter\assets\FilterAsset;
    use yii\helpers\Html;

    FilterAsset::register($this);
?>

<div class="fltr-wrapper">
	<?php foreach ($filter as $property):?>
		<div class='fltr-cat clearfix' id='<?=Html::encode($property['property'])?>'>
			<span class="fltr-cat-caption"><?=Html::encode($property['caption'])?></span>
			<?php foreach ($property['values'] as $value):?>
				 <?=Html::a(Html::encode($value), 'javascript:', ['value' => Html::encode($value), 'class' => 'fltr-check'])?>
			<?php endforeach;?>
		</div>
	<?php endforeach;?>
</div>