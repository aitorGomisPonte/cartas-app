<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Route::middleware('check-user')->group(function(){
    Route::prefix('usuario')->group(function(){
     Route::put('/login',[UsuarioController::class, 'LogIn'])->withoutMiddleware("check-user");
     Route::put('/registro',[UsuarioController::class, 'RegistroUsuario']);
    //  Route::get('/listar',[UsuarioController::class, 'listaEmpleados']);
    //  Route::get('/detalles',[UsuarioController::class, 'detallesEmpleado']);
    //  Route::get('/verPerfil',[UsuarioController::class, 'verPerfil'])->withoutMiddleware("check-user");
    //  Route::post('/modificar',[UsuarioController::class, 'modificarDatos']);
    //  Route::post('recuperar',[UsuarioController::class, 'recuperarPass'])->withoutMiddleware("check-user");
    
           });
//  });
