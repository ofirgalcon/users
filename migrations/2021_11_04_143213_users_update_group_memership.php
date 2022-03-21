<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class UsersUpdateGroupMemership extends Migration
{
    private $tableName = 'local_users';

    public function up()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->text('group_memership')->change();
        });
    }
    
    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->string('group_memership')->change();
        });
    }
}