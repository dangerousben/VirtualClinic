<?php

/**
 * This is the model class for table "virtual_clinic".
 *
 * The followings are the available columns in table 'virtual_clinic':
 * @property string $id
 * @property string $display_name
 * @property string $name
 * @property string $last_modified_user_id
 * @property string $last_modified_date
 * @property string $created_user_id
 * @property string $created_date
 * @property string $module_name
 * @property string $subspecialty_id
 *
 * The followings are the available model relations:
 * @property Subspecialty $subspecialty
 * @property User $createdUser
 * @property User $lastModifiedUser
 */
class VirtualClinic extends CActiveRecord {

  /**
   * Returns the static model of the specified AR class.
   * @param string $className active record class name.
   * @return VirtualClinic the static model class
   */
  public static function model($className = __CLASS__) {
    return parent::model($className);
  }

  /**
   * @return string the associated database table name
   */
  public function tableName() {
    return 'virtual_clinic';
  }

  /**
   * @return array validation rules for model attributes.
   */
  public function rules() {
    // NOTE: you should only define rules for those attributes that
    // will receive user inputs.
    return array(
        array('module_name', 'required'),
        array('display_name, name, module_name', 'length', 'max' => 50),
        array('last_modified_user_id, created_user_id, subspecialty_id', 'length', 'max' => 10),
        array('last_modified_date, created_date', 'safe'),
        // The following rule is used by search().
        // Please remove those attributes that should not be searched.
        array('id, display_name, name, last_modified_user_id, last_modified_date, created_user_id, created_date, module_name, subspecialty_id', 'safe', 'on' => 'search'),
    );
  }

  /**
   * @return array relational rules.
   */
  public function relations() {
    // NOTE: you may need to adjust the relation name and the related
    // class name for the relations automatically generated below.
    return array(
        'subspecialty' => array(self::BELONGS_TO, 'Subspecialty', 'subspecialty_id'),
        'createdUser' => array(self::BELONGS_TO, 'User', 'created_user_id'),
        'lastModifiedUser' => array(self::BELONGS_TO, 'User', 'last_modified_user_id'),
    );
  }

  /**
   * @return array customized attribute labels (name=>label)
   */
  public function attributeLabels() {
    return array(
        'id' => 'ID',
        'display_name' => 'Display Name',
        'name' => 'Name',
        'last_modified_user_id' => 'Last Modified User',
        'last_modified_date' => 'Last Modified Date',
        'created_user_id' => 'Created User',
        'created_date' => 'Created Date',
        'module_name' => 'Module Name',
        'subspecialty_id' => 'Subspecialty',
    );
  }

  /**
   * Retrieves a list of models based on the current search/filter conditions.
   * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
   */
  public function search() {
    // Warning: Please modify the following code to remove attributes that
    // should not be searched.

    $criteria = new CDbCriteria;

    $criteria->compare('id', $this->id, true);
    $criteria->compare('display_name', $this->display_name, true);
    $criteria->compare('name', $this->name, true);
    $criteria->compare('last_modified_user_id', $this->last_modified_user_id, true);
    $criteria->compare('last_modified_date', $this->last_modified_date, true);
    $criteria->compare('created_user_id', $this->created_user_id, true);
    $criteria->compare('created_date', $this->created_date, true);
    $criteria->compare('module_name', $this->module_name, true);
    $criteria->compare('subspecialty_id', $this->subspecialty_id, true);

    return new CActiveDataProvider($this, array(
                'criteria' => $criteria,
            ));
  }

  /**
   * Use reflection to search through the list of sub-specialities and
   * attempt to find module [SubSepcialityName]VirtualClinic. Note white
   * space and special characters ('-', '&' etc.) are removed; so
   * 'Accident & Emergency' becomes AccidentEmergency, for example.
   * Therefore the sub-speciality name and module name are closely linked.
   * 
   * @return array list of key : value pairs of subspeciality_id : class_name
   * pairs of implemented clinics. The empty list is returned if no
   * clinics exist.
   */
  public function getClinics() {
    $clinics = array();
    foreach (Yii::app()->params['virtualClinic.clinics'] as $clinic) {
      try {
        $vc = VirtualClinic::model()->find('name=\'' . $clinic . '\'');
        if ($vc) {
          $clinics[$vc->id] = $vc;
        }
      } catch (Exception $e) {
        
      }
    }
    return $clinics;
  }

  /**
   * Use reflection to search through the list of sub-specialities and
   * attempt to find module [SubSepcialityName]VirtualClinic. Note white
   * space and special characters ('-', '&' etc.) are removed; so
   * 'Accident & Emergency' becomes AccidentEmergency, for example.
   * Therefore the sub-speciality name and module name are closely linked.
   * 
   * @return array list of key : value pairs of subspeciality_id : class_name
   * pairs of implemented clinics. The empty list is returned if no
   * clinics exist.
   */
  public function getColumnNames($subspeciality) {
    return array_keys(Yii::app()->params['virtualClinic.columns'][$subspeciality->name]);
  }

  /**
   * Get all values for the specified column.
   * 
   * @param type $pid the patient to search for.
   * 
   * @param type $columnName the column to get the values for.
   * 
   * @param string $subspeciality the clinic to get the values from.
   * 
   * @return mixed if the column, as given by the clinic's column definitions
   * contains only one value, then that value is returned; otherwise
   * an array of values is returned.
   */
  public function getColumnValue($pid, $subspeciality, $columnName) {
    return $this->getColumnValues($pid,
            Yii::app()->params['virtualClinic.columns'][$subspeciality->name][$columnName]);
  }

