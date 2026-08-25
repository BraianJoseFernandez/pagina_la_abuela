<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryImage;
use App\Models\EventSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Usuarios con roles (Español Latino)
        User::updateOrCreate(
            ['email' => 'admin@laabuela.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'personal@laabuela.com'],
            [
                'name' => 'Personal de Atención',
                'password' => Hash::make('personal123'),
                'role' => 'personal',
                'is_active' => true,
            ]
        );

        // 2. Ajustes generales del negocio
        $settings = [
            'restaurant_name' => 'Rotisería La Abuela',
            'restaurant_slogan' => 'Cocinar con amor te alimenta el alma',
            'whatsapp_phone' => '5493794565528',
            'display_phone' => '3794-565528',
            'address' => 'Av. libertad 5445',
            'maps_url' => 'https://maps.app.goo.gl/JAgMpxXPBgX4BGqbA?g_st=aw',
            'instagram_user' => '@RotiLaAbuela',
            'instagram_url' => 'https://www.instagram.com/rotilaabuela?utm_source=qr&igsh=MW82Y2J1ODVhNzd3dA==',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // 3. Configuración de la sección Eventos
        EventSetting::updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Oferta Especial',
                'subtitle' => '¡Aprovechá nuestras mejores promociones!',
                'image_path' => 'imagenes/eventos/mundial/oferta_mundial.jpeg',
                'badge_left_emoji' => '⚽🇦🇷',
                'badge_right_emoji' => '⚽🇦🇷',
                'confetti_emojis' => '⚽,🇦🇷,🏆,🎉',
                'confetti_colors' => '#75AADB,#FFFFFF,#F6B40E',
                'whatsapp_custom_text' => 'Hola! Quiero consultar por la promo especial ⚽🇦🇷',
                'is_active' => true,
            ]
        );

        // 4. Seeding exacto de categorías y productos extraídos de los archivos HTML

        // --- PIZZAS ---
        $pizzasCat = Category::create([
            'name' => 'Pizzas',
            'slug' => 'pizzas',
            'icon' => 'fas fa-pizza-slice',
            'subtitle' => 'Masa artesanal y los mejores ingredientes',
            'order' => 1,
            'is_active' => true,
        ]);

        $pizzaImages = [
            ['image_path' => 'imagenes/pizzas/napolitana.jpeg', 'alt_text' => 'Pizza Napolitana'],
            ['image_path' => 'imagenes/pizzas/jamonymorrones.jpeg', 'alt_text' => 'Pizza Jamon y Morrón'],
            ['image_path' => 'imagenes/pizzas/aCaballo.jpeg', 'alt_text' => 'Pizza A Caballo'],
            ['image_path' => 'imagenes/pizzas/laAbuela.jpeg', 'alt_text' => 'Pizza La Abuela'],
            ['image_path' => 'imagenes/pizzas/calabreza.jpeg', 'alt_text' => 'Pizza Calabresa'],
            ['image_path' => 'imagenes/pizzas/gallega.jpeg', 'alt_text' => 'Pizza Gallega'],
            ['image_path' => 'imagenes/pizzas/especial.jpeg', 'alt_text' => 'Pizza Especial'],
            ['image_path' => 'imagenes/pizzas/champiñones.jpeg', 'alt_text' => 'Pizza Champiñones'],
            ['image_path' => 'imagenes/pizzas/laAbuelayRucula.jpeg', 'alt_text' => 'Pizza La Abuela y Rucula'],
            ['image_path' => 'imagenes/pizzas/muzarella.jpeg', 'alt_text' => 'Pizza Muzarella'],
            ['image_path' => 'imagenes/pizzas/palmitos.jpeg', 'alt_text' => 'Pizza Palmitos'],
            ['image_path' => 'imagenes/pizzas/rucula.jpeg', 'alt_text' => 'Pizza Rucula'],
            ['image_path' => 'imagenes/pizzas/rustica.jpeg', 'alt_text' => 'Pizza Rústica'],
        ];
        foreach ($pizzaImages as $idx => $img) {
            CategoryImage::create([
                'category_id' => $pizzasCat->id,
                'image_path' => $img['image_path'],
                'alt_text' => $img['alt_text'],
                'order' => $idx + 1,
            ]);
        }

        $pizzasData = [
            ['name' => 'Pizza Muzzarella', 'desc' => 'Muzzarella, salsa de tomate, orégano.', 'media' => 7000, 'entera' => 14000, 'badge' => null],
            ['name' => 'Pizza Especial', 'desc' => 'Muzzarella, salsa de tomate, jamón, huevo, orégano.', 'media' => 8000, 'entera' => 16000, 'badge' => null],
            ['name' => 'Pizza Napolitana', 'desc' => 'Muzzarella, salsa de tomate, ajo, perejil, rodajas de tomate, orégano.', 'media' => 8000, 'entera' => 16000, 'badge' => null],
            ['name' => 'Pizza Fugazzeta', 'desc' => 'Muzzarella, salsa de tomate, cebolla, orégano.', 'media' => 8000, 'entera' => 16000, 'badge' => null],
            ['name' => 'Pizza Calabresa', 'desc' => 'Muzzarella, salsa de tomate, calabresa, orégano.', 'media' => 9000, 'entera' => 18000, 'badge' => null],
            ['name' => 'Pizza Gallega', 'desc' => 'Muzzarella, salsa de tomate, carne, orégano.', 'media' => 8500, 'entera' => 17000, 'badge' => null],
            ['name' => 'Pizza Humita', 'desc' => 'Muzzarella, salsa de tomate, choclo, orégano.', 'media' => 8000, 'entera' => 16000, 'badge' => null],
            ['name' => 'Pizza Morrón', 'desc' => 'Muzzarella, salsa de tomate, jamón, morrón, orégano.', 'media' => 8000, 'entera' => 16000, 'badge' => null],
            ['name' => 'Pizza a Caballo', 'desc' => 'Muzzarella, salsa de tomate, huevos fritos, orégano.', 'media' => 8500, 'entera' => 17000, 'badge' => null],
            ['name' => 'Pizza Roquefort', 'desc' => 'Muzzarella, salsa de tomate, roquefort, orégano.', 'media' => 9000, 'entera' => 18000, 'badge' => null],
            ['name' => 'Pizza Sardina', 'desc' => 'Muzzarella, salsa de tomate, sardinas, orégano.', 'media' => 8000, 'entera' => 16000, 'badge' => null],
            ['name' => 'Pizza La Abuela', 'desc' => 'Muzzarella, salsa de tomate, jamón, huevo, rodajas de tomate, morrón, aceitunas, orégano.', 'media' => 10000, 'entera' => 20000, 'badge' => '⭐ La Abuela'],
            ['name' => 'Pizza Palmitos', 'desc' => 'Muzzarella, salsa de tomate, palmitos, salsa golf, orégano.', 'media' => 9000, 'entera' => 18000, 'badge' => null],
            ['name' => 'Pizza Rúcula', 'desc' => 'Muzzarella, salsa de tomate, jamón crudo, rúcula, aceitunas negras, orégano.', 'media' => 10000, 'entera' => 20000, 'badge' => null],
            ['name' => 'Pizza Champiñones', 'desc' => 'Muzzarella, salsa de tomate, champiñones, aceitunas, orégano.', 'media' => 10000, 'entera' => 20000, 'badge' => null],
            ['name' => 'Pizza Rústica', 'desc' => 'Muzzarella, salsa de tomate, papas fritas, huevos fritos y orégano.', 'media' => 10000, 'entera' => 20000, 'badge' => null],
        ];
        foreach ($pizzasData as $idx => $p) {
            $prod = Product::create([
                'category_id' => $pizzasCat->id,
                'name' => $p['name'],
                'description' => $p['desc'],
                'badge' => $p['badge'],
                'order' => $idx + 1,
                'is_available' => true,
            ]);
            ProductVariant::create(['product_id' => $prod->id, 'name' => 'Media', 'price' => $p['media'], 'order' => 1]);
            ProductVariant::create(['product_id' => $prod->id, 'name' => 'Entera', 'price' => $p['entera'], 'order' => 2]);
        }

        // --- EMPANADAS ---
        $empanadasCat = Category::create([
            'name' => 'Empanadas',
            'slug' => 'empanadas',
            'icon' => 'fas fa-bowl-food',
            'subtitle' => 'Fritas u Horno',
            'order' => 2,
            'is_active' => true,
        ]);

        $empanadaImages = [
            ['image_path' => 'imagenes/empanadas/empanadasLaAbuela.jpeg', 'alt_text' => 'Empanadas de Carne al Horno'],
            ['image_path' => 'imagenes/empanadas/Imagen de WhatsApp 2025-07-26 a las 02.28.08_4ae49493.jpg', 'alt_text' => 'Empanadas de carne'],
            ['image_path' => 'imagenes/empanadas/IMG-20250726-WA0021.jpg', 'alt_text' => 'Canastitas de verduras'],
            ['image_path' => 'imagenes/empanadas/IMG-20250726-WA0017.jpg', 'alt_text' => 'Empanadas de queso y cebolla'],
            ['image_path' => 'imagenes/empanadas/IMG-20250725-WA0013.jpg', 'alt_text' => 'Empanadas de pollo y jamón y queso'],
        ];
        foreach ($empanadaImages as $idx => $img) {
            CategoryImage::create([
                'category_id' => $empanadasCat->id,
                'image_path' => $img['image_path'],
                'alt_text' => $img['alt_text'],
                'order' => $idx + 1,
            ]);
        }

        $empanadasData = [
            ['name' => 'Empanadas de Carne', 'media' => 7500, 'docena' => 15000],
            ['name' => 'Empanadas de pollo', 'media' => 7500, 'docena' => 15000],
            ['name' => 'Empanadas de jamón y queso', 'media' => 8000, 'docena' => 16000],
            ['name' => 'Empanadas de humita', 'media' => 7000, 'docena' => 14000],
            ['name' => 'Canastitas de verduras', 'media' => 7000, 'docena' => 14000],
            ['name' => 'Empanadas de queso y cebolla', 'media' => 6500, 'docena' => 13000],
            ['name' => 'Empanadas de queso y huevo', 'media' => 6500, 'docena' => 13000],
        ];
        foreach ($empanadasData as $idx => $e) {
            $prod = Product::create([
                'category_id' => $empanadasCat->id,
                'name' => $e['name'],
                'description' => 'Elaboradas artesanalmente (Fritas u Horno)',
                'order' => $idx + 1,
                'is_available' => true,
            ]);
            ProductVariant::create(['product_id' => $prod->id, 'name' => 'Media Docena (6 un.)', 'price' => $e['media'], 'order' => 1]);
            ProductVariant::create(['product_id' => $prod->id, 'name' => 'Docena (12 un.)', 'price' => $e['docena'], 'order' => 2]);
        }

        // --- HAMBURGUESAS ---
        $hamburguesasCat = Category::create([
            'name' => 'Hamburguesas',
            'slug' => 'hamburguesas',
            'icon' => 'fas fa-hamburger',
            'subtitle' => 'Carne 100% vacuna con el mejor sabor',
            'order' => 3,
            'is_active' => true,
        ]);

        $hambImages = [
            ['image_path' => 'imagenes/hamburgesas/laBomba.jpeg', 'alt_text' => 'Hamburguesa "La Bomba"'],
            ['image_path' => 'imagenes/hamburgesas/especial.jpeg', 'alt_text' => 'Hamburguesa Especial'],
            ['image_path' => 'imagenes/hamburgesas/gratinadas.jpeg', 'alt_text' => 'Hamburguesa Gratinada'],
            ['image_path' => 'imagenes/hamburgesas/laabuela.jpeg', 'alt_text' => 'Hamburguesa La Abuela'],
            ['image_path' => 'imagenes/hamburgesas/LaPromo.jpeg', 'alt_text' => 'Promo de Hamburguesa'],
        ];
        foreach ($hambImages as $idx => $img) {
            CategoryImage::create([
                'category_id' => $hamburguesasCat->id,
                'image_path' => $img['image_path'],
                'alt_text' => $img['alt_text'],
                'order' => $idx + 1,
            ]);
        }

        $hambData = [
            ['name' => 'Común', 'desc' => 'Medallón de carne, mayonesa, lechuga, tomate, queso.', 'price' => 5200, 'badge' => null],
            ['name' => 'Especial', 'desc' => 'Medallón de carne, mayonesa, lechuga, tomate, queso, jamón, huevo.', 'price' => 6200, 'badge' => null],
            ['name' => 'Super', 'desc' => 'Medallón de carne, mayonesa, lechuga, tomate, queso, jamón, choclo, huevo.', 'price' => 6500, 'badge' => null],
            ['name' => 'La Abuela', 'desc' => 'Medallón de carne, mayonesa, lechuga, tomate, queso, jamón, cebolla rehogada, morrón, huevo.', 'price' => 6500, 'badge' => '⭐ La Abuela'],
            ['name' => 'Doble', 'desc' => 'Doble medallón de carne, mayonesa, lechuga, tomate, huevo doble, doble queso, doble jamón.', 'price' => 6800, 'badge' => null],
            ['name' => 'Gratinada', 'desc' => 'Medallón de carne, mayonesa, lechuga, tomate, queso, jamón, huevo.', 'price' => 7500, 'badge' => null],
            ['name' => 'La bomba', 'desc' => 'Doble medallón de carne, salsa barbacoa, lechuga, tomate, huevo, doble queso, doble jamón, choclo, Gratinado.', 'price' => 8000, 'badge' => 'Bomba'],
        ];
        foreach ($hambData as $idx => $h) {
            Product::create([
                'category_id' => $hamburguesasCat->id,
                'name' => $h['name'],
                'description' => $h['desc'],
                'price' => $h['price'],
                'badge' => $h['badge'],
                'order' => $idx + 1,
                'is_available' => true,
            ]);
        }

        // --- PAPAS FRITAS ---
        $papasCat = Category::create([
            'name' => 'Papas Fritas',
            'slug' => 'papas-fritas',
            'icon' => 'icon-papas-fritas',
            'subtitle' => 'Crujientes y doradas en el punto justo',
            'order' => 4,
            'is_active' => true,
        ]);

        $papasImages = [
            ['image_path' => 'imagenes/papas_fritas/Imagen de WhatsApp 2025-12-05 a las 14.58.53_6d00a606.jpg', 'alt_text' => 'Papas fritas gratinadas'],
            ['image_path' => 'imagenes/papas_fritas/IMG-20250725-WA0024.jpg', 'alt_text' => 'Papas fritas simples'],
            ['image_path' => 'imagenes/papas_fritas/WhatsApp Image 2026-01-29 at 23.45.07.jpeg', 'alt_text' => 'Salchipapas'],
        ];
        foreach ($papasImages as $idx => $img) {
            CategoryImage::create([
                'category_id' => $papasCat->id,
                'image_path' => $img['image_path'],
                'alt_text' => $img['alt_text'],
                'order' => $idx + 1,
            ]);
        }

        $papasData = [
            ['name' => 'Simple', 'desc' => 'Porción de papas fritas clásicas.', 'price' => 6000],
            ['name' => 'Gratinadas', 'desc' => 'Papas fritas con queso gratinado al horno.', 'price' => 7000],
            ['name' => 'Con cheddar', 'desc' => 'Papas fritas bañadas en salsa de queso cheddar.', 'price' => 7000],
            ['name' => 'Salchipapas', 'desc' => 'Papas fritas con salchichas trozadas y aderezos.', 'price' => 9000],
        ];
        foreach ($papasData as $idx => $p) {
            Product::create([
                'category_id' => $papasCat->id,
                'name' => $p['name'],
                'description' => $p['desc'],
                'price' => $p['price'],
                'order' => $idx + 1,
                'is_available' => true,
            ]);
        }

        // --- FIGAZAS ---
        $figazasCat = Category::create([
            'name' => 'Figazas',
            'slug' => 'figazas',
            'icon' => 'fas fa-stroopwafel',
            'subtitle' => 'Figazas de hamburguesa y de lomo',
            'order' => 5,
            'is_active' => true,
        ]);

        $figazaImages = [
            ['image_path' => 'imagenes/figazas/1.jpg', 'alt_text' => 'Figaza de hamburguesa Gratinada'],
            ['image_path' => 'imagenes/figazas/3.jpg', 'alt_text' => 'Figaza de lomo Especial'],
            ['image_path' => 'imagenes/figazas/2.jpg', 'alt_text' => 'Figaza de lomo Común'],
            ['image_path' => 'imagenes/figazas/4.jpg', 'alt_text' => 'Figaza de lomo Especial'],
        ];
        foreach ($figazaImages as $idx => $img) {
            CategoryImage::create([
                'category_id' => $figazasCat->id,
                'image_path' => $img['image_path'],
                'alt_text' => $img['alt_text'],
                'order' => $idx + 1,
            ]);
        }

        $figazasData = [
            ['name' => 'Figaza de Hamburguesa Común', 'desc' => 'Medallón de carne, mayonesa, lechuga, tomate.', 'price' => 7500],
            ['name' => 'Figaza de Hamburguesa Especial', 'desc' => 'Medallón de carne, mayonesa, ketchup, salsa golf, mostaza, lechuga, tomate, queso, jamón, huevo.', 'price' => 8500],
            ['name' => 'Figaza de Lomo Común', 'desc' => 'Carne de lomo, mayonesa, lechuga, tomate, queso.', 'price' => 15000],
            ['name' => 'Figaza de Lomo Especial', 'desc' => 'Carne de lomo, mayonesa, ketchup, salsa golf, mostaza, lechuga, tomate, queso, jamón, huevo.', 'price' => 18000],
            ['name' => 'Figaza de Lomo Completa', 'desc' => 'Carne de lomo, mayonesa, ketchup, salsa golf, mostaza, lechuga, tomate, queso, jamón, huevo, morrón, cebolla rehogada.', 'price' => 19000],
            ['name' => 'Figaza de hamburguesa Especial Gratinada', 'desc' => 'Medallón de carne, mayonesa, lechuga, tomate, jamón, queso, huevo gratinado.', 'price' => 11000],
            ['name' => 'Figaza de lomo Completa Gratinada', 'desc' => 'Carne de lomo, mayonesa, lechuga, tomate, jamón, queso, huevo, morrón, cebolla, gratinado.', 'price' => 20000],
        ];
        foreach ($figazasData as $idx => $f) {
            Product::create([
                'category_id' => $figazasCat->id,
                'name' => $f['name'],
                'description' => $f['desc'],
                'price' => $f['price'],
                'order' => $idx + 1,
                'is_available' => true,
            ]);
        }

        // --- COMIDAS AL PLATO ---
        $comidasCat = Category::create([
            'name' => 'Comidas al Plato',
            'slug' => 'comidas-al-plato',
            'icon' => 'fas fa-utensils',
            'subtitle' => 'Platos abundantes con guarnición incluida',
            'order' => 6,
            'is_active' => true,
        ]);

        $comidasImages = [
            ['image_path' => 'imagenes/camidas_al_plato/1.jpg', 'alt_text' => 'Lomito al Plato con ensalada'],
            ['image_path' => 'imagenes/camidas_al_plato/mila_caballo_papas.jpg', 'alt_text' => 'Milanesa a caballo con papas'],
            ['image_path' => 'imagenes/camidas_al_plato/milanesa_napo.jpg', 'alt_text' => 'Milanesa Napolitana con papas'],
            ['image_path' => 'imagenes/camidas_al_plato/3.jpg', 'alt_text' => 'Hamburguesa al Plato con papas'],
            ['image_path' => 'imagenes/camidas_al_plato/mila_papas.jpg', 'alt_text' => 'Milanesa simple con papas'],
        ];
        foreach ($comidasImages as $idx => $img) {
            CategoryImage::create([
                'category_id' => $comidasCat->id,
                'image_path' => $img['image_path'],
                'alt_text' => $img['alt_text'],
                'order' => $idx + 1,
            ]);
        }

        $comidasData = [
            ['name' => 'Hamburguesa al plato con guarnición (papas o ensalada)', 'desc' => '2 medallones de carne, lechuga, tomate, jamón, queso, huevo.', 'price' => 10000],
            ['name' => 'Lomito al plato con guarnición (papas o ensalada)', 'desc' => 'Bife de lomo, lechuga, tomate, jamón, queso, huevo.', 'price' => 16000],
            ['name' => 'Milanesa con guarnición Simple', 'desc' => 'Milanesa de carne/pollo con papas fritas o ensalada.', 'price' => 10500],
            ['name' => 'Milanesa con guarnición Napolitana', 'desc' => 'Milanesa de carne/pollo, salsa de tomate, jamón, queso, rodajas de tomates, aceituna, orégano con guarnición.', 'price' => 12000],
            ['name' => 'Milanesa con guarnición A caballo', 'desc' => 'Milanesa de carne/pollo, huevos fritos con guarnición.', 'price' => 12000],
        ];
        foreach ($comidasData as $idx => $c) {
            Product::create([
                'category_id' => $comidasCat->id,
                'name' => $c['name'],
                'description' => $c['desc'],
                'price' => $c['price'],
                'order' => $idx + 1,
                'is_available' => true,
            ]);
        }

        // --- LOMITOS ---
        $lomitosCat = Category::create([
            'name' => 'Lomitos',
            'slug' => 'lomitos',
            'icon' => 'fas fa-hotdog',
            'subtitle' => 'Carne de lomo seleccionada en pan flauta',
            'order' => 7,
            'is_active' => true,
        ]);

        $lomitoImages = [
            ['image_path' => 'imagenes/lomitos/lomito_especial.jpg', 'alt_text' => 'Lomito Especial'],
            ['image_path' => 'imagenes/lomitos/lomito_completo.jpg', 'alt_text' => 'Lomito Completo'],
        ];
        foreach ($lomitoImages as $idx => $img) {
            CategoryImage::create([
                'category_id' => $lomitosCat->id,
                'image_path' => $img['image_path'],
                'alt_text' => $img['alt_text'],
                'order' => $idx + 1,
            ]);
        }

        $lomitosData = [
            ['name' => 'Lomito Común', 'desc' => 'Pan Flauta, carne de lomo, mayonesa, lechuga, tomate, queso.', 'price' => 13000],
            ['name' => 'Lomito Especial', 'desc' => 'Pan Flauta, carne de lomo, mayonesa, lechuga, tomate, queso, jamón, huevo.', 'price' => 16000],
            ['name' => 'Lomito Completo', 'desc' => 'Pan Flauta, carne de lomo, mayonesa, lechuga, tomate, queso, jamón, huevo, cebolla rehogada, morrón.', 'price' => 17000],
        ];
        foreach ($lomitosData as $idx => $l) {
            Product::create([
                'category_id' => $lomitosCat->id,
                'name' => $l['name'],
                'description' => $l['desc'],
                'price' => $l['price'],
                'order' => $idx + 1,
                'is_available' => true,
            ]);
        }

        // --- TARTAS ---
        $tartasCat = Category::create([
            'name' => 'Tartas',
            'slug' => 'tartas',
            'icon' => 'fas fa-cookie',
            'subtitle' => 'Porciones individuales, medias y enteras',
            'order' => 8,
            'is_active' => true,
        ]);

        $tartaImages = [
            ['image_path' => 'imagenes/tartas/1.jpg', 'alt_text' => 'Tartas surtidas de Cuarto'],
            ['image_path' => 'imagenes/tartas/4.jpg', 'alt_text' => 'Media tarta de pollo'],
            ['image_path' => 'imagenes/tartas/3.jpg', 'alt_text' => 'Tarta entera de carne'],
            ['image_path' => 'imagenes/tartas/2.jpg', 'alt_text' => 'Tarta de verdura de cuarto'],
        ];
        foreach ($tartaImages as $idx => $img) {
            CategoryImage::create([
                'category_id' => $tartasCat->id,
                'image_path' => $img['image_path'],
                'alt_text' => $img['alt_text'],
                'order' => $idx + 1,
            ]);
        }

        $tartasData = [
            ['name' => 'Carne', 'cuarto' => 5500, 'media' => 9500, 'entera' => 15000],
            ['name' => 'Pollo', 'cuarto' => 5500, 'media' => 9500, 'entera' => 15000],
            ['name' => 'Jamón y Queso', 'cuarto' => 6500, 'media' => 11000, 'entera' => 16000],
            ['name' => 'Verdura', 'cuarto' => 5500, 'media' => 9500, 'entera' => 15000],
            ['name' => 'Humita', 'cuarto' => 5000, 'media' => 9000, 'entera' => 13000],
        ];
        foreach ($tartasData as $idx => $t) {
            $prod = Product::create([
                'category_id' => $tartasCat->id,
                'name' => 'Tarta de ' . $t['name'],
                'description' => 'Masa casera con abundante relleno fresco.',
                'order' => $idx + 1,
                'is_available' => true,
            ]);
            ProductVariant::create(['product_id' => $prod->id, 'name' => 'Cuarto (1 porción)', 'price' => $t['cuarto'], 'order' => 1]);
            ProductVariant::create(['product_id' => $prod->id, 'name' => 'Media', 'price' => $t['media'], 'order' => 2]);
            ProductVariant::create(['product_id' => $prod->id, 'name' => 'Entera', 'price' => $t['entera'], 'order' => 3]);
        }

        // --- TORTILLA DE PAPA ---
        $tortillasCat = Category::create([
            'name' => 'Tortilla de papa',
            'slug' => 'tortillas',
            'icon' => 'fas fa-egg',
            'subtitle' => 'Clásica y Napolitana',
            'order' => 9,
            'is_active' => true,
        ]);

        $tortillaImages = [
            ['image_path' => 'imagenes/tortillas/Imagen de WhatsApp 2025-07-25 a las 12.03.04_9abcdf4a.jpg', 'alt_text' => 'Tortilla simple'],
            ['image_path' => 'imagenes/tortillas/IMG-20250725-WA0020.jpg', 'alt_text' => 'Tortilla Napolitana'],
        ];
        foreach ($tortillaImages as $idx => $img) {
            CategoryImage::create([
                'category_id' => $tortillasCat->id,
                'image_path' => $img['image_path'],
                'alt_text' => $img['alt_text'],
                'order' => $idx + 1,
            ]);
        }

        $tortillasData = [
            ['name' => 'Simple', 'desc' => 'Tortilla de papas tradicional jugosa.', 'price' => 8000],
            ['name' => 'Napolitana', 'desc' => 'Tortilla de papas cubierta con salsa, jamón, queso y orégano.', 'price' => 10000],
        ];
        foreach ($tortillasData as $idx => $to) {
            Product::create([
                'category_id' => $tortillasCat->id,
                'name' => 'Tortilla ' . $to['name'],
                'description' => $to['desc'],
                'price' => $to['price'],
                'order' => $idx + 1,
                'is_available' => true,
            ]);
        }

        // --- SANDWICH ---
        $sandwichCat = Category::create([
            'name' => 'Sandwich',
            'slug' => 'sandwiches',
            'icon' => 'fas fa-bacon',
            'subtitle' => 'Pan flauta y pan figaza (Milanesa de Carne o Pollo)',
            'order' => 10,
            'is_active' => true,
        ]);

        $sandwichImages = [
            ['image_path' => 'imagenes/sandwiches/Promo de sandwich de Mila de carne.jpeg', 'alt_text' => 'Sandwich de mila mas papas'],
            ['image_path' => 'imagenes/sandwiches/Sandwich de milanesa de carne especial.jpeg', 'alt_text' => 'Sandwich de milanesa de carne especial'],
            ['image_path' => 'imagenes/sandwiches/Promo de sandwich de Mila de pollo.jpeg', 'alt_text' => 'Promo sandwich de milanesa de pollo'],
        ];
        foreach ($sandwichImages as $idx => $img) {
            CategoryImage::create([
                'category_id' => $sandwichCat->id,
                'image_path' => $img['image_path'],
                'alt_text' => $img['alt_text'],
                'order' => $idx + 1,
            ]);
        }

        $sandwichData = [
            ['name' => 'Pan Flauta - Común', 'desc' => 'Pan Flauta, milanesa, mayonesa, lechuga, tomate.', 'price' => 8500],
            ['name' => 'Pan Flauta - Especial', 'desc' => 'Pan Flauta, milanesa, mayonesa, lechuga, tomate, jamón, queso, huevo.', 'price' => 9500],
            ['name' => 'Pan Flauta - Promo (con papas)', 'desc' => 'Pan Flauta, milanesa, mayonesa, lechuga, tomate, jamón, queso, huevo, guarnición de papas fritas.', 'price' => 10500],
            ['name' => 'Pan Figaza - Común', 'desc' => 'Pan figaza, milanesa, mayonesa, lechuga, tomate.', 'price' => 9500],
            ['name' => 'Pan Figaza - Especial', 'desc' => 'Pan figaza, milanesa, mayonesa, lechuga, tomate, jamón, queso, huevo.', 'price' => 11000],
            ['name' => 'Pan Figaza - Promo (con papas)', 'desc' => 'Pan figaza, milanesa, mayonesa, lechuga, tomate, jamón, queso, huevo, guarnición de papas fritas.', 'price' => 12000],
        ];
        foreach ($sandwichData as $idx => $s) {
            Product::create([
                'category_id' => $sandwichCat->id,
                'name' => $s['name'],
                'description' => $s['desc'],
                'price' => $s['price'],
                'order' => $idx + 1,
                'is_available' => true,
            ]);
        }

        // --- TOSTADOS ---
        $tostadosCat = Category::create([
            'name' => 'Tostados',
            'slug' => 'tostados',
            'icon' => 'fas fa-bread-slice',
            'subtitle' => 'En pan de miga tostado',
            'order' => 11,
            'is_active' => true,
        ]);

        $tostadoImages = [
            ['image_path' => 'imagenes/tostados/Carlito.jpeg', 'alt_text' => 'Carlito'],
            ['image_path' => 'imagenes/tostados/Triple especial de pollo.jpeg', 'alt_text' => 'Triple especial de pollo'],
            ['image_path' => 'imagenes/tostados/Tostado doble jamón y queso.jpeg', 'alt_text' => 'Tostado doble jamón y queso'],
        ];
        foreach ($tostadoImages as $idx => $img) {
            CategoryImage::create([
                'category_id' => $tostadosCat->id,
                'image_path' => $img['image_path'],
                'alt_text' => $img['alt_text'],
                'order' => $idx + 1,
            ]);
        }

        $tostadosData = [
            ['name' => 'Carlito', 'desc' => 'Pan de miga, mayonesa, jamón, queso.', 'price' => 6000],
            ['name' => 'Triple común', 'desc' => 'Pan de miga, mayonesa, jamón, queso, lechuga, tomate.', 'price' => 7000],
            ['name' => 'Triple especial', 'desc' => 'Pan de miga, mayonesa, jamón, queso, lechuga, tomate, zanahoria, huevo.', 'price' => 8000],
            ['name' => 'Doble jamón y queso', 'desc' => 'Pan de miga, mayonesa, doble jamón, doble queso.', 'price' => 9000],
            ['name' => 'Triple de pollo', 'desc' => 'Pan de miga, mayonesa, pollo, jamón, queso, lechuga, tomate, zanahoria, huevo.', 'price' => 16000],
            ['name' => 'Triple de lomo', 'desc' => 'Pan de miga, mayonesa, lomo, jamón, queso, lechuga, tomate, zanahoria, huevo.', 'price' => 16000],
        ];
        foreach ($tostadosData as $idx => $to) {
            Product::create([
                'category_id' => $tostadosCat->id,
                'name' => $to['name'],
                'description' => $to['desc'],
                'price' => $to['price'],
                'order' => $idx + 1,
                'is_available' => true,
            ]);
        }

        // --- PICADAS ---
        $picadasCat = Category::create([
            'name' => 'Picadas',
            'slug' => 'picadas',
            'icon' => 'fas fa-cheese',
            'subtitle' => 'Completas y abundantes para compartir',
            'order' => 12,
            'is_active' => true,
        ]);

        $picadaImages = [
            ['image_path' => 'imagenes/picadas/picada_chica.jpg', 'alt_text' => 'Picada chica'],
            ['image_path' => 'imagenes/picadas/picada_grande.jpg', 'alt_text' => 'Picada grande'],
            ['image_path' => 'imagenes/picadas/3.jpg', 'alt_text' => 'Picada "La Abuela"'],
        ];
        foreach ($picadaImages as $idx => $img) {
            CategoryImage::create([
                'category_id' => $picadasCat->id,
                'image_path' => $img['image_path'],
                'alt_text' => $img['alt_text'],
                'order' => $idx + 1,
            ]);
        }

        $picadasData = [
            ['name' => 'Chica (Comen 2, pican 4)', 'desc' => 'Milanesa de pollo y carne, papas fritas, jamón, queso, rodajas de calabresa, aceitunas.', 'price' => 20000, 'badge' => null],
            ['name' => 'Grande (Comen 4, pican 6)', 'desc' => 'Milanesa de pollo y carne, papas fritas, 6 empanadas surtidas, jamón, queso, rodajas de calabresa, aceitunas.', 'price' => 30000, 'badge' => null],
            ['name' => 'La Abuela ⭐ (Comen 5, pican 7)', 'desc' => 'Milanesa de pollo y carne, papas fritas, 6 empanadas surtidas, albóndigas, aro de cebolla, jamón, queso, rodajas de calabresa, aceitunas.', 'price' => 32000, 'badge' => '⭐ La Abuela'],
        ];
        foreach ($picadasData as $idx => $pi) {
            Product::create([
                'category_id' => $picadasCat->id,
                'name' => $pi['name'],
                'description' => $pi['desc'],
                'price' => $pi['price'],
                'badge' => $pi['badge'],
                'order' => $idx + 1,
                'is_available' => true,
            ]);
        }

        // --- BEBIDAS ---
        $bebidasCat = Category::create([
            'name' => 'Bebidas',
            'slug' => 'bebidas',
            'icon' => 'fas fa-glass-cheers',
            'subtitle' => 'Para acompañar tu comida favorita',
            'order' => 13,
            'is_active' => true,
        ]);

        $bebidasData = [
            ['name' => 'Agua Mineral 500 ml', 'desc' => 'Agua mineral con o sin gas 500 ml.', 'price' => 2000],
            ['name' => 'Agua Mineral 1.5 L', 'desc' => 'Agua mineral con o sin gas 1.5 Litros.', 'price' => 2500],
            ['name' => 'Agua Saborizada 500 ml', 'desc' => 'Agua saborizada frutal 500 ml.', 'price' => 2700],
            ['name' => 'Agua Saborizada 1.5 L', 'desc' => 'Agua saborizada frutal 1.5 Litros.', 'price' => 3800],
            ['name' => 'Gaseosa 500 ml (Línea Coca o Pepsi)', 'desc' => 'Botella de 500 ml.', 'price' => 3000],
            ['name' => 'Gaseosa 1.5 L (Línea Coca o Pepsi)', 'desc' => 'Botella de 1.5 Litros.', 'price' => 5000],
            ['name' => 'Coca Cola 1 L', 'desc' => 'Botella de 1 Litro.', 'price' => 4000],
            ['name' => 'Cerveza Brahma 1 L', 'desc' => 'Botella de 1 Litro.', 'price' => 5000],
            ['name' => 'Cerveza Corona 330 ml', 'desc' => 'Porrón 330 ml.', 'price' => 3500],
            ['name' => 'Cerveza Corona 710 ml', 'desc' => 'Botella 710 ml.', 'price' => 6000],
            ['name' => 'Cerveza Miller', 'desc' => 'Botella individual.', 'price' => 3500],
            ['name' => 'Cerveza Sol', 'desc' => 'Botella individual.', 'price' => 3500],
            ['name' => 'Cerveza Stella Artois', 'desc' => 'Botella individual.', 'price' => 3500],
            ['name' => 'Lata Brahma', 'desc' => 'Lata 473 ml.', 'price' => 3000],
            ['name' => 'Lata Quilmes', 'desc' => 'Lata 473 ml.', 'price' => 3000],
            ['name' => 'Lata Imperial', 'desc' => 'Lata 473 ml.', 'price' => 3000],
            ['name' => 'Lata Budweiser', 'desc' => 'Lata 473 ml.', 'price' => 3000],
            ['name' => 'Speed Energy Drink', 'desc' => 'Bebida energizante.', 'price' => 2000],
            ['name' => 'Dr Lemon', 'desc' => 'Trago listo para tomar.', 'price' => 3500],
            ['name' => 'Pronto', 'desc' => 'Trago listo para tomar.', 'price' => 3500],
        ];
        foreach ($bebidasData as $idx => $b) {
            Product::create([
                'category_id' => $bebidasCat->id,
                'name' => $b['name'],
                'description' => $b['desc'],
                'price' => $b['price'],
                'order' => $idx + 1,
                'is_available' => true,
            ]);
        }

        // --- ENSALADAS ---
        $ensaladasCat = Category::create([
            'name' => 'Ensaladas',
            'slug' => 'ensaladas',
            'icon' => 'fas fa-plate-wheat',
            'subtitle' => 'Frescas del día',
            'order' => 14,
            'is_active' => true,
        ]);

        $ensaladaImages = [
            ['image_path' => 'imagenes/extras/2.jpg', 'alt_text' => 'Ensalada Simple'],
            ['image_path' => 'imagenes/extras/ensalada_completa.jpg', 'alt_text' => 'Ensalada Completa'],
        ];
        foreach ($ensaladaImages as $idx => $img) {
            CategoryImage::create([
                'category_id' => $ensaladasCat->id,
                'image_path' => $img['image_path'],
                'alt_text' => $img['alt_text'],
                'order' => $idx + 1,
            ]);
        }

        $ensaladasData = [
            ['name' => 'Ensalada Simple', 'desc' => 'Lechuga y tomate frescos.', 'price' => 4000],
            ['name' => 'Ensalada Completa', 'desc' => 'Lechuga, tomate, zanahoria, huevo y cebolla.', 'price' => 5000],
            ['name' => 'Ensalada Con rúcula', 'desc' => 'Rúcula, tomate, zanahoria, huevo y cebolla (opcional).', 'price' => 6000],
        ];
        foreach ($ensaladasData as $idx => $en) {
            Product::create([
                'category_id' => $ensaladasCat->id,
                'name' => $en['name'],
                'description' => $en['desc'],
                'price' => $en['price'],
                'order' => $idx + 1,
                'is_available' => true,
            ]);
        }
    }
}
