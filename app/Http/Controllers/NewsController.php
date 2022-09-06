<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\User;
use Illuminate\Http\Request;
use function Ramsey\Uuid\v4;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $allPost = News::all();

        if(count($allPost) === 0){
            return response([
                'status' => false,
                'message' => 'new not found'
            ], 404)
                ->setStatusCode(404,'news not found');
        }

        return response([
            'status' => true,
            'news' => $allPost
        ], 200)
            ->setStatusCode(200, 'news found');
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
        $title = $request->title;
        $descr = $request->descr;
        $image = $request->hasFile('image');

        $errors = [];

        $author = User::find(intval(substr($token, 60)));

        if($author === null){
            $author = $request->author;

            if(empty($author)){
                $errors[] = array('author_required' => 'author required');
            }
        } else{
            $author = $author->name;
        }

        if($image){
            if($request->file('image')->getSize() > 2 * 1024 * 1024){
                $errors[] = array('images_size' => 'images size max 2 mb');
            }
            if(
                !strpos($request->file('image')->getClientOriginalName(), '.jpg') &&
                !strpos($request->file('image')->getClientOriginalName(), '.png')
            ){
                $errors[] = array('type_image' => 'you download not image');
            }
        } else {
            $errors[] = array('image_required' => 'images required');
        }

        $searchTitle = News::where('title','=', $title)->get();

        if(count($searchTitle) !== 0){
            $errors[] = array('title_unique' => 'title exists');
        }

        if(empty($title)){
            $errors[] = array('title_required' => 'title required');
        }
        if(empty($descr)){
            $errors[] = array('descr_required' => 'description required');
        }

        if(count($errors) !== 0){
            return response([
                'status' => false,
                'errors' => $errors
            ], 400)
                ->setStatusCode(400, 'errors validation');
        }

        $fileName = v4() . '.jpg';
        $request->file('image')->move(public_path('/images/news'), $fileName);

        $createNew = News::create([
            'title' => $title,
            'descr' => $descr,
            'image' => $fileName,
            'author' => $author
        ]);

        return response([
            'status' => false,
            'news_id' => $createNew->id
        ], 201)
            ->setStatusCode(201, 'news created')
            ->header('authorization', $token);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function show(News $news, $id, Request $request)
    {
        $oneNew = News::find($id);

        if($oneNew === null){
            return response([
                'status' => false,
                'message' => 'new not found'
            ], 404)
                ->setStatusCode(404, 'new not found');
        }

        return response([
            'status' => true,
            'new' => $oneNew
        ], 200)
            ->setStatusCode(200, 'new found')
            ->header('authorization', $request->header('authorization'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, News $news, $id)
    {
        $token = $request->header('authorization');
        $searchUser = User::where('bearerToken','=', $token)->get();
        if(count($searchUser) === 0){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }

        $searchAuthor = News::find($id);

        if($searchAuthor === null){
            return response([
                'status' => false,
                'message' => 'new not found'
            ], 404)
                ->setStatusCode(404, 'new not found');
        }

        if($searchAuthor->author !== $searchUser[0]->name && $searchUser[0]->name === 'admin'){
            return response([
                'status' => false,
                'message' => 'access denied'
            ], 403)
                ->setStatusCode(403, 'access denied');
        }

        $title = $request->title;
        $descr = $request->descr;
        $image = $request->hasFile('image');

        $errors = [];

        if(empty($title)){
            $errors[] = array('title_required' => 'Title required');
        }
        if(empty($descr)){
            $errors[] = array('descr_required' => 'Description required');
        }

        if($image){
            if($request->file('image')->getSize() > 2 * 1024 * 1024){
                $errors[] = array('image_size' => 'Image size not more 2 MB');
            }
            if(
                !strpos($request->file('image')->getClientOriginalName(), '.png') &&
                !strpos($request->file('image')->getClientOriginalName(), '.jpg')
            ){
                $errors[] = array('image_type' => 'Download not image');
            }
        } else{
            $errors[] = array('image_required' => 'Image required');
        }

        if(count($errors) !== 0){
            return response([
                'status' => false,
                'errors' => $errors
            ], 400)
                ->setStatusCode(400, 'errors validation');
        }

        $fileName = v4() . '.jpg';
        $request->file('image')->move(public_path('/images/news/'), $fileName);

        $searchAuthor->update([
            'title' => $title,
            'descr' => $descr,
            'image' => $fileName
        ]);

        return response([
            'status' => true,
            'new' => $searchAuthor
        ], 200)
            ->setStatusCode(200, 'updated successful')
            ->header('authorization', $token);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function destroy(News $news, $id, Request $request)
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

        $searchAuthor = News::find($id);

        if($searchAuthor === null){
            return response([
                'status' => false,
                'message' => 'new not found'
            ], 404)
                ->setStatusCode(404, 'new not found');
        }

        if($searchAuthor->author !== $searchUser[0]->name && $searchUser[0]->name === 'admin'){
            return response([
                'status' => false,
                'message' => 'access denied user'
            ], 403)
                ->setStatusCode(403, 'access denied user');
        }

        $searchAuthor->delete();

        return response([
            'status' => true,
            'message' => 'deleted successful'
        ], 200)
            ->setStatusCode(200, 'deleted successful')
            ->header('authorization', $token);
    }
}
