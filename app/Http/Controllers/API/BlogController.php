<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class BlogController extends Controller
{
    public function createCategory(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if (Category::where('title', $request->title)->exists()) {
            return response()->json([
                'message' => 'Judul kategori tersebut sudah ada.',
            ], 409);
        }

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create([
            'title' => $request->title,
            'image' => $path ? Storage::url($path) : null,
        ]);

        return response()->json([
            'message' => 'Kategori berhasil dibuat.',
            'data' => $category,
        ], 201);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        }

        $request->validate([
            'title' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->filled('title')) {
            $category->title = $request->title;
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $category->image));
            }
            $path = $request->file('image')->store('categories', 'public');
            $category->image = Storage::url($path);
        }

        $category->save();

        return response()->json([
            'message' => 'Kategori berhasil diubah.',
            'data' => $category,
        ], 200);
    }

    public function createArticle(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('articles', 'public');
        }

        $article = Article::create([
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'image' => $path ? Storage::url($path) : null,
        ]);

        return response()->json([
            'message' => 'Artikel berhasil dibuat.',
            'data' => $article,
        ], 201);
    }

    public function updateArticle(Request $request, $id)
    {
        $article = Article::find($id);
        if (!$article) {
            return response()->json(['message' => 'Artikel tidak ditemukan.'], 404);
        }

        $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->filled('title')) {
            $article->title = $request->title;
        }

        if ($request->filled('description')) {
            $article->description = $request->description;
        }

        if ($request->hasFile('image')) {
            if ($article->image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $article->image));
            }
            $path = $request->file('image')->store('articles', 'public');
            $article->image = Storage::url($path);
        }

        $article->save();

        return response()->json([
            'message' => 'Artikel berhasil diubah.',
            'data' => $article,
        ]);
    }

    public function getCategoryArticleSummary($categoryId, $articleId)
    {
        $article = Article::where('id', $articleId)
                          ->where('category_id', $categoryId)
                          ->first();

        if (!$article) {
            return response()->json([
                'message' => 'Artikel tidak ditemukan dalam kategori ini.'
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'image' => $article->image,
                'description' => $this->getFirstTwoSentences($article->description),
            ]
        ]);
    }

    public function getCategoryArticleDetail($categoryId, $articleId)
    {
        $article = Article::where('id', $articleId)
                          ->where('category_id', $categoryId)
                          ->first();

        if (!$article) {
            return response()->json([
                'message' => 'Artikel tidak ditemukan dalam kategori ini.'
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'image' => $article->image,
                'description' => $article->description,
            ]
        ]);
    }

    private function getFirstTwoSentences($text)
    {
        if (!$text) return null;
    
        $words = preg_split('/\s+/', strip_tags($text));
        $summary = implode(' ', array_slice($words, 0, 30));
    
        return count($words) > 30 ? $summary . '...' : $summary;
    }
}
