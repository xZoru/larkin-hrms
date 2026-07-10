<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add position_id foreign key
            $table->foreignId('position_id')
                ->nullable()
                ->after('department_id')
                ->constrained()
                ->nullOnDelete();

            // Drop the old position column (optional - or keep for backward compatibility)
            // $table->dropColumn('position');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
        });
    }
};