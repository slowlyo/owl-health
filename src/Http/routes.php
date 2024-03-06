<?php

use Slowlyo\OwlHealth\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('owl-health', [Controllers\OwlHealthController::class, 'index']);
