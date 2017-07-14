<?php
use yii\grid\GridView;
use yii\helpers\Html;
/* @var $this yii\web\View */

$this->title = Yii::$app->params['siteTitle'];
?>
<div class="site-index">
    <div class="body-content">
		<div class="row">
			<div class="col-lg-12">
				<h2>Congratulations! You have an account!</h2>
				<h2>Volunteer for a core below, and <a href="https://tickets.fireflyartscollective.org">click here for the ticketing dashboard and to register for a ticket!</a></h2>

<?php foreach ($events as $event) { ?>
    
                <h1><?php echo Html::encode($event->name);?></h1>
				<?php echo GridView::widget([
					'dataProvider' => $teams[$event->id],
					'layout' => '{items}',
					'columns' => [
						[
							'attribute' => 'name',
							'format' => 'raw',
							'value' => function($model){return Html::a($model->name, ['team/view', 'id' => $model->id]);},
						],
						[
							'attribute' => 'status',
							'contentOptions' => function($model, $k, $i, $c)
							{
								return ['class' => $model->statusClass];
							},
						],
						[
							'label' => 'Actions',
							'format' => 'raw',
							'value' => function($model){
								return sprintf("%s",
									Html::a("Sign Up/View Schedule", ['/team/view', 'id' => $model->id], ['class' => 'btn btn-primary btn-xs'])
								);
							},
						],
					],
				]);
				?>

<?php } ?>

           </div>
		</div>
    </div>
</div>

