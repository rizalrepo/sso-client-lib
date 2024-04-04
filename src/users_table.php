<?php
// copy code dibawah ini ganti code dibawah pada file create_users_table

public function up(): void
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('username')->unique();
        $table->string('phone')->unique();
        $table->bigInteger('oauth_client_role_id');
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
    });
}
