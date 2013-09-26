<?php

class m130912_141806_load_subspecialties extends CDbMigration {

  // Use safeUp/safeDown to do migration with transaction
  public function safeUp() {
    $subspecialities = Subspecialty::model()->findAll();
    foreach ($subspecialities as $index => $subspeciality) {
      $this->insert('virtual_clinic',array('subspecialty_id'=>$subspeciality->id,
          'display_name'=>$subspeciality->name, 'name'=>$subspeciality->name));
    }
  }

  public function safeDown() {
    $this->delete('virtual_clinic');
  }

}