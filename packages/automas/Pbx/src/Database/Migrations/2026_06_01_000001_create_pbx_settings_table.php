<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pbx_settings')) {
            Schema::create('pbx_settings', function (Blueprint $table) {
                $table->id();
                $table->string('pbx_name')->nullable();
                $table->string('pbx_host')->nullable();
                $table->string('ami_host')->nullable();
                $table->unsignedInteger('ami_port')->default(5038);
                $table->string('ami_username')->nullable();
                $table->text('ami_password')->nullable();
                $table->string('sip_domain')->nullable();
                $table->string('websocket_url')->nullable();
                $table->string('stun_server')->nullable();
                $table->string('sip_trunk_name')->nullable();
                $table->unsignedInteger('extension_start')->default(100);
                $table->unsignedInteger('extension_end')->default(199);
                $table->unsignedInteger('max_extensions')->default(50);
                $table->boolean('is_enabled')->default(false)->index();
                $table->foreignId('creator_id')->nullable()->index();
                $table->foreignId('created_by')->nullable()->index();
                $table->timestamps();

                $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pbx_settings');
    }
};
