<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LogInTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_exampleLogIn()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_Correcto()
    {
        $response = $this->postJson('/api/usuario/login',[
            "email_usuario" => "pruebas@gmail.com",
            "password_usuario" => "Pruebas1234?"
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                   "status" => 1
        ]);
        
    }
    public function test_NoCorrectoUsuario()
    {
        $response = $this->postJson('/api/usuario/login',[
            "email_usuario" => "j@gmail.com",
            "password_usuario" => "Jimy1234?"
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                   "status" => 0
        ]);
        
    }
    public function test_NoCorrectoPass()
    {
        $response = $this->postJson('/api/usuario/login',[
            "email_usuario" => "pruebas@gmail.com",
            "password_usuario" => "j?"
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                   "status" => 0
        ]);
        
    }
}
