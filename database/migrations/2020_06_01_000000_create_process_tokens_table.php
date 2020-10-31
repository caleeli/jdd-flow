<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('process_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('process_id');
            $table->string('element');
            $table->string('status');
            $table->string('type');
            $table->string('name')->nullable();
            $table->string('implementation')->nullable();
            $table->string('index')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->timestamps();
            $table->foreign('process_id')
                ->references('id')->on('processes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->index(['element', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('process_tokens');
    }
}
