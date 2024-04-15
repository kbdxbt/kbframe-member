<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->comment('会员表');
            $table->id();
            $table->string('username');
            $table->string('nickname')->nullable();
            $table->string('password');
            $table->string('auth_key')->nullable();
            $table->string('remember_token')->nullable();
            $table->tinyInteger('type')->nullable();
            $table->tinyInteger('source')->nullable();
            $table->string('mobile')->nullable();
            $table->integer('pid')->nullable();
            $table->tinyInteger('is_real_auth')->nullable()->default(0)->comment(\Modules\Core\Enums\BooleanEnum::allToDatabaseNote('是否认证'));
            $table->tinyInteger('is_bind_mobile')->nullable()->default(0)->comment(\Modules\Core\Enums\BooleanEnum::allToDatabaseNote('是否绑定手机'));
            $table->status()->comment(\Modules\Core\Enums\StatusEnum::allToDatabaseNote('状态'));
            $table->extJson();
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
        Schema::dropIfExists('member');
    }
};
