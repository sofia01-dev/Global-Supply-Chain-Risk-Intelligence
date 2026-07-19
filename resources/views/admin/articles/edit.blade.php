@extends('layouts.app')

@push('styles')
<style>
    .admin-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        background-color: #fff;
    }
    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #555;
    }
    .form-control, .form-select {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #e0e0e0;
        font-size: 0.9rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3E53A0;
        box-shadow: 0 0 0 0.25rem rgba(62, 83, 160, 0.1);
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
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width: 900px;">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Edit Artikel</h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Perbarui konten dan informasi artikel</p>
        </div>
        <a href="{{ route('admin.articles.index') }}" class="btn btn-light border" style="border-radius: 8px; font-size: 0.85rem;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- Form Card -->
    <div class="admin-card card p-4">
        @if($errors->any())
            <div class="alert alert-danger" style="border-radius: 8px;">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.articles.update', $article->id) }}">
            @csrf
            @method('PUT')
            
            <div class="row g-4 mb-4">
                <div class="col-md-12">
                    <label class="form-label">Judul Artikel</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $article->title) }}" placeholder="Contoh: Dampak Krisis di Laut Merah..." required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category', $article->category) }}" placeholder="Contoh: Shipping, Economy, Weather">
                </div>

                <div class="col-md-6">
                    <label class="form-label">URL Gambar Sampul (Opsional)</label>
                    <input type="url" name="image" class="form-control" value="{{ old('image', $article->image) }}" placeholder="https://contoh.com/gambar.jpg">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Tags (Pisahkan dengan koma)</label>
                    <input type="text" name="tags" class="form-control" value="{{ old('tags', implode(', ', $article->tags ?? [])) }}" placeholder="Contoh: Laut Merah, Logistik, Krisis">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Isi Artikel</label>
                    <textarea name="content" class="form-control" rows="8" placeholder="Tuliskan analisis Anda di sini..." required>{{ old('content', $article->content) }}</textarea>
                </div>

                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $article->is_published) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="is_published">Langsung Publikasikan Artikel</label>
                    </div>
                    <div class="form-text" style="font-size: 0.75rem;">Jika tidak dicentang, artikel akan disimpan sebagai Draft.</div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('admin.articles.index') }}" class="btn btn-light border px-4" style="border-radius: 8px;">Batal</a>
                <button type="submit" class="btn btn-navy px-4" style="border-radius: 8px;">
                    <i class="bi bi-save me-1"></i> Simpan Artikel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
