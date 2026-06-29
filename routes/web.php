<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('/tags','pages::tags.panel')->name('tags');
    Route::livewire('/notes','pages::notes.panel')->name('notes');
    Route::livewire('/notes/{note}','pages::notes.editor')->name('notes.show');
});

require __DIR__.'/settings.php';
