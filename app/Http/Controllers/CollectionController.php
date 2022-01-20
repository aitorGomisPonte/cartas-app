<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Collection;
use App\Models\Carta_pertenece;
use App\Models\Card;

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
                        $pertenece->card_id = $datos->id_card;
                        $respuesta['msg'] = "Se ha registrado la carta, con nombre: ".$datos->nombre_carta;//Nos devolbemos un mensaje para saber quien se ha guardado (util para comprobar)
                        $respuesta['status'] = 1; 
                    }else{
                        $card = new Card();
                        $card->nombre_card = $datos->nombre_carta;
                        $card->desc_card = $datos->desc_carta;
                        $card->save();

                        $pertenece = new Carta_pertenece();
                        $pertenece->card_id = $card->id;
                        $pertenece->collection_id = $collection->id;
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
}
