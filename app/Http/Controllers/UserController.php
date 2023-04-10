<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Mail\RegistrationGreetingMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(){
        return $this->userService->getAll();
    }

    public function login(Request $request) {

        $plainTextToken = $this->userService->login($request->email,$request->password);
        return response(['token' => $plainTextToken], 200);
    }

    public function store(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'name' => 'nullable|string',
        ]);

        return $this->userService->register($request);
    }

    public function confirmAccount(Request $request){
        $myToken = $request->query('token');
        $this->userService->confirmAccount($myToken);
    }

    public function me(Request $request) {
        return $request->user();
    }

    public function update(Request $request) {

        $loggedInUser = $request->user();
        if ($loggedInUser->id == $loggedInUser->id) {
            $loggedInUser->update();
        } elseif ($loggedInUser->tokenCan('admin')) {
            $loggedInUser->update();
        } else {
            throw new MissingAbilityException('Not Authorized');
        }

        return $loggedInUser;
    }

    public function forget(Request $request){
        $this->userService->forgetPassword($request->email);
        return true;
    }

    public function resetPassword(Request $request){
        $msg = $this->userService->resetPassword($request->token,$request->newPassword,$request->confirmPassword);
        return response(['message'=>$msg],200);
    }


}
