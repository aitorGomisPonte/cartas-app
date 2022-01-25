<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use App\Models\Carta_pertenece;
use App\Models\Carta_venta;
use App\Models\Collection;
use App\Models\Usuario;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    /*Funcion encargada de crear una carta:
    -recibe: un JSON qu econtiene su nombre, la desc, y la collection a la que pertenece
     -primero: recibimos los datos
     -segundo: validamos los datos recibidos
     -tercero: si la validacion es correcta, procedemos a crearnos un objeto de carta para rellenar sus campos
     -cuarto: tras guardar esta carta en la base de datos, */
    public function CrearCard (Request $req){

        $respuesta = ["status" => 1,"msg" => ""];

        $datos = $req->getContent();//Recibimos los datos por body
        $datos = json_decode($datos);//Decodificamos los datos

        $validator = Validator::make(json_decode($req->getContent(),true),[//Este es el validator, dodne comprobamos la validez de los datos introducidos en un json
            'name' => "required",//Obligatorio
            'desc' => "required",//Obligatorio, unico en la tabla de usuarios, cumple una estructura especifica (email:rfc, dns)
            'collection' => "required",//Obligatorio, y que cumpla el enum
            
            
            ]);
            //Comporbamos el estado del validador
            if($validator->fails()){
                $respuesta['msg'] = "Ha habido un fallo con los datos introducidos";
                $respuesta['status'] = 0;    
                
            }else{//Si no ha habido ningun fallo, hacemops la crecion del objeto y lo guardamos en la base de datos
                try {
                    
                    if(!(Collection::where("id",$datos->collection)->first())){
                        $respuesta['msg'] = "No se ha podido hacer la operacion ya que la colecion no existe";
                        $respuesta['status'] = 0;    
                    }else{
                        $card = new Card();
                        $card->nombre_card = $datos->name;
                        $card->desc_card = $datos->desc;
                        $card->save();

                        $pertenece = new Carta_pertenece();
                        $pertenece->card_id = $card->id;
                        $pertenece->collection_id = $datos->collection;
                        $pertenece->save();
                        $respuesta['msg'] = "Se ha registrado la carta, con nombre: ".$datos->name;//Nos devolbemos un mensaje para saber quien se ha guardado (util para comprobar)
                        $respuesta['status'] = 1;  
                    }
                } catch (\Exception $e) {
                    $respuesta['msg'] = $e->getMessage();
                    $respuesta['status'] = 0; 
                }
            }
        return response()->json($respuesta);
    }
    /*Funcion encargada de buscar los id de las cartas por nombre recibido:
    -Recibe: el nombre de la carta
     -primero: busca la carta dentro de la base de datos
     -segundo: una vez encontramos la carta, buscamos su id
    -Devuelve: el id de la carta  */
    public function BuscarCartasId(Request $req){
        $respuesta = ["status" => 1,"msg" => ""];

        $datos = $req->getContent();//Recibimos los datos por body
        $datos = json_decode($datos);//Decodificamos los datos

        if(!(isset($datos->nombre_carta))){
            $respuesta['msg'] = "No se han pasado los datos correctos";
            $respuesta['status'] = 0;  
        }else{
            try {
                /* En esta secion unimos las tabla de cartas con la de usuarios y cartas en venta:
                - juntamos las entradas cullos id conincidan en la tabl de cartas ventas con cartas
                - juntamos los usuarios que coincidad con la tabla de cartas y la tabla de usuarios*/
                $cartas = DB::table('cards')
                ->join('carta_ventas', 'cards.id', '=', 'carta_ventas.id_carta')
                ->join('usuarios', 'carta_ventas.id_usuario', '=', 'usuarios.id')
                ->select('carta_ventas.id_carta', 'cards.nombre_card', 'carta_ventas.precio_venta','usuarios.nombre_usuario')//Selecionamos
                ->where("cards.nombre_card","like","%".$datos->nombre_carta."%")//Solo selecionamos las cuales coinciden con el nombre introducido
                ->orderBy("precio_venta")
                ->get();
                if(sizeof($cartas) === 0){
                    $respuesta['msg'] = "No existen cartas con ese nombre";
                    $respuesta['status'] = 0;
               }else{
                    $respuesta['msg'] = "Los cartas son : ".$cartas;
                    $respuesta['status'] = 0;
                }
            } catch (\Exception $e) {
                $respuesta['msg'] = $e->getMessage();
                $respuesta['status'] = 0; 
            }
        }

        return response()->json($respuesta);
    }
    /*Funcion encargda de buscar el id de la carta para los vendedores
     -Recibe: nombre de la carta
      -primero: busca la carta dentro de la base de datos
      -segundo: busca los ids correspondientes con la busqueda
     -Devuelve: el id de las cartas  */
    public function BuscarCartasIdVender(Request $req){
        $respuesta = ["status" => 1,"msg" => ""];

        $datos = $req->getContent();//Recibimos los datos por body
        $datos = json_decode($datos);//Decodificamos los datos

        if(!(isset($datos->nombre_carta))){
            $respuesta['msg'] = "No se han pasado los datos correctos";
            $respuesta['status'] = 0;  
        }else{
            try {
                $cartas = Card::select("id","nombre_card")->where("nombre_card","like","%".$datos->nombre_carta."%")->where("alta_card","true")->get();
                if(sizeof($cartas) === 0){
                    $respuesta['msg'] = "No existen cartas con ese nombre";
                    $respuesta['status'] = 0;
               }else{
                    $respuesta['msg'] = "Los cartas son : ".$cartas;
                    $respuesta['status'] = 0;
                }
            } catch (\Exception $e) {
                $respuesta['msg'] = $e->getMessage();
                $respuesta['status'] = 0; 
            }
        }
        return response()->json($respuesta);
    }
    /*Funcion encargada de dar de alta las cartas, solo accesible si eres admin
     -primero: comprueba que la carta exista
     -segundo: comprueba que la cart no este dada de alta
     -tercero: da de alta la carta, y guarda la fecha de alta */
    public function DarAltaCarta(Request $req){

        $respuesta = ["status" => 1,"msg" => ""];

        $datos = $req->getContent();//Recibimos los datos por body
        $datos = json_decode($datos);//Decodificamos los datos
        if(isset($datos->id_carta)){
            try {
                $card = Card::where("id",$datos->id_carta)->first();
                if($card){
                    if($card->alta_card){
                        $respuesta['msg'] = "La carta ya esta dada de alta";
                        $respuesta['status'] = 1; 
                    }else{
                        $card->alta_card = true;
                        $card->fecha_alta_card = Carbon::now();//Carbon.now()nos permite guardar la fecha actual en la que nos encontramos
                        $card->save();
                        $respuesta['msg'] = "La carta se ha dado de alta";
                        $respuesta['status'] = 2;  
                    }
                }else{
                    $respuesta['msg'] = "El id de la carta no existe";
                    $respuesta['status'] = 0;  
                }
            } catch (\Exception $e) {
                $respuesta['msg'] = $e->getMessage();
                $respuesta['status'] = 0;
            }
        }else{
            $respuesta['msg'] = "No se ha enviado ningun id de la carta";
            $respuesta['status'] = 0;
        }
        return response()->json($respuesta);
    }
    /*Funcion encargada de asociar cartas a collectiones
     -prmimero: validamos los datos que hemos recibido
     -segundo: comprobamos qu la carta y la collection existn
     -tercero: comprobamos que la carta no pertenezca ya a la collection
     -cuerto: si no es asi, nos creamos un nuevo objeto de pertenencia y lo gaurdamos */
    public function AsociarCarta(Request $req){

        $respuesta = ["status" => 1,"msg" => ""];

        $datos = $req->getContent();//Recibimos los datos por body
        $datos = json_decode($datos);//Decodificamos los datos

        $validator = Validator::make(json_decode($req->getContent(),true),[//Este es el validator, dodne comprobamos la validez de los datos introducidos en un json
            'id_carta' => "required",//Obligatorio
            'id_collection' => "required",//Obligatorio, y que cumpla el enum 
            ]);
            //Comporbamos el estado del validador
            if($validator->fails()){
                $respuesta['msg'] = "Ha habido un fallo con los datos introducidos";
                $respuesta['status'] = 0;          
            }else{
                try {
                    if((Collection::where("id",$datos->id_collection)->first())&&(Card::where("id",$datos->id_carta)->first())){
                        if(Carta_pertenece::where("card_id",$datos->id_carta)->where("collection_id",$datos->id_collection)->first()){
                            $respuesta['msg'] = "La carta ya pertenece a la collection";
                            $respuesta['status'] = 0;
                        }else{
                            $pertenece = new Carta_pertenece();
                            $pertenece->card_id = $datos->id_carta;
                            $pertenece->collection_id = $datos->id_collection;
                            $pertenece->save();
                            $respuesta['msg'] = "La carta y la collection se han asociado correctamente";
                            $respuesta['status'] = 1;
                        }
                    }else{
                        $respuesta['msg'] = "La carta o la collecion no existen";
                        $respuesta['status'] = 0;
                    }//code...
                } catch (\Exception $e) {
                    $respuesta['msg'] = $e->getMessage();
                    $respuesta['status'] = 0;
                }
                
            } 
        return response()->json($respuesta);
    }
    /*Funcion encargda de poner cartas a la venta:
    Recibe: id_carta, id_usuario, precio y cantidad
     -primero: valdamos los datos que se nos han pasado
     -segundo: comprobamos que la carta y usuario existan
     -tercero: comprobamos que la carta este dada de alta
     -cuarto: creamos un nuevo objeto de venta */
    public function PonerCartaVenta(Request $req){
        $respuesta = ["status" => 1,"msg" => "test"];

        $datos = $req->getContent();//Recibimos los datos por body
        $datos = json_decode($datos);//Decodificamos los datos

        $validator = Validator::make(json_decode($req->getContent(),true),[//Este es el validator, dodne comprobamos la validez de los datos introducidos en un json
            'id_carta' => "required",//Obligatorio
            'id_usuario' => "required",//Obligatorio, y que cumpla el enum 
            'precio' => "required|integer",
            'cantidad' => "required|integer",
            ]);
            //Comporbamos el estado del validador
            if($validator->fails()){
                $respuesta['msg'] = "Ha habido un fallo con los datos introducidos";
                $respuesta['status'] = 0;          
            }else{
                try {
                    $card = Card::where("id",$datos->id_carta)->first();
                    if(($card)&&(Usuario::where("id",$datos->id_usuario)->first())){
                        if($card->alta_card){
                             $venta = new Carta_venta();
                             $venta->id_usuario = $datos->id_usuario;
                             $venta->id_carta = $datos->id_carta;
                             $venta->precio_venta = $datos->precio;
                             $venta->cantidad_venta = $datos->cantidad;
                             $venta->save();
                             $respuesta['msg'] = "La carta ".$card->id."se ha puesto a la venta";
                             $respuesta['status'] = 1; 

                        }else{
                            $respuesta['msg'] = "La carta no est dada de alta";
                            $respuesta['status'] = 0;   
                        }
                    }else{
                        $respuesta['msg'] = "El id de usuario o carta no existe";
                        $respuesta['status'] = 0; 
                    }
                } catch (\Exception $e) {
                    $respuesta['msg'] = $e->getMessage();
                    $respuesta['status'] = 0;
                }
            }   
        return response()->json($respuesta);
    }
}
