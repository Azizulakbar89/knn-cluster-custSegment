<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('knn_train', function (Blueprint $table) {
            $table->id();
            $table->string('sex');
            $table->string('marital_status');
            $table->integer('age');
            $table->string('education');
            $table->decimal('income', 10, 2);
            $table->string('occupation');
            $table->string('settlement_size');
            $table->timestamps();
        });

        Schema::create('knn_test', function (Blueprint $table) {
            $table->id();
            $table->string('sex');
            $table->string('marital_status');
            $table->integer('age');
            $table->string('education');
            $table->decimal('income', 10, 2);
            $table->string('occupation');
            $table->string('settlement_size')->nullable();
            $table->string('predicted_settlement')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('knn_train');
        Schema::dropIfExists('knn_test');
    }
};
