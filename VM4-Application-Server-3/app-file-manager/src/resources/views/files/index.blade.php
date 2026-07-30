@extends('layouts.app')

@section('title', 'Files')

@section('content')
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">Files</h1>
            <p class="page-subtitle mb-0">
                {{ $files->count() }} {{ Str::plural('file', $files->count()) }} shown
                @if ($search !== '')
                    · <a href="{{ route('files.index') }}">clear search</a>
                @endif
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <span class="pill">Stored: {{ $totalFiles }}</span>
            <span class="pill pill-accent">{{ round($totalBytes / 1048576, 2) }} MB</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <form method="POST" action="{{ route('files.store') }}" enctype="multipart/form-data"
                  class="card-surface card-pad">
                @csrf

                <h2 class="card-title-sm">Upload a file</h2>
                <p class="card-text-sm mb-3">
                    Up to {{ round($maxKilobytes / 1024) }} MB. Files are kept on a Docker volume,
                    so they survive a container rebuild.
                </p>

                <div class="mb-3">
                    <label for="file" class="form-label">File *</label>
                    <input type="file" id="file" name="file"
                           class="form-control @error('file') is-invalid @enderror" required>
                    @error('file')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" id="description" name="description" value="{{ old('description') }}"
                           class="form-control @error('description') is-invalid @enderror"
                           placeholder="What is this file?">
                    @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-accent w-100">Upload</button>
            </form>
        </div>

        <div class="col-lg-8">
            <form method="GET" action="{{ route('files.index') }}" class="card-surface card-pad mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-9">
                        <label for="q" class="form-label">Search</label>
                        <input type="text" id="q" name="q" value="{{ $search }}"
                               class="form-control" placeholder="File name or description">
                    </div>
                    <div class="col-3">
                        <button type="submit" class="btn btn-ghost w-100">Search</button>
                    </div>
                </div>
            </form>

            <div class="card-surface">
                <div class="table-responsive">
                    <table class="table table-crm align-middle">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Type</th>
                                <th class="text-end">Size</th>
                                <th>Uploaded</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($files as $file)
                                <tr>
                                    <td>
                                        <span class="cell-strong d-block">{{ $file->original_name }}</span>
                                        @if ($file->description)
                                            <span class="text-dim small">{{ $file->description }}</span>
                                        @endif
                                        @unless ($file->existsOnDisk())
                                            <span class="pill pill-danger mt-1">missing on disk</span>
                                        @endunless
                                    </td>
                                    <td><span class="pill">{{ $file->extension() }}</span></td>
                                    <td class="text-end mono">{{ $file->humanSize() }}</td>
                                    <td class="text-dim small">{{ $file->created_at->format('d M Y H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('files.download', $file) }}"
                                           class="btn btn-ghost btn-sm">Download</a>

                                        <form method="POST" action="{{ route('files.destroy', $file) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Delete {{ $file->original_name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger-soft btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-dim py-4">
                                        No file stored yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection