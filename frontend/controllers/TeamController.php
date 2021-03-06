<?php
namespace frontend\controllers;

use Yii;
use common\models\LoginForm;
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
use common\models\Team;
use common\models\Shift;
use common\components\MDateTime;

/**
 * Team controller
 */
class TeamController extends Controller
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

	public function actionView($id)
	{
		$team = $this->findModel($id);
		$event = $team->event;

		$start = new MDateTime($event->start, new \DateTimeZone('EST5EDT'));
		$start->subToStart('D');

		$days = [];

		while($start->timestamp < $event->end)
		{
			$days[$start->timestamp] = $team->getDayDataProvider($start->timestamp);

			$start->add(new \DateInterval('P1D'));
		}

		return $this->render('view', [
			'event' => $event,
			'team' => $team,
			'days' => $days,
		]);
	}

	public function actionSchedule($id)
	{
		$team = $this->findModel($id);
		$event = $team->event;

		$start = new MDateTime($event->start, new \DateTimeZone('EST5EDT'));
		$start->subToStart('D');

		$days = [];

		while($start->timestamp < $event->end)
		{
			$days[$start->timestamp] = $team->getDayDataProvider($start->timestamp);

			$start->add(new \DateInterval('P1D'));
		}

		return $this->render('schedule', [
			'event' => $event,
			'team' => $team,
			'days' => $days,
		]);
	}

	protected function findModel($id)
    {
        if (($model = Team::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
