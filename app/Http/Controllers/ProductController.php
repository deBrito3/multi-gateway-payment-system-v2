<?php

namespace App\Http\Controllers;

use App\Actions\Product\CreateProductAction;
use App\Actions\Product\DeleteProductAction;
use App\Actions\Product\ListProductsAction;
use App\Actions\Product\UpdateProductAction;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(ListProductsAction $action): JsonResponse
    {
        return ProductResource::collection($action->execute())->response();
    }

    public function store(StoreProductRequest $request, CreateProductAction $action): JsonResponse
    {
        $product = $action->execute($request->validated());
        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function show(Product $product): JsonResponse
    {
        return (new ProductResource($product))->response();
    }

    public function update(UpdateProductRequest $request, Product $product, UpdateProductAction $action): JsonResponse
    {
        $product = $action->execute($product, $request->validated());
        return (new ProductResource($product))->response();
    }

    public function destroy(Product $product, DeleteProductAction $action): JsonResponse
    {
        $action->execute($product);
        return response()->json(['message' => 'Product deleted']);
    }
}
