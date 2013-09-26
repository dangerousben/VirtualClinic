<?php

/**
 * Class to perform actions for adding/updating patients within a clinic,
 * as well as to flag and review them.
 */
class Clinic {

  /**
   * Updates the clinic with the specified patient's details. A clinic update
   * is performed when a new patient is added to the clinic for the first time,
   * or when a patient needs their details updating.
   * 
   * Note that to identify a patient with a given clinic, the patient's ID,
   * as well as site, subspecialty IDs must be supplied.
   * 
   * @param type $params an array of parameters for the clinic update, as
   * key => value pairs, as follows:
   * 
   * Required parameters:
   *  patient_id: the patient's (OE) ID;
   *  site_id: site for the clinic update;
   *  subspeciality_id: subspeciality ID of the clinic
   *  firm_id: the firm associated with the clinic; this could be the logged in user's firm, for example;
   * 
   * Optional parameters:
   *  seen_by_user_id: the session's user will be used if not supplied;
   *  clinic: the clinic's name to add the specified patient to - used if 'virtual_clinic_id' is not set;
   *  virtual_clinic_id: the clinic's ID; used if 'clinic' is not set;
   *  follow_up: follow up time for the exam;
   *  visit_date: date the patient entered the clinic.
   * 
   */
  public function clinicUpdate($params) {
    // TODO Class loading appears to be failing, too often - needs to be fixed:
    Yii::import('VirtualClinic.models.VirtualClinicPatient', true);
    Yii::import('VirtualClinic.models.VirtualClinic', true);
    
    $criteria = new CdbCriteria();
    $criteria->addCondition('patient_id=:patient_id');
    $criteria->params = array(':patient_id' => $params['patient_id']);
    $patient = VirtualClinicPatient::model()->find($criteria);
    
    $criteria = new CdbCriteria();
    $criteria->addCondition('name=:name');
    $criteria->params = array(':name' => $params['clinic']);
    $clinic = VirtualClinic::model()->find($criteria);
    
    $clinic_id = $clinic->id;
    if (!isset($params['virtual_clinic_id'])) {
      $params['virtual_clinic_id'] = $clinic_id;
      unset($params['clinic']);
    }
    if (!isset($params['seen_by_user_id'])) {
      $params['seen_by_user_id'] = Yii::app()->user->id;;
    }
    if (is_null($patient)) {
      // we've never added this patient to the clinic before, so add them
      $sql = "insert into virtual_clinic_patient"
              . " (patient_id, follow_up, visit_date, seen_by_user_id, site_id, "
              . "subspeciality_id, virtual_clinic_id, firm_id) values (:patient_id, :follow_up, :visit_date, :seen_by_user_id, :site_id, :subspeciality_id, :virtual_clinic_id, :firm_id)";
      $command = Yii::app()->db->createCommand($sql);
      $command->bindParam("patient_id", $params['patient_id']);
      $command->bindParam("follow_up", $params['follow_up']);
      $command->bindParam("visit_date", $params['visit_date']);
      $command->bindParam("seen_by_user_id", $params['seen_by_user_id']);
      $command->bindParam("site_id", $params['site_id']);
      $command->bindParam("subspeciality_id", $params['subspeciality_id']);
      $command->bindParam("virtual_clinic_id", $clinic_id);
      $command->bindParam("firm_id", $params['firm_id']);
      $command->execute();
    } else {
      $criteria = new CdbCriteria();
      $criteria->addCondition('patient_id=:patient_id');
      $criteria->params = array(':patient_id' => $params['patient_id']);
      $patient = VirtualClinicPatient::model()->find($criteria);
      unset($params['patient_id']);
      
      foreach ($params as $key => $value) {
        $patient->$key = $value;
        $patient->save();
      }
    }
  }

  /**
   * Marks the patient as reviewed, if they are already in clinic for
   * the specified subspciality ID and virtual clinic ID.
   * 
   * @param array $params an array of key => value pairs, as follows:
   * 
   *  reviewed: value (0 or 1) of the review;
   *  patient_id: the patient's ID;
   *  site_id: the site the patient is at;
   *  subspeciality_id: subspeciality to search for for this patient;
   *    if not supplied, the session's firm ID will be used to obtain the
   *    subspeciality;
   *  virtual_clinic_id: virtual clinic ID for this patient to search on.
   */
  public function reviewPatient($params) {
    if (!isset($params['subspeciality_id'])) {
      $firm = Firm::model()->findByPk(Yii::app()->session['selected_firm_id']);
      $params['subspeciality_id'] = $firm->serviceSubspecialtyAssignment->subspecialty_id;
    }
    $patient = $this->getPatientForClinicSiteAndSubspeciality($params);
    $params = $this->unsetSearchParams($params);
    foreach ($params as $key => $value) {
      $patient->$key = $value;
      $patient->save();
    }
  }

  /**
   * Flags the patient, marking them as a priority for the clinic. This is
   * done in cases where a clinician wants to keep an eye on all patients
   * that for some reason need to be monitored in clinic for the attention
   * of other clinicians.
   * 
   * @param type $params an array of key => value pairs, containing:
   * 
   *  flag: value (0 or 1) to mark the patient as flagged (1) or not (0);
   *  patient_id: the patient's ID;
   *  site_id: the site the patient is at;
   *  subspeciality_id: subspeciality to search for for this patient;
   *    if not supplied, the session's firm ID will be used to obtain the
   *    subspeciality;
   *  virtual_clinic_id: virtual clinic ID for this patient to search on.
   */
  public function flagPatient($params) {
    if (!isset($params['subspeciality_id'])) {
      $firm = Firm::model()->findByPk(Yii::app()->session['selected_firm_id']);
      $params['subspeciality_id'] = $firm->serviceSubspecialtyAssignment->subspecialty_id;
    }
    $patient = $this->getPatientForClinicSiteAndSubspeciality($params);
    // unset the values we're not interested in:
    $params = $this->unsetSearchParams($params);
    foreach ($params as $key => $value) {
      $patient->$key = $value;
      $patient->save();
    }
  }
  
  /**
   * Unset search keys for patient, virtual clinic, subsepciality and site IDs.
   * 
   * @param type $params array of key => value pairs to be unset.
   */
  private function unsetSearchParams($params) {
    unset($params['patient_id']);
    unset($params['virtual_clinic_id']);
    unset($params['subspeciality_id']);
    unset($params['site_id']);
    return $params;
  }
  
  /**
   * Gets the patient based on patient, site, subspeciality and clinic IDs.
   * 
   * @param type $params an array of key => value pairs, containing:
   * 
   *  flag: value (0 or 1) to mark the patient as flagged (1) or not (0);
   *  patient_id: the patient's ID;
   *  site_id: the site the patient is at;
   *  subspeciality_id: subspeciality to search for for this patient;
   *    if not supplied, the session's firm ID will be used to obtain the
   *    subspeciality;
   *  virtual_clinic_id: virtual clinic ID for this patient to search on.
   * 
   * @return type the found patient, null otherwise.
   */
  private function getPatientForClinicSiteAndSubspeciality($params) {
    return VirtualClinicPatient::model()->find(
            'patient_id=' . $params['patient_id']
            . ' and subspeciality_id=' . $params['subspeciality_id']
            . ' and virtual_clinic_id=' . $params['virtual_clinic_id']
            . ' and site_id=' . $params['site_id']);
  }

}

?>
