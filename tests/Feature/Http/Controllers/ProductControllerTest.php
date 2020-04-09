<?php

namespace Tests\Feature\Http\Controllers;

use App\Category;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use WithFaker;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // public function testExample()
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }

    /**
     * @test
     */
    public function test_store_data()
    {
        //TODO: code inside here --Created by Kiddy
    
        //Membuat objek user yang otomatis menambahkannya ke database.
        $user = factory(User::class)->create();

        //Membuat objek category yang otomatis menambahkannya ke database.
        $category = factory(Category::class)->create();

        //Acting as berfungsi sebagai autentikasi, jika kita menghilangkannya maka akan error.
        $response = $this->actingAs($user)
        //Hit post ke method store, fungsinya ya akan lari ke fungsi store.
        ->post(route('product.store'), [
            //isi parameter sesuai kebutuhan request
            'name' => $this->faker->words(3, true),
            'cat' => $category->id,
            'quantity' => $this->faker->randomNumber(3),
            'buy_price' => $this->faker->randomNumber(6),
            'sell_price' => $this->faker->randomNumber(6),
        ]);

        //Tuntutan status 302, yang berarti redirect status code.
        $response->assertStatus(302);

        //Tuntutan bahwa abis melakukan POST URL akan dialihkan ke URL product atau routenya adalah product.index
        $response->assertRedirect(route('product.index'));
    }

    /**
     * @test
     */
    public function test_store_qty_err()
    {
        //TODO: code inside here --Created by Kiddy
    
        //Membuat objek user yang otomatis menambahkannya ke database.
        $user = factory(User::class)->create();

        //Membuat objek category yang otomatis menambahkannya ke database.
        $category = factory(Category::class)->create();

        //Acting as berfungsi sebagai autentikasi, jika kita menghilangkannya maka akan error.
        $response = $this->actingAs($user)
        //Hit post ke method store, fungsinya ya akan lari ke fungsi store.
        ->post(route('product.store'), [
            //isi parameter sesuai kebutuhan request
            'name' => $this->faker->words(3, true),
            'cat' => $category->id,
            'quantity' => $this->faker->words(3),
            'buy_price' => $this->faker->randomNumber(6),
            'sell_price' => $this->faker->randomNumber(6),
        ]);

        $response->assertStatus(500);
    }
}
