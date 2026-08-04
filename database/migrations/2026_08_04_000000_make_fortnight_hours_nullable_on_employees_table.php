<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * fortnight_hours becomes nullable. NULL now means "no per-employee
     * override — inherit the company's regular_hours." This is distinct
     * from an explicit value, so it must not be confused with 0 or 84.
     *
     * NOTE: adjust the column type below (integer/decimal) to match
     * whatever migration originally added fortnight_hours — verify with:
     *   php artisan tinker --execute="dd(DB::selectOne(\"SHOW COLUMNS FROM employees WHERE Field = 'fortnight_hours'\"));"
     * This uses integer as a best guess; change to decimal(8,2) if needed.
     * Requires doctrine/dbal for ->change().
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('fortnight_hours')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('fortnight_hours')->default(84)->nullable(false)->change();
        });
    }
};
