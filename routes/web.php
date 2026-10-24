<?php

use App\Http\Controllers\EmbedController;
use App\Models\Note;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $wikiNotes = Note::with('tags', 'user')->where('visibility', 'public')->latest('updated_at')->take(6)->get();

    return view('welcome', compact('wikiNotes'));
})->name('home');

// Public wiki: readable by anyone, without logging in. Editing/creating
// stays gated behind auth checks inside the components themselves so
// wikilinks embedded in a public page keep working for guests.
Route::livewire('/wiki', 'pages::wiki.panel')->name('wiki');
Route::livewire('/wiki/{slug}', 'pages::wiki.editor')->name('wiki.show')->where('slug', '.*');

// Embeds: sandboxed HTML/JS pages. Readable by anyone (public wiki pages can
// include them); creating and editing is gated inside the components.
Route::livewire('/embeds', 'pages::embeds.panel')->name('embeds');
Route::get('/embeds/{embed}/raw', [EmbedController::class, 'raw'])->name('embeds.raw');
Route::livewire('/embeds/{slug}', 'pages::embeds.show')->name('embeds.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $recentNotes = auth()->user()->notes()->with('tags')->latest('updated_at')->take(6)->get();

        return view('dashboard', compact('recentNotes'));
    })->name('dashboard');
    Route::livewire('/tags', 'pages::tags.panel')->name('tags');
    Route::livewire('/notes', 'pages::notes.panel')->name('notes');
    Route::livewire('/notes/{slug}', 'pages::notes.editor')->name('notes.show')->where('slug', '.*');
});

require __DIR__.'/settings.php';
