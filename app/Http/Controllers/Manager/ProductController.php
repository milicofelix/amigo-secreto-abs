<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function __construct(private Product $product)
    {
        
    }

    public function index()
    {
        $products = $this->product->orderBy('id', 'desc')->paginate(5);
        return view('manager.products.index', compact('products'));
    }

    public function create()
    {
        return view('manager.products.create');
    }

    public function store(Request $request)
    {
        $this->product->create($request->all());
        return redirect()->route('manager.products.index');
    }

    public function edit($product)
    {
        $product = $this->product->find($product);
        return view('manager.products.edit', compact('product'));
    }

    public function show($product)
    {
        return redirect()->route('manager.products.edit', $product);
    }

    public function update(Request $request, $product)
    {
        //dd($request->all(), $product);
        $this->product->findOrFail($product)->update($request->all());
        return redirect()->route('manager.products.index');
    }

    public function destroy($product)
    {
        $this->product->findOrFail($product)->delete();
        return redirect()->back(); //return redirect()->route('manager.products.index');
    }
}
