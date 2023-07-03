<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Artel\Support\Services\EntityService;
use App\Repositories\CartItemRepository;

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

    public function create($data)
    {
        $existProduct=$this->repository->where('product_id',$data['product_id'])->first();
        if($existProduct){
            $existProduct->quantity += 1;
            $existProduct->save();
            return $existProduct;
        }
        
        else{
        return $this->repository->create($data);
        }
       
    }
    public function delete($id)
    {
        // $this->repository->destroy($id);
        $cartItem = $this->find($id);
        if ($cartItem) 
        {
            $cartItem->delete();
        }
        return;
       
    }
}
