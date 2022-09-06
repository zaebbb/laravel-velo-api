<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\TableProduct;
use App\Models\User;
use Illuminate\Http\Request;

class TableProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($idProduct, Request $request)
    {
        $token = $request->header('authorization');
        $searchProduct = Products::find($idProduct);
        $searchTableProduct = TableProduct::where('product_id','=',$idProduct)->get();

        if($searchProduct === null){
            return response([
                'status' => false,
                'message' => 'product not found'
            ], 404)
                ->setStatusCode(404, 'product not found');
        }

        if(count($searchTableProduct) === 0){
            return response([
                'status' => false,
                'message' => 'table data not found'
            ], 404)
                ->setStatusCode(404, 'table data not found');
        }

        return response([
            'status' => true,
            'table_data' => $searchTableProduct
        ], 200)
            ->setStatusCode(200, 'table data found')
            ->header('authorization', $token);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $idProduct)
    {
        $token = $request->header('authorization');
        $searchUser = User::where('bearerToken','=',$token)->get();
        if(count($searchUser) === 0){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }
        if($searchUser[0]->name !== 'admin'){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }
        $articleProduct = $request->articleProduct;
        $sizeProduct = $request->sizeProduct;
        $colorProduct = $request->colorProduct;
        $existsProduct = $request->existsProduct;

        $errors = [];

        $searchProducts = TableProduct::where('product_id','=',$idProduct)->get();

        if(count($searchProducts) > 10){
            return response([
                'status' => false,
                'message' => 'Достигнуто максимально допустимое количество ячеек в таблице, создайте новый товар!'
            ], 400)
                ->setStatusCode(400, 'error created');
        }

        if(empty($articleProduct)){
            $errors[] = array('article_required' => 'Article required');
        }
        if(empty($sizeProduct)){
            $errors[] = array('size_required' => 'Size required');
        }
        if(empty($colorProduct)){
            $errors[] = array('color_required' => 'Size required');
        }
        if(empty($existsProduct)){
            $errors[] = array('exists_required' => 'Exists required');
        }

        if(count($errors) !== 0){
            return response([
                'status' => false,
                'errors' => $errors
            ], 400)
                ->setStatusCode(400, 'error validation');
        }

        $createInfoTable = TableProduct::create([
            'product_id' => $idProduct,
            'article_product' => $articleProduct,
            'size_product' => $sizeProduct,
            'color_product' => $colorProduct,
            'exists_product' => $existsProduct
        ]);

        return response([
            'status' => true,
            'table_id' => $createInfoTable->id
        ], 201)
            ->setStatusCode(201, 'created successful')
            ->header('authorization', $token);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TableProduct  $tableProduct
     * @return \Illuminate\Http\Response
     */
    public function show(TableProduct $tableProduct, Request $request, $idProduct)
    {
        $token = $request->header('authorization');
        $searchItems = TableProduct::where('product_id','=',$idProduct)->get();

        if(count($searchItems) === 0){
            return response([
                'status' => false,
                'message' => 'items not found'
            ], 400)
                ->setStatusCode(404, 'items table not found not found');
        }

        return response([
            'status' => false,
            'id_product' => $idProduct,
            'items_table' => $searchItems
        ], 200)
            ->setStatusCode(200, "items table product $idProduct fount")
            ->header('authorization', $token);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TableProduct  $tableProduct
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TableProduct $tableProduct, $idProduct, $idItemTable)
    {
        $token = $request->header('authorization');
        $searchUser = User::where('bearerToken','=',$token)->get();
        if(count($searchUser) === 0){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }
        if($searchUser[0]->name !== 'admin'){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }

        $searchProduct = TableProduct::find($idItemTable);
        if($searchProduct === null){
            return response([
                'status' => false,
                'message' => 'items not found'
            ], 404)
                ->setStatusCode(404, 'item not found');
        }

        $articleProduct = $request->articleProduct;
        $sizeProduct = $request->sizeProduct;
        $colorProduct = $request->colorProduct;
        $existsProduct = $request->existsProduct;

        $errors = [];

        if(empty($articleProduct)){
            $errors[] = array('article_required' => 'Article required');
        }
        if(empty($sizeProduct)){
            $errors[] = array('size_required' => 'Size required');
        }
        if(empty($colorProduct)){
            $errors[] = array('color_required' => 'Color required');
        }
        if(empty($existsProduct)){
            $errors[] = array('exists_required' => 'Exists required');
        }

        if(count($errors) !== 0){
            return response([
                'status' => false,
                'errors' => $errors
            ], 400)
                ->setStatusCode(400, 'errors validation');
        }

        $searchProduct->update([
            'article_product' => $articleProduct,
            'size_product' => $sizeProduct,
            'color_product' => $colorProduct,
            'exists_product' => $existsProduct,
        ]);

        return response([
            'status' => true,
            'product_id' => $idProduct,
            'item' => $searchProduct,
        ], 200)
            ->setStatusCode(200, 'updated successful')
            ->header('authorization', $token);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TableProduct  $tableProduct
     * @return \Illuminate\Http\Response
     */
    public function destroy(TableProduct $tableProduct, $idProduct, $idItemTable, Request $request)
    {
        $token = $request->header('authorization');
        $searchUser = User::where('bearerToken','=',$token)->get();
        if(count($searchUser) === 0){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }
        if($searchUser[0]->name !== 'admin'){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }

        $searchItemTable = TableProduct::find($idItemTable);

        if($searchItemTable === null){
            return response([
                'status' => false,
                'message' => 'item table not found'
            ], 404)
                ->setStatusCode(404, 'item not found');
        }

        $searchItemTable->delete();

        return response([
            'status' => false,
            'message' => 'deleted successful',
            'product_id' => $idProduct
        ], 200)
            ->setStatusCode(200, 'deleted successful');
    }
}

