<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use App\Slide;
use Storage;

class SlideController extends Controller
{
		public function getUserModel($request = null){
			if ($request != null){
				if (isset($request->userid)){
					return User::where('id', $request->userid)->first();
				}
			}
			$token = request()->token;
			return JWTAuth::parseToken()->toUser();
		}

		public function responseInvalidLogin(){
			return response()->json(["msg" => "Invalid login"], 401);
		}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

			$slides = Slide::orderBy('order')->get();

			foreach($slides as $slide){
				// $slide->image = asset("public/slide/" . $slide->image);
				// $slide->image = 'https://localhost/sentral-api' . Storage::url('slide/' . $slide->image);
				$slide->image = asset('public/slide/' . $slide->image);
				// $slide->image = asset(Storage::url('1.png'));
			}

			$response = [
				'msg' => 'List of Slides',
				'slides' => $slides,
			];
				
			return response()->json($response, 200);

    }

    public function store(Request $request)
    {
			$request->validate( [
				'slideData' => 'required',
			]);

			$slideDataAll = json_decode($request->input('slideData'));

			$images = $request->file('images');
			$imageIndex = 0;

			foreach ($slideDataAll as $index => $slideData){
				// $slide = new Slide([
				// 	'id' => $slideData->id,
				// 	'name' => $slideData->name,
				// 	'price' => $slideData->price,
				// 	'order' => $slideData->order,
				// 	'visible' => $slideData->visible,
				// ]);

				$imageFileName = "";

				if(isset($slideData->uploadedImageFile)){
					$imageFileName = $slideData->id . "." . $images[$index]->getClientOriginalExtension();

					$images[$index]->move(public_path('/slide'), $imageFileName );
					// $imageIndex++;
				}

				$slide = Slide::firstOrNew(['id'=> $slideData->id]);

				$slide->name = $slideData->name;
				$slide->price = $slideData->price;
				if ($imageFileName != ""){
					$slide->image = $imageFileName;
				}
				$slide->order = $index;
				$slide->visible = $slideData->visible;

				$slide->save();

				// Slide::updateOrCreate(
				// 	['id' => $slideData->id],
				// 	[
				// 	'name' => $slideData->name,
				// 	'price' => $slideData->price,
				// 	'image' => $imageFileName != "" ? $imageFileName : ,
				// 	'order' => $index,
				// 	'visible' => $slideData->visible,
				// 	]
				// );
			}


			// dd($request->input('slideData'));

			// $title = $request->input('title');
			// $user = $userModel->id;
			// $song = $request->input('song');

			// $playlist = new Playlist([
			// 	'title' => $title,
			// 	'user' => $user,
			// 	'song' => $song,
			// ]);

			// if ($playlist->save()) {
			// 	$playlist->view_playlist = [
			// 		'href' => 'api/v1/playlist/' . $playlist->id,
			// 		'method' => 'GET'
			// 	];
			// 	$response = [
			// 		'msg' => 'Playlist Created',
			// 		'playlist' => $playlist
			// 	];
			// 	return response()->json($response, 201);
			// }

			return response()->json(['msg' => 'An error occured while creating Playlist'], 200);

		}

    public function show(Request $request, $id)
    {

		}

    public function update(Request $request, $id)
    {
			$request->validate( [
				'title' => 'string',
				'song' => 'string',
				'item' => 'string'
			]);

			$userModel = $this->getUserModel($request);

			if (isset($userModel)){
			
				$title = $request->input('title');
				$user = $userModel->id;
				$song = $request->input('song');
				$item = $request->input('item');

				if (!$playlist = Playlist::where('id', $id)->where('user', $user)->first()){
					return response()->json(['msg' => 'Playlist does not exists / User invalid'], 500);
				};

				$playlist->title = $title != '' ? $title : $playlist->title;

				if ($item != ''){
					$beforeSong = $playlist->song;
					$offset = strpos($beforeSong, $item);
					if ($offset > -1){ //REMOVE SONG
						$resultSong = '';
						$resultSong .= substr($beforeSong, 0, $offset - 1);
						$resultSong .= substr($beforeSong, $offset + strlen($item), strlen($beforeSong) - $offset - strlen($item));
					}
					else{
						$resultSong = $beforeSong . "," . $item;
					}
					$playlist->song = $resultSong;
				}
				else if ($song != ''){
					$playlist->song = $song != '' ? $song : $playlist->song;
				}

				if (!$playlist->update()){
					return response()->json(['msg' => 'An error occured while updating Playlist'], 500);
				}

				$playlist->view_playlist = [
					'href' => 'api/v1/playlist/' . $playlist->id,
					'method' => 'GET'
				];

				$response = [
					'msg' => 'Playlist Updated',
					'playlist' => $playlist
				];

				return response()->json($response, 200);

			}

			return responseInvalidLogin();
		}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
			$userModel = $this->getUserModel($request);

			if (isset($userModel)){

				if (!$playlist = Playlist::where('id', $id)->where('user', $userModel->id)->first()){
					return response()->json(['msg' => 'Playlist does not exists / User invalid'], 500);
				}

				if (!$playlist->delete()){
					return response()->json(['msg' => 'An error occured while deleting Playlist'], 500);
				}

				$response = [
					'msg' => 'Playlist Deleted',
				];

				return response()->json($response, 200);

			}

			return responseInvalidLogin();

		}
}
