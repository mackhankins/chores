<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::checkin')->name('checkin');
Route::livewire('/chores', 'pages::chores')->name('chores');
