<?php

namespace backend\controllers;

use Yii;
use common\models\Shift;
use common\models\ShiftSearch;
use common\models\Requirement;
use common\models\Participant;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii2mod\rbac\components\AccessControl;
use yii\data\ActiveDataProvider;
use backend\models\AddParticipantForm;

/**
 * ShiftController implements the CRUD actions for Shift model.
 */
class ShiftController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
			],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Shift models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ShiftSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Shift model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
		$model = $this->findModel($id);
		$dp = new ActiveDataProvider([
			'query' => Participant::find()->where(['shift_id' => $id]),
		]);

		$form = new AddParticipantForm();
		$form->shift_id = $id;

        if($form->load(Yii::$app->request->post())) 
		{
			$form->addUser();
        }

        return $this->render('view', [
			'model' => $model,
			'form' => $form,
			'dp' => $dp,
        ]);
    }

	public function actionEmails($id)
	{
		$model = $this->findModel($id);
		$users = $model->participants;

		return $this->renderPartial('emails', [
			'users' => $users,
		]);
	}

    /**
     * Creates a new Shift model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Shift();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Shift model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
		
		if(!$model->team->event->active)
		{
			Yii::$app->session->addFlash('error', 'Shifts can not be updated once an event is closed');
			Yii::$app->user->setReturnUrl(Yii::$app->request->referrer);
			return $this->goBack();
		}

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
			$requirements = Requirement::find()->orderBy('name ASC')->all();
            return $this->render('update', [
				'requirements' => $requirements,
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Shift model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
		$model = $this->findModel($id);
		if($model->delete() !== false)
		{
			Yii::$app->session->addFlash('success', 'Shift deleted.');
		}
			
		return $this->redirect(['/team/view', 'id' => $model->team_id]);
    }

    /**
     * Finds the Shift model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Shift the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Shift::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
