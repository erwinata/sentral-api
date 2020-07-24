<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Hash;
use Socialite;
use Str;
use Illuminate\Support\Facades\Http;

use Google_Client;
use JWTAuth;

use App\User;
use App\UserData;

class AuthController extends Controller
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

		// public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['login', 'logout']]);
    // }


		public function logout(){

			if (JWTAuth::getToken()){
				JWTAuth::invalidate(JWTAuth::getToken());
			}

			return response()->json(['msg' => "Successfully logged out"], 200);

		}

		public function loginxxx(Request $request){

			// $request->validate( [
			// 	'code' => 'required',
			// ]);

			// $code = $request->input('code');

			$clientID = env('GOOGLE_CLIENT_ID');
			$client = new Google_Client();  // Specify the CLIENT_ID of the app that accesses the backend
			$client->setAuthConfigFile('storage/client_secrets.json');

			$client->addScope("https://www.googleapis.com/auth/youtube.readonly");
			$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php');
			// offline access will give you both an access and refresh token so that
			// your app can refresh the access token without user interaction.
			// $client->setAccessType('offline');
			// Using "consent" ensures that your application always receives a refresh token.
			// If you are not using offline access, you can omit this.
			$client->setApprovalPrompt("consent");

			// $client->setAccessType('offline');
			// $client->setApprovalPrompt('force');
			// $au = $client->authenticate($code);

			// $access_token = $client->getAccessToken();

			// $payload = $client->verifyIdToken($access_token['id_token']);

			// if ($payload) {

			// 	// $response = [
			// 	// 	'msg' => 'Login successful',
			// 	// 	'payload' => $payload,
			// 	// ];

			// 	// return response()->json($response, 200);

			// 	$userid = $payload['sub'];
			// 	$email = $payload['email'];
			// 	$name = $payload['name'];
				
			// 	$user = User::firstOrCreate([
			// 		'email' => $email
			// 	], [
			// 		'name' => $name,
			// 		'password' => Hash::make($userid)
			// 	]);

			// 	$collection = Collection::firstOrCreate([
			// 		'user' => $user->id
			// 	], [
			// 		'song' => "",
			// 	]);
	

			// 	// $creds = $request->only(['email', 'password']);
			// 	// $creds = $request->only('email', 'password');
			// 	$creds = [
			// 		'email' => $email,
			// 		'password' => $userid
			// 	];

			// 	if (! $token = auth('api')->attempt($creds)) {
			// 		return response()->json(['error' => $creds['email'], 'error22' => $creds['password'], 'token' => $token], 401);
			// 	}

			// 	$response = [
			// 		'msg' => 'Login successful',
			// 		'payload' => $payload,
			// 		'userid' => $userid,
			// 		'user' => $user,
			// 		'token' => $token,
			// 		'access_token' => $access_token,
			// 	];

			// 	return response()->json($response, 200);
			// } else {
			// 	// Invalid ID token
			// 	$response = [
			// 		'msg' => 'Invalid Google Token',
			// 	];
			// 	return response()->json($response, 404);
			// }
		}

		public function loginwithtoken(Request $request){

			$request->validate( [
				'code' => 'required',
			]);

			$code = $request->input('code');

			$clientID = env('GOOGLE_CLIENT_ID');
			$client = new Google_Client();  // Specify the CLIENT_ID of the app that accesses the backend
			$client->setAuthConfigFile('storage/client_secrets.json');
			$client->setAccessType('offline');
			$client->setApprovalPrompt('force');
			$client->setRedirectUri('postmessage');
			$au = $client->authenticate($code);

			$access_token = $client->getAccessToken();

			$payload = $client->verifyIdToken($access_token['id_token']);

			if ($payload) {

				// $response = [
				// 	'msg' => 'Login successful',
				// 	'payload' => $payload,
				// ];

				// return response()->json($response, 200);

				$userid = $payload['sub'];
				$email = $payload['email'];
				$name = $payload['name'];
				$refresh = "";
					if (isset($access_token['refresh_token'])){
					$refresh = $access_token['refresh_token'];
				}
				
				$user = User::firstOrCreate([
					'email' => $email
				], [
					'name' => $name,
					'password' => Hash::make($userid),
					'refresh_token' => $refresh
				]);

				$collection = Collection::firstOrCreate([
					'user' => $user->id
				], [
					'song' => "",
				]);
	

				// $creds = $request->only(['email', 'password']);
				// $creds = $request->only('email', 'password');
				$creds = [
					'email' => $email,
					'password' => $userid
				];

				if (! $token = auth('api')->attempt($creds)) {
					return response()->json(['error' => $creds['email'], 'error22' => $creds['password'], 'token' => $token], 401);
				}

				$response = [
					'msg' => 'Login successful',
					'payload' => $payload,
					'userid' => $userid,
					'user' => $user,
					'token' => $token,
					'access_token' => $access_token,
				];

				return response()->json($response, 200);
			} else {
				// Invalid ID token
				$response = [
					'msg' => 'Invalid Google Token',
				];
				return response()->json($response, 404);
			}
		}

		public function login(Request $request){

			$request->validate( [
				'id_token' => 'required',
			]);

			$id_token = $request->input('id_token');

			$clientID = env('GOOGLE_CLIENT_ID');

			$client = new Google_Client(['client_id' => $clientID ]);  // Specify the CLIENT_ID of the app that accesses the backend
			$payload = $client->verifyIdToken($id_token);
			if ($payload) {
				
				$userid = $payload['sub'];
				$email = $payload['email'];
				$name = $payload['name'];
				
				$user = User::firstOrCreate([
					'email' => $email
				], [
					'name' => $name,
					'password' => Hash::make($userid),
				]);

				$userData = UserData::firstOrCreate([
					'user' => $user->id
				], [
					'collection' => "",
					'song_played' => "",
				]);

				$creds = [
					'email' => $email,
					'password' => $userid
				];

				if (! $token = auth('api')->attempt($creds)) {
					$response = [
						'msg' => 'Failed to create token',
					];
					return response()->json($response, 500);
				}

				$response = [
					'msg' => 'Login successful',
					'payload' => $payload,
					'userid' => $userid,
					'user' => $user,
					'token' => $token
				];

				return response()->json($response, 200);
			} else {
				// Invalid ID token
				$response = [
					'msg' => 'Invalid Google Token',
				];
				return response()->json($response, 404);
			}
		}

		public function checkToken(Request $request){

			$userModel = $this->getUserModel($request);

			if ($user = User::where('id', $userModel->id)->first()){
				
				$response = [
					'msg' => 'Token Accepted',
				];
					
				return response()->json($response, 200);
			}
			else{
				return response()->json(['msg' => "Invalid Login"], 401);
			}
		}

		public function refreshGoogleToken(Request $request){

			$userModel = $this->getUserModel($request);

			if ($user = User::where('id', $userModel->id)->first()){
				
				$client = new Google_Client();  // Specify the CLIENT_ID of the app that accesses the backend
				$client->setAuthConfigFile('storage/client_secrets.json');
				$client->setAccessType('offline');
				$client->setApprovalPrompt('force');
				$client->refreshToken($user->refresh_token);
				$access_token = $client->getAccessToken();
				
				$response = [
					'msg' => 'Refresh Token Successful',
					'access_token' => $access_token['access_token'],
				];
					
				return response()->json($response, 200);
			}
			else{
				return response()->json(['msg' => "Invalid User"], 400);
			}
		}

}
