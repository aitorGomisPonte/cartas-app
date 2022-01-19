<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carta;
use App\Models\Carta_pertenece;
use App\Models\Collection;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;

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
                        $respuesta['msg'] = "No se ha podido hacer la operacion ya qu ela colecion no existe";
                        $respuesta['status'] = 0;    
                    }else{
                        $card = new Carta();
                        $card->nombre_card = $datos->name;
                        $card->desc_card = $datos->desc;
                        $card->save();

                        $id_carta = $this->BuscarCartaId($card->nombre_card);

                        $pertenece = new Carta_pertenece();
                        $pertenece->card_id = $id_carta ;
                        $pertenece->collection_id = $datos->collection;
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
    private function BuscarCartaId($card){
        try {
            $carta = Carta::where("nombre_card",$card)->first();
            $id_carta = $carta->id;
        } catch (\Exception $e) {
            $respuesta['msg'] = $e->getMessage();
            $respuesta['status'] = 0; 
        }
    return $id_carta;
    }
}
