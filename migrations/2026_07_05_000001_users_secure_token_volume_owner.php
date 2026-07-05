<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class UsersSecureTokenVolumeOwner extends Migration
{
    private $tableName = 'local_users';

    public function up()
    {
        $capsule = new Capsule();

        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->boolean('secure_token')->nullable();
            $table->boolean('volume_owner')->nullable();
        });

        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->index('secure_token');
            $table->index('volume_owner');
        });
    }

    public function down()
    {
        $capsule = new Capsule();

        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->dropColumn(['secure_token', 'volume_owner']);
        });
    }
}
