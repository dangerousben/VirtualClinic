<?php

/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
return array(
    'components' => array(
        'urlManager' => array(
            'rules' => array(
                // Virtual Clinic:
                'virtualClinic/review/<id:\d+>/<selected:\w+>/<clinic_id:\d+>/<site_id:\d+>/<subspeciality_id:\d+>' => '/VirtualClinic/VirtualClinic/review',
                'virtualClinic/flag/<id:\d+>/<selected:\w+>/<clinic_id:\d+>/<site_id:\d+>/<subspeciality_id:\d+>' => '/VirtualClinic/VirtualClinic/flag',
                'virtualClinic/results/<page_num:\d+>/<sort_dir:\d+>/<sort_by:\d+>/<site_id:\d+>/<clinic_id:\d+>' => '/VirtualClinic/virtualClinic/results/',
            ),
        ),
    ),
    'params' => array(
        /*
         * Each clinic entry must be matched 1-to-1 with each 'columns' entry
         * (see below).
         */
        'virtualClinic.clinics' => array('Glaucoma', 'Cataract', 'Accident & Emergency'),
        /*
         * 
         * The columns declaration must contain an array of
         * clinic_name => array(column_definitions), where clinic_name matches
         * one of the defined clinics from the clinic list.
         * 
         * Format of the columns is an array of coumn names (as the key)
         * against an array of contruction data for the clinic data. The idea is
         * that each column contains at least one piece of information, possibly
         * more (like for readings that have two values, one for L/E and R/E).
         * 
         * Each column is defined by obtaining data from one event type (although
         * different columns can have different event types); one class name
         * for that event type; and a field, which is either a string representing
         * a property on the object, or an array defining a nested object
         * hierarchy (for one value of a property), or an array of arrays
         * containing nested object properties, used when several properties
         * are required for a column.
         * 
         * For example, to obtain a property directly from a class object, use
         * the format:
         * 
         * 'History' => array(
         *       'event_type' => 'OphCiExamination',
         *       'class_name' => 'Element_OphCiExamination_History',
         *       'field' => 'description')
         * 
         * For a nested property, use the format:
         * 
         * 'IOP' => array(
         *       'event_type' => 'OphCiExamination',
         *       'class_name' => 'Element_OphCiExamination_IntraocularPressure',
         *       'field' => array('left_reading', 'value'))
         * 
         * Taking this further we can create an array of properties for two
         * readings for the IOP element:
         * 
         * 'IOP' => array(
         *       'event_type' => 'OphCiExamination',
         *       'class_name' => 'Element_OphCiExamination_IntraocularPressure',
         *       'field' => array(array('left_reading', 'value'),
         *                        array('right_reading', 'value'))
         * 
         * Note that when returning multiple values for a single column entry,
         * implementing clinics can add their own [clinic_name]Clinic.php to
         * models/ and override @link #formatTableData(clinicName, columnName, mixed)
         * 
         * @var array 
         */
        'virtualClinic.columns' => array(
            'Glaucoma' => array(
                'IOP' => array(
                    'event_type' => 'OphCiExamination',
                    'class_name' => 'Element_OphCiExamination_IntraocularPressure',
                    'field' => array(array('left_reading', 'value'), array('right_reading', 'value'))),
//                'CCT' => array(
//                    'event_type' => 'OphCiExamination',
//                    'class_name' => 'Element_OphCiExamination_CentralCornealThickness',
//                    'field' => array(array('left_cct'), array('right_cct'))),
//                'Comments' => array(
//                    'event_type' => 'OphCiExamination',
//                    'class_name' => 'Element_OphCiExamination_GlaucomaManagement',
//                    'field' => 'comments'),
//                'Medications' => array(
//                    'event_type' => 'OphCiExamination',
//                    'class_name' => 'Element_OphCiExamination_GlaucomaManagement',
//                    'field' => array(array('med_1_right', 'shortname'),
//                        array('med_2_right', 'shortname'),
//                        array('med_3_right', 'shortname'),
//                        array('med_1_left', 'shortname'),
//                        array('med_2_left', 'shortname'),
//                        array('med_3_left', 'shortname')))
            ),
            'Cataract' => array(
                'History' => array(
                    'event_type' => 'OphCiExamination',
                    'class_name' => 'Element_OphCiExamination_History',
                    'field' => 'description'),
            ),
            'Accident & Emergency' => array(
                'History' => array(
                    'event_type' => 'OphCiExamination',
                    'class_name' => 'Element_OphCiExamination_History',
                    'field' => 'description'),)
        )
    )
);
