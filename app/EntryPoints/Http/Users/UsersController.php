<?php

namespace App\EntryPoints\Http\Users;

use Illuminate\Http\Response;
use Throwable;
use App\Foundation\Laravel\AppController;
use App\EntryPoints\Http\Users\ActionsProcessors\{UsersCreateProcessor,
    UsersDestroyProcessor,
    UsersIndexProcessor,
    UsersShowProcessor,
    UsersUpdateProcessor};
use App\EntryPoints\Http\Users\ActionsRequests\{UsersCreateRequest,
    UsersDestroyRequest,
    UsersIndexRequest,
    UsersShowRequest,
    UsersUpdateRequest};
use App\EntryPoints\Http\Users\ActionsPresentations\{UsersCreatePresentation,
    UsersDestroyPresentation,
    UsersIndexPresentation,
    UsersShowPresentation,
    UsersUpdatePresentation};
use App\Foundation\Laravel\Responses\{
    SuccessResponse,
    ErrorResponse
};

class UsersController extends AppController
{
    public function index(
        UsersIndexRequest $request,
        UsersIndexProcessor $processor,
        UsersIndexPresentation $presentation): ErrorResponse|SuccessResponse
    {
        try {
            return new SuccessResponse(
                $presentation->beautify(
                    $processor->execute($request)
                )
            );
        } catch (Throwable $exception) {
            return new ErrorResponse(
                'An error occurred while trying to list the Users',
                $exception
            );
        }
    }

    public function create(
        UsersCreateRequest $request,
        UsersCreateProcessor $processor,
        UsersCreatePresentation $presentation
    ): ErrorResponse|SuccessResponse {
        try {
            return new SuccessResponse(
                $presentation->beautify($processor->execute($request)),
                ['message' => 'User was created successfully'],
                Response::HTTP_CREATED
            );
        } catch (Throwable $exception) {
            return new ErrorResponse('An error occurred while trying to create the User', $exception);
        }
    }

    public function show(
        UsersShowRequest $request,
        UsersShowProcessor $processor,
        UsersShowPresentation $presentation,
        int|string $id
    ): ErrorResponse|SuccessResponse {
        try {
            return new SuccessResponse(
                $presentation->beautify(
                    $processor->execute($request, $id)
                )
            );
        } catch (Throwable $exception) {
            return new ErrorResponse(
                message: 'An error occurred while trying to show the User',
                exception: $exception
            );
        }
    }

    public function update(
        UsersUpdateRequest $request,
        UsersUpdateProcessor $processor,
        UsersUpdatePresentation $presentation,
        int|string $id
    ): ErrorResponse|SuccessResponse {
        try {
            return new SuccessResponse(
                data: $presentation->beautify(
                    $processor->execute($request, $id)
                ),
                metadata: ['message' => 'User was updated successfully',],
                code: Response::HTTP_ACCEPTED
            );
        } catch (Throwable $exception) {
            return new ErrorResponse(
                message: 'An error occurred while trying to update the User',
                exception: $exception
            );
        }
    }

    public function destroy(
        UsersDestroyRequest $request,
        UsersDestroyProcessor $processor,
        UsersDestroyPresentation $presentation,
        int|string $id
    ): ErrorResponse|SuccessResponse {
        try {
            return new SuccessResponse(
                $presentation->beautify(
                    $processor->execute($request, $id)
                ),
                ['message' => 'User was destroyed successfully',],
                Response::HTTP_ACCEPTED
            );
        } catch (Throwable $exception) {
            return new ErrorResponse(
                message: 'An error occurred while trying to destroy the User',
                exception: $exception
            );
        }
    }
}
