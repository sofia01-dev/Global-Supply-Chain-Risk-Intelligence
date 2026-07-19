@extends('layouts.app')

@push('styles')
<style>
    .admin-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        background-color: #fff;
    }
    .kpi-card {
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .kpi-icon.primary { background: rgba(28, 85, 255, 0.1); color: #1C55FF; }
    .kpi-icon.success { background: rgba(16, 185, 129, 0.1); color: #10B981; }
    .kpi-icon.warning { background: rgba(245, 158, 11, 0.1); color: #F59E0B; }
    .kpi-icon.info { background: rgba(59, 130, 246, 0.1); color: #3B82F6; }
    
    .table-container {
        overflow-x: auto;
    }
    .table {
        margin-bottom: 0;
        vertical-align: middle;
    }
    .table th {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #6B7280;
        font-weight: 600;
        border-bottom: 1px solid #E5E7EB;
        padding: 12px 16px;
    }
    .table td {
        padding: 12px 16px;
        font-size: 0.85rem;
        border-bottom: 1px solid #F3F4F6;
    }
    .table tbody tr {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .table tbody tr:hover {
        background-color: #F9FAFB;
    }
    .table tbody tr.active-row {
        background-color: #EFF6FF;
    }
    
    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .status-published { background: #D1FAE5; color: #059669; }
    .status-draft { background: #FEF3C7; color: #D97706; }
    
    .category-badge {
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        background: #EEF2FF;
        color: #4F46E5;
    }
    
    .btn-navy {
        background-color: #3E53A0;
        color: white;
        border: none;
    }
    .btn-navy:hover {
        background-color: #2c3e80;
        color: white;
    }
    
    /* Side Panel Styles */
    #articleDetailPanel {
        transition: opacity 0.3s ease;
        opacity: 0.5;
        pointer-events: none;
    }
    #articleDetailPanel.active {
        opacity: 1;
        pointer-events: auto;
    }
    .article-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    
    .article-thumbnail {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 6px;
    }
    
    .tag-badge {
        border: 1px solid #E5E7EB;
        color: #4B5563;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        margin-right: 5px;
        margin-bottom: 5px;
        display: inline-block;
    }
</style>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Analysis Articles</h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola artikel analisis dan insight terkait risiko rantai pasok global</p>
        </div>
        <div class="d-flex gap-3 align-items-center">
            <div class="text-muted" style="font-size: 0.85rem;">
                <i class="bi bi-calendar3 me-1"></i> {{ now()->format('d M Y, H:i') }} WIB
            </div>
            <a href="{{ route('admin.articles.create') }}" class="btn btn-navy" style="border-radius: 8px;">
                <i class="bi bi-plus-lg me-1"></i> Artikel Baru
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="admin-card card kpi-card">
                <div class="kpi-icon primary">
                    <i class="bi bi-file-text"></i>
                </div>
                <div>
                    <div class="text-muted mb-1" style="font-size: 0.75rem; font-weight:600;">Total Artikel</div>
                    <div class="fs-4 fw-bold text-dark mb-0" style="line-height: 1;">{{ $totalArticles }}</div>
                    <div class="text-muted mt-1" style="font-size: 0.7rem;">Semua artikel</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-card card kpi-card">
                <div class="kpi-icon success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <div class="text-muted mb-1" style="font-size: 0.75rem; font-weight:600;">Dipublikasikan</div>
                    <div class="fs-4 fw-bold text-dark mb-0" style="line-height: 1;">{{ $publishedArticles }}</div>
                    <div class="text-muted mt-1" style="font-size: 0.7rem;">{{ $publishedPercentage }}% dari total</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-card card kpi-card">
                <div class="kpi-icon warning">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div>
                    <div class="text-muted mb-1" style="font-size: 0.75rem; font-weight:600;">Draft</div>
                    <div class="fs-4 fw-bold text-dark mb-0" style="line-height: 1;">{{ $draftArticles }}</div>
                    <div class="text-muted mt-1" style="font-size: 0.7rem;">Belum dipublikasikan</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-card card kpi-card">
                <div class="kpi-icon info">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <div class="text-muted mb-1" style="font-size: 0.75rem; font-weight:600;">Terakhir Diperbarui</div>
                    <div class="fs-5 fw-bold text-dark mb-0" style="line-height: 1;">{{ $latestArticle ? $latestArticle->updated_at->format('d M Y') : '-' }}</div>
                    <div class="text-muted mt-1" style="font-size: 0.7rem;">{{ $latestArticle ? $latestArticle->updated_at->format('H:i') . ' WIB' : '' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="admin-card card p-3 mb-4">
        <form method="GET" action="{{ route('admin.articles.index') }}" class="d-flex flex-wrap gap-3 align-items-end justify-content-between">
            <div class="search-box" style="flex-grow: 1; max-width: 350px;">
                <input type="text" name="search" class="form-control" placeholder="Cari artikel..." value="{{ request('search') }}">
            </div>
            
            <div class="d-flex gap-3 align-items-end">
                <div class="d-flex flex-column">
                    <label style="font-size:0.7rem; color:#888; font-weight:600; margin-bottom:5px;">Kategori</label>
                    <select name="category" class="form-select" style="min-width: 150px;">
                        <option value="all">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->category }}" {{ request('category') == $cat->category ? 'selected' : '' }}>{{ $cat->category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex flex-column">
                    <label style="font-size:0.7rem; color:#888; font-weight:600; margin-bottom:5px;">{{ __('Status') }}</label>
                    <select name="status" class="form-select" style="min-width: 150px;">
                        <option value="all">Semua Status</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Dipublikasikan</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-navy" style="border-radius: 8px;">
                    <i class="bi bi-funnel"></i>{{ __('Filter') }}</button>
            </div>
        </form>
    </div>

    <div class="row g-4">
        <!-- Table Column -->
        <div class="col-lg-8">
            <div class="admin-card card h-100">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Daftar Artikel</h6>
                </div>
                <div class="card-body p-0 table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 5%;">No.</th>
                                <th style="width: 40%;">Artikel</th>
                                <th style="width: 15%;">Kategori</th>
                                <th style="width: 15%;">Penulis</th>
                                <th style="width: 15%;">Tanggal</th>
                                <th style="width: 10%;">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($articles as $index => $article)
                            <tr onclick="showArticleDetail({{ json_encode($article) }})" class="article-row" id="row-{{ $article->id }}">
                                <td class="ps-4">{{ $articles->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($article->image)
                                            <img src="{{ $article->image }}" class="article-thumbnail">
                                        @else
                                            <div class="article-thumbnail bg-light d-flex align-items-center justify-content-center text-muted">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        @endif
                                        <div class="text-dark fw-medium" style="font-size:0.85rem; line-height: 1.3;">
                                            {{ \Illuminate\Support\Str::limit($article->title, 50) }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="category-badge">{{ $article->category ?? 'General' }}</span>
                                </td>
                                <td>{{ $article->admin->name ?? 'Admin' }}</td>
                                <td>
                                    <div style="font-size:0.8rem; color:#555;">{{ $article->created_at->format('d M Y') }}</div>
                                    <div style="font-size:0.75rem; color:#888;">{{ $article->created_at->format('H:i') }} WIB</div>
                                </td>
                                <td>
                                    @if($article->is_published)
                                        <span class="status-badge status-published">Dipublikasikan</span>
                                    @else
                                        <span class="status-badge status-draft">Draft</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>
                                    Tidak ada artikel yang ditemukan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($articles->hasPages())
                <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                    <div class="text-muted" style="font-size: 0.8rem;">
                        Menampilkan {{ $articles->firstItem() }} - {{ $articles->lastItem() }} dari {{ $articles->total() }} artikel
                    </div>
                    <div>
                        {{ $articles->links('pagination::bootstrap-4') }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Detail Panel Column -->
        <div class="col-lg-4">
            <div class="admin-card card h-100 p-4" id="articleDetailPanel">
                <h6 class="fw-bold mb-3">Detail Artikel</h6>
                
                <img id="detailImage" src="" class="article-image bg-light d-none" alt="Cover Image">
                <div id="detailNoImage" class="article-image bg-light d-flex align-items-center justify-content-center text-muted mb-3">
                    <i class="bi bi-image fs-1"></i>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span id="detailStatus" class="status-badge status-published">Dipublikasikan</span>
                </div>

                <h5 id="detailTitle" class="fw-bold text-dark mb-2" style="font-size: 1.1rem; line-height:1.4;">--</h5>
                <p id="detailContent" class="text-muted mb-4" style="font-size: 0.85rem; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden;"></p>

                <div class="mb-3">
                    <div class="text-dark fw-bold mb-2" style="font-size:0.8rem;">Kategori</div>
                    <span id="detailCategory" class="category-badge">--</span>
                </div>

                <div class="mb-4">
                    <div class="text-dark fw-bold mb-2" style="font-size:0.8rem;">Tags</div>
                    <div id="detailTagsContainer">
                        <span class="text-muted" style="font-size:0.8rem;">Tidak ada tag</span>
                    </div>
                </div>

                <div class="mt-auto pt-3 border-top d-flex gap-2">
                    <a href="#" id="btnEditArticle" class="btn btn-navy flex-grow-1" style="border-radius:8px; font-size:0.85rem;"><i class="bi bi-pencil me-1"></i> Edit</a>
                    
                    <form id="formDeleteArticle" action="#" method="POST" class="flex-grow-1 m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100" style="border-radius:8px; font-size:0.85rem;">
                            <i class="bi bi-trash me-1"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showArticleDetail(article) {
        // Highlight active row
        document.querySelectorAll('.article-row').forEach(row => row.classList.remove('active-row'));
        const activeRow = document.getElementById('row-' + article.id);
        if (activeRow) activeRow.classList.add('active-row');

        const panel = document.getElementById('articleDetailPanel');
        panel.classList.add('active');

        // Populate Image
        const imgEl = document.getElementById('detailImage');
        const noImgEl = document.getElementById('detailNoImage');
        if (article.image) {
            imgEl.src = article.image;
            imgEl.classList.remove('d-none');
            noImgEl.classList.add('d-none');
        } else {
            imgEl.src = '';
            imgEl.classList.add('d-none');
            noImgEl.classList.remove('d-none');
            noImgEl.classList.add('d-flex');
        }

        // Status
        const statusEl = document.getElementById('detailStatus');
        if (article.is_published) {
            statusEl.className = 'status-badge status-published';
            statusEl.textContent = 'Dipublikasikan';
        } else {
            statusEl.className = 'status-badge status-draft';
            statusEl.textContent = 'Draft';
        }

        // Text content
        document.getElementById('detailTitle').textContent = article.title;
        document.getElementById('detailContent').textContent = article.content || 'Tidak ada konten.';
        document.getElementById('detailCategory').textContent = article.category || 'General';

        // Tags
        const tagsContainer = document.getElementById('detailTagsContainer');
        tagsContainer.innerHTML = '';
        if (article.tags && Array.isArray(article.tags) && article.tags.length > 0) {
            article.tags.forEach(tag => {
                const span = document.createElement('span');
                span.className = 'tag-badge';
                span.textContent = tag;
                tagsContainer.appendChild(span);
            });
        } else {
            tagsContainer.innerHTML = '<span class="text-muted" style="font-size:0.8rem;">Tidak ada tag</span>';
        }

        // Action Buttons
        document.getElementById('btnEditArticle').href = `/admin/articles/${article.id}/edit`;
        document.getElementById('formDeleteArticle').action = `/admin/articles/${article.id}`;
    }

    // Chart.js initialization
    document.addEventListener('DOMContentLoaded', function() {
        // Auto select first row if exists
        const firstRow = document.querySelector('.article-row');
        if (firstRow) {
            firstRow.click();
        }
    });
</script>
@endpush