<?php

namespace App\Http\Controllers;
use App\Models\Category;
use Illuminate\Http\Request;

class Categorycontroller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 15);

        $query = Category::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('cate_name', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->paginate($perPage);

        return response()->json([
            'message' => 'Categories retrieved successfully',
            'list' => $categories->items(),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page'    => $categories->lastPage(),
                'per_page'     => $categories->perPage(),
                'total'        => $categories->total(),
                'from'         => $categories->firstItem(),
                'to'           => $categories->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string',
            'cate_name' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);
        if (Category::where('cate_name', $request->cate_name)->exists()) {
            return response()->json([
                "message" => "Category name already exists",
            ], 400);
        }
        $category = Category::create([
            "item_name" => $request->item_name,
            "cate_name" => $request->cate_name,
            "description" => $request->description,
            "status" => $request->status,
        ]);
        $category->save();
        return response()->json([
            "message" => "Category created successfully",
            "data" => $category,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                "message" => "Category not found",
            ], 404);
        }
        return response()->json([
            "data" => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::findorFail($id);
        if (!$category) {
            return response()->json([
                "message" => "Category not found",
            ], 404);
        }
        if (Category::where('cate_name', $request->cate_name)->where('id', '!=', $id)->exists()) {
            return response()->json([
                "message" => "Category name already exists",
            ], 400);
        }

        $request->validate([
            'item_name' => 'required|string',
            'cate_name' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $category->update([
            "item_name" => $request->item_name,
            "cate_name" => $request->cate_name,
            "description" => $request->description,
            "status" => $request->status,
        ]);
        $category->save();

        return response()->json([
            "message" => "Category updated successfully",
            "data" => $category,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                "message" => "Category not found",
            ], 404);
        }

        $category->delete();

        return response()->json([
            "message" => "Category deleted successfully",
        ]);
    }
}
