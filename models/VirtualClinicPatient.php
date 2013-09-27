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
 * A virtual clinic patient is an extension of the class 'patient',
 * and encapsulates the same attributes, as well as adding clinic-related
 * elements.
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
 * The patient is defined as a relation to the clinic patient:
 * 
 * @property Patient $patient
 * 
 * All patient relations are available, namely:
 * 
 * @property Episode[] $episodes
 * @property Address[] $addresses
 * @property Address $address Primary address
 * @property HomeAddress $homeAddress Home address
 * @property CorrespondAddress $correspondAddress Correspondence address
 * @property Contact[] $contacts
 * @property Gp $gp
 */
class VirtualClinicPatient extends Patient {

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
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        return array(
            'patient' => array(self::BELONGS_TO, 'Patient', 'patient_id'),
            'virtual_clinic' => array(self::BELONGS_TO, 'VirtualClinic', 'virtual_clinic_id'),
            'firm' => array(self::BELONGS_TO, 'Firm', 'firm_id'),
            'subspecialty' => array(self::BELONGS_TO, 'Subspecialty', 'subspeciality_id'),
//            'clinic_type' => array(self::BELONGS_TO, 'VirtualClinicType', 'clinic_type_id'),
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
//        $criteria->join = "JOIN virtual_clinic_patient ON virtual_clinic_patient.patient_id = t.patient_id JOIN contact ON contact.parent_id = t.patient_id AND contact.parent_class='Patient'"
//                . " JOIN patient ON patient.id = t.patient_id";
        $criteria->join = "JOIN virtual_clinic_patient ON virtual_clinic_patient.patient_id = t.patient_id"
                . " JOIN patient ON patient.id = t.patient_id";
//        $criteria->join = "JOIN contact ON contact.parent_id = t.patient_id AND contact.parent_class='Patient'"
//                . " JOIN patient ON patient.id = t.patient_id";
        $condition = $this->getRealClinicId($params['virtual_clinic_id']);
        $criteria->condition = 'virtual_clinic_patient.site_id=' . $params['site_id'] . $condition;
        $criteria->condition .= ' and virtual_clinic_patient.reviewed=' . $params['reviewed'];
        $x = $criteria->condition;
        if (is_array($params['sort_by'])) {
            foreach ($params['sort_by'] as $sort) {
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
        $criteria->join = 
//                "JOIN contact ON contact.parent_id = t.patient_id"
                " JOIN patient ON patient.id = t.patient_id";
        if (is_array($params['sort_by'])) {
            foreach ($params['sort_by'] as $sort) {
                $criteria->order = $sort . ' ' . $params['sort_dir'];
            }
        } else {
            $criteria->order = $params['sort_by'] . ' ' . $params['sort_dir'];
        }
        Yii::app()->event->dispatch('patient_search_criteria', array('patient' => $this, 'criteria' => $criteria, 'params' => $params));
        
        $condition = '';//$this->getRealClinicId($params['virtual_clinic_id']);
        
        $criteria->condition = 'site_id=' . $params['site_id'] . $condition;
        $criteria->condition .= ' and reviewed=' . $params['reviewed'];

        $dataProvider = new CActiveDataProvider(get_class($this), array(
                    'criteria' => $criteria,
                    'pagination' => array('pageSize' => $params['pageSize'], 'currentPage' => $params['currentPage'] - 1)
                ));

        return $dataProvider;
    }
    /**
     * The virtual clinic maintains a subspeciality ID and a clinic
     * type ID. The former is for subspeciality clinics; the latter is
     * for non-subspeciality clinics. If the
     * specified ID is within the ID range of all subspecialities, a
     * subspeciality contion will be returned; else, the offset of the
     * subspecialities will be subtracted from the ID to form the basis
     * of the non-subspeciality clinic.
     * 
     * This enables the search to base it's criteria either on the
     * subspeciality ID or the clinic type ID of the clinic table.
     *  
     * @param type $clinic_id the clinic ID to base the condition on.
     * 
     * @return string a conditional string to add to search criteria.
     */
    private function getRealClinicId($clinic_id) {

        $condition = ' and virtual_clinic_patient.subspeciality_id=' . $clinic_id;
//        $subspecialities = $subspecialities = Subspecialty::model()->findAll();
//        $subspeciality_count = count($subspecialities);
//        if ($clinic_id > $subspeciality_count) {
//            $condition = ' and clinic_type_id=' . ($clinic_id - $subspeciality_count);
//        }
        return $condition;
    }

}
