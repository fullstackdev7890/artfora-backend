<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Artel\Support\Services\EntityService;
use App\Repositories\CartItemRepository;
use App\Models\CartItem;

/**
 * @mixin CartItemRepository
 * @property CartItemRepository $repository
 */
class CartItemService extends EntityService
{
    public function __construct()
    {
        $this->setRepository(CartItemRepository::class);
    }
    public function read(){

        return $this->repository->with(['product'])->get();
    }

    public function create($data)
    {
        $existProduct=CartItem::where('product_id',$data['product_id'])->first();
        if($existProduct){
            $existProduct['quantity'] = $existProduct['quantity'] + 1;
            $existProduct->save();
            return $existProduct->load(['product']);
        }
        
        else{
        $res = $this->repository->create($data);
        return $res->load(['product']);
        }
       
    }
    public function delete($id)
    {
        $cartItem = $this->find($id);
        if ($cartItem) 
        {
            $cartItem->delete();
        }
        return;
       
    }
    
}
