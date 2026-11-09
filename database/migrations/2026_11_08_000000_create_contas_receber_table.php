<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contas_receber', function (Blueprint $table) {
            $table->id();
            $table->string('cliente');
            $table->string('descricao')->nullable();
            $table->string('documento', 60)->nullable();
            $table->decimal('valor', 12, 2);
            $table->date('data_vencimento')->index();
            $table->date('data_pagamento')->nullable();
            $table->string('status', 20)->default('pendente')->index();
            $table->string('forma_pagamento', 50)->nullable();
            $table->text('observacao')->nullable();
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
        Schema::dropIfExists('contas_receber');
    }
};
