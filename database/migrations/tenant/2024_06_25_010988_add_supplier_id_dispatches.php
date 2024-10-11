<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierIdDispatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->unsignedInteger('supplier_id')->nullable();
            $table->foreign('supplier_id')->references('id')->on('persons');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    
    
    }
}
