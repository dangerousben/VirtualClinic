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
 */
class VirtualClinic {

    /**
     * Returns the static model of the specified AR class.
     * @return Patient the static model class
     */
    public function model($className = __CLASS__) {
        return parent::model($className);
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
        $subspecialities = Subspecialty::model()->findAll();
        foreach ($subspecialities as $subspeciality) {
            $class_name = $this->getClinicName($subspeciality->name) . 'Module';
            try {
                $module_name = $this->getModuleName($subspeciality->name) .  '.' . $class_name;
                Yii::import($module_name, true);
                if (class_exists($class_name, true)) {
                    $clinics[$subspeciality->id] = $subspeciality->name;
                }
            } catch (Exception $ex) {
                // TODO how to deal with exception? Report or just leave?
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
        $columns = array();
        $class_name = $this->getClinicName($subspeciality) . 'Module';
        try {
            $module_name = $this->getModuleName($subspeciality).  '.' . $class_name;
            Yii::import($module_name, true);
            if (class_exists($class_name, true)) {
                $clinic = new $class_name($this->getClinicName($subspeciality), null);
                $columns = array_keys($clinic->columns);
            }
        } catch (Exception $ex) {
            // TODO how to deal with exception? Report or just leave?
        }
        return $columns;
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
        $value = null;
        $class_name = $this->getClinicName($subspeciality) . 'Module';
        try {
            $module_name = $this->getModuleName($subspeciality). '.' . $class_name;
            Yii::import($module_name, true);
            if (class_exists($class_name, false)) {
                $clinic = new $class_name($this->getClinicName($subspeciality), null);
                $value = $this->getColumnValues($pid, $clinic->columns[$columnName]);
            }
        } catch (Exception $ex) {
            // TODO how to deal with exception? Report or just leave?
        }
        return $value;
    }

    /**
     * Attempts to format the data for the specified column and clinic.
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
     * HTML BR element, separating each element in the data in a row;
     * - otherwise, the data itself is returned.
     * 
     * @param mixed $data the data to be formatted.
     * 
     * @return string the formatted table data.
     */
    public function formatTableData($clinicName, $columnName, $data) {
        
        $clinic = $this->getClinicName($clinicName) . 'Module';
        if (method_exists($clinic, 'formatData')) {
            $value = $clinic::formatData($columnName, $data);
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
     * @param type $columnName the column to get the values for.
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
        $obj = $this->getElementForLatestEventInEpisode($pid, $event_type,
                $nameOfClass);
        $ret = null;
        if ($obj) {
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
     * Remove all non-alpha numeric chararcters, including white space, from
     * the specified string.
     * 
     * @param string $speciality the speciality with white space and non-
     * alpha numeric characters removed.
     */
    private function specialityToCamelCase($speciality) {

        // we're creating a class name so strip out non-desirable characters:
        return preg_replace("/[\s\W]/", "", preg_replace("/[^A-Za-z0-9]/", "", $speciality));
    }
    
    /**
     * Gets the full path of the module as a dot-separated package name.
     * 
     * @param type $subspeciality the subspeciality of the clinic that defines
     * the module path and name.
     * 
     * @return string the name of the module, in the format
     * application.modules.[subspeciality]VirtualClinic
     */
    private function getModuleName($subspeciality) {
        return 'application.modules.' . $this->getClinicName($subspeciality);
    }
    
    /**
     * Gets the name of the clinic.
     * 
     * @param type $subspeciality the subspeciality of the clinic.
     * 
     * @return string the name of the subspeciality, suffixed 
     * with 'VirtualClinic'.
     */
    private function getClinicName($subspeciality) {
        return $this->specialityToCamelCase($subspeciality) . 'VirtualClinic';
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
