<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\EventSetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $categoriesCount = Category::count();
        $productsCount = Product::count();
        $availableProductsCount = Product::where('is_available', true)->count();
        $usersCount = User::count();
        $ordersCount = Order::count();
        $recentOrders = Order::with('items')->latest()->take(5)->get();
        $activeEvent = EventSetting::where('is_active', true)->first();

        return view('admin.dashboard', compact(
            'categoriesCount',
            'productsCount',
            'availableProductsCount',
            'usersCount',
            'ordersCount',
            'recentOrders',
            'activeEvent'
        ));
    }
}
