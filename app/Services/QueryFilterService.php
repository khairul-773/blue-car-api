<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QueryFilterService
{
    public static function applyFilters(Builder $query, Request $request, array $searchableFields = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // 🔹 Search Filter (Supports multiple fields)
        if ($search = $request->get('search')) {
            $keywords = explode(',', $search);
            $query->where(function ($q) use ($keywords, $searchableFields) {
                foreach ($keywords as $keyword) {
                    foreach ($searchableFields as $field) {
                        $q->orWhere($field, 'LIKE', "%{$keyword}%");
                    }
                }
            });
        }

        // 🔹 Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 🔹 Pagination
        $perPage = $request->get('page_size', 10);
        return $query->paginate($perPage);
    }
}