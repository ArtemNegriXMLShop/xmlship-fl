<?php

namespace App\EntryPoints\Http\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Foundation\Laravel\AppController;
use App\EntryPoints\Http\Auth\ActionsRequests\{
    AuthLoginRequest,
};
use App\Foundation\Laravel\Responses\{
    SuccessResponse,
    ErrorResponse
};
use Throwable;

class AuthController extends AppController {
    const SECONDS_IN_MINUTE = 60;

    public function login(
        AuthLoginRequest $request): ErrorResponse|SuccessResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            if (!$token = auth()->attempt($credentials)) {
                return new ErrorResponse('Unauthorized', null, 401);
            }

            $this->setToken(
                auth()->user()->id,
                $token
            );

            return new SuccessResponse([
                'token' => $token,
                'type' => 'bearer'
            ]);
        } catch (Throwable $exception) {
            return new ErrorResponse(
                'An error occurred while login attempt',
                $exception
            );
        }
    }

    public function logout(): ErrorResponse|SuccessResponse
    {
        try {
            $userId = auth()->user()->id;
            auth()->logout();
            Redis::del("user:{$userId}:jwt_token");

            return new SuccessResponse([
                'message' => 'Successfully logged out',
            ]);
        } catch (Throwable $exception) {
            return new ErrorResponse(
                'An error occurred while logout process',
                $exception
            );
        }
    }

    public function refresh(): ErrorResponse|SuccessResponse
    {
        try {
            $newToken = auth()->refresh();
            $this->setToken(
                auth()->user()->id,
                $newToken
            );

            return new SuccessResponse([
                'token' => $newToken,
                'type' => 'bearer'
            ]);
        } catch (Throwable $exception) {
            return new ErrorResponse(
                'An error occurred while token refresh',
                $exception
            );
        }
    }

    private function setToken(int $id, string $token): void
    {
        Redis::setex(
            "user:{$id}:jwt_token",
            env('JWT_TTL') * self::SECONDS_IN_MINUTE,
            $token
        );
    }
}
