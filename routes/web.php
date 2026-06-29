<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $recentNotes = auth()->user()->notes()->with('tags')->latest('updated_at')->take(6)->get();
        return view('dashboard', compact('recentNotes'));
    })->name('dashboard');
    Route::livewire('/tags','pages::tags.panel')->name('tags');
    Route::livewire('/notes','pages::notes.panel')->name('notes');
    Route::livewire('/notes/{note}','pages::notes.editor')->name('notes.show');
});

require __DIR__.'/settings.php';
