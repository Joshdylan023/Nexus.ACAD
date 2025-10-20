<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aqui é onde registamos as rotas web para a nossa aplicação.
| Para uma SPA, geralmente temos apenas uma rota "catch-all".
|
*/

/**
 * Rota "Catch-All" para a Aplicação Vue.js
 *
 * Esta rota garante que qualquer URL que não seja uma API (ex: /admin/cursos, /login, etc.)
 * carregue a mesma view Blade (`app.blade.php`). Uma vez que a página é carregada,
 * o Vue Router assume o controlo e renderiza o componente correto com base na URL.
 */
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

Route::get('/relatorios', function () {
    return inertia('Relatorios/Relatorios');
})->name('relatorios.index');

Route::middleware(['auth'])->group(function () {
    Route::get('/change-password', [App\Http\Controllers\Auth\ChangePasswordController::class, 'show'])->name('password.change');
    Route::post('/change-password', [App\Http\Controllers\Auth\ChangePasswordController::class, 'update'])->name('password.update');
});