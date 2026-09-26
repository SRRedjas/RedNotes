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

        $this->addSlugScopes();
        $this->addSlugIndexes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The indexes have to go before the generated columns they are built
        // on: SQLite refuses to drop an indexed column.
        Schema::table('notes', function (Blueprint $table) {
            $table->dropUnique('notes_private_slug_unique');
            $table->dropUnique('notes_public_slug_unique');
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['private_slug_scope', 'public_slug_scope']);
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['slug', 'visibility']);
        });
    }

    /**
     * Generated columns that carry the scope each slug has to be unique
     * within: the owner's id for a private note, a constant for a public wiki
     * page (they share one global namespace).
     *
     * They exist because the rule is conditional and MySQL, unlike SQLite and
     * Postgres, has no partial ("WHERE ...") indexes. Each column is NULL for
     * the rows its rule does not cover, and a unique index never enforces a
     * key holding a NULL, so indexing these columns applies each rule to
     * exactly the rows it belongs to. The columns are VIRTUAL (computed on
     * read, no storage) because that is the only kind SQLite's ALTER TABLE
     * accepts; MySQL indexes them just as happily as stored ones.
     */
    private function addSlugScopes(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->unsignedBigInteger('private_slug_scope')
                ->virtualAs("CASE WHEN visibility = 'private' THEN user_id END")
                ->after('visibility');

            $table->unsignedBigInteger('public_slug_scope')
                ->virtualAs("CASE WHEN visibility = 'public' THEN 1 END")
                ->after('private_slug_scope');
        });
    }

    /**
     * One unique index per scope. Kept in its own statement so the generated
     * columns above are guaranteed to exist first on every driver.
     */
    private function addSlugIndexes(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->unique(['private_slug_scope', 'slug'], 'notes_private_slug_unique');
            $table->unique(['public_slug_scope', 'slug'], 'notes_public_slug_unique');
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
