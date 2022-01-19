<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartaVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carta_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId("id_usuario")->constrained("usuarios");
            $table->foreignId("id_carta")->constrained("cards");
            $table->integer("precio_venta");
            $table->integer("cantidad_venta");
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
        Schema::dropIfExists('carta_ventas');
    }
}
