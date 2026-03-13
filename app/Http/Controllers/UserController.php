<?php

namespace App\Http\Controllers;

use App\Actions\User\CreateUserAction;
use App\Actions\User\DeleteUserAction;
use App\Actions\User\ListUsersAction;
use App\Actions\User\UpdateUserAction;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(ListUsersAction $action): JsonResponse
    {
        return UserResource::collection($action->execute())->response();
    }

    public function store(StoreUserRequest $request, CreateUserAction $action): JsonResponse
    {
        $user = $action->execute($request->validated());
        return (new UserResource($user->load('roles')))->response()->setStatusCode(201);
    }

    public function show(User $user): JsonResponse
    {
        return (new UserResource($user->load('roles')))->response();
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserAction $action): JsonResponse
    {
        $user = $action->execute($user, $request->validated());
        return (new UserResource($user->load('roles')))->response();
    }

    public function destroy(User $user, DeleteUserAction $action): JsonResponse
    {
        $action->execute($user);
        return response()->json(['message' => 'User deleted']);
    }
}
