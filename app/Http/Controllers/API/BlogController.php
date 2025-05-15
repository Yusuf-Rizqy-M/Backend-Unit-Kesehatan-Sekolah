<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        if (Category::where('title', $request->title)->where('status', 'active')->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Judul kategori tersebut sudah ada.',
            ], 409);
        }

        $path = $request->hasFile('image') ? $request->file('image')->store('categories', 'public') : null;

        $category = Category::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $path ? Storage::url($path) : null,
            'status' => 'active',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil dibuat.',
            'data' => $category,
        ], 201);
    }

    public function getCategory($id)
    {
        $category = Category::where('id', $id)->where('status', 'active')->first();
        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil ditemukan.',
            'data' => $category
        ], 200);
    }

    public function getCategories()
    {
        $categories = Category::where('status', 'active')->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada kategori yang ditemukan.',
                'data' => []
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil diambil.',
            'data' => $categories
        ], 200);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::where('id', $id)->where('status', 'active')->first();
        if (!$category) {
            return response()->json(['status' => false, 'message' => 'Kategori tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        if ($request->filled('title')) {
            $category->title = $request->title;
        }
        if ($request->filled('description')) {
            $category->description = $request->description;
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
            'status' => true,
            'message' => 'Kategori berhasil diubah.',
            'data' => $category,
        ]);
    }

    public function deleteCategory($id)
    {
        $category = Category::where('id', $id)->where('status', 'active')->first();
        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan.'
            ], 404);
        }

        // Check if category has associated active articles
        if ($category->articles()->where('status', 'active')->count() > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak dapat dihapus karena masih memiliki artikel terkait.'
            ], 409);
        }

        $category->status = 'inactive';
        $category->save();

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil dinonaktifkan.'
        ]);
    }

    public function createArticle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        // Ensure the category is active
        $category = Category::where('id', $request->category_id)->where('status', 'active')->first();
        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan atau tidak aktif.'
            ], 404);
        }

        $path = $request->hasFile('image') ? $request->file('image')->store('articles', 'public') : null;

        $article = Article::create([
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'image' => $path ? Storage::url($path) : null,
            'status' => 'active',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Artikel berhasil dibuat.',
            'data' => $article,
        ], 201);
    }

    public function getArticlesByCategory($id)
    {
        $category = Category::where('id', $id)->where('status', 'active')->first();
        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan.',
                'data' => []
            ], 404);
        }

        $articles = Article::where('category_id', $id)->where('status', 'active')->get();

        if ($articles->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada artikel yang ditemukan untuk kategori ini.',
                'data' => []
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Artikel berhasil diambil.',
            'data' => $articles
        ], 200);
    }

    public function updateArticle(Request $request, $id)
    {
        $article = Article::where('id', $id)->where('status', 'active')->first();
        if (!$article) {
            return response()->json(['status' => false, 'message' => 'Artikel tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

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
            'status' => true,
            'message' => 'Artikel berhasil diubah.',
            'data' => $article,
        ]);
    }

    public function deleteArticle($id)
    {
        $article = Article::where('id', $id)->where('status', 'active')->first();
        if (!$article) {
            return response()->json([
                'status' => false,
                'message' => 'Artikel tidak ditemukan.'
            ], 404);
        }

        $article->status = 'inactive';
        $article->save();

        return response()->json([
            'status' => true,
            'message' => 'Artikel berhasil dinonaktifkan.'
        ]);
    }

    public function getCategoryArticleSummary($categoryId, $articleId)
    {
        $article = Article::where('id', $articleId)
            ->where('category_id', $categoryId)
            ->where('status', 'active')
            ->first();

        if (!$article) {
            return response()->json([
                'status' => false,
                'message' => 'Artikel tidak ditemukan dalam kategori ini.'
            ], 404);
        }

        return response()->json([
            'status' => true,
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
            ->where('status', 'active')
            ->first();

        if (!$article) {
            return response()->json([
                'status' => false,
                'message' => 'Artikel tidak ditemukan dalam kategori ini.'
            ], 404);
        }

        return response()->json([
            'status' => true,
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
        if (!$text) {
            return null;
        }

        $words = preg_split('/\s+/', strip_tags($text));
        $summary = implode(' ', array_slice($words, 0, 30));

        return count($words) > 30 ? $summary . '...' : $summary;
    }

    private function validationErrorResponse($validator)
    {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'data' => $validator->errors(),
        ], 422);
    }
}