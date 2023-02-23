<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('code');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('countries')->insert(
            [
                [
                    'name' => 'Ukraine',
                    'code' => 'UA',
                ],
                [
                    'name' => 'Italy',
                    'code' => 'IT',
                ],
                [
                    'name' => 'Germany',
                    'code' => 'DE',
                ],
                [
                    'name' => 'Germany',
                    'code' => 'DE',
                ],
            ]);

        Schema::create('products_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('products_id');
            $table->string('name');
            $table->string('sku')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products_distribution', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('products_id');
            $table->string('name');
            $table->string('countries');
            $table->float('price');
            $table->string('currency', 3);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
