<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('background_image_id')->nullable();
            $table->unsignedInteger('avatar_image_id')->nullable();

            $table
                ->foreign('background_image_id')
                ->references('id')
                ->on('media')
                ->onDelete('set null');

            $table
                ->foreign('avatar_image_id')
                ->references('id')
                ->on('media')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('background_image_id');
            $table->dropColumn('avatar_image_id');
        });
    }
};
