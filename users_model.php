<?php

use munkireport\models\MRModel as Eloquent;

class Users_model extends Eloquent
{
    protected $table = 'local_users';

    protected $fillable = [
		'serial_number',
		'real_name',
		'ard_priv',
		'node_location',
		'password_hint',
		'generated_uuid',
		'home_directory',
		'primary_group_id',
		'record_name',
		'group_memership',
		'administrator',
		'ssh_access',
		'screenshare_access',
		'unique_id',
		'user_shell',
		'meta_record_name',
		'email_address',
		'smb_group_rid',
		'smb_home',
		'smb_home_drive',
		'smb_primary_group_sid',
		'smb_sid',
		'smb_script_path',
		'original_node_name',
		'primary_nt_domain',
		'copy_timestamp',
		'smb_password_last_set',
		'creation_time',
		'failed_login_count',
		'failed_login_timestamp',
		'password_last_set_time',
		'last_login_timestamp',
		'password_history_depth',
		'linked_full_name',
		'linked_timestamp',
    ];

    public $timestamps = false;
}
