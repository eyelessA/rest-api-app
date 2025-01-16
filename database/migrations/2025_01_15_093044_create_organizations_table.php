<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_number');
            $table->unsignedBigInteger('building_id')->index();
            $table->foreign('building_id')->references('id')->on('buildings');
            $table->unsignedBigInteger('activity_id')->index();
            $table->foreign('activity_id')->references('id')->on('activities');
            $table->timestamps();
        });
//        DB::statement('CREATE INDEX idx_name_trgm ON organizations USING gin (name gin_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
