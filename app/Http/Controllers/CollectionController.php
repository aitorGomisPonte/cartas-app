<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use App\Models\Collection;
use App\Models\Carta_pertenece;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class CollectionController extends Controller
{
    public function Crearcollection (Request $req){

        $respuesta = ["status" => 1,"msg" => ""];
        $path = 0;
        $datos = $req->getContent();//Recibimos los datos por body
        $datos = json_decode($datos);//Decodificamos los datos

        if(isset($datos->id_carta)){
            $validator = Validator::make(json_decode($req->getContent(),true),[//Este es el validator, dodne comprobamos la validez de los datos introducidos en un json
                'nombre_collection' => "required",//Obligatorio
                'img_collection' => "required",//Obligatorio, unico en la tabla de usuarios, cumple una estructura especifica (email:rfc, dns)
                'id_carta' => "required"//Obligatorio, y que cumpla el enum
                ]);
                //Comporbamos el estado del validador
                if($validator->fails()){
                    $respuesta['msg'] = "Ha habido un fallo con los datos introducidos, para crear una collecion es obligatorio pasar una carta nueva o una carta ya existente";
                    $respuesta['status'] = 0;      
                          
                }else{   
                    try {
                        if(Card::where("id",$datos->id_carta)->first()){
                            $path = 1;  
                        }else{
                            $respuesta['msg'] = "La carta itroducida no existe";
                            $respuesta['status'] = 0; 
                        }
                    } catch (\Exception $e) {
                        $respuesta['msg'] = $e->getMessage();
                        $respuesta['status'] = 0; 
                    }  
                }
        }else{
            $validator = Validator::make(json_decode($req->getContent(),true),[//Este es el validator, dodne comprobamos la validez de los datos introducidos en un json
                'nombre_collection' => "required",//Obligatorio
                'img_collection' => "required",//Obligatorio, unico en la tabla de usuarios, cumple una estructura especifica (email:rfc, dns)
                'nombre_carta' => "required",//Obligatorio
                'desc_carta' => "required",//Obligatorio, unico en la tabla de usuarios, cumple una estructura especifica (email:rfc, dns)          
                ]);
                //Comporbamos el estado del validador
                if($validator->fails()){
                    $respuesta['msg'] = "Ha habido un fallo con los datos introducidos, para crear una collecion es obligatorio pasar una carta nueva o una carta ya existente";
                    $respuesta['status'] = 0;   
                }else{   
                    try {
                        
                            $path = 2;  
                            $respuesta['msg'] = "La carta itroducida no existe";
                            $respuesta['status'] = 0; 
                        
                    } catch (\Exception $e) {
                        $respuesta['msg'] = $e->getMessage();
                        $respuesta['status'] = 0; 
                    }  
                }
        }
        if($path != 0){
            try {
                    $collection = new Collection();
                    $collection->nombre_collection = $datos->nombre_collection;
                    $collection->img_collection = $datos->img_collection;
                    $collection->save();
                    if($path == 1){
                        $pertenece = new Carta_pertenece();
                        $pertenece->collection_id = $collection->id;
                        $pertenece->card_id = $datos->id_carta;
                        $pertenece->save();
                        $respuesta['msg'] = "bien ";//Nos devolbemos un mensaje para saber quien se ha guardado (util para comprobar)
                        $respuesta['status'] = 1; 
                    }else{
                        $card = new Card();
                        $card->nombre_card = $datos->nombre_carta;
                        $card->desc_card = $datos->desc_carta;
                        $card->save();

                        $pertenece = new Carta_pertenece();
                        $pertenece->card_id = $card->id;
                        $pertenece->collection_id = $collection->id;
                        $pertenece->save();
                        $respuesta['msg'] = "Se ha registrado la carta, con nombre: ".$datos->nombre_carta;//Nos devolbemos un mensaje para saber quien se ha guardado (util para comprobar)
                        $respuesta['status'] = 1;
                    } 
            } catch (\Exception $e) {
                $respuesta['msg'] = $e->getMessage();
                $respuesta['status'] = 0; 
            }
        }      
        return response()->json($respuesta);
    }

    public function DarAltaCollection(Request $req){

        $respuesta = ["status" => 1,"msg" => ""];

        $datos = $req->getContent();//Recibimos los datos por body
        $datos = json_decode($datos);//Decodificamos los datos
        if(isset($datos->id_collection)){
            try {
                $collection = Collection::where("id",$datos->id_collection)->first();
                if($collection){
                    if($collection->alta_collection){
                        $respuesta['msg'] = "La collection ya esta dada de alta";
                        $respuesta['status'] = 1; 
                    }else{
                        $collection->alta_collection = true;
                        $collection->fecha_activacion_collection = Carbon::now();
                        $collection->save();
                        $respuesta['msg'] = "La collection se ha dado de alta";
                        $respuesta['status'] = 2;  
                    }
                }else{
                    $respuesta['msg'] = "El id de la collection no existe";
                    $respuesta['status'] = 0;  
                }
            } catch (\Exception $e) {
                $respuesta['msg'] = $e->getMessage();
                $respuesta['status'] = 0;
            }
        }else{
            $respuesta['msg'] = "No se ha enviado ningun id de la collection";
            $respuesta['status'] = 0;
        }
        return response()->json($respuesta);
    }
    public function BuscarIdCollection(Request $req){
        $respuesta = ["status" => 1,"msg" => ""];

        $datos = $req->getContent();//Recibimos los datos por body
        $datos = json_decode($datos);//Decodificamos los datos
        if(isset($datos->nombre_collection)){
            try {
                $aux = Collection::where("nombre_collection",$datos->nombre_collection)->first();
                if($aux){
                    $respuesta ["msg"] = "El id de esta collection es: ".$aux->id;
                    $respuesta["status"] = 1; 
                }else{
                    $respuesta ["msg"] = "El nombre de esta collection no existe";
                    $respuesta["status"] = 1; 
                }
            } catch (\Exception $e){
                $respuesta['msg'] = $e->getMessage();
                $respuesta['status'] = 0;
            }
        }else{
            $respuesta ["msg"] = "No se han pasado los datos correctos";
            $respuesta["status"] = 0;
        }   
        return response()->json($respuesta);
    }
    public function PonerCartaVenta(Request $req){
        
    }
}
