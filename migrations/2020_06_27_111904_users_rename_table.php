<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class UsersRenameTable extends Migration
{
    private $tableName = 'users';
    private $tableNameV2 = 'local_users';

    public function up()
    {
        $capsule = new Capsule();
        if ($capsule::schema()->hasTable($this->tableName)) {
            $capsule::schema()->rename($this->tableName, $this->tableNameV2);
        }

    }
    
    public function down()
    {
        $capsule = new Capsule();
        if ($capsule::schema()->hasTable($this->tableNameV2)) {
            $capsule::schema()->rename($this->tableNameV2, $this->tableName);
        }
    }
}