  /**
   * Other clinics should override this method in their own class named
   * [clinic_name]Clinic.php placed in models/. If a clinic name
   * contains spaces or non-alphanumeric characters, strip them out -
   * so the following example names would be modified as follows
   * (also showing their clinic PHP file names):
   * 
   *  'Medical Retinal' => MedicalRetinal => MedicalRetinal.php
   * 
   *  'Neuro-ophthalmology' => Neuroophthalmology => NeuroophthalmologyClinic.php
   * 
   * Given some data, format it for the specified column and clinic.
   * Note that this method attempts to determine if the specified clinic
   * has a predefined method named 'formatData($columnName, $data)'. If it
   * does, it calls the specified method. It is up to implementing clinics
   * to determine if they need to format the data for the specified column;
   * formatting can do extra tings, like create a canvas for eyedraw elements,
   * for example.
   * 
   * If there is no such method, or the method returns null, the
   * following is returned:
   * 
   * - if the data is an array, each element is returned separated by the
   * HTML BR element, separating each element in the data in a row for display;
   * - otherwise, the data itself is returned.
   * 
   * @param mixed $data the data to be formatted.
   * 
   * @return string the formatted table data.
   */
  public function formatTableData($clinicName, $columnName, $data) {

    $clinic = $clinicName->name . 'Clinic';
    try {

      $exists = file_exists(Yii::getPathOfAlias('application.modules.VirtualClinic.models')
              . DIRECTORY_SEPARATOR . $clinic . '.php');
      if ($exists) {
        if (method_exists($clinicName->name . 'Clinic', 'formatData')) {
          $value = $clinic::formatData($columnName, $data);
        }
      }
    } catch (Exception $e) {
      
    }
    if (!isset($value)) {
      // no formatting provided; if there are multiple elements,
      // separate them by break markup:
      if (is_array($data)) {
        $value = "";
        for ($i = 0; $i < count($data) - 1; $i++) {
          $value = $value . $data[$i] . "<br>";
        }
        $value = $value . $data[$i];
      } else {
        $value = $data;
      }
    }
    return $value;
  }

  /**
   * Get all values for the specified column.
   * 
   * @param type $pid the patient to search for.
   * 
   * @param type $col the column to get the values for.
   * 
   * @return mixed if the column, as given by the clinic's column definitions
   * contains only one value, then that value is returned; otherwise
   * an array of values is returned.
   */
  public function getColumnValues($pid, $col) {
    $event_type = $col['event_type'];
    $class_name = $col['class_name'];
    $field = $col['field'];
    $nameOfClass = new $class_name();
    $obj = $this->getElementForLatestEventInEpisode($pid, $event_type, $nameOfClass);
    $ret = null;
    if ($obj) {
      $x = $obj->id;
      if (is_array($field)) {
        $ret = array();
        foreach ($field as $fld) {
          array_push($ret, $this->getLeafMember($obj, $fld));
        }
      } else {
        $ret = $obj->$field;
      }
    }
    return $ret;
  }

  /**
   * Recursive function to traverse an object graph/tree to extract a
   * member variable. For any list of child objects greater than one,
   * the first child becomes the object instance for the function
   * when called recursively and the process is performed again for all
   * remaining children.
   * 
   * @param instance $obj the non-null obejct to traverse the graph for.
   * @param array $children an array of string values for an object graph
   * to traverse.
   * 
   * @return object the value held in the leaf node of the tree, if it
   * exists.
   */
  private function getLeafMember($obj, $children) {
    $ret = null;
    if (count($children) > 1) {
      $ret = $this->getLeafMember($obj[$children[0]], array_slice($children, 1));
    } else {
      $ret = $obj[$children[0]];
    }
    return $ret;
  }

  /**
   * Gets the specified event type, if it exists.
   * 
   * @param string $field the class name of the event type to get.
   * 
   * @return Event if it exists; null otherwise.
   */
  private function getEventType($field) {
    $criteria = new CdbCriteria;
    $criteria->compare('class_name', $field);
    $criteria->distinct = true;
    return EventType::model()->find($criteria);
  }

  /**
   * Gets the episode associated with the patient for the given speciality.
   * 
   * Adapted from the Patient method of the same name. Since patient contains
   * more data than this class, it has been re-implemented here (more
   * specifically, in a clinic no single patient is considered).
   * 
   * @param type $patient_id the patient to get the episode for.
   * 
   * @return type
   */
  public function getEpisodeForCurrentSubspecialty($patient_id) {
    $firm = Firm::model()->findByPk(Yii::app()->session['selected_firm_id']);

    $ssa = $firm->serviceSubspecialtyAssignment;

    // Get all firms for the subspecialty
    $firm_ids = array();
    foreach (Firm::model()->findAll('service_subspecialty_assignment_id=?', array($ssa->id)) as $firm) {
      $firm_ids[] = $firm->id;
    }

    return Episode::model()->find('patient_id=? and firm_id in (' . implode(',', $firm_ids) . ')', array($patient_id));
  }

  /**
   * Gets the last element for the specified episode.
   * 
   * @param int $patient_id the patient ID to search for the episode and
   * element.
   * @param string $event_class the event class to search for.
   * @param string $element the name to search for.
   * @return instance an instance of the specified element if it exists;
   * null otherwise.
   */
  public function getElementForLatestEventInEpisode($patient_id, $event_class, $element) {
    $event_type = $this->getEventType($event_class);
    $episode = $this->getEpisodeForCurrentSubspecialty($patient_id);
    $x = $episode->id;
    if ($episode) {
      $event = $episode->getMostRecentEventByType($event_type->id);
      if ($event) {
        $criteria = new CDbCriteria;
        $criteria->compare('episode_id', $episode->id);
        $criteria->compare('event_id', $event->id);
        $criteria->order = 'datetime desc';

        return $element::model()
                        ->with('event')
                        ->find($criteria);
      }
    }
  }

}