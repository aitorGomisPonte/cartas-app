<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /*Funcion de registro de usuario:
    -Recibe: JSON(name, email, password, role)
     -primero: recibimmos los datos
     -segundo: pasamos por el validator para comporbar que se nos ha pasado correctamente la info
     -tercero: nos creamos un objeto nuevo del modelo a guardar
     -cuarto: nos rellenamos el modelo, y lo guardamos
    -Devuelbe: 0 si no se ha echo correctamente, 1 si se ha echo bien */
    public function RegistroUsuario(Request $req){

        $respuesta = ["status" => 1,"msg" => ""];

        $datos = $req->getContent();//Recibimos los datos por body
        $datos = json_decode($datos);//Decodificamos los datos

        $validator = Validator::make(json_decode($req->getContent(),true),[//Este es el validator, dodne comprobamos la validez de los datos introducidos en un json
            'nombre_usuario' => "required",//Obligatorio
            'email_usuario' => "required|unique:usuarios|email:rfc,dns",//Obligatorio, unico en la tabla de usuarios, cumple una estructura especifica (email:rfc, dns)
            'password_usuario' => "required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}/ ",//Obligatorio, 8 cifras mayusculas minusculas y numeros obligatorios
            'role_usuario' => "required",//Obligatorio, y que cumpla el enum
            
            
            ]);
            //Comporbamos el estado del validador
            if($validator->fails()){
                $respuesta['msg'] = "Ha habido un fallo con los datos introducidos";
                $respuesta['status'] = 0;    
                
            }else{//Si no ha habido ningun fallo, hacemops la crecion del objeto y lo guardamos en la base de datos
                try {
                    $user = new Usuario();
                    $user->nombre_usuario = $datos->nombre_usuario;
                    $user->email_usuario = $datos->email_usuario;
                    $user->password_usuario = Hash::make($datos->password_usuario);
                    $user->role_usuario = $datos->role_usuario;
                    $user->save();
                    $respuesta['msg'] = "Se ha registrado el nuevo usuario, con nombre: ".$datos->nombre_usuario;//Nos devolbemos un mensaje para saber quien se ha guardado (util para comprobar)
                    $respuesta['status'] = 1;  
                } catch (\Exception $e) {
                    $respuesta['msg'] = $e->getMessage();
                    $respuesta['status'] = 0; 
                }
            }
        return response()->json($respuesta);
    }
    /*En esta funcion hacemos el log in de los usuarios:
     -primero: Recibimos los datos por el body, lo decodificamos de json
     -segundo: usamos el validator para comprobar lo recibido
     -tercero: una vez comprobado, buscamos el email y la contraseña, y si estas coinsiden
     -cuarto: si es asi, nos creamos un nuevo token, guardado en la tabla para que se pueda usar durante un tiempo(en nustro caso no tenemos que se caduque, asi que sirve hasta que se ha ga otro login)
     -quinto: */
     public function LogIn(Request $req){

        $respuesta = ["status" => 1,"msg" => ""];//Usamos esto para comunicarnos con el otro lado del servidor

        $datos = $req->getContent(); //Nos recibimos los datos por el body
        $datos = json_decode($datos); //Decodificamos el json para poder ver los distintos componentes
        try {//Encapsulamos las consultasal servidor por si perdemos la conexion
            $usuario = Usuario::where("email_usuario",$datos->email_usuario)->first();//Buscamos el usuario por su email
            if($usuario){ //Comprobamos que se halla encontrado un usuario
                
                if( Hash::check($datos->password_usuario, $usuario->password_usuario)){//Si es asi comprobamos la contraseña de este 
                    $token = $this->crearToken($usuario);//Si todo va bien entonces nos creamos un token usando la funcion de crear token
                    $usuario->Api_token = $token;//Nos guardamos la token en el json 
                    $usuario->save();//Guardamos el nuevo Json en la tabla
                    $respuesta['msg'] = "Se ha echo el login, apitoken creada";
                    $respuesta['status'] = 1;
                } else{//Si la contraseña no coincide entonces llegamos aqui
                    $respuesta['msg'] = "La contraseña no coincide con la contraseña del usuario";
                    $respuesta['status'] = 0;
                }
             }else{//Si el email no existe entonces llegamos aqui
                $respuesta['msg'] = "El email no existe o esta mal escrito";
                $respuesta['status'] = 0; 
             }
        } catch (\Exception $e) {//Fallo en el try(posible fallo con la conexion del servidor)
            $respuesta['msg'] = $e->getMessage();
            $respuesta['status'] = 0;
        }       
        return response()->json($respuesta);//Nos devolbemos una respuesta con un mensaje
    }
     /*Funcion encargada de hacer kas token:
     -primero: nos creamos un numero de 6 cifras
     -segundo: usamos la codificacion md5 ya que no queremos que se introduzcan caracteres especiales,
    ademas de que queremos que el numeor sea mas complicado de repetirse por lo que al ponerlo por una codificacion aumentamos la posibilidad de resultados*/
    private function crearToken($usuario){

        $tokenAux = $usuario->email;//Aprovechamos que el email y el id son unicos para crearnos una token unica
        $posiblesNumeros = [0,1,2,3,4,5,6,7,8,9];//Array de numeros 
        for ($i=0; $i < 6; $i++) {//Hscemos este for 6 veces para selecionar 6 numeros random
            $tokenAux .= $posiblesNumeros[array_rand($posiblesNumeros)];//Lo añadimos a un string, array rand para numero random
        }
    return md5($tokenAux);//Encriptamos con md5 el token para no tener problams en los json o rutas 
    }
    public function RecuperarPassword(Request $req){
        $respuesta = ["status" => 1,"msg" => ""];//Usamos esto para comunicarnos con el otro lado del servidor

        $datos = $req->getContent(); //Nos recibimos los datos por el body
        $datos = json_decode($datos); //Decodificamos el json para poder ver los distintos componentes

        $validator = Validator::make(json_decode($req->getContent(),true),[//Este es el validator, dodne comprobamos la validez de los datos introducidos en un json
           
            'email' => "required|email:rfc,dns",//Obligatorio, cumple una estructura especifica (email:rfc, dns)        
            ]);
            //Comporbamos el estado del validador
            if($validator->fails()){
                $respuesta['msg'] = "Ha habido un fallo con los datos introducidos, no se ah introduido un email correcto";
                $respuesta['status'] = 0;    
                
            }else{
                try {
                    $usuario = Usuario::where("email_usuario",$datos->email)->first();
                    if($usuario){
                        $passNueva = $this->automaticPass(); 
                        $usuario->password_usuario = Hash::make($passNueva);
                        $usuario->save();
                        $respuesta["msg"] = "La contraseña se ha cambiado a la nueva: ".$passNueva;
                        $respuesta["status"] = 1;
                    }else{
                        $respuesta["msg"] = "El email no existe dentro de la base de datos";
                        $respuesta["status"] = 0;
                    }
                } catch (\Exception $e) {
                    $respuesta['msg'] = $e->getMessage();
                    $respuesta['status'] = 0;
                }
            }
        return response()->json($respuesta);//Nos devolbemos una respuesta con un mensaje
    }
    /*Creacion de la contraseña automatica */
    private function automaticPass(){
        $controlEx = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}/";
        $password = "";
        do {
            $option = rand(1,3);
            switch ($option) {
                case '1'://Numbers
                    $aux = rand(48,57);
                    $password .= chr($aux);
                    break;
                case '2'://Upper case
                    $aux = rand(65,90);
                    $password .=chr($aux);
                    break;
                case '3'://Lower case
                    $aux = rand(97,122);
                    $password .= chr($aux);
                    break;    
                default://No necesitamos caracteres especiales, pero si hay algu fallo en el switch lo sabremos is estan estos presentes
                    $aux = rand(33,46);
                    $password .= chr($aux);
                    break;
            }              
        } while((preg_match($controlEx, $password) === 0));// Comprobamos is la contraseña construida cumple con los requisitos
    return $password;
    }
}
