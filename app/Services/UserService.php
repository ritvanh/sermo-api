<?php

namespace App\Services;

use App\Exceptions\GenericJsonException;
use App\Models\Role;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Mail\RegistrationGreetingMail;
use App\Mail\PasswordReset;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Spatie\Ignition\Tests\TestClasses\Models\Car;

class UserService {
    public function getUser($id) {
        // code to get user by id
    }

    public function login($email,$password){
        $user = User::where('email', $email)->first();
        if (! $user || ! Hash::check($password, $user->password)) {
            throw new GenericJsonException("Wrong credentials",401);
        }
        $user->tokens()->delete();

        $roles = $user->roles->pluck('name')->toArray();

        return $user->createToken('token',$roles)->plainTextToken;
    }

    public function  loginOAuth($email){
        $user = User::where('email', $email)->first();
        $user->tokens()->delete();
        $roles = $user->roles->pluck('name')->toArray();

        return $user->createToken('token',$roles)->plainTextToken;
    }

    public function getAll(){
        return User::all();
    }

    public function confirmAccount($token){
        $myUser = User::where('email_verification_token','=',$token)->first();
        if(!$myUser){
            throw new GenericJsonException("User not found!",404);
        }
        if($myUser->email_verified_at){
            throw new GenericJsonException("User already verified!",400);
        }
        $myUser->email_verified_at = Carbon::now();
        $myUser->save();
        return true;
    }

    public function register(Request $dto){
        $user = User::where('email', $dto->email)->first();
        if ($user) {
            throw new GenericJsonException("A user with this email is already registered",400);
        }

        DB::transaction(function () use ($dto,$user) {
            $user = User::create([
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'name' => $dto->name,
            'profilePhotoPath' => "",
            'email_verification_token' => Str::random(20),
        ]);

        $user->roles()->attach(Role::where('name', 'user')->first());

        Mail::to($user->email)->send(new RegistrationGreetingMail($user->name,$user->email_verification_token));

        return $user;
        });

    }

    public function forgetPassword($email){

        $existingUser = User::where('email','=',$email)->first();
        if(!$existingUser){
            throw new GenericJsonException("A user with this email could not be found",404);
        }
        DB::transaction(function() use($email,$existingUser) {
        $exisitingForgetPasswordToken = PasswordResetToken::where('email','=',$email)->first();
        if($exisitingForgetPasswordToken){
            $exisitingForgetPasswordToken->token = Str::random(20);
            $exisitingForgetPasswordToken->updated_at = Carbon::now();
            $exisitingForgetPasswordToken->save();
        }else{
            $exisitingForgetPasswordToken = PasswordResetToken::create([
                'email'=>$email,
                'token'=>Str::random(20),
                'created_at'=>Carbon::now(),
                'updated_at'=>null
            ]);
        }
        Mail::to($existingUser->email)->send(new PasswordReset($existingUser->name,$exisitingForgetPasswordToken->token));
        });

    }

    public function resetPassword($token,$newPassword,$confirmPassword){
        if($newPassword != $confirmPassword){
            throw new GenericJsonException("New password and confirm password dont match",400);
        }

        $tokenFromDb = PasswordResetToken::where('token',$token)->first();
        if($tokenFromDb == null){
            throw new GenericJsonException("Token was not found",404);
        }
        $creationDate;
        if($tokenFromDb->updated_at != null){
            $creationDate = $tokenFromDb->updated_at;
        }else{
            $creationDate = $tokenFromDb->created_at;
        }
        if($creationDate->addHours(24) < Carbon::now()){
            throw new GenericJsonException("Token expired",400);
        }

        $userFromDb = User::where('email',$tokenFromDb->email)->first();
        if(!$userFromDb){
            throw new GenericJsonException("User was not found",404);
        }

        if(Hash::check($newPassword, $userFromDb->password)){
            throw new GenericJsonException("You can't set a password that you have used before",400);
        }

        DB::transaction(function () use($userFromDb,$tokenFromDb,$newPassword) {
            $userFromDb->tokens()->delete();
            $userFromDb->password = Hash::make($newPassword);
            $userFromDb->save();
            $tokenFromDb->delete();
        });

        return "password reset succesfully";
    }

    public function loginUsingGooleUser($googleUser){
        $email = $googleUser->getEmail();
        $profilePic = $googleUser->getAvatar();
        $name = $googleUser->getName();
        $user = User::with('roles')->where('email',$email)->first();
        if(!$user){
            DB::transaction(function () use ($name,$profilePic,$email) {
                $user = User::create([
                    'name' => $name,
                    'profilePhotoPath' => $profilePic,
                    'email' => $email,
                    'password' => '',
                    'email_verification_token' => ''
                ]);

                $user->email_verified_at = Carbon::now();
                $user->save();
                $user->roles()->attach(Role::where('name', 'user')->first());
            });

            $token = $this->loginOAuth($email);
            return $token;
        }

    }
}
