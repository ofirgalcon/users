<?php 

/**
 * Users module class
 *
 * @package munkireport
 * @author tuxudo
 **/
class Users_controller extends Module_controller
{

	/*** Protect methods with auth! ****/
	function __construct()
	{
		// Store module path
		$this->module_path = dirname(__FILE__);
        
        // Add local config
        configAppendFile(__DIR__ . '/config.php');
	}

	/**
	 * Default method
	 * @author tuxudo
	 *
	 **/
	function index()
	{
		echo "You've loaded the users module!";
	}

    /**
     * REST API for retrieving screen sharing users for widget
     * @tuxudo
     *
     **/
     public function screensharing_users()
     {
        jsonView(
            Users_model::selectRaw('record_name, COUNT(record_name) AS count')
                ->where('screenshare_access', '=', 1)
                ->filter()
                ->groupBy('record_name')
                ->orderBy('count', 'desc')
                ->get()
                ->toArray()
        );
   }

    /**
     * REST API for retrieving users with automatic login enabled for widget
     * @tuxudo
     *
     **/
     public function autologin_users()
     {
        jsonView(
            Users_model::selectRaw('record_name, COUNT(record_name) AS count')
                ->where('autologin_enabled', '=', 1)
                ->filter()
                ->groupBy('record_name')
                ->orderBy('count', 'desc')
                ->get()
                ->toArray()
        );
   }

    /**
     * REST API for retrieving ssh users for widget
     * @tuxudo
     *
     **/
     public function ssh_users()
     {
        jsonView(
            Users_model::selectRaw('record_name, COUNT(record_name) AS count')
                ->where('ssh_access', '=', 1)
                ->filter()
                ->groupBy('record_name')
                ->orderBy('count', 'desc')
                ->get()
                ->toArray()
        );
   }

    /**
     * REST API for retrieving local admin users count for widget
     * @tuxudo
     *
     **/
     public function get_local_admin()
     {
        $threshold = (int) conf('users_local_admin_threshold');
         
        jsonView(
            Users_model::selectRaw('local_users.serial_number, machine.computer_name, COUNT(local_users.record_name) AS count')
                ->join('machine', 'machine.serial_number', '=', 'local_users.serial_number')
                ->where('administrator', '=', 1)
                ->having('count', '>=', $threshold)
                ->filter()
                ->orderBy('count', 'desc')
                ->groupBy('machine.computer_name', 'local_users.serial_number')
                ->get()
                ->toArray()             
        );
   }

    /**
     * REST API for retrieving admin users for widget
     * @tuxudo
     *
     **/
     public function admin_users()
     {
         jsonView(
            Users_model::selectRaw('record_name, COUNT(record_name) AS count')
                ->where('administrator', '=', 1)
                ->filter()
                ->groupBy('record_name')
                ->orderBy('count', 'desc')
                ->get()
                ->toArray()
         );
   }

	/**
     * Retrieve data in json format
     *
     **/
    public function get_tab_data($serial_number = '')
    {
         jsonView(
            Users_model::selectRaw('record_name, real_name, unique_id, password_hint, home_directory, primary_group_id, administrator, ssh_access, screenshare_access, autologin_enabled, user_shell, generated_uuid, last_login_timestamp, creation_time, password_last_set_time, failed_login_count, failed_login_timestamp, password_history_depth, linked_full_name, linked_timestamp, group_memership, meta_record_name, email_address, smb_group_rid, smb_home, smb_home_drive, smb_primary_group_sid, smb_sid, smb_script_path, smb_password_last_set, original_node_name, primary_nt_domain, copy_timestamp')
                ->where('local_users.serial_number', $serial_number)
                ->filter()
                ->get()
                ->toArray()
         );
    }
} // End class Users_controller
