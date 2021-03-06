<?php
namespace frontend\controllers;

use Yii;
use common\models\LoginForm;
use common\components\MDateTime;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use common\models\Shift;
use common\models\Participant;
use common\models\Event;

/**
 * Shift controller
 */
class ShiftController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

	public function actionSignup($id, $uid = null)
	{
		if($uid == null)
		{
			$uid = Yii::$app->user->id;
		}

		$shift = $this->findModel($id);

		$shift->addParticipant($uid);

		if(isset(Yii::$app->request->referrer))
		{
			return $this->redirect(Yii::$app->request->referrer);
		}

		return $this->redirect(['/site/index']);
		//$this->redirect(['team/view', 'id' => $shift->team_id]);
	}

	public function actionCancel($id, $uid = null)
	{
		if($uid == null)
		{
			$uid = Yii::$app->user->id;
		}

		$shift = $this->findModel($id);

		$shift->removeParticipant($uid);

		if(isset(Yii::$app->request->referrer))
		{
			return $this->redirect(Yii::$app->request->referrer);
		}

		return $this->redirect(['/site/index']);
		//$this->redirect(['team/view', 'id' => $shift->team_id]);
	}

	protected function findModel($id)
    {
        if (($model = Shift::find()->where(['id' => $id])->with('participants')->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
