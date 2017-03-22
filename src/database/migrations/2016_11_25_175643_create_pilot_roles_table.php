<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use VivienLN\Pilot\PilotRole;

class CreatePilotRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('pilot_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label');
            $table->string('slug');
            $table->timestamps();
        });

        PilotRole::create([
            'label' => 'Super Admin',
            'slug' => str_slug('Super Admin')
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pilot_roles');
    }
}
