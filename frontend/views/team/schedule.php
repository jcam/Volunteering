<?php
use yii\grid\GridView;
use yii\helpers\Html;
/* @var $this yii\web\View */
?>
<h1><?php echo sprintf("%s %s", Html::encode($event->name), Html::encode($team->name));?> shifts</h1>
<p><strong>Contact:</strong> <?php echo Html::mailto($team->contact, $team->contact);?></p>
<p><?php echo Yii::$app->formatter->asHtml($team->description);?></p>
<?php foreach($days as $timestamp => $dp):?>
<h3><?php echo date('l, M j, Y', $timestamp);?></h3>
<?php echo GridView::widget([
	'dataProvider' => $dp,
	'columns' => [
		'title',
		[
			'label' => 'Time',
			'attribute' => 'start_time',
			'format' => 'text',
			'value' => function($model){
				$start = date('g:i a', $model->start_time);
				$end = date('g:i a', $model->start_time + ($model->length * 3600));
				return sprintf("%s - %s (%u hours)", $start, $end, $model->length);
			},
		],
		[
			'attribute' => 'volunteerNameList',
			'format' => 'raw',
		],
	],
]);?>
<?php endforeach;?>
