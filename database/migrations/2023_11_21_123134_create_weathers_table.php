<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weathers', function (Blueprint $table) {
            $table->id();
            $table->string('location', 100);
            $table->integer('no'); // 1~
            $table->string('mode'); // current or forecast
            $table->datetime('datetime');
            $table->string('weather1', 10);
            $table->string('weather2', 10);
            $table->float('temperature');
            $table->float('rainfall');
            $table->float('wind');
            $table->float('pressure');
            $table->timestamps();

            $table->unique(['location', 'mode', 'datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weathers');
    }
};
