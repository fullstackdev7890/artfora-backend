<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderItem\CreateOrderItemRequest;
use App\Services\OrderItemService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderItemController extends Controller
{
    public function create(CreateOrderItemRequest $request, OrderItemService $service)
    {
        $data = $request->onlyValidated();

        // $data['user_id'] = $request->user()->id;

        $result = $service->create($data);

        return response()->json($result, Response::HTTP_CREATED);
    }
}
