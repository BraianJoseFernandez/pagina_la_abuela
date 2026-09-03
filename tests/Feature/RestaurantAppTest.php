<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmationMail;
use App\Mail\ResetPasswordMail;
use App\Models\Category;
use App\Models\EventSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class RestaurantAppTest extends TestCase
{
    public function test_public_menu_home_page_is_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Rotiseria');
        $response->assertSee('La Abuela');
    }

    public function test_public_category_route_returns_category_view(): void
    {
        $response = $this->get('/categoria/pizzas');
        $response->assertStatus(200);
        $response->assertSee('Pizza Muzzarella');
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Iniciar Sesión');
    }

    public function test_admin_can_login_and_access_dashboard(): void
    {
        $admin = User::where('email', 'admin@laabuela.com')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Resumen General');
    }

    public function test_personal_user_can_login_and_access_products_and_orders(): void
    {
        $personal = User::where('email', 'personal@laabuela.com')->first();
        $this->assertNotNull($personal);

        $response = $this->actingAs($personal)->get('/admin/products');
        $response->assertStatus(200);

        // Personal should NOT access users management
        $usersResponse = $this->actingAs($personal)->get('/admin/users');
        $usersResponse->assertRedirect('/admin');
    }

    public function test_admin_can_create_new_category_and_product(): void
    {
        $admin = User::where('email', 'admin@laabuela.com')->first();

        $catResponse = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Promociones Especiales Test',
            'slug' => 'promociones-especiales-test',
            'icon' => 'fas fa-star',
            'is_active' => '1',
            'order' => 99,
        ]);
        $catResponse->assertRedirect('/admin/categories');

        $category = Category::where('slug', 'promociones-especiales-test')->first();
        $this->assertNotNull($category);

        $prodResponse = $this->actingAs($admin)->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Combo Super Familiar Test',
            'description' => '1 Pizza grande + 6 empanadas + 1 Coca Cola',
            'price' => 25000,
            'badge' => '⭐ Promo',
            'is_available' => '1',
        ]);
        $prodResponse->assertRedirect();

        $product = Product::where('name', 'Combo Super Familiar Test')->first();
        $this->assertNotNull($product);
        $this->assertEquals(25000, $product->price);

        // Clean up test records
        $category->delete();
    }

    public function test_order_can_be_saved_via_api(): void
    {
        $payload = [
            'customer_name' => 'Test Cliente',
            'customer_phone' => '3794123456',
            'delivery_type' => 'delivery',
            'delivery_address' => 'Av. San Martín 123',
            'payment_method' => 'Efectivo',
            'notes' => 'Tocar timbre',
            'total_amount' => 14000,
            'items' => [
                [
                    'product_id' => null,
                    'product_name' => 'Pizza Muzzarella',
                    'variant_name' => 'Entera',
                    'unit_price' => 14000,
                    'quantity' => 1,
                    'subtotal' => 14000,
                    'notes' => null,
                ]
            ]
        ];

        $response = $this->postJson('/pedido/guardar', $payload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_admin_can_reorder_categories(): void
    {
        $admin = User::where('email', 'admin@laabuela.com')->first();
        $cats = Category::take(3)->pluck('id')->toArray();
        $reversed = array_reverse($cats);

        $response = $this->actingAs($admin)->postJson('/admin/categories/reorder', [
            'order' => $reversed,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_admin_can_reorder_products(): void
    {
        $admin = User::where('email', 'admin@laabuela.com')->first();
        $products = Product::take(3)->pluck('id')->toArray();
        $reversed = array_reverse($products);

        $response = $this->actingAs($admin)->postJson('/admin/products/reorder', [
            'order' => $reversed,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_admin_can_create_product_with_cooking_options(): void
    {
        $admin = User::where('email', 'admin@laabuela.com')->first();
        $category = Category::where('slug', 'empanadas')->first();
        $this->assertNotNull($category);

        $response = $this->actingAs($admin)->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Empanada Criolla Dulce Test',
            'description' => 'Carne cortada a cuchillo con pasas',
            'price' => 1500,
            'has_cooking_options' => '1',
            'cooking_options' => ['Horno', 'Frita'],
            'is_available' => '1',
        ]);
        $response->assertRedirect();

        $product = Product::where('name', 'Empanada Criolla Dulce Test')->first();
        $this->assertNotNull($product);
        $this->assertTrue($product->has_cooking_options);
        $this->assertEquals(['Horno', 'Frita'], $product->cooking_options);
        $this->assertTrue($product->hasCookingOptions());

        // Test updating cooking options
        $updateResponse = $this->actingAs($admin)->put('/admin/products/' . $product->id, [
            'category_id' => $category->id,
            'name' => 'Empanada Criolla Dulce Test',
            'description' => 'Carne cortada a cuchillo con pasas',
            'price' => 1600,
            'has_cooking_options' => '1',
            'cooking_options' => ['Horno'],
            'is_available' => '1',
        ]);
        $updateResponse->assertRedirect();

        $product->refresh();
        $this->assertEquals(['Horno'], $product->cooking_options);

        $product->delete();
    }

    public function test_order_with_cooking_method_can_be_saved(): void
    {
        $payload = [
            'customer_name' => 'Juan Pérez Empanadas',
            'customer_phone' => '3794998877',
            'delivery_type' => 'takeaway',
            'payment_method' => 'Efectivo',
            'total_amount' => 15000,
            'items' => [
                [
                    'product_id' => 17,
                    'product_name' => 'Empanadas de Carne',
                    'variant_name' => 'Docena (12 un.)',
                    'cooking_method' => 'Horno',
                    'unit_price' => 15000,
                    'quantity' => 1,
                    'subtotal' => 15000,
                    'notes' => 'Bien doraditas',
                ]
            ]
        ];

        $response = $this->postJson('/pedido/guardar', $payload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('order_items', [
            'product_name' => 'Empanadas de Carne',
            'cooking_method' => 'Horno',
        ]);
    }

    public function test_admin_create_and_edit_views_render_cooking_options(): void
    {
        $admin = User::where('email', 'admin@laabuela.com')->first();

        // Create view
        $createRes = $this->actingAs($admin)->get('/admin/products/create');
        $createRes->assertStatus(200);
        $createRes->assertSee('Opción de Cocción (Horno / Freír)');
        $createRes->assertSee('has_cooking_options');

        // Edit view for product 17 (Empanadas de Carne)
        $editRes = $this->actingAs($admin)->get('/admin/products/17/edit');
        $editRes->assertStatus(200);
        $editRes->assertSee('Opción de Cocción (Horno / Freír)');
        $editRes->assertSee('has_cooking_options');
        $editRes->assertSee('🔥 Horno');
        $editRes->assertSee('checked', false); // Verify it has checked status for empanadas
    }

    public function test_public_menu_and_admin_render_fire_horno_badge(): void
    {
        $response = $this->get('/categoria/empanadas');
        $response->assertStatus(200);
        $response->assertSee('🔥 Horno o Frita');

        $admin = User::where('email', 'admin@laabuela.com')->first();
        $adminRes = $this->actingAs($admin)->get('/admin/products');
        $adminRes->assertStatus(200);
        $adminRes->assertSee('🔥 Horno / Frita');
    }

    public function test_product_model_normalizes_al_horno_to_horno(): void
    {
        $product = Product::where('id', 17)->first();
        $product->cooking_options = ['Al Horno', 'Frita'];
        $this->assertEquals(['Horno', 'Frita'], $product->getCookingOptionsList());
    }

    public function test_order_with_customer_email_sends_confirmation_email(): void
    {
        Mail::fake();

        $payload = [
            'customer_name' => 'Cliente Con Email',
            'customer_phone' => '3794112233',
            'customer_email' => 'cliente@test.com',
            'delivery_type' => 'delivery',
            'delivery_address' => 'Av. Siempre Viva 742',
            'payment_method' => 'Transferencia',
            'total_amount' => 7500,
            'items' => [
                [
                    'product_id' => 17,
                    'product_name' => 'Empanadas de Carne',
                    'variant_name' => 'Media Docena (6 un.)',
                    'cooking_method' => 'Horno',
                    'unit_price' => 7500,
                    'quantity' => 1,
                    'subtotal' => 7500,
                    'notes' => 'Con servilletas',
                ]
            ]
        ];

        $response = $this->postJson('/pedido/guardar', $payload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Cliente Con Email',
            'customer_email' => 'cliente@test.com',
        ]);

        Mail::assertSent(OrderConfirmationMail::class, function ($mail) {
            return $mail->hasTo('cliente@test.com') && $mail->order->customer_email === 'cliente@test.com';
        });
    }

    public function test_forgot_password_sends_reset_email(): void
    {
        Mail::fake();

        $response = $this->post('/forgot-password', [
            'email' => 'admin@laabuela.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        Mail::assertSent(ResetPasswordMail::class, function ($mail) {
            return $mail->hasTo('admin@laabuela.com');
        });
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::where('email', 'admin@laabuela.com')->first();
        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'admin@laabuela.com',
            'password' => 'nueva_clave_segura_123',
            'password_confirmation' => 'nueva_clave_segura_123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('nueva_clave_segura_123', $user->password));

        // Restore admin password back to 'admin123' for subsequent tests
        $user->password = Hash::make('admin123');
        $user->save();
    }
}
