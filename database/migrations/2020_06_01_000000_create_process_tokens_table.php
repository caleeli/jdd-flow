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
            $table->bigInteger('instance_id');
            $table->bigInteger('user_id')->nullable();
            $table->string('definitions');
            $table->string('element');
            $table->string('status');
            $table->string('type');
            $table->string('name')->nullable();
            $table->string('implementation')->nullable();
            $table->string('index')->nullable();
            $table->text('log')->nullable();
            $table->timestamps();
            $table->foreign('instance_id')
                ->references('id')->on('process_instances')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->index(['element', 'status']);
            $table->index(['definitions', 'element', 'status']);
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
