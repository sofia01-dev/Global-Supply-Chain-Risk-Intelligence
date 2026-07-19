<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Article;

class ArticleController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Article::with('admin')->latest();

        // Filters
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_published', $request->status === 'published');
        }

        $articles = $query->paginate(10);

        // KPIs
        $totalArticles = Article::count();
        $publishedArticles = Article::where('is_published', true)->count();
        $draftArticles = $totalArticles - $publishedArticles;
        $publishedPercentage = $totalArticles > 0 ? round(($publishedArticles / $totalArticles) * 100, 1) : 0;
        
        // Latest Update
        $latestArticle = Article::latest('updated_at')->first();
        
        // Charts Data
        // Monthly stats (last 12 months)
        $monthlyStats = Article::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('count', 'month')->toArray();
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Des'];
        $chartData = [];
        foreach(range(1, 12) as $m) {
            $chartData[] = $monthlyStats[$m] ?? 0;
        }

        // Category distribution
        $categories = Article::selectRaw('category, COUNT(*) as count')
            ->whereNotNull('category')
            ->groupBy('category')
            ->get();

        return view('admin.articles.index', compact(
            'articles', 
            'totalArticles', 
            'publishedArticles', 
            'draftArticles', 
            'publishedPercentage',
            'latestArticle',
            'months',
            'chartData',
            'categories'
        ));
    }

    public function show($id)
    {
        $article = Article::find($id);
        return view('admin.articles.show', compact('article'));
    }

    public function create()
    {
        return view('admin.articles.create');
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|url',
            'tags' => 'nullable|string',
            'content' => 'required|string',
        ]);

        $tagsArray = [];
        if ($request->tags) {
            $tagsArray = array_map('trim', explode(',', $request->tags));
        }

        Article::create([
            'admin_id' => auth()->id(),
            'title' => $request->title,
            'slug' => \Illuminate\Support\Str::slug($request->title),
            'category' => $request->category,
            'image' => $request->image,
            'tags' => $tagsArray,
            'content' => $request->content,
            'is_published' => $request->has('is_published'),
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit(Article $article)
    {
        return view('admin.articles.edit', compact('article'));
    }

    public function update(\Illuminate\Http\Request $request, Article $article)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|url',
            'tags' => 'nullable|string',
            'content' => 'required|string',
        ]);

        $tagsArray = [];
        if ($request->tags) {
            $tagsArray = array_map('trim', explode(',', $request->tags));
        }

        $article->update([
            'title' => $request->title,
            'slug' => \Illuminate\Support\Str::slug($request->title),
            'category' => $request->category,
            'image' => $request->image,
            'tags' => $tagsArray,
            'content' => $request->content,
            'is_published' => $request->has('is_published'),
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil dihapus.');
    }
}