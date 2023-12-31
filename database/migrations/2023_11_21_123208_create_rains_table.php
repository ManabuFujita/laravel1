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
        Schema::create('rains', function (Blueprint $table) {
            $table->id();
            $table->string('location', 100);
            $table->integer('no'); // 1~6
            $table->datetime('datetime');
            $table->float('rainfall');
            $table->timestamps();

            $table->unique(['location', 'no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rains');
    }
};
