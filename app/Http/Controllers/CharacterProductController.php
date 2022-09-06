<?php

namespace App\Http\Controllers;

use App\Models\CharacterProduct;
use App\Models\User;
use Illuminate\Http\Request;

class CharacterProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $token = $request->header('authorization');
        $allCharacter = CharacterProduct::all();
        if(count($allCharacter) === 0){
            return response([
                'status' => false,
                'message' => 'characters not found'
            ], 404)
                ->setStatusCode(404, 'characters not found');
        }

        return response([
            'status' => true,
            'message' => $allCharacter
        ], 200)
            ->setStatusCode(200, 'characters found')
            ->header('authorization', $token);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$idProduct)
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

        $searchCharacter = CharacterProduct::where('product_id','=',$idProduct)->get();
        if(count($searchCharacter) !== 0){
            return response([
                'status' => false,
                'message' => 'character unique'
            ], 400)
                ->setStatusCode(400, 'character unique');
        }

        $descriptionProduct = $request->descriptionProduct;
        $instructionProduct = $request->instructionProduct;
        $technologyProduct = $request->technologyProduct;
        $brandInfoProduct = $request->brandInfoProduct;

        $errors = [];

        if(empty($descriptionProduct)) {
            $errors[] = array('description_required' => 'Description required');
        }
        if(empty($instructionProduct)) {
            $errors[] = array('instruction_required' => 'Instruction required');
        }
        if(empty($technologyProduct)) {
            $errors[] = array('technology_required' => 'Technology required');
        }
        if(empty($brandInfoProduct)) {
            $errors[] = array('brand_info_required' => 'Brand info required');
        }

        if(count($errors) !== 0){
            return response([
                'status' => false,
                'errors' => $errors
            ], 400)
                ->setStatusCode(400, 'errors validation');
        }

        $createCharacter = CharacterProduct::create([
            'product_id' => $idProduct,
            'description_product' => $descriptionProduct,
            'instruction_product' => $instructionProduct,
            'technology_product' => $technologyProduct,
            'brand_info_product' => $brandInfoProduct,
        ]);

        return response([
            'status' => true,
            'message' => $createCharacter->id
        ], 200)
            ->setStatusCode(200, 'created succesfful')
            ->header('authorization', $token);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CharacterProduct  $characterProduct
     * @return \Illuminate\Http\Response
     */
    public function show(CharacterProduct $characterProduct, $idProduct, Request $request)
    {
        $token = $request->header('authorization');
        $searchCharacter = CharacterProduct::where('product_id','=',$idProduct)->get();

        if(count($searchCharacter) === 0){
            return response([
                'status' => false,
                'message' => 'character not found'
            ], 404)
                ->setStatusCode(404, 'character not found');
        }

        return response([
            'status' => false,
            'characteristic' => $searchCharacter
        ], 200)
            ->setStatusCode(200, 'characte found')
            ->header('authorization', $token);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CharacterProduct  $characterProduct
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CharacterProduct $characterProduct, $idProduct, $idCharacter)
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

        $searchCharacter = CharacterProduct::find($idCharacter);

        if($searchCharacter === null){
            return response([
                'status' => false,
                'message' => 'character not found'
            ], 404)
                ->setStatusCode(404, 'character not found');
        }

        $descriptionProduct = $request->descriptionProduct;
        $instructionProduct = $request->instructionProduct;
        $technologyProduct = $request->technologyProduct;
        $brandInfoProduct = $request->brandInfoProduct;

        $errors = [];

        if(empty($descriptionProduct)) {
            $errors[] = array('description_required' => 'Description required');
        }
        if(empty($instructionProduct)) {
            $errors[] = array('instruction_required' => 'Instruction required');
        }
        if(empty($technologyProduct)) {
            $errors[] = array('technology_required' => 'Technology required');
        }
        if(empty($brandInfoProduct)) {
            $errors[] = array('brand_info_required' => 'Brand info required');
        }

        if(count($errors) !== 0){
            return response([
                'status' => false,
                'errors' => $errors
            ], 400)
                ->setStatusCode(400, 'errors validation');
        }

        $searchCharacter->update([
            'product_id' => $idProduct,
            'description_product' => $descriptionProduct,
            'instruction_product' => $instructionProduct,
            'technology_product' => $technologyProduct,
            'brand_info_product' => $brandInfoProduct,
        ]);

        return response([
            'status' => true,
            'message' => $searchCharacter
        ], 200)
            ->setStatusCode(200, 'updated succesfful')
            ->header('authorization', $token);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CharacterProduct  $characterProduct
     * @return \Illuminate\Http\Response
     */
    public function destroy(CharacterProduct $characterProduct, $idProduct, $idCharacter, Request $request)
    {
        $token = $request->header('authorization');
        $searchUser = User::where('bearerToken', '=', $token)->get();
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

        $searchCharacter = CharacterProduct::find($idCharacter);

        if($searchCharacter === null){
            return response([
                'status' => false,
                'message' => 'characteristic not found'
            ], 404)
                ->setStatusCode(404, 'characteristic not found');
        }

        $searchCharacter->delete();

        return response([
            'status' => true,
            'message' => 'deleted successful'
        ], 200)
            ->setStatusCode(200, 'deleted successful')
            ->header("authorization", $token);
    }
}
