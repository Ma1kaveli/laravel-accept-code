<?php

namespace AcceptCode\Database\Migrations;

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
        Schema::create('accept_codes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->index();

            $table->string('credetinal');
            $table->string('code');
            $table->enum('type', ['email', 'phone']);
            $table->enum('slug', config('accept-code.accept_code_slugs'));

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accept_codes');
    }
};
