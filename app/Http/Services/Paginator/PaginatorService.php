<?php

namespace App\Http\Services\Paginator;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class PaginatorService
{
    protected $currentPage;
    protected $perPage;

    public function __construct($currentPage = 1, $perPage = 10)
    {
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
    }

    public function paginate(Builder $query): LengthAwarePaginator
    {
        $total = $query->count();
        $results = $query->forPage($this->currentPage, $this->perPage)->get();

        return new LengthAwarePaginator(
            $results,
            $total,
            $this->perPage,
            $this->currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );
    }

    public function paginateCollection($collection): LengthAwarePaginator
    {
        $total = $collection->count();
        $results = $collection->slice(($this->currentPage - 1) * $this->perPage, $this->perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $total,
            $this->perPage,
            $this->currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function getPerPage()
    {
        return $this->perPage;
    }

    public function setPage($page)
    {
        $this->currentPage = $page;
    }

    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;
    }
}
