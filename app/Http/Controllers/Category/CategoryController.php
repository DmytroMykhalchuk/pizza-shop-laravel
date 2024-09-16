<?php

namespace App\Http\Controllers\Category;

use App\Actions\Category\CategoryAction;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

class CategoryController extends BaseController
{
    private CategoryAction $categoryAction;

    public function __construct()
    {
        $this->categoryAction = new CategoryAction();
    }

    public function index(): JsonResponse
    {
        $data = $this->categoryAction->index();
        
        return $this->formatResponse($data);
    }
}
