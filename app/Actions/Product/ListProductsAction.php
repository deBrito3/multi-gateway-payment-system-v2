<?php

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ListProductsAction
{
    public function execute(): Collection
    {
        return Product::all();
    }
}
