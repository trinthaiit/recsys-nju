<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $id
 * @property string $email
 * @property string $password
 * @property string $nickname
 * @property integer $age
 * @property string $gender
 * @property string $occupation
 * @property string $zipCode
 * @property integer $ageCommunityId
 * @property integer $genreCommunityId
 * @property integer $occupationCommunityId
 * @property integer $ratingCommunityId
 * @property integer $locationCommunityId
 *
 * The followings are the available model relations:
 * @property LocationCommunity $locationCommunity
 * @property AgeCommunity $ageCommunity
 * @property GenreCommunity $genreCommunity
 * @property OccupationCommunity $occupationCommunity
 * @property RatingCommunity $ratingCommunity
 * @property UserMovie[] $userMovies
 */
class User extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('email, password, nickname, age, zipCode', 'required'),
			array('email, nickname', 'length', 'max'=>50),
			array('password, ageCommunityId, genreCommunityId, occupationCommunityId, ratingCommunityId, locationCommunityId', 'length', 'max'=>255),
			array('age', 'length', 'max'=>11),
			array('gender', 'length', 'max'=>1),
			array('occupation', 'length', 'max'=>100),
			array('zipCode', 'length', 'max'=>5),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, email, password, nickname, age, gender, occupation, zipCode, ageCommunityId, genreCommunityId, occupationCommunityId, ratingCommunityId, locationCommunityId', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'locationCommunity' => array(self::BELONGS_TO, 'LocationCommunity', 'locationCommunityId'),
			'ageCommunity' => array(self::BELONGS_TO, 'AgeCommunity', 'ageCommunityId'),
			'genreCommunity' => array(self::BELONGS_TO, 'GenreCommunity', 'genreCommunityId'),
			'occupationCommunity' => array(self::BELONGS_TO, 'OccupationCommunity', 'occupationCommunityId'),
			'ratingCommunity' => array(self::BELONGS_TO, 'RatingCommunity', 'ratingCommunityId'),
			'userMovies' => array(self::HAS_MANY, 'UserMovie', 'userId'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Id',
			'email' => 'Email',
			'password' => 'Password',
			'nickname' => 'Nickname',
			'age' => 'Age',
			'gender' => 'Gender',
			'occupation' => 'Occupation',
			'zipCode' => 'Zip Code',
			'ageCommunityId' => 'Age Community',
			'genreCommunityId' => 'Genre Community',
			'occupationCommunityId' => 'Occupation Community',
			'ratingCommunityId' => 'Rating Community',
			'locationCommunityId' => 'Location Community',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('nickname',$this->nickname,true);
		$criteria->compare('age',$this->age,true);
		$criteria->compare('gender',$this->gender,true);
		$criteria->compare('occupation',$this->occupation,true);
		$criteria->compare('zipCode',$this->zipCode,true);
		$criteria->compare('ageCommunityId',$this->ageCommunityId,true);
		$criteria->compare('genreCommunityId',$this->genreCommunityId,true);
		$criteria->compare('occupationCommunityId',$this->occupationCommunityId,true);
		$criteria->compare('ratingCommunityId',$this->ratingCommunityId,true);
		$criteria->compare('locationCommunityId',$this->locationCommunityId,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}