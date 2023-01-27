<?php

namespace App\Http\Controllers;

use App\Http\Requests\Products\CreateProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Requests\Products\DeleteProductRequest;
use App\Http\Requests\Products\GetProductRequest;
use App\Http\Requests\Products\SearchProductsRequest;
use App\Services\ProductService;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function create(CreateProductRequest $request, ProductService $service)
    {
        $data = $request->onlyValidated();

        $result = $service->create($data);

        return response()->json($result);
    }

    public function get(GetProductRequest $request, ProductService $service, $id)
    {
        $result = $service
            ->with($request->input('with', []))
            ->withCount($request->input('with_count', []))
            ->find($id);

        return response()->json($result);
    }

    public function search(SearchProductsRequest $request, ProductService $service)
    {
        $result = $service->search($request->onlyValidated());

        return response()->json($result);
    }

    public function update(UpdateProductRequest $request, ProductService $service, $id)
    {
        $service->update($id, $request->onlyValidated());

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function delete(DeleteProductRequest $request, ProductService $service, $id)
    {
        $service->delete($id);

        return response('', Response::HTTP_NO_CONTENT);
    }

}
