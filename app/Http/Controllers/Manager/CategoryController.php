<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private Category $category)
    {
        
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = $this->category->orderBy('id', 'desc')->paginate(5);
        return view('manager.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('manager.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->category->create($request->all());
        return redirect()->route('manager.categories.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($category)
    {
        return redirect()->route('manager.categories.edit', $category);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($category)
    {
        $category = $this->category->find($category);
        return view('manager.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $category)
    {
        $this->category->findOrFail($category)->update($request->all());
        return redirect()->route('manager.categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($category)
    {
        $this->category->findOrFail($category)->delete();
        return redirect()->back();
    }
}
