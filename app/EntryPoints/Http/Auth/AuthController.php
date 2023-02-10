<?php

namespace App\EntryPoints\Http\Auth;

use App\EntryPoints\Http\Auth\ActionsPresentations\AuthForgotPasswordPresentation;
use App\EntryPoints\Http\Auth\ActionsPresentations\AuthLoginPresentation;
use App\EntryPoints\Http\Auth\ActionsPresentations\AuthNewPasswordPresentation;
use App\EntryPoints\Http\Auth\ActionsProcessors\AuthForgotPasswordProcessor;
use App\EntryPoints\Http\Auth\ActionsProcessors\AuthLoginProcessor;
use App\EntryPoints\Http\Auth\ActionsProcessors\AuthLogoutProcessor;
use App\EntryPoints\Http\Auth\ActionsProcessors\AuthNewPasswordProcessor;
use App\EntryPoints\Http\Auth\ActionsRequests\AuthForgotPasswordRequest;
use App\EntryPoints\Http\Auth\ActionsRequests\AuthLoginRequest;
use App\EntryPoints\Http\Auth\ActionsRequests\AuthLogoutRequest;
use App\EntryPoints\Http\Auth\ActionsRequests\AuthNewPasswordRequest;
use App\Foundation\Laravel\AppController;
use App\Foundation\Laravel\Responses\{ErrorResponse, SuccessResponse};
use Throwable;

class AuthController extends AppController
{
    public function logout(AuthLogoutRequest $request, AuthLogoutProcessor $processor): ErrorResponse|SuccessResponse
    {
        try {
            return new SuccessResponse($processor->execute($request));
        } catch (Throwable $exception) {
            return new ErrorResponse('An error occurred while trying to logout the Users', $exception);
        }
    }

    public function login(
        AuthLoginRequest $request,
        AuthLoginProcessor $processor,
        AuthLoginPresentation $presentation,
    ): ErrorResponse|SuccessResponse {
        try {
            return new SuccessResponse($presentation->beautify($processor->execute($request)));
        } catch (Throwable $exception) {
            return new ErrorResponse('An error occurred while trying to login the Users', $exception);
        }
    }

    public function forgotPassword(
        AuthForgotPasswordRequest $request,
        AuthForgotPasswordProcessor $processor,
        AuthForgotPasswordPresentation $presentation,
    ): ErrorResponse|SuccessResponse {
        try {
            return new SuccessResponse($presentation->beautify($processor->execute($request)));
        } catch (Throwable $exception) {
            return new ErrorResponse('An error occurred while trying to reset user password', $exception);
        }
    }

    public function newPassword(
        AuthNewPasswordRequest $request,
        AuthNewPasswordProcessor $processor,
        AuthNewPasswordPresentation $presentation,
    ): ErrorResponse|SuccessResponse {
        try {
            return new SuccessResponse($presentation->beautify($processor->execute($request)));
        } catch (Throwable $exception) {
            return new ErrorResponse('An error occurred while trying to create new password', $exception);
        }
    }
}
