@forelse ($prompts as $prompt)
    <div class="card mb-2">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <strong>{{ $prompt->title }}</strong>
                    @if ($prompt->is_active)
                        <span class="badge bg-success ms-2">Active</span>
                    @endif
                    <div class="text-muted small mt-1">{{ Str::limit($prompt->prompt_text, 150) }}</div>
                </div>
                <div class="d-flex gap-1">
                    @unless ($prompt->is_active)
                        <button type="button" class="btn btn-sm btn-outline-success activate-prompt-btn" data-id="{{ $prompt->id }}">
                            <i class="bi bi-check-circle"></i> Activate
                        </button>
                    @endunless

                    <button type="button" class="btn btn-sm btn-outline-primary edit-prompt-btn"
                        data-id="{{ $prompt->id }}"
                        data-title="{{ $prompt->title }}"
                        data-text="{{ $prompt->prompt_text }}">
                        <i class="bi bi-pencil"></i>
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-danger delete-prompt-btn" data-id="{{ $prompt->id }}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="text-center text-muted py-4">
        <i class="bi bi-chat-left-text fs-4 d-block mb-1"></i>
        No prompts added yet.
    </div>
@endforelse