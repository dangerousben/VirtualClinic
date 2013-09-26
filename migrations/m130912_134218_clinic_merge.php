<?php

/**
 * Originally, subspeciality clinics came for 'free' and did not need any
 * database migrating, and could just define a module by certain naming
 * restrictions and it would work out of the box. This came at a cost -
 * some ugly offsetting that would not work later if new subspecialities were
 * added. This migration solves this by creating default subspeciality
 * entries based on existing subspecialities.
 */
class m130912_134218_clinic_merge extends CDbMigration {

  public function safeUp() {
    // rename table:
    $this->renameTable('virtual_clinic_type', 'virtual_clinic');
    $this->addColumn('virtual_clinic', 'module_name', 'varchar(50) NOT NULL');
    
    $this->addColumn('virtual_clinic', 'subspecialty_id', 'int(10) unsigned NOT NULL DEFAULT 0');
    $this->createIndex('subspecialty_id_fk', 'virtual_clinic', 'subspecialty_id');
    $this->addForeignKey('subspecialty_id_fk', 'virtual_clinic', 'subspecialty_id', 'subspecialty', 'id');
  }

  public function safeDown() {
    $this->dropForeignKey('subspecialty_id_fk', 'virtual_clinic');
    $this->dropIndex('subspecialty_id_fk', 'virtual_clinic');
    $this->dropColumn('virtual_clinic', 'subspecialty_id');
    $this->dropColumn('virtual_clinic', 'module_name');
    $this->renameTable('virtual_clinic', 'virtual_clinic_type');
  }

}