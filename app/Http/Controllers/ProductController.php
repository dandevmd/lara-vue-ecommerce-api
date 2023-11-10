<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ProductListResource;

class ProductController extends Controller
{

    public function index()
    {
        //get an instance of current request 
        $perPage = request('per_page', 3);
        $search = request('search', '');
        $sortField = request('sort_field', 'updated_at');
        $sortDirection = request('sort_direction', 'desc');

        $query = Product::query()
            ->where('title', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);


        return ProductListResource::collection($query);
    }


    public function store(ProductRequest $request)
    {

        $data = $request->validated();
        $image = $data['image'] ?? null;

        if ($image) {
            $data['image_mime'] = $image->getClientMimeType();
            $data['image_size'] = $image->getSize();
            $savedImagePath = $this->saveImage($image);
            $data['image'] = URL::to('/images/' . $savedImagePath->getFilename());
        }

        $product = Product::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']),
            'image' => $data['image'],
            'image_mime' => $data['image_mime'] ?? null,
            'image_size' => $data['image_size'] ?? null,
            'description' => $data['description'],
            'price' => $data['price'],
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return new ProductResource($product);
    }


    public function show($id)
    {
        $product = Product::findOrFail($id);
        return new ProductResource($product);
    }


    public function update($id)
    {

        $product = Product::findOrFail($id);
        $data = request()->all();
        $image = $data['image'] ?? $product->image;

        if ($image && $image != $product->image) {
            $data['image_mime'] = $image->getClientMimeType();
            $data['image_size'] = $image->getSize();
            $savedImagePath = $this->saveImage($image);
            $data['image'] = URL::to('/images/' . $savedImagePath->getFilename());
        }

        $product->update([
            'title' => $data['title'] ?? $product->title,
            'slug' => Str::slug($data['title'] ?? $product->title),
            'image' => $data['image'] ?? $product->image,
            'image_mime' => $data['image_mime'] ?? $product->image_mime,
            'image_size' => $data['image_size'] ?? $product->image_size,
            'description' => $data['description'] ?? $product->description,
            'price' => $data['price'] ?? $product->price,
            'updated_by' => auth()->user()->id
        ]);

        return new ProductResource($product);
    }


    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            $name = basename($product->image);
            unlink(public_path() . '/images/' . $name);
        }


        $product->delete();

        return response('', 204);
    }

    public function saveImage(UploadedFile $image)
    {

        $path = public_path() . '/images';

        // check if the image dir in public folder does not exist then create it
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $name = Str::random() . '.' . $image->getClientOriginalExtension();
        $savedFileWithNewName = $image->move($path, $name);

        return $savedFileWithNewName;
    }
}