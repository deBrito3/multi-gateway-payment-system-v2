<?php

namespace App\Http\Controllers;

use App\Actions\Client\ListClientsAction;
use App\Actions\Client\ShowClientAction;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function index(ListClientsAction $action): JsonResponse
    {
        return ClientResource::collection($action->execute())->response();
    }

    public function show(Client $client, ShowClientAction $action): JsonResponse
    {
        return (new ClientResource($action->execute($client)))->response();
    }
}
