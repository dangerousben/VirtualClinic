<?php

/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2012
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2012, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

/**
 * This is the model class for table "patient".
 *
 * The followings are the available columns in table 'patient':
 * @property string  $id
 * @property string  $pas_key
 * @property string  $title
 * @property string  $first_name
 * @property string  $last_name
 * @property string  $dob
 * @property string  $date_of_death
 * @property string  $gender
 * @property string  $hos_num
 * @property string  $nhs_num
 * @property string  $primary_phone
 * @property string  $gp_id
 * @property string  $created_date
 * @property string  $last_modified_date
 * @property string  $created_user_id
 * @property string  $last_modified_user_id
 * 
 * The followings are the available model relations:
 * @property Episode[] $episodes
 * @property Address[] $addresses
 * @property Address $address Primary address
 * @property HomeAddress $homeAddress Home address
 * @property CorrespondAddress $correspondAddress Correspondence address
 * @property Contact[] $contacts
 * @property Gp $gp
 */
class VirtualClinicPatient extends Patient {

    const CHILD_AGE_LIMIT = 16;

    public $use_pas = TRUE;

    /**
     * Returns the static model of the specified AR class.
     * @return Patient the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'virtual_clinic_patient';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return array(
//			array('pas_key', 'length', 'max' => 10),
//			array('hos_num, nhs_num', 'length', 'max' => 40),
//			array('gender', 'length', 'max' => 1),
//			array('dob, date_of_death', 'safe'),
//			array('dob, hos_num, nhs_num, date_of_death', 'safe', 'on' => 'search'),
//			array('iop_left, iop_right, first_name, last_name, dob, hos_num, nhs_num, primary_phone, date_of_death', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        return array(
            'patient' => array(self::BELONGS_TO, 'Patient', 'patient_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'pas_key' => 'PAS Key',
            'dob' => 'Date of Birth',
            'date_of_death' => 'Date of Death',
            'gender' => 'Gender',
            'hos_num' => 'Hospital Number',
            'nhs_num' => 'NHS Number',
        );
    }

    /**
     * 
     * @param type $hosNums an array of hospital numbers to search for.
     * @return type
     */
    public function searchHospitalNumbers($params) {
        $criteria = new CDbCriteria;
        $criteria->join = "JOIN contact ON contact.parent_id = t.patient_id AND contact.parent_class='Patient'"
                . " JOIN patient ON patient.id = t.patient_id";

        $criteria->condition = 'site_id=' . $params['site_id'];
        if (is_array($params['sort_by'])) {
            foreach($params['sort_by'] as $sort) {
                $criteria->order = $sort . ' ' . $params['sort_dir'];
            }
        } else {
            $criteria->order = $params['sort_by'] . ' ' . $params['sort_dir'];
        }
        $criteria->order = 'DESC';

        return $this->count($criteria);
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search($params = false) {
        if (!is_array($params)) {
            $params = array(
                'pageSize' => 20,
                'currentPage' => 1,
                'sort_by' => 'hos_num*1',
                'sort_dir' => 'asc',
                'site_id' => 1,
            );
        }

        $criteria = new CDbCriteria;
        $criteria->join = "JOIN contact ON contact.parent_id = t.patient_id AND contact.parent_class='Patient'"
                . " JOIN patient ON patient.id = t.patient_id";
        if (is_array($params['sort_by'])) {
            foreach($params['sort_by'] as $sort) {
                $criteria->order = $sort . ' ' . $params['sort_dir'];
            }
        } else {
            $criteria->order = $params['sort_by'] . ' ' . $params['sort_dir'];
        }
        Yii::app()->event->dispatch('patient_search_criteria', array('patient' => $this, 'criteria' => $criteria, 'params' => $params));
        $criteria->condition = 'site_id=' . $params['site_id'];

        $dataProvider = new CActiveDataProvider(get_class($this), array(
                    'criteria' => $criteria,
                    'pagination' => array('pageSize' => $params['pageSize'], 'currentPage' => $params['currentPage'] - 1)
                ));

        return $dataProvider;
    }

}
