<?php

namespace App\Http\Controllers;
use App\Services\CartItemService;
use App\Http\Requests\CartItems\CreateCartItemRequest;
use App\Http\Requests\CartItems\DeleteCartItemRequest;

use Illuminate\Http\Request;

class CartItemController extends Controller
{
    public function create(CreateCartItemRequest $request, CartItemService $service)
    {
        $data = $request->onlyValidated();

        $result = $service->create($data);

        return response()->json($result);
    }
    public function read(Request $request, CartItemService $service){

        $result = $service->read();

        return response()->json($result);
    }
    public function delete(DeleteCartItemRequest $request, CartItemService $service,$id){
        $result = $service->delete($id);
    }
}
