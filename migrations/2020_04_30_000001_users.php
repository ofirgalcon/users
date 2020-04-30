<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class Users extends Migration
{
    private $tableName = 'users';

    public function up()
    {
        $capsule = new Capsule();

        $capsule::schema()->create($this->tableName, function (Blueprint $table) {
            $table->increments('id');

            $table->string('serial_number');
            $table->string('real_name')->nullable();
            $table->string('ard_priv')->nullable();
            $table->string('node_location')->nullable();
            $table->string('password_hint')->nullable();
            $table->string('generated_uuid')->nullable();
            $table->string('home_directory')->nullable();
            $table->string('primary_group_id')->nullable();
            $table->string('record_name')->nullable();
            $table->string('group_memership')->nullable();
            $table->boolean('administrator')->nullable();
            $table->boolean('ssh_access')->nullable();
            $table->boolean('screenshare_access')->nullable();
            $table->string('unique_id')->nullable();
            $table->string('user_shell')->nullable();
            $table->string('meta_record_name')->nullable();
            $table->string('email_address')->nullable();
            $table->string('smb_group_rid')->nullable();
            $table->string('smb_home')->nullable();
            $table->string('smb_home_drive')->nullable();
            $table->string('smb_primary_group_sid')->nullable();
            $table->string('smb_sid')->nullable();
            $table->string('smb_script_path')->nullable();
            $table->string('original_node_name')->nullable();
            $table->string('primary_nt_domain')->nullable();
            $table->bigInteger('copy_timestamp')->nullable();
            $table->bigInteger('smb_password_last_set')->nullable();
            $table->bigInteger('creation_time')->nullable();
            $table->string('failed_login_count')->nullable();
            $table->bigInteger('failed_login_timestamp')->nullable();
            $table->bigInteger('password_last_set_time')->nullable();
            $table->bigInteger('last_login_timestamp')->nullable();
            $table->string('password_history_depth')->nullable();
            $table->string('linked_full_name')->nullable();
            $table->bigInteger('linked_timestamp')->nullable();

            $table->index('serial_number');
            $table->index('real_name');
            $table->index('node_location');
            $table->index('password_hint');
            $table->index('home_directory');
            $table->index('record_name');
            $table->index('administrator');
            $table->index('ssh_access');
            $table->index('screenshare_access');
            $table->index('user_shell');
            $table->index('meta_record_name');
            $table->index('unique_id');
        });
    }

    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->dropIfExists('users');
    }
}
