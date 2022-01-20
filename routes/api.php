<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CollectionController;



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
        Route::put('/login',[UsuarioController::class, 'LogIn']);//->withoutMiddleware("check-user");
        Route::post('/registro',[UsuarioController::class, 'RegistroUsuario']);
        Route::get('/recuperarPass',[UsuarioController::class, 'RecuperarPassword']);
    //  Route::get('/listar',[UsuarioController::class, 'listaEmpleados']);
    //  Route::get('/detalles',[UsuarioController::class, 'detallesEmpleado']);
    //  Route::get('/verPerfil',[UsuarioController::class, 'verPerfil'])->withoutMiddleware("check-user");
    //  Route::post('/modificar',[UsuarioController::class, 'modificarDatos']);
    //  Route::post('recuperar',[UsuarioController::class, 'recuperarPass'])->withoutMiddleware("check-user");
    
           });
    Route::prefix('cards')->group(function(){
        Route::put('/crear',[CardController::class, 'CrearCard']);//->withoutMiddleware("check-user");
        Route::post('/registro',[UsuarioController::class, 'RegistroUsuario']);
        Route::get('/recuperarPass',[UsuarioController::class, 'RecuperarPassword']);
    //  Route::get('/listar',[UsuarioController::class, 'listaEmpleados']);
    //  Route::get('/detalles',[UsuarioController::class, 'detallesEmpleado']);
    //  Route::get('/verPerfil',[UsuarioController::class, 'verPerfil'])->withoutMiddleware("check-user");
    //  Route::post('/modificar',[UsuarioController::class, 'modificarDatos']);
    //  Route::post('recuperar',[UsuarioController::class, 'recuperarPass'])->withoutMiddleware("check-user");
           
                  }); 
    Route::prefix('collection')->group(function(){
        Route::put('/crear',[CollectionController::class, 'CrearCollection']);//->withoutMiddleware("check-user");
        Route::post('/registro',[UsuarioController::class, 'RegistroUsuario']);
        Route::get('/recuperarPass',[UsuarioController::class, 'RecuperarPassword']);
                //  Route::get('/listar',[UsuarioController::class, 'listaEmpleados']);
                //  Route::get('/detalles',[UsuarioController::class, 'detallesEmpleado']);
                //  Route::get('/verPerfil',[UsuarioController::class, 'verPerfil'])->withoutMiddleware("check-user");
                //  Route::post('/modificar',[UsuarioController::class, 'modificarDatos']);
                //  Route::post('recuperar',[UsuarioController::class, 'recuperarPass'])->withoutMiddleware("check-user");
                       
                              });                     
//  });
