<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // depots
        Schema::create('depots', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('地区id');
            $table->bigInteger('user_id')->index()->comment('用户id');
            $table->string('path', 255)->comment('图片地址');
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement("ALTER TABLE depots comment '图库表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('depots');
    }
}
