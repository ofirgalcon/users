<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class UsersIsHidden extends Migration
{
    private $tableName = 'local_users';

    public function up()
    {
        $capsule = new Capsule();

        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->boolean('is_hidden')->nullable();
        });

        // Create indexes
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->index('is_hidden');
        });
    }

    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('is_hidden');
        });
    }
}
