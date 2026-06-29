@forelse ($knowledgeBases as $kb)
    <div class="card mb-2">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <strong>{{ $kb->title }}</strong>
                    @if ($kb->is_active)
                        <span class="badge bg-success ms-2">Active</span>
                    @else
                        <span class="badge bg-secondary ms-2">Inactive</span>
                    @endif
                    <div class="text-muted small mt-1">{{ Str::limit($kb->content, 150) }}</div>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary toggle-kb-btn" data-id="{{ $kb->id }}"
                        title="{{ $kb->is_active ? 'Deactivate' : 'Activate' }}">
                        <i class="bi bi-{{ $kb->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-primary edit-kb-btn"
                        data-id="{{ $kb->id }}"
                        data-title="{{ $kb->title }}"
                        data-content="{{ $kb->content }}">
                        <i class="bi bi-pencil"></i>
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-danger delete-kb-btn" data-id="{{ $kb->id }}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="text-center text-muted py-4">
        <i class="bi bi-journal-text fs-4 d-block mb-1"></i>
        No knowledge base entries added yet.
    </div>
@endforelse