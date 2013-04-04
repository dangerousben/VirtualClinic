<?php

class m130402_121135_initial_migration extends CDbMigration {

  private $tablename = 'virtual_clinic_patient';

  /**
   * Returns all the default table array elements that all tables share.
   * This is a convenience method for all table creation.
   * 
   * @param $suffix the table name suffix - this is the name of the table
   * without the formal table name 'et_[spec][group][code]_'.
   * 
   * @param useEvent by default, the event type is created as a foreign
   * key to the event table; set this to false to not create this key.
   * 
   * @return an array of defaults to merge in to the table array data required.
   */
  function getDefaults($table_name, $useEvent = true, $key_names = array('eid_fk' => '_event_id_fk',
      'cuid_fk' => '_created_user_id_fk',
      'lmuid_fk' => '_last_modified_user_id_fk')) {
    $defaults = array('last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
        'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01
			00:00:00\'',
        'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
        'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
        'PRIMARY KEY (`id`)',
        'KEY `' . $table_name . $key_names['eid_fk'] . '` (`event_id`)',
        'KEY `' . $table_name . $key_names['cuid_fk'] . '`
			(`created_user_id`)',
        'KEY `' . $table_name . $key_names['lmuid_fk'] . '`
			(`last_modified_user_id`)',
        'CONSTRAINT `' . $table_name . $key_names['eid_fk'] . '` FOREIGN KEY
			(`event_id`) REFERENCES `event` (`id`)',
        'CONSTRAINT `' . $table_name . $key_names['cuid_fk'] . '`
			FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
        'CONSTRAINT
			`' . $table_name . $key_names['lmuid_fk'] . '` FOREIGN KEY
			(`last_modified_user_id`) REFERENCES `user` (`id`)',);
    if ($useEvent == false) {
      $defaults = array('last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
          'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01
        00:00:00\'',
          'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
          'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
          'PRIMARY KEY (`id`)',
          'KEY `' . $table_name . $key_names['cuid_fk'] . '`
			(`created_user_id`)',
          'KEY `' . $table_name . $key_names['lmuid_fk'] . '`
			(`last_modified_user_id`)',
          'CONSTRAINT `' . $table_name . $key_names['cuid_fk'] . '`
			FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
          'CONSTRAINT
			`' . $table_name . $key_names['lmuid_fk'] . '` FOREIGN KEY
			(`last_modified_user_id`) REFERENCES `user` (`id`)');
    }
    return $defaults;
  }

  public function safeUp() {

    $this->createTable($this->tablename, array_merge(array(
                'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
                'patient_id' => 'int(10) unsigned',
                'subspeciality_id' => 'int(10) unsigned default 0',
                'site_id' => 'int(10) unsigned default 0',
                'seen_by_user_id' => 'int(10) unsigned default 0',
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