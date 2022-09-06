<?php

namespace App\Http\Controllers;

use App\Models\CharacterProduct;
use App\Models\Products;
use App\Models\TableProduct;
use App\Models\User;
use Illuminate\Http\Request;
use function Ramsey\Uuid\v4;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $token = $request->header('authorization');
        $allProducts = Products::all();

        if(count($allProducts) === 0){
            return response([
                'status' => false,
                'message' => 'products not found'
            ], 404)
                ->setStatusCode(404, 'products not found');
        }

        return response([
            'status' => true,
            'products' => $allProducts
        ], 200)
            ->setStatusCode(200, 'products found')
            ->header('authorization', $token);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $token = $request->header('authorization');
        $searchAdmin = User::where('bearerToken','=',$token)->get();
        if(count($searchAdmin) === 0){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }
        if($searchAdmin[0]->name !== 'admin'){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }

        $title = $request->title;
        $brand = $request->brand;
        $price = $request->price;
        $price_sale = $request->price_sale || 0;
        $description = $request->description;
        $images = $request->hasFile('images');

        $errors = [];

        if(empty($title)){
            $errors[] = array('title_required' => 'Title required');
        }
        if(empty($brand)){
            $errors[] = array('brand_required' => 'Brand required');
        }
        if(empty($price)){
            $errors[] = array('price_required' => 'Price required');
        }
        if(empty($description)){
            $errors[] = array('description_required' => 'Description required');
        }
        if($images){
            if($request->file('images')->getSize() > 2 * 1024 * 1024){
                $errors[] = array('image_size' => 'Image size more to 2 MB');
            }
            if(
                !strpos($request->file('images')->getClientOriginalName(), '.jpg') &&
                !strpos($request->file('images')->getClientOriginalName(), '.png')
            ){
                $errors[] = array('file_type' => 'Downloaded not image');
            }
        } else {
            $errors[] = array('image_required' => 'Image required');
        }

        if(count($errors) !== 0){
            return response([
                'status' => false,
                'errors' => $errors
            ], 400)
                ->setStatusCode(400, 'validation error');
        }

        $fileName = v4() . '.jpg';
        $request->file('images')->move(public_path('/images/products/'), $fileName);

        $newProduct = Products::create([
            'title' => $title,
            'price' => $price,
            'brand' => $brand,
            'price_sale' => $price_sale,
            'description' => $description,
            'images' => $fileName
        ]);

        $sett = 'Генерарируется автоматически';

        $createCharacter = CharacterProduct::create([
            'product_id' => $newProduct->id,
            'description_product' => $sett,
            'instruction_product' => $sett,
            'technology_product' => $sett,
            'brand_info_product' => $sett
        ]);

        $createTableInfo = TableProduct::create([
            'product_id' => $newProduct->id,
            'article_product' => $sett,
            'size_product' => $sett,
            'color_product' => $sett,
            'exists_product' => $sett
        ]);

       return response([
           'status' => true,
           'message' => $newProduct->id,
           'data_product' => [
               'product' => $newProduct,
               'table_info' => $createTableInfo,
               'character_info' => $createCharacter
           ]
       ], 201)
           ->setStatusCode(201, 'product created')
           ->header('authorization', $token);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function show(Products $products, $id)
    {
        $searchProduct = Products::find($id);

        if($searchProduct === null){
            return response([
                'status' => false,
                'message' => 'product not found'
            ], 404)
                ->setStatusCode(404, 'product not found');
        }

        $characterProduct = CharacterProduct::where('product_id','=',$searchProduct->id)->get();
        $tableProduct = TableProduct::where('product_id','=',$searchProduct->id)->get();

        return response([
            'status' => true,
            'product' => $searchProduct,
            'info_product' => [
                'product_character' => $characterProduct,
                'product_table' => $tableProduct
            ]
        ], 200)
        ->setStatusCode(200, 'products found');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Products $products, $id)
    {
        $token = $request->header('authorization');
        $searchAdmin = User::where('bearerToken','=',$token)->get();
        if(count($searchAdmin) === 0){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }
        if($searchAdmin[0]->name !== 'admin'){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }

        $title = $request->title;
        $brand = $request->brand;
        $price = $request->price;
        $price_sale = $request->price_sale;
        $description = $request->description;
        $images = $request->hasFile('images');

        $errors = [];

        if(empty($title)){
            $errors[] = array('title_required' => 'Title required');
        }
        if(empty($brand)){
            $errors[] = array('brand_required' => 'Brand required');
        }
        if(empty($price)){
            $errors[] = array('price_required' => 'Price required');
        }
        if(empty($description)){
            $errors[] = array('description_required' => 'Description required');
        }
        if($images){
            if($request->file('images')->getSize() > 2 * 1024 * 1024){
                $errors[] = array('images_size' => 'Image size not more 2 MB');
            }
            if(
                !strpos($request->file('images')->getClientOriginalName(), '.jpg') &&
                !strpos($request->file('images')->getClientOriginalName(), '.png')
            ){
                $errors[] = array('images_type' => 'Downloaded not images');
            }
        } else{
            $errors[] = array('images_required' => 'Images required');
        }

        $searchProduct = Products::find($id);

        if($searchProduct === null){
            return response([
                'status' => false,
                'message' => 'product not found'
            ], 404)
                ->setStatusCode(404, 'product not found');
        }

        if(count($errors) !== 0){
            return response([
                'status' => false,
                'message' => $errors
            ], 400)
                ->setStatusCode(400, 'errors validation');
        }

        // delete images
        unlink(public_path("/images/products/$searchProduct->images"));

        $fileName = v4() . '.jpg';
        $request->file('images')->move(public_path('/images/products'), $fileName);

        $searchProduct->update([
            'title' => $title,
            'brand' => $brand,
            'description' => $description,
            'price' => $price,
            'price_sale' => $price_sale,
            'images' => $fileName
        ]);

        return response([
            'status' => true,
            'product' => $searchProduct
        ], 200)
            ->setStatusCode(200, 'updated successful')
            ->header('authorization', $token);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function destroy(Products $products, $id, Request $request)
    {
        $token = $request->header('authorization');
        $searchAdmin = User::where('bearerToken','=',$token)->get();
        if(count($searchAdmin) === 0){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }
        if($searchAdmin[0]->name !== 'admin'){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }

        $searchProduct = Products::find($id);

        if($searchProduct === null){
            return response([
                'status' => false,
                'message' => 'product not found'
            ], 404)
                ->setStatusCode(404, 'product not found');
        }

        unlink(public_path("/images/products/$searchProduct->images"));

        $searchInstruction = TableProduct::where('product_id','=',$searchProduct->id)->get();
        $searchCharacteristic = CharacterProduct::where('product_id', '=', $searchProduct->id)->get();

        if(count($searchInstruction) !== 0){
            foreach($searchInstruction as $key){
                $key->delete();
            }
        }
        if(count($searchCharacteristic) !== 0){
            foreach($searchCharacteristic as $key){
                $key->delete();
            }
        }

        $searchProduct->delete();

        return response([
            'status' => true,
            'message' => 'deleted successful'
        ], 200)
            ->setStatusCode(200, 'deleted successful')
            ->header('authorization', $token);
    }
}
