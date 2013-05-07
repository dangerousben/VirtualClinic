<?php

require_once 'DbHelper.php';

class m130402_121135_initial_migration extends DbHelper {

  private $tablename = 'virtual_clinic_patient';

  public function safeUp() {

    $this->createTable($this->tablename, array_merge(array(
                'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
                'patient_id' => 'int(10) unsigned',
                'subspeciality_id' => 'int(10) unsigned default NULL',
                'site_id' => 'int(10) unsigned default 0',
                'seen_by_user_id' => 'int(10) unsigned default 0',
                'follow_up' => 'varchar(50)',
                'reviewed' => 'int(1) unsigned default 0',
                'flag' => 'int(1) unsigned default 0',
                'visit_date' => 'datetime',
                'KEY `' . $this->tablename . '_patient_id_fk`
			(`patient_id`)',
                'CONSTRAINT `' . $this->tablename . '_patient_id_fk` FOREIGN KEY
			(`patient_id`) REFERENCES `patient` (`id`)',
                    ), $this->getDefaults($this->tablename, false)), 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin');
  }

  public function safeDown() {
    $this->dropTable($this->tablename);
  }

}
