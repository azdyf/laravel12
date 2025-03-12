<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // get all products
        $products = Product::latest()->paginate(10);

        // render view with products
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // validate form
        $request->validate([
            'image'         => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title'         => 'required|min:5',
            'description'   => 'required|min:10',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric',
        ]);

        // upload image
        $image = $request->file('image');
        $image->storeAs('products', $image->hashName());

        // prep data
        $data = [
            'image'         => $image->hashName(),
            'title'         => $request->title,
            'description'   => $request->description,
            'price'         => $request->price,
            'stock'         => $request->stock,
        ];

        // create product
        Product::create($data);

        // redirect to index
        return redirect()->route('products.index')->with('success', 'Data successfully saved!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        // get product by id
        $product = Product::findOrFail($id);

        // render view with products
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        // get product by id
        $product = Product::findOrFail($id);

        // render view with product
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        // validate form
        $request->validate([
            'image'         => 'image|mimes:jpeg,jpg,png|max:2048',
            'title'         => 'required|min:5',
            'description'   => 'required|min:10',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric'
        ]);

        // get product by id
        $product = Product::findOrFail($id);

        // prep data
        $data = [
            'title'         => $request->title,
            'description'   => $request->description,
            'price'         => $request->price,
            'stock'         => $request->stock,
        ];

        // check if image is uploaded
        if ($request->hasFile('image')) {
            // delete old image
            Storage::delete('products/' . $product->image);

            // upload new image
            $image = $request->file('image');
            $image->storeAs('products', $image->hashName());

            // add image data
            $data['image'] = $image->hashName();
        }

        // update product
        $product->update($data);

        //  redirect to index
        return redirect()->route('products.index')->with('success', 'Data successfully changed!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        // get product by id
        $product = Product::findOrFail($id);

        // delete image
        Storage::delete('products/' . $product->image);

        // delete product
        $product->delete();

        // redirect to index
        return redirect()->route('products.index')->with('success', 'Data successfully deleted!');
    }
}
