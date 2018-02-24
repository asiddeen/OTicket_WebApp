<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueuesTable extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id')->unique();
            $table->unsignedInteger('branch_service_id');
            $table->integer('ticket_serving_now')->nullable();
            $table->integer('wait_time');
            $table->bigInteger('start_time');
            $table->bigInteger('end_time');
            $table->string('status');
            $table->text('ticket_ids')->nullable();
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
        Schema::dropIfExists('queues');
        Schema::enableForeignKeyConstraints(); 
    }
}
