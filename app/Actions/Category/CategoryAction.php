<?php

namespace App\Actions\Category;

use App\Http\Resources\Category\CategoryIndexResource;
use App\Models\ShopCategory;

class CategoryAction
{
    public function __construct()
    {
    }

    public function index(): array
    {
        $categories = ShopCategory::orderBy('position')->get();

        return [
            'code' => 200,
            'status' => 'success',
            'message' => 'Success',
            'data' => CategoryIndexResource::collection($categories),
        ];
    }
}
