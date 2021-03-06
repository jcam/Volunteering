<?php

namespace common\models;

use Yii;
use yii\helpers\Html;
use common\models\Participant;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "shift".
 *
 * @property integer $id
 * @property integer $team_id
 * @property string $title
 * @property float $length
 * @property integer $start_time
 * @property integer $active
 * @property integer $requirement_id
 * @property integer $min_needed
 * @property integer $max_needed
 */
class Shift extends \yii\db\ActiveRecord
{
	const DATE_FORMAT = "M j Y, h:i A";

	protected $_participants;
	protected $_team;
	protected $_event;

	public $formStart;
	public $participant_num;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shift';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['team_id', 'title', 'length'], 'required'],
            [['team_id', 'start_time', 'active', 'requirement_id', 'min_needed', 'max_needed'], 'integer'],
			['length', 'number'],
            [['title'], 'string', 'max' => 255],
			['formStart', 'date', 'timestampAttribute' => 'start_time', 'format' => 'php:' . self::DATE_FORMAT],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'team_id' => 'Team ID',
            'title' => 'Title',
            'length' => 'Length',
            'start_time' => 'Start Time',
			'formStart' => 'Start Time',
			'min_needed' => 'Minimum Volunteers Needed',
			'max_needed' => 'Maximum Volunteers Needed',
            'participant_num' => 'Number of Participants',
            'active' => 'Active',
            'requirement.name' => 'User Requirement',
			'volunteerList' => 'Volunteers',
			'volunteerNameList' => 'Volunteers',
			'volunteerRealNameList' => 'Volunteers',
			'filled' => 'Spots Filled',
        ];
    }

	public function afterFind()
	{
		if(isset($this->start_time))
		{
			$this->formStart = date(self::DATE_FORMAT, $this->start_time);
		}
	}

	public function getTeam($reset = false)
	{
		if($reset || !isset($this->_team))
		{
			$this->_team = $this->hasOne(Team::className(), ['id' => 'team_id']);
		}

		return $this->_team;
	}

	public function getParticipants($reset = false)
	{
		if($reset || !isset($this->_participants))
		{
			$this->_participants = $this->hasMany(Participant::className(), ['shift_id' => 'id']);
		}

		return $this->_participants;
	}

	protected function getEvent($reset = false)
	{
		if($reset || !isset($this->_event))
		{
			$this->_event = $this->team->event;
		}

		return $this->_event;
	}

	public function getRequirement()
	{
		return $this->hasOne(Requirement::className(), ['id' => 'requirement_id']);
	}

	public function getFilled()
	{
		return count($this->participants);
	}

	public function getMinSpots()
	{
		if(isset($this->max_needed) && !isset($this->min_needed))
		{
			return $this->max_needed;
		}

		return $this->min_needed;
	}

	public function getMaxSpots()
	{
		if(isset($this->min_needed) && !isset($this->max_needed))
		{
			return $this->min_needed;
		}

		return $this->max_needed;
	}

	public function getRemainingSpots()
	{
		return $this->maxSpots - $this->filled;
	}

	public function meetsRequirement($user_id = null)
	{
		if($this->requirement)
		{
			return $this->requirement->check($user_id);
		}

		return true;
	}

	public function canBeFilled()
	{
		return $this->RemainingSpots > 0;
	}

	public function getStatus()
	{
		if($this->filled < $this->minSpots)
		{
			if($this->maxSpots === $this->minSpots)
			{
				return sprintf("Needs %u more volunteers", $this->remainingSpots);
			}

			$count = $this->minSpots - $this->filled;
			$word = $count == 1 ? "volunteer" : "volunteers";
			return sprintf("Needs at least %u more %s", $count, $word);
		}

		if($this->remainingSpots > 0)
		{
			return sprintf("Minimum reached! Room for %u more", $this->remainingSpots);
		}

		return "Filled";
	}

	public function getStatusClass()
	{
		if($this->filled === 0)
		{
			return 'danger';
		}
		if($this->filled < $this->minSpots)
		{
				return 'warning';
		}

		if($this->remainingSpots > 0)
		{
			return 'info';
		}

		return 'success';
	}

	public function hasParticipant($user_id)
	{
		foreach($this->participants as $participant)
		{
			if($participant->user_id == $user_id)
			{
				return true;
			}
		}

		return false;
	}

	public function generateSignupLink($user_id = null)
	{
		if($user_id == null)
		{
			return "Login to sign up";
		}

		if(!$this->event->active)
		{
			return Html::a('Event Closed', '#', ['class' => 'btn btn-xs btn-default disabled']);
		}

		$classes = 'btn btn-xs';
		$title = '';
		$url = '#';

		if($this->hasParticipant($user_id) == true)
		{
			$classes .= ' btn-danger';
			$url = ["shift/cancel", "id" => $this->id];
			$title = "Cancel";
		}
		elseif(!$this->canBeFilled())
		{
			$classes .= ' btn-default disabled';
			$title = "Shift Full";
		}
		elseif(($this->requirement) && (!$this->meetsRequirement($user_id)))
		{
			$title = "Requires: " . Html::encode($this->requirement->name);
			$url = ["shift/signup", "id" => $this->id];
			$classes .= " btn-warning";
		}
		else
		{
			$classes .= ' btn-primary';
			$url = ["shift/signup", "id" => $this->id];
			$title = "Sign Up";
		}
		
		return Html::a($title, $url, ['class' => $classes]);
	}

	public function addParticipant($user_id)
	{
		if($this->hasParticipant($user_id) == true)
		{
		
			Yii::$app->session->addFlash("error", "You are already signed up for this shift.");
			return false;
		}

		if(!$this->canBeFilled())
		{
			Yii::$app->session->addFlash("error", "This shift is full.");
			return false;
		}

		if(!$this->meetsRequirement($user_id))
		{
			Yii::$app->session->addFlash("error", $this->requirement->errorMessageString);
			return false;
		}

		$participant = new Participant();
		$participant->shift_id = $this->id;
		$participant->user_id = $user_id;

		if($participant->save())
		{
			Yii::$app->session->addFlash("success", sprintf("You are now signed up for the %s '%s' shift on %s", 
				$this->team->name, $this->title, date('M j \a\t g:i a', $this->start_time)));
			return true;
		}

		Yii::$app->session->addFlash("error", "There was an error saving: " . print_r($participant->errors, true));
		return false;
	}

	public function removeParticipant($user_id)
	{
		if($this->hasParticipant($user_id) == true)
		{
			foreach($this->participants as $p)
			{
				if($p->user_id == $user_id)
				{
					$p->delete();
					Yii::$app->session->addFlash("success", sprintf("You have been removed from the %s '%s' shift on %s", 
						$this->team->name, $this->title, date('M j \a\t g:i a', $this->start_time)));
					return true;
				}
			}
		}

		Yii::$app->session->addFlash("error", "You must be signed up for a shift to be removed from it.");
		return false;
	}

	public function getVolunteerNameList()
	{
		$output = [];
		foreach($this->participants as $p)
		{
			$output[] = Html::encode(!empty($p->user->burn_name) ? $p->user->burn_name : $p->user->real_name);
		}
		return implode("<br>\n", $output);
	}

	public function getVolunteerRealNameList()
	{
		$output = [];
		foreach($this->participants as $p)
		{
			$output[] = Html::encode($p->user->real_name);
		}
		return implode("<br>\n", $output);
	}

	public function getVolunteerList()
	{
		$output = [];
		foreach($this->participants as $p)
		{
			$output[] = Html::encode($p->user->username);
		}
		return implode("<br>\n", $output);
	}

	public function beforeDelete()
	{
		if(!$this->team->event->active)
		{
			Yii::$app->session->addFlash('error', 'Shifts cannot be deleted once an event is closed');
			return false;
		}

		foreach($this->participants as $p)
		{
			if(!$p->delete())
			{
				return false;
			}
		}

		return true;
	}

	public function getLengthString()
	{
		$h = floor($this->length);
		$m = round(60 * ($this->length - $h));

		$h_string = $h < 2 ? "hour" : "hours";
		$m_string = $m < 2 ? "minute" : "minutes";
		
		if($m == 0)
		{
			return sprintf("%u %s", $h, $h_string);
		}

		if($h == 0)
		{
			return sprintf("%u %s", $m, $m_string);
		}

		return sprintf("%u %s, %u %s", $h, $h_string, $m, $m_string);
	}

	public function getTimeAndLength()
	{
		$start = date('g:i a', $this->start_time);
		$end = date('g:i a', $this->start_time + ($this->length * 3600));
		return sprintf("%s - %s (%s)", $start, $end, $this->lengthString);
	}
}
