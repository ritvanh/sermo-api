<?php

namespace App\Http\Controllers;

use App\Exceptions\GenericJsonException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use App\Services\UserService;
use Laravel\Socialite\Facades\Socialite;
use Google_Client;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(){
        return $this->userService->getAll();
    }

    public function login(Request $request) {

        $plainTextToken = $this->userService->login($request->email,$request->password);
        return response($plainTextToken, 200);
    }
    public function logout(){
        \auth()->guard('web')->logout();
    }

    public function store(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'name' => 'nullable|string',
        ]);

        $this->userService->register($request);
        return response(['message'=> 'registration successful'],201);
    }
    public function updateProfilePic(Request $request){
        $file = $request->file('profilePhoto');
        if(!$file){
            throw new GenericJsonException('File was not found',400);
        }
        return $this->userService->updateProfilePic($file,auth()->id());
    }
    public function updateBio(Request $request){
        return $this->userService->updateBio($request->bio,auth()->id());
    }
    public function updateName(Request $request){
        return $this->userService->updateName($request->name,auth()->id());
    }

    public function confirmAccount(Request $request){
        $myToken = $request->query('token');
        $this->userService->confirmAccount($myToken);
    }

    public function me(Request $request) {
        return auth()->user();
    }

    public function forget(Request $request){
        $this->userService->forgetPassword($request->email);
        return true;
    }

    public function resetPassword(Request $request){
        $msg = $this->userService->resetPassword($request->token,$request->newPassword,$request->confirmPassword);
        return response(['message'=>$msg],200);
    }

    public function googleAuthRedirect(){
        return response()->json([
            'url' => Socialite::driver('google')
                ->stateless()
                ->redirect()
                ->getTargetUrl(),
        ]);
    }
    public function googleAuthCallback(Request $request){
        $token = $request->query('token');
        if(!$token){
            throw new GenericJsonException("Token is missing",400);
        }
        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($token);
        if ($payload) {
            $token = $this->userService->loginUsingGooleUser($payload);
            return response($token, 200);
        } else {
            throw new GenericJsonException("Invalid token",401);
        }
    }


}
