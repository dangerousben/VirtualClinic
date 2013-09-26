<?php

class m130923_121217_firm_id extends CDbMigration { // Use safeUp/safeDown to do migration with transaction

  public function safeUp() {
    $this->addColumn('virtual_clinic_patient', 'firm_id', 'int(10) unsigned NOT NULL');
    $this->createIndex('firm_id_fk', 'virtual_clinic_patient', 'firm_id');

    Yii::import('VirtualClinic.models.VirtualClinicPatient', true);
    Yii::import('VirtualClinic.models.VirtualClinic', true);
    $patients = VirtualClinicPatient::model()->findAll();

    if (count($patients) > 0) {
      $criteria = new CdbCriteria();
      $criteria->addCondition('name=:name');
      $criteria->params = array(':name' => 'James Morgan');
      $firm = Firm::model()->find($criteria);
      foreach ($patients as $patient) {
        if ($patient->seen_by_user_id != null && $patient->seen_by_user_id > 0) {
          $this->execute('update virtual_clinic_patient '
                  . ' set firm_id=' . $patient->seen_by_user_id
                  . ' where patient_id=' . $patient->patient_id);
        } else {
          // there's been an error, need to update firm ID (or won't be able
          // to add this as a FK):
          // TODO paraneterise this and extract ID based on 'James Morgan'
          // (since no other installations have used this in anger yet):
          $this->execute('update virtual_clinic_patient '
                  . ' set firm_id=' . $firm->id
                  . ' where patient_id=' . $patient->patient_id);
        }
      }
    }
    $this->addForeignKey('firm_id_fk', 'virtual_clinic_patient', 'firm_id', 'firm', 'id');
  }

  /**
   * 
   */
  public function safeDown() {
    $this->dropForeignKey('firm_id_fk', 'virtual_clinic_patient');
    $this->dropColumn('virtual_clinic_patient', 'firm_id');
  }

}