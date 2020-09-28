<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\VerificationCodeRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Overtrue\EasySms\EasySms;

class VerificationCodesController extends Controller
{
    public function store(VerificationCodeRequest $request, EasySms $easySms)
    {
        $captcha_key = $request->input('captcha_key');
        $captchaData = Cache::get($captcha_key);
        if(!$captchaData) {
            abort(Response::HTTP_FORBIDDEN, '图片验证码已失效');
        }
        $captcha_code = $request->input('captcha_code');
        if(!hash_equals($captchaData['code'], $captcha_code)) {
            Cache::forget($captcha_key);
            throw new AuthorizationException('验证码错误');
        }

        $phone = $captchaData['phone'];
        if (!app()->environment('production')) {
            $code = '1234';
        } else {
            $code = str_pad(random_int(1, 999999), 6, 0, STR_PAD_LEFT);
            try {
                $easySms->send($phone, [
                    'template' => config('easysms.gateways.aliyun.templates.register'),
                    'data' => [
                        'code' => $code
                    ],
                ]);
            } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
                $message = $exception->getException('aliyun')->getMessage();
                abort(Response::HTTP_INTERNAL_SERVER_ERROR, $message ?: '短信发送异常');
            }
        }

        $key = 'verificationCode_' . Str::random(15);
        $expiredAt = now()->addMinutes(5);
        // 缓存验证码 5 分钟过期。
        Cache::put($key, ['phone' => $phone, 'code' => $code], $expiredAt);
        Cache::forget($captcha_key);
        return response()->json([
            'key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
        ])->setStatusCode(Response::HTTP_CREATED);
    }
}
