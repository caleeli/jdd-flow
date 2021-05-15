<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('process_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('process_instance_id');
            $table->unsignedBigInteger('process_token_id');
            $table->string('definitions');
            $table->string('element');
            $table->string('event');
            $table->string('name')->nullable();
            $table->timestamps();
            $table->foreign('instance_id')
                ->references('id')->on('process_instances')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('token_id')
                ->references('id')->on('process_tokens')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('process_actions');
    }
}
