<?php

namespace App\EntryPoints\Http\Settings\GroupUsers;

use Illuminate\Http\Response;
use Throwable;
use App\Foundation\Laravel\AppController;
use App\EntryPoints\Http\Settings\GroupUsers\ActionsProcessors\{
    GroupUsersDestroyProcessor,
    GroupUsersIndexProcessor,
    GroupUsersShowProcessor,
    GroupUsersStoreProcessor,
    GroupUsersUpdateProcessor,
};
use App\EntryPoints\Http\Settings\GroupUsers\ActionsRequests\{
    GroupUsersDestroyRequest,
    GroupUsersIndexRequest,
    GroupUsersShowRequest,
    GroupUsersStoreRequest,
    GroupUsersUpdateRequest,
};
use App\EntryPoints\Http\Settings\GroupUsers\ActionsPresentations\{
    GroupUsersDestroyPresentation,
    GroupUsersIndexPresentation,
    GroupUsersShowPresentation,
    GroupUsersStorePresentation,
    GroupUsersUpdatePresentation,
};
use App\Foundation\Laravel\Responses\{
    SuccessResponse,
    ErrorResponse
};

class GroupUsersController extends AppController
{
    public function index(
        GroupUsersIndexRequest $request,
        GroupUsersIndexProcessor $processor,
        GroupUsersIndexPresentation $presentation): ErrorResponse|SuccessResponse
    {
        try {
            return new SuccessResponse(
                $presentation->beautify(
                    $processor->execute($request)
                )
            );
        } catch (Throwable $exception) {
            return new ErrorResponse(
                'An error occurred while trying to list the GroupUsers',
                $exception
            );
        }
    }

    public function store(
        GroupUsersStoreRequest $request,
        GroupUsersStoreProcessor $processor,
        GroupUsersStorePresentation $presentation): ErrorResponse|SuccessResponse
    {
        try {
            return new SuccessResponse(
                $presentation->beautify(
                    $processor->execute($request)
                ),
                ['message' => 'GroupUser was stored successfully'],
                Response::HTTP_CREATED
            );
        } catch (Throwable $exception) {
            return new ErrorResponse(
                'An error occurred while trying to store the GroupUser',
                $exception
            );
        }
    }

    public function show(
        GroupUsersShowRequest $request,
        GroupUsersShowProcessor $processor,
        GroupUsersShowPresentation $presentation,
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
                message: 'An error occurred while trying to show the GroupUser',
                exception: $exception
            );
        }
    }

    public function update(
        GroupUsersUpdateRequest $request,
        GroupUsersUpdateProcessor $processor,
        GroupUsersUpdatePresentation $presentation,
        int|string $id
    ): ErrorResponse|SuccessResponse {
        try {
            return new SuccessResponse(
                data: $presentation->beautify(
                    $processor->execute($request, $id)
                ),
                metadata: ['message' => 'GroupUser was updated successfully',],
                code: Response::HTTP_ACCEPTED
            );
        } catch (Throwable $exception) {
            return new ErrorResponse(
                message: 'An error occurred while trying to update the GroupUser',
                exception: $exception
            );
        }
    }

    public function destroy(
        GroupUsersDestroyRequest $request,
        GroupUsersDestroyProcessor $processor,
        GroupUsersDestroyPresentation $presentation,
        int|string $id
    ): ErrorResponse|SuccessResponse {
        try {
            return new SuccessResponse(
                $presentation->beautify(
                    $processor->execute($request, $id)
                ),
                ['message' => 'GroupUser was destroyed successfully',],
                Response::HTTP_ACCEPTED
            );
        } catch (Throwable $exception) {
            return new ErrorResponse(
                message: 'An error occurred while trying to destroy the GroupUser',
                exception: $exception
            );
        }
    }
}
