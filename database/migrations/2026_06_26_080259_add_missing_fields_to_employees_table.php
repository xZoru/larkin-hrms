<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add name fields after employee_number
            $table->string('first_name')->nullable()->after('employee_number');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('extension_name')->nullable()->after('last_name');
            
            // Add contact fields after date_of_birth (or wherever they make sense)
            $table->string('phone')->nullable()->after('date_of_birth');
            $table->string('email')->nullable()->after('phone');
            $table->text('address')->nullable()->after('email');
            
            // Add workshift after position
            $table->string('workshift')->nullable()->after('position');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'middle_name',
                'last_name',
                'extension_name',
                'phone',
                'email',
                'address',
                'workshift'
            ]);
        });
    }
};