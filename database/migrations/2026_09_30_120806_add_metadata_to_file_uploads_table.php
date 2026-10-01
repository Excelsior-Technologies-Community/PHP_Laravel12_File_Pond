<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_uploads', function (Blueprint $table) {
            $table->string('original_filename')->nullable()->after('filename');
            $table->string('mime_type')->nullable()->after('original_filename');
            $table->unsignedBigInteger('size')->default(0)->after('mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('file_uploads', function (Blueprint $table) {
            $table->dropColumn([
                'original_filename',
                'mime_type',
                'size',
            ]);
        });
    }
};