<?php

namespace App\Http\Controllers;

use Auth;

//New added
use JWTAuth;
use validator;
use App\Models\User;
use Illuminate\Support\Str;
// use App\Http\Resources\DataResource;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
//For password


use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\Http\Resources\DataResource;
use App\Http\Requests\ResetPasswordRequest;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Validator as IlluminateValidator;

use App\Http\Resources\PostResource;

use App\Jobs\SendPasswordResetEmail;


class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::all();
            return new DataResource($users, 'User list retrieved successfully', 200);
        } catch (\Exception $e) {
            return new DataResource(null, 'An error occurred', 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return new DataResource($user, 'User found successfully', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new DataResource(null, 'User not found', 404);
        } catch (\Exception $e) {
            return new DataResource(null, 'An error occurred', 500);
        }
    }

    public function store(UserRequest $request)
    {
        try {
            $user = new User();
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));
            $user->save();

            return new DataResource($user, 'User created successfully', 201);
        } catch (\Exception $e) {
            return new DataResource(null, 'Email already exists', 400);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            if ($request->has('password')) {
                $user->password = Hash::make($request->input('password'));
            }
            $user->save();

            return new DataResource($user, 'User updated successfully', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new DataResource(null, 'User not found', 404);
        } catch (\Exception $e) {
            return new DataResource(null, 'An error occurred', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return new DataResource(null, 'User deleted successfully', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new DataResource(null, 'User not found', 404);
        } catch (\Exception $e) {
            return new DataResource(null, 'An error occurred', 500);
        }
    }


    public function login(Request $request)
    {
        $validator = IlluminateValidator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return new DataResource($validator->errors(), 'Validation Error', 400);
        }

        try {
            if (!$token = auth()->attempt($validator->validated())) {
                // Check if the user exists with the provided email
                $user = User::where('email', $request->email)->first();
                if (!$user) {
                    return new DataResource(null, 'Email not registered', 401);
                } else {
                    return new DataResource(null, 'Wrong password', 401);
                }
            }
            return $this->respondWithToken($token);

        } catch (\Exception $e) {
            return new DataResource($e->getMessage(), 'Internal Server Error', 500);
        }
    }




        // 1. auth() refers to Laravel's authentication system.
        // 2. The attempt() method is used to attempt the authentication using the provided credentials (email and password).
        // 3. $validator->validated() retrieves the validated data from the validator, which should contain the input email and password if validation passes.If the provided credentials are valid and match a user in the database, the attempt() method will generate a token for the user. If the credentials are invalid, the condition will evaluate to false.

        //By separating the code that creates the token response into its own function, it becomes easier to manage and reuse. The main login function is responsible for checking the credentials and handling authentication, while the respondWithToken function takes care of how the token information is structured in the response.


    protected function respondWithToken($token)
    {
        $response = [
            'message' => 'User Login Sucessful',
            'status' => 200,
            'access_token' => $token,
            'token_type' => 'bearer',
            // 'expires_in' => auth()->factory()->getTTL() * 60
        ];
        return response()->json(['response' => $response]);
    }


    public function profile()
    {
        if (auth()->check()) {//check that the bearer token you have passed has some values of user or not because it will have when user is login. whne you logged off i
            return new DataResource(auth()->user(), 'User Profile', 200);
        } else {
            return new DataResource(null, 'Invalid Token !!', 401);
        }
    }



    public function logout()
    {
        try {
            auth()->logout();
            return new DataResource(null, 'User Logout', 200);
        } catch (\Exception $e) {
            return new DataResource(null, 'Invalid Token', 401);
        }
    }


    // public function forgotPassword(Request $request)
    // {
    //     $request->validate(['email' => 'required|email']);

    //     $user = User::where('email', $request->email)->first();
    //     if (!$user) {
    //         return new DataResource(null, 'User not found', 404);
    //     }

    //     // Generate a random token for password reset
    //     $token = Str::random(60);

    //     // Save the token in the user's remember_token field
    //     $user->forceFill([
    //         'remember_token' => $token,
    //     ])->save();

    //     // Notify user with the reset password link and token
    //     $user->notify(new ResetPasswordNotification($token,$request->email));

    //     return new DataResource(['token' => $token], 'Password reset link sent to your email', 200);
    // }

    //     public function forgotPassword(Request $request)
    //     {
    //         $request->validate(['email' => 'required|email']);

    //         $user = User::where('email', $request->email)->first();
    //         if (!$user) {
    //             return new DataResource(null, 'User not found', 404);
    //         }

    //         // Generate a random token for password reset
    //         $token = Str::random(60);

    //         // Save the token in the user's remember_token field
    //         $user->forceFill([
    //             'remember_token' => $token,
    //         ])->save();

    //         // Dispatch the job to send the email
    //         SendPasswordResetEmail::dispatch($user, $token);

    //         return new DataResource(['token' => $token], 'Password reset link sent to your email', 200);
    //     }

    //     public function checkTokenValidation(Request $request)
    //     {
    //         $user = User::where('email', $request->email)->first();

    //         if (!$user) {
    //             return new DataResource(null, 'Email not found', 404);
    //         }

    //         // Check if the provided token matches the user's remember_token
    //         if ($user->remember_token !== $request->token) {
    //             return new DataResource(null, 'Invalid token', 401);
    //         }

    //         //return new DataResource(['email' => $user->email], 'Token is valid', 200);
    //         // return ['redirect' => route('password.reset')];


    //     }



    //     public function resetPassword(Request $request)
    //     {

    //         $user = User::where('email', $request->email)->first();

    //         if (!$user) {
    //             return new DataResource(null, 'Email not found', 404);
    //         }

    //         // Check if the provided token matches the user's remember_token
    //         if ($user->remember_token !== $request->token) {
    //             return new DataResource(null, 'Invalid token', 401);
    //         }

    //         $request->validate([
    //             'email' => 'required|email',
    //             'password' => 'required|confirmed|min:1',
    //         ]);

    //         try {
    //             $user = User::where('email', $request->email)->first();

    //             if (!$user) {
    //                 return new DataResource(null, 'Email not found', 404);
    //             }

    //             // Reset the user's password and clear the remember_token
    //             $user->forceFill([
    //                 'password' => Hash::make($request->password),
    //                 'remember_token' => null,
    //             ])->save();

    //             return new DataResource(null, 'Password reset successful', 200);
    //         } catch (\Exception $e) {
    //             return new DataResource($e->getMessage(), 'An error occurred while resetting the password', 500);
    //         }
    //     }
    // }


    // class UserController extends Controller
    // {
    //     // All are new methods which are here

    //     public function register(UserRequest $request)
    //     {

    //         try {
    //             $user = User::create([
    //                 'name' => $request->name,
    //                 'email' => $request->email,
    //                 'password' => Hash::make($request->password),
    //                 'phone' => $request->phone,
    //                 'status' => 1,
    //             ]);
    //             return new DataResource($user, 'User registered successfully', 201);

    //         } catch (\Exception $e) {
    //             return new DataResource($e->getMessage(), 'Registration Error', 500);
    //         }
    //     }


    //     public function login(Request $request)
    //     {
    //         $validator = IlluminateValidator::make($request->all(), [
    //             'email' => 'required|string|email',
    //             'password' => 'required|string|min:1',
    //         ]);

    //         if ($validator->fails()) {
    //             return new DataResource($validator->errors(), 'Validation Error', 400);
    //         }

    //         try {
    //             if (!$token = auth()->attempt($validator->validated())) {
    //                 // Check if the user exists with the provided email
    //                 $user = User::where('email', $request->email)->first();
    //                 if (!$user) {
    //                     return new DataResource(null, 'Email not registered', 401);
    //                 } else {
    //                     return new DataResource(null, 'Wrong password', 401);
    //                 }
    //             }
    //             return $this->respondWithToken($token);

    //         } catch (\Exception $e) {
    //             return new DataResource($e->getMessage(), 'Internal Server Error', 500);
    //         }
    //     }




    //         // 1. auth() refers to Laravel's authentication system.
    //         // 2. The attempt() method is used to attempt the authentication using the provided credentials (email and password).
    //         // 3. $validator->validated() retrieves the validated data from the validator, which should contain the input email and password if validation passes.If the provided credentials are valid and match a user in the database, the attempt() method will generate a token for the user. If the credentials are invalid, the condition will evaluate to false.

    //         //By separating the code that creates the token response into its own function, it becomes easier to manage and reuse. The main login function is responsible for checking the credentials and handling authentication, while the respondWithToken function takes care of how the token information is structured in the response.


    //     protected function respondWithToken($token)
    //     {
    //         $response = [
    //             'message' => 'User Login Sucessful',
    //             'status' => 200,
    //             'access_token' => $token,
    //             'token_type' => 'bearer',
    //             'expires_in' => auth()->factory()->getTTL() * 60
    //         ];
    //         return response()->json(['response' => $response]);
    //     }


    //     public function profile()
    //     {
    //         if (auth()->check()) {//check that the bearer token you have passed has some values of user or not because it will have when user is login. whne you logged off i
    //             return new DataResource(auth()->user(), 'User Profile', 200);
    //         } else {
    //             return new DataResource(null, 'Invalid Token !!', 401);
    //         }
    //     }



    //     public function logout()
    //     {
    //         try {
    //             auth()->logout();
    //             return new DataResource(null, 'User Logout', 200);
    //         } catch (\Exception $e) {
    //             return new DataResource(null, 'Invalid Token', 401);
    //         }
    //     }


    //     // public function forgotPassword(Request $request)
    //     // {
    //     //     $request->validate(['email' => 'required|email']);

    //     //     $user = User::where('email', $request->email)->first();
    //     //     if (!$user) {
    //     //         return new DataResource(null, 'User not found', 404);
    //     //     }

    //     //     // Generate a random token for password reset
    //     //     $token = Str::random(60);

    //     //     // Save the token in the user's remember_token field
    //     //     $user->forceFill([
    //     //         'remember_token' => $token,
    //     //     ])->save();

    //     //     // Notify user with the reset password link and token
    //     //     $user->notify(new ResetPasswordNotification($token,$request->email));

    //     //     return new DataResource(['token' => $token], 'Password reset link sent to your email', 200);
    //     // }

    //     public function forgotPassword(Request $request)
    //     {
    //         $request->validate(['email' => 'required|email']);

    //         $user = User::where('email', $request->email)->first();
    //         if (!$user) {
    //             return new DataResource(null, 'User not found', 404);
    //         }

    //         // Generate a random token for password reset
    //         $token = Str::random(60);

    //         // Save the token in the user's remember_token field
    //         $user->forceFill([
    //             'remember_token' => $token,
    //         ])->save();

    //         // Dispatch the job to send the email
    //         SendPasswordResetEmail::dispatch($user, $token);

    //         return new DataResource(['token' => $token], 'Password reset link sent to your email', 200);
    //     }

    //     public function checkTokenValidation(Request $request)
    //     {
    //         $user = User::where('email', $request->email)->first();

    //         if (!$user) {
    //             return new DataResource(null, 'Email not found', 404);
    //         }

    //         // Check if the provided token matches the user's remember_token
    //         if ($user->remember_token !== $request->token) {
    //             return new DataResource(null, 'Invalid token', 401);
    //         }

    //         //return new DataResource(['email' => $user->email], 'Token is valid', 200);
    //         // return ['redirect' => route('password.reset')];


    //     }



    //     public function resetPassword(Request $request)
    //     {

    //         $user = User::where('email', $request->email)->first();

    //         if (!$user) {
    //             return new DataResource(null, 'Email not found', 404);
    //         }

    //         // Check if the provided token matches the user's remember_token
    //         if ($user->remember_token !== $request->token) {
    //             return new DataResource(null, 'Invalid token', 401);
    //         }

    //         $request->validate([
    //             'email' => 'required|email',
    //             'password' => 'required|confirmed|min:1',
    //         ]);

    //         try {
    //             $user = User::where('email', $request->email)->first();

    //             if (!$user) {
    //                 return new DataResource(null, 'Email not found', 404);
    //             }

    //             // Reset the user's password and clear the remember_token
    //             $user->forceFill([
    //                 'password' => Hash::make($request->password),
    //                 'remember_token' => null,
    //             ])->save();

    //             return new DataResource(null, 'Password reset successful', 200);
    //         } catch (\Exception $e) {
    //             return new DataResource($e->getMessage(), 'An error occurred while resetting the password', 500);
    //         }
    //     }

    // ///th

    // }

}
