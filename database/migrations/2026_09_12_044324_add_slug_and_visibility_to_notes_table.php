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
        Schema::table('notes', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
            $table->enum('visibility', ['private', 'public'])->default('private')->after('content');
        });

        $this->backfillSlugs();

        DB::statement('CREATE UNIQUE INDEX notes_private_slug_unique ON notes (user_id, slug) WHERE visibility = \'private\'');
        DB::statement('CREATE UNIQUE INDEX notes_public_slug_unique ON notes (slug) WHERE visibility = \'public\'');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS notes_private_slug_unique');
        DB::statement('DROP INDEX IF EXISTS notes_public_slug_unique');

        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['slug', 'visibility']);
        });
    }

    /**
     * Give every existing note a slug so the new unique indexes have
     * something valid to enforce against.
     */
    private function backfillSlugs(): void
    {
        DB::table('notes')->orderBy('id')->select('id', 'user_id', 'title')->get()
            ->groupBy('user_id')
            ->each(function ($notes) {
                $used = [];

                foreach ($notes as $note) {
                    $base = collect(explode('/', $note->title))
                        ->map(fn (string $segment) => Str::slug($segment) ?: 'nota')
                        ->implode('/');

                    $slug = $base;
                    $suffix = 2;
                    while (in_array($slug, $used, true)) {
                        $slug = $base.'-'.$suffix++;
                    }
                    $used[] = $slug;

                    DB::table('notes')->where('id', $note->id)->update(['slug' => $slug]);
                }
            });
    }
};
