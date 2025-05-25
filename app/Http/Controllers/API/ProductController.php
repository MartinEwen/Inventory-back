<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Summary of index
     * @return mixed|\Illuminate\Http\JsonResponse
     * Fonction de retour de l'ensemble des produits de la BDD
     */
    public function index()
    {
        $products = Product::with('category')->get();
        return response()->json($products, 200);
    }

    /**
     * Summary of show
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     * fonction de retour d'un produit spécifique en fonction de son ID
     */
    public function show($id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product, 200);
    }

    /**
     * Summary of store
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     * fonction de creation de produit dans la BDD
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = Product::create($request->all());

        return response()->json($product, 201);
    }


    /**
     * Summary of update
     * @param \Illuminate\Http\Request $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     * fonction de mise à jour d'un produit dans la BDD
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'sometimes|required|integer|min:0',
            'alerte' => 'sometimes|required|integer|min:0',
            'price' => 'sometimes|required|numeric|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product->update($request->all());

        return response()->json($product, 200);
    }

    /**
     * Summary of destroy
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     * fonction de supression d'un produit de la BDD
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }

    /**
     * Summary of checkStockLevel
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     * fonction d'alerte si le stock est plus bas que l'alerte et envoie une notification
     */
    public function checkStockLevel($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        if (auth('api')->check() && $product->quantity <= $product->alerte) {
            $user = auth('api')->user();
            $user->notify(new \App\Notifications\StockLowNotification($product));

            return response()->json([
                'message' => 'Stock low notification sent.',
                'product_id' => $product->id,
                'product_name' => $product->name
            ], 200);
        }

        return response()->json(['message' => 'Stock level is sufficient.'], 200);
    }

    /**
     * Summary of lowStock
     * @return mixed|\Illuminate\Http\JsonResponse
     * fonction qui recupere tous les produits en dessous du seuil d'alerte 
     */
    public function lowStock()
    {
        $products = Product::with('category')
            ->whereColumn('quantity', '<=', 'alerte')
            ->get();

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products below the alert threshold.'], 200);
        }

        return response()->json($products, 200);
    }

}
