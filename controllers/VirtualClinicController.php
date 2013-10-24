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
    $reviewed = isset($_GET['reviewed']) ? $_GET['reviewed'] : '0';
    $sort = ($sort_dir == 0) ? 'asc' : 'desc';
    switch ($sort_by) {
      case 0:
        $useCrn = Yii::app()->params['virtualClinic.index.CRN'] ? true : false;
        if ($useCrn) {
          $sort_by = 'nhs_num * 1';
        } else {
          $sort_by = 'hos_num * 1';
        }
        break;
      case 1: // TODO - contact have been refactored, need to changed how this is done:
        $sort_by = 'first_name';
        break;
      case 2: // TODO - contact have been refactored, need to changed how this is done:
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
      case 6:
        $sort_by = 'visit_date';
        break;
      case 7:
        $sort_by = 'flag';
        break;
      case 8:
        $sort_by = 'reviewed';
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
        'virtual_clinic_id' => $clinic_id,
        'reviewed' => $reviewed,
            ));

    $nr = $model->searchHospitalNumbers(array(
        'currentPage' => $page_num,
        'pageSize' => $pageSize,
        'sort_by' => $sort_by,
        'sort_dir' => $sort,
        'site_id' => $site_id,
        'virtual_clinic_id' => $clinic_id,
        'reviewed' => $reviewed,));

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
        'virtual_clinic_id' => $clinic_id,
        'reviewed' => $reviewed,
            ));
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
  
  /**
   * Attempts to mark a patient as reviewed, based on the patient's ID,
   * clinic and site ID. If no clinic and matching site entry exists for the 
   * review is not carried out. That is, only patients with a matching
   * ID, site and clinic entry are reviewed.
   * 
   * Requires request parameters 'id' for the patient's ID, 'selected' as
   * true/false for the review value, 'clinic_id' for the clinic and 
   * 'site_id' for the clinic site to update.
   */
  public function actionReview() {
    if (isset($_GET['id'])) {
      $id = $_GET['id'];
    }
    if (isset($_GET['clinic_id'])) {
      $clinic_id = $_GET['clinic_id'];;
    }
    if (isset($_GET['site_id'])) {
      $site_id = $_GET['site_id'];
    }
    if (isset($_GET['subspeciality_id'])) {
      $subspeciality_id = $_GET['subspeciality_id'];
    }
     
    if (isset($_GET['selected'])) {
      $checked = $_GET['selected'];
    }
    if ($checked == 'true') {
      
      Yii::app()->event->dispatch('virtual_clinic_review', array(
          'patient_id' => $id,
          'subspeciality_id' => $subspeciality_id,
          'virtual_clinic_id' => $clinic_id,
          'site_id' => $site_id,
          'reviewed' => 1,
              ));
    } else {
      // TODO this is not used yet - once a patient is reviewed, they
      // essentially disappear from the clinic. In fact, they are still
      // part of the clinic list, but clinics only display patients that
      // have not been reviewed. One reason to keep this would be to have
      // a page that shows reviewed patients per-clinic, enabling users
      // to send users back to the same clinic (although this can be done via
      // updateClinic($patient_id, $data))
      Yii::app()->event->dispatch('virtual_clinic_review', array(
          'patient_id' => $id,
          'reviewed' => 0,
              ));
    }
  }
  
  /**
   * Flags a patient, marking them as priority.
   * 
   * Set request parameters 'id' for the patient's ID and 'selected' as
   * true/false for the flag value.
   */
  public function actionFlag() {
    if (isset($_GET['id'])) {
      $id = $_GET['id'];
    }
    if (isset($_GET['clinic_id'])) {
      $clinic_id = $_GET['clinic_id'];;
    }
    if (isset($_GET['site_id'])) {
      $site_id = $_GET['site_id'];
    }
    if (isset($_GET['selected'])) {
      $checked = $_GET['selected'];
    }
    if (isset($_GET['subspeciality_id'])) {
      $subspecialty_id = $_GET['subspeciality_id'];
    }
    if ($checked == 'true') {
      Yii::app()->event->dispatch('virtual_clinic_flag', array(
          'patient_id' => $id,
          'subspeciality_id' => $subspecialty_id,
          'virtual_clinic_id' => $clinic_id,
          'site_id' => $site_id,
          'flag' => 1,
          ));
    } else {
      Yii::app()->event->dispatch('virtual_clinic_flag', array(
          'patient_id' => $id,
          'subspeciality_id' => $subspecialty_id,
          'virtual_clinic_id' => $clinic_id,
          'site_id' => $site_id,
          'flag' => 0,
          ));
    }
    
  }

}
