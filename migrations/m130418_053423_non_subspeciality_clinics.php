<?php

require_once 'DbHelper.php';

class m130418_053423_non_subspeciality_clinics extends DbHelper {
    
    private $tablename = 'virtual_clinic_type';
    
    public function safeUp() {
        $this->createTable($this->tablename, array_merge(array(
                    'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
                    'display_name' => 'varchar(50)',
                    'name' => 'varchar(50)',
                        ), $this->getDefaults($this->tablename, false)), 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin');
        
        $this->addColumn('virtual_clinic_patient', 'clinic_type_id',
                'int(10) unsigned NULL');
        $this->createIndex('virtual_clinic_patient_clinic_type_id_fk',
                'virtual_clinic_patient', 'clinic_type_id');
        $this->addForeignKey('virtual_clinic_patient_clinic_type_id_fk',
                'virtual_clinic_patient', 'clinic_type_id',
                $this->tablename, 'id');
    }

    public function safeDown() {
        $this->dropForeignKey('virtual_clinic_patient_clinic_type_id_fk', 'virtual_clinic_patient');
        $this->dropIndex('virtual_clinic_patient_clinic_type_id_fk', 'virtual_clinic_patient');
        $this->dropColumn('virtual_clinic_patient', 'clinic_type_id');
        $this->dropTable($this->tablename);
    }

}
