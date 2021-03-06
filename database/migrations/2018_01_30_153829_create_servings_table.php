<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id')->unique();
            $table->unsignedBigInteger('ticket_id')->index();
            $table->unsignedInteger('staff_id');
            $table->unsignedInteger('branch_counter_id');
            $table->datetime('serve_time');
            $table->datetime('done_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('servings');
        Schema::enableForeignKeyConstraints(); 
    }
}
