<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Requests\CategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::withCount('tasks')->orderBy('created_at','desc')->get();

        return view('categories.index',compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        Category::create($request->validated());

        return redirect()->route('categories.index')
            ->with('success','カテゴリーを作成しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category->load('tasks');

        return view('categories.show',compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('categories.edit',compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')
            ->with('success','カテゴリーを更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if($category->tasks()->count() > 0){
            return redirect()->route('categories.index')
                ->with('error','タスクが紐付いているカテゴリーは削除できません。');
        }
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'カテゴリーを削除しました。');
    }
}
