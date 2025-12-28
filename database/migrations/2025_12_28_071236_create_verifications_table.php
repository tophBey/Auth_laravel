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
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->string('uniq_id');
            $table->unsignedBigInteger('user_id');
            $table->string('otp');
            $table->enum('type',['register', 'reset_password']);
            $table->enum('send_via', ['email', 'whatsapp', 'sms']);
            $table->integer('resend')->default(0);
            $table->enum('status', ['active','valid', 'invalid']);

            $table->foreign('user_id')->on('users')->references('id')->onDelete('cascade');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifications');
    }
};
