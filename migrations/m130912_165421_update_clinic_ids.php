<?php

class m130912_165421_update_clinic_ids extends CDbMigration {

  // Use safeUp/safeDown to do migration with transaction
  public function safeUp() {
    
    Yii::import('VirtualClinic.models.VirtualClinicPatient', true);
    Yii::import('VirtualClinic.models.VirtualClinic', true);
    $this->addColumn('virtual_clinic_patient', 'virtual_clinic_id', 'int(10) unsigned NOT NULL');
    $this->createIndex('virtual_clinic_patient_id_fk', 'virtual_clinic_patient', 'virtual_clinic_id');
    
    $patients = VirtualClinicPatient::model()->findAll();
    if (count($patients) > 0) {
      // add FK will fail, mend broken subsp. ids:
      // (note that since no other installations are using the clinic,
      // this is perfectly safe to do)
      $criteria = new CDbCriteria();
      $criteria->addCondition('name=:name');
      $criteria->params = array(':name' => 'Glaucoma');
      $subspecialty = Subspecialty::model()->find($criteria);
      // all patients are linked via a clinic by the clinic ID now,
      // NOT subspeciality:
      foreach ($patients as $patient) {
        if ($patient->subspeciality_id) {
          $criteria = new CdbCriteria();
          $criteria->addCondition('subspecialty_id=:subsp_id');
          $criteria->params = array(':subsp_id' => $patient->subspeciality_id);
          $clinic = VirtualClinic::model()->find($criteria);
          $patient->virtual_clinic_id = $clinic->id;
          $patient->save();
        } else {
          // there's been an error, need to update firm ID (or won't be able
          // to add this as a FK):
          // TODO paraneterise this and extract ID based on 'James Morgan'
          // (since no other installations have used this in anger yet):
          $patient->virtual_clinic_id = $subspecialty->id;
          $patient->save();
        }
      }
    }
    $this->addForeignKey('virtual_clinic_patient_id_fk', 'virtual_clinic_patient', 'virtual_clinic_id', 'virtual_clinic', 'id');
  }

  public function safeDown() {
    echo "m130912_165421_update_clinic_ids does not support migration downwards.";
    return false;
  }

}