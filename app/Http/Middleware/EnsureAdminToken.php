<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Usuario;
use Illuminate\Http\Request;

class EnsureAdminToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    /*Este middlaware se encarga de comprobar que sea un administrador el que esta haciendo la peticion. */
    public function handle(Request $request, Closure $next)
    {
        $respuesta = ["status" => 1,"msg" => ""];//Usamos esto para comunicarnos con el otro lado del servidor
        $datos = $request->getContent(); //Nos recibimos los datos por el body
        $datos = json_decode($datos); //Decodificamos el json para poder ver los distintos componentes
        
       try {
          
          if(isset($datos->api_token)){
                $user = Usuario::Where("api_token",$datos->api_token)->first();
                if($user){
                    if($user->role_usuario == "Administrador"){
                        return $next($request);
                    }else{
                        $respuesta['msg'] = "El usuario no teine permisos para ejecutar esta funcion";
                        $respuesta['status'] = 0;
                    }
                }else{
                    $respuesta['msg'] = "El token no existe";
                    $respuesta['status'] = 0;
                }
          }else{
                $respuesta['msg'] = "No se han pasado los datos adecuados";
                $respuesta['status'] = 0;
          }
       } catch (\Exception $e) {
          $respuesta['msg'] = $e->getMessage();
          $respuesta['status'] = 0;
       }          
    return response()->json($respuesta);
    }
}

