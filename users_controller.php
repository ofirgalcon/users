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
            Users_model::selectRaw('record_name, real_name, password_hint, generated_uuid, home_directory, primary_group_id, administrator, ssh_access, screenshare_access, unique_id, user_shell, last_login_timestamp, creation_time, password_last_set_time, failed_login_count, failed_login_timestamp, password_history_depth, linked_full_name, linked_timestamp, group_memership, meta_record_name, email_address, smb_group_rid, smb_home, smb_home_drive, smb_primary_group_sid, smb_sid, smb_script_path, smb_password_last_set, original_node_name, primary_nt_domain, copy_timestamp')
                ->where('users.serial_number', $serial_number)
                ->filter()
                ->get()
                ->toArray()
        );
    }
} // End class Users_controller