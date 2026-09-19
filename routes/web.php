<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Public wiki: readable by anyone, without logging in. Editing/creating
// stays gated behind auth checks inside the components themselves so
// wikilinks embedded in a public page keep working for guests.
Route::livewire('/wiki', 'pages::wiki.panel')->name('wiki');
Route::livewire('/wiki/{slug}', 'pages::wiki.editor')->name('wiki.show')->where('slug', '.*');

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
