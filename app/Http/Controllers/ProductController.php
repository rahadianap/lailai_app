<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        $data = Product::where('is_aktif', 1)->get();
        return Inertia::render('Products/Index', [
            'data' => $data
        ]);
    }
}
