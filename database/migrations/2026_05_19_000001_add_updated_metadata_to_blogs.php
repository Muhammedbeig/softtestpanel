<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('blogs')) {
            return;
        }

        Schema::table('blogs', function (Blueprint $table) {
            if (! Schema::hasColumn('blogs', 'updated_on')) {
                $table->date('updated_on')->nullable()->after('published_at');
            }

            if (! Schema::hasColumn('blogs', 'updated_by_author_id')) {
                $table->foreignId('updated_by_author_id')
                    ->nullable()
                    ->after('updated_on')
                    ->constrained('authors')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('blogs')) {
            return;
        }

        Schema::table('blogs', function (Blueprint $table) {
            if (Schema::hasColumn('blogs', 'updated_by_author_id')) {
                $table->dropConstrainedForeignId('updated_by_author_id');
            }

            if (Schema::hasColumn('blogs', 'updated_on')) {
                $table->dropColumn('updated_on');
            }
        });
    }
};
