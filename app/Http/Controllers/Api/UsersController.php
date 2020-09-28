<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsersResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Http\Requests\Api\UserRequest;

class UsersController extends Controller
{
    public function store(UserRequest $request)
    {
        $code_key = $request->input('verification_key');
        $verifyData = Cache::get($code_key);
        if (!$verifyData) {
            abort(Response::HTTP_FORBIDDEN, '验证码已失效');
        }
        $code = $request->input('verification_code');
        if (!hash_equals($verifyData['code'], $code)) {
            throw new AuthorizationException('验证码错误');
        }
        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => $request->password,
        ]);
        Cache::forget($code_key);

        return new UsersResource($user);
    }
}
