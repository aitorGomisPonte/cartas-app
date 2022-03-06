<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CrearCartaTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    public function test_CreacionCorrecta()
    {
        $response = $this->putJson('/api/cards/crear',[
            "name" => "el mago",
            "desc" => "hace magia",
            "collection" => "1",
            "api_token" => "a063b4c28812b873335489d33c83fef2"
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                   "status" => 1
        ]);
        
    }
    public function test_FalloToken()
    {
        $response = $this->putJson('/api/cards/crear',[
            "name" => "el mago",
            "desc" => "hace magia",
            "collection" => "1",
            "api_token" => ""
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                   "status" => 0
        ]);
        
    }
    public function test_DatosVacios()
    {
        $response = $this->putJson('/api/cards/crear',[
            "name" => "",
            "desc" => "",
            "collection" => "1",
            "api_token" => "a063b4c28812b873335489d33c83fef2"
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                   "status" => 0
        ]);
        
    }
    public function test_CollectionVacia()
    {
        $response = $this->putJson('/api/cards/crear',[
            "name" => "el mago",
            "desc" => "hace magia",
            "collection" => "",
            "api_token" => "a063b4c28812b873335489d33c83fef2"
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                   "status" => 0
        ]);
        
    }
    
}
