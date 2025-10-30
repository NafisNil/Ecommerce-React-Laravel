<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
        });

        // Backfill slugs for existing records, unique per department
        $categories = DB::table('categories')->select('id', 'name', 'department_id', 'slug')->orderBy('id')->get();
        $used = [];
        foreach ($categories as $cat) {
            $dept = (string) $cat->department_id;
            if (!isset($used[$dept])) $used[$dept] = [];

            $base = Str::slug($cat->name ?: 'category');
            $slug = $base;
            $suffix = 1;
            while (in_array($slug, $used[$dept], true)) {
                $slug = $base.'-'.$suffix;
                $suffix++;
            }
            $used[$dept][] = $slug;

            if (empty($cat->slug)) {
                DB::table('categories')->where('id', $cat->id)->update(['slug' => $slug]);
            }
        }

        // Add a composite unique index to ensure uniqueness per department
        Schema::table('categories', function (Blueprint $table) {
            try {
                // Attempt to detect existing index using Doctrine (if installed)
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = array_map(fn($idx) => $idx->getName(), $sm->listTableIndexes('categories'));
                if (!in_array('categories_department_id_slug_unique', $indexes, true)) {
                    $table->unique(['department_id', 'slug']);
                }
            } catch (\Throwable $e) {
                // Fallback: try to add the unique index (migration runs once)
                try { $table->unique(['department_id', 'slug']); } catch (\Throwable $ignored) {}
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drop unique index if exists
            try {
                $table->dropUnique('categories_department_id_slug_unique');
            } catch (\Throwable $e) {
                // ignore
            }
            if (Schema::hasColumn('categories', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
