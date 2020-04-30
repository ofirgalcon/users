Users module
==============

Users module for MunkiReport. Gathers information about user accounts on the Mac.



Table Schema
-----

Database:
* real_name - varchar(255) - Full username
* ard_priv - varchar(255) - ARD permissions bit
* node_location - varchar(255) - Records source
* password_hint - varchar(255) - Password hint
* generated_uuid - varchar(255) - Generated UUID
* home_directory - varchar(255) - Home directory path
* primary_group_id - varchar(255) - Primary group ID
* record_name - varchar(255) - Username
* group_memership - varchar(255) - Groups user is a part of
* administrator - boolean - Is an administrator
* ssh_access - boolean - SSH access
* screenshare_access - boolean - Screen Sharing access
* unique_id - varchar(255) - UID of account
* user_shell - varchar(255) - User's shell
* meta_record_name - varchar(255) - Account record name on network
* email_address - varchar(255) - Network email address
* smb_group_rid - varchar(255) - AD group RID
* smb_home - varchar(255) - Network home address
* smb_home_drive - varchar(255) - Network home drive letter
* smb_primary_group_sid - varchar(255) - AD primary group SID
* smb_sid - varchar(255) - AD SID
* smb_script_path - varchar(255) - AD logon script path
* original_node_name - varchar(255) - OU of account
* primary_nt_domain - varchar(255) - Primary NT domain
* copy_timestamp - bigint - Mobile account copy time
* smb_password_last_set - bigint - Network password last set
* creation_time - bigint - Account creation time
* failed_login_count - varchar(255) - Failed login count
* failed_login_timestamp - bigint - Last failed login timestamp
* password_last_set_time - bigint - When password was last set
* last_login_timestamp - bigint - Timestamp of last login
* password_history_depth - varchar(255) - Number of passwords to have as history
* linked_full_name - varchar(255) - Linked Apple ID
* linked_timestamp - bigint - Timestamp of when Apple ID was linked to account


