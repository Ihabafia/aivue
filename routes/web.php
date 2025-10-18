<?php

use App\Http\Controllers\BlogEvaluationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Blog Evaluation Routes
|--------------------------------------------------------------------------
|
| Routes for the blog post evaluation system using 5 different AI LLMs.
|
*/

Route::get('/blog-evaluator', [BlogEvaluationController::class, 'index'])
    ->name('blog-evaluator.index');

Route::post('/api/blog-evaluator/evaluate', [BlogEvaluationController::class, 'evaluate'])
    ->name('blog-evaluator.evaluate');
