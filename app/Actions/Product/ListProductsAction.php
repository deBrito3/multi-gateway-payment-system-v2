<?php

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProductsAction
{
    public function execute(): LengthAwarePaginator
    {
        return Product::paginate(100);
    }
}
