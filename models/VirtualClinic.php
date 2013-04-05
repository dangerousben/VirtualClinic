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
            $base_name = $this->specialityToCamelCase($subspeciality->name) . 'VirtualClinic';
            $class_name = $base_name . 'Module';
            try {
                $module_name = 'application.modules.' . $base_name
                        . '.' . $class_name;
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
     * Remove all non-alpha numeric chararcters, including white space, from
     * the specified string.
     * 
     * @param type $speciality
     */
    public function specialityToCamelCase($speciality) {

        // we're creating a class name so strip out non-desirable characters:
        return preg_replace("/[\s\W]/", "", preg_replace("/[^A-Za-z0-9]/", "", $speciality));
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
        $base_name = $this->specialityToCamelCase($subspeciality) . 'VirtualClinic';
        $class_name = $base_name . 'Module';
        try {
            $module_name = 'application.modules.' . $base_name
                    . '.' . $class_name;
            Yii::import($module_name, true);
            if (class_exists($class_name, true)) {
                $clinic = new $class_name();
                $columns = array_keys($clinic->columns);
            }
        } catch (Exception $ex) {
            // TODO how to deal with exception? Report or just leave?
        }
        return $columns;
    }

    /**
     * 
     * @param type $pid
     * @param type $subspeciality
     * @param type $columnName
     * @return type
     */
    public function getColumnValue($pid, $subspeciality, $columnName) {
        $value = null;
        $base_name = $this->specialityToCamelCase($subspeciality) . 'VirtualClinic';
        $class_name = $base_name . 'Module';
        try {
            $module_name = 'application.modules.' . $base_name
                    . 'VirtualClinic.' . $class_name;
            Yii::import($module_name, true);
            if (class_exists($class_name, false)) {
                $clinic = new $class_name();
                $value = $clinic->getColumnValue($pid, $subspeciality, $columnName);
            }
        } catch (Exception $ex) {
            // TODO how to deal with exception? Report or just leave?
        }
        return $value;
    }

    /**
     * 
     * @param type $data
     * @return type
     */
    public function formatTableData($data, $prefixes = null) {
        $text = "";
        if (is_array($data)) {
            for ($i = 0; $i < count($data) - 1; $i++) {
                if ($prefixes) {
                    $text = $text . $prefixes[$i];
                }
                $text = $text . $data[$i] . "<br>";
            }
            if ($prefixes) {
                $text = $text . $prefixes[$i];
            }
            $text = $text . $data[$i];
        } else {
            $text = $data;
        }
        return $text;
    }

}
