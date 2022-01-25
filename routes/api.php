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



    Route::prefix('usuario')->group(function(){
        Route::post('/login',[UsuarioController::class, 'LogIn']);
        Route::put('/registro',[UsuarioController::class, 'RegistroUsuario']);
        Route::get('/recuperarPass',[UsuarioController::class, 'RecuperarPassword']);
    });

Route::middleware('check-token')->group(function(){      
    Route::prefix('cards')->group(function(){
        Route::put('/crear',[CardController::class, 'CrearCard']);
        Route::put('/darAlta',[CardController::class, 'DarAltaCarta'])->middleware('check-admin');
        Route::put('/asociarCarta',[CardController::class, 'AsociarCarta']);
        Route::put('/ponerVenta',[CardController::class, 'PonerCartaVenta']);
        Route::get('/buscarCartaVenta',[CardController::class, 'BuscarCartasIdVender']);
        Route::get('/buscarCartas',[CardController::class, 'BuscarCartasId'])->withoutMiddleware("check-token");       
    }); 
}); 

Route::middleware('check-token')->group(function(){   
    Route::prefix('collection')->group(function(){
        Route::put('/crear',[CollectionController::class, 'CrearCollection']);
        Route::put('/darAlta',[CollectionController::class, 'DarAltaCollection']);
        Route::get('/buscarNombre',[CollectionController::class, 'BuscarIdCollection']);                 
    });   
});                     

