<?php

namespace App\Http\Controllers;

use App\Actions\Client\ListClientsAction;
use App\Actions\Client\ShowClientAction;
use App\Models\Client;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function index(ListClientsAction $action): JsonResponse
    {
        return response()->json(['data' => $action->execute()]);
    }

    public function show(Client $client, ShowClientAction $action): JsonResponse
    {
        return response()->json(['data' => $action->execute($client)]);
    }
}
