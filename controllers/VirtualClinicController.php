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
class VirtualClinicController extends BaseController {

    public $layout = '//layouts/column2';
    public $patient;
    public $service;
    public $firm;
    public $editing;
    public $event;
    public $event_type;
    public $title;

    public function filters() {
        return array('accessControl');
    }

    public function accessRules() {
        return array(
            array('allow',
                'users' => array('@')
            ),
            // non-logged in can't view anything
            array('deny',
                'users' => array('?')
            ),
        );
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $this->render('index', array(
        ));
    }

    /**
     * Updates the virtual clinic module for the specified patient.
     * 
     * @param int $patient_id the patient's id that will be updated.
     * 
     * @param array $data mixed data of the form key => value where
     * key is the column to update and data is the value of the column
     * to update.
     */
    public static function updateClinic($patient_id, $data) {

        $sql = "select patient_id from virtual_clinic_patient"
                . " where patient_id='" . $patient_id . "'";
        $res = Yii::app()->db->createCommand($sql)->query();
        if (!$res->count()) {
            // we've never added this patient to the clinic before, so add them
            $sql = "insert into virtual_clinic_patient"
                    . " (patient_id) values (:patient_id)";
            $command = Yii::app()->db->createCommand($sql);
            $command->bindParam("patient_id", $patient_id);
            $command->execute();
        }
        foreach ($data as $key => $value) {
            $sql = "update virtual_clinic_patient"
                    . " set " . $key . "=:" . $key . " where patient_id="
                    . $patient_id;
            $command = Yii::app()->db->createCommand($sql);
            $command->bindParam($key, $value);
            $command->execute();
        }
    }

    /**
     * 
     * @param type $page
     */
    public function actionResults($page = false) {
        $page_num = 1;
        if (isset($_GET['page_num'])) {
            $page_num = (integer) @$_GET['page_num'];
        }
        $site_id = isset($_GET['site_id']) ? $_GET['site_id'] : '1';
        $sort_dir = isset($_GET['sort_dir']) ? $_GET['sort_dir'] : '0';
        $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : '0';
        $clinic_id = isset($_GET['clinic_id']) ? $_GET['clinic_id'] : '0';
        $sort = ($sort_dir == 0) ? 'asc' : 'desc';
        switch ($sort_by) {
            case 0:
                $sort_by = 'hos_num*1';
                break;
            case 1:
                $sort_by = 'first_name';
                break;
            case 2:
                $sort_by = 'last_name';
                break;
            case 3:
                $sort_by = 'dob';
                break;
            case 4:
                $sort_by = 'seen_by_user_id';
                break;
            case 5:
                $sort_by = 'follow_up';
                break;
        }
        $model = new VirtualClinicPatient();
        $pageSize = 20;
        $dataProvider = $model->search(array(
            'currentPage' => $page_num,
            'pageSize' => $pageSize,
            'sort_by' => $sort_by,
            'sort_dir' => $sort,
            'site_id' => $site_id,
            'clinic_id' => $clinic_id,
                ));

        $nr = $model->searchHospitalNumbers(array(
            'currentPage' => $page_num,
            'pageSize' => $pageSize,
            'sort_by' => $sort_by,
            'sort_dir' => $sort,
            'site_id' => $site_id,
            'clinic_id' => $clinic_id,));

        $pages = ceil($nr / $pageSize);
        $content = $this->render('results', array(
            'dataProvider' => $dataProvider,
            'pages' => $pages,
            'items_per_page' => $pageSize,
            'total_items' => $nr,
            'pagen' => $page_num,
            'sort_by' => isset($_GET['sort_by']) ? $_GET['sort_by'] : 0,
            'sort_dir' => $sort_dir,
            'site_id' => $site_id,
            'clinic_id' => $clinic_id,
                ));
//        }
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id) {
        $model = VirtualClinicPatient::model()->findByPk((int) $id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

}
