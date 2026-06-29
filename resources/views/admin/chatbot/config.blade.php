@extends('layouts.app')

@section('title', 'Database Chatbot')
@section('page-title', 'Database Chatbot')

@section('content')
    <div class="container mt-4">

        <div id="ajaxMessage">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-1"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-x-circle me-1"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-x-circle me-1"></i>
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h4 class="mb-0">
                    <i class="bi bi-robot me-2"></i>Database Chatbot
                </h4>
                <span class="text-muted small">
                    Configure your chatbot's welcome message, prompts, and knowledge base.
                </span>
            </div>

            @if($isAdmin)
                <a href="{{ route('admin.chatbot.history', $client->id) }}"
                    class="btn btn-outline-secondary px-3">
                    <i class="bi bi-arrow-left"></i> Back to History
                </a>
            @endif
        </div>

        {{-- WELCOME MESSAGE (normal submit, page reload) --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0"><i class="bi bi-emoji-smile me-1"></i> Welcome Message</h6>
                <span class="text-muted small">First message shown to every visitor when they open your chatbot.</span>
            </div>
            <div class="card-body">
                <form action="{{ $isAdmin
                    ? route('chatbot.config.welcome.update', $client->id)
                    : route('client.chatbot.config.welcome.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <textarea name="welcome_message" rows="4"
                            class="form-control @error('welcome_message') is-invalid @enderror"
                            placeholder="e.g. Welcome to our store!&#10;How can I help you today?">{{ old('welcome_message', $client->welcome_message) }}</textarea>
                        @error('welcome_message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2"></i> Save Message
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- PROMPTS (AJAX) --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="mb-0"><i class="bi bi-chat-left-text me-1"></i> Prompts</h6>
                    <span class="text-muted small">Only one prompt is active at a time.</span>
                </div>
                <a href="#promptFormCard" class="btn btn-sm btn-primary" id="showAddPromptBtn">
                    <i class="bi bi-plus-lg"></i> Add Prompt
                </a>
            </div>
            <div class="card-body">

                <div class="card border-primary mb-3 d-none" id="promptFormCard">
                    <div class="card-body">
                        <h6 class="mb-3" id="promptFormTitle">Add New Prompt</h6>
                        <form id="promptForm">
                            <input type="hidden" id="promptFormMethod" value="POST">
                            <input type="hidden" id="promptIdInput" value="">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Title</label>
                                <input type="text" name="title" id="promptTitleInput" class="form-control"
                                    placeholder="e.g. Default Prompt" required>
                                <div class="invalid-feedback" id="promptTitleError"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">Prompt Text</label>
                                <textarea name="prompt_text" id="promptTextInput" class="form-control" rows="5"
                                    placeholder="You are a helpful customer support assistant..." required></textarea>
                                <div class="invalid-feedback" id="promptTextError"></div>
                            </div>
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-outline-secondary" id="cancelPromptFormBtn">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="savePromptBtn"><i class="bi bi-check2"></i> Save Prompt</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="promptsListContainer">
                    @include('admin.chatbot.partials.prompts-list', ['prompts' => $prompts, 'isAdmin' => $isAdmin, 'client' => $client])
                </div>
            </div>
        </div>

        {{-- KNOWLEDGE BASE (AJAX) --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="mb-0"><i class="bi bi-journal-text me-1"></i> Knowledge Base</h6>
                    <span class="text-muted small">All active entries are combined and given to your chatbot as reference data.</span>
                </div>
                <a href="#kbFormCard" class="btn btn-sm btn-primary" id="showAddKbBtn">
                    <i class="bi bi-plus-lg"></i> Add Entry
                </a>
            </div>
            <div class="card-body">

                <div class="card border-primary mb-3 d-none" id="kbFormCard">
                    <div class="card-body">
                        <h6 class="mb-3" id="kbFormTitle">Add New Knowledge Base Entry</h6>
                        <form id="kbForm">
                            <input type="hidden" id="kbFormMethod" value="POST">
                            <input type="hidden" id="kbIdInput" value="">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Title</label>
                                <input type="text" name="title" id="kbTitleInput" class="form-control"
                                    placeholder="e.g. Products, FAQs, Return Policy" required>
                                <div class="invalid-feedback" id="kbTitleError"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">Content</label>
                                <textarea name="content" id="kbContentInput" class="form-control" rows="8"
                                    placeholder="Enter your business info, products, FAQs, policies here..." required></textarea>
                                <div class="invalid-feedback" id="kbContentError"></div>
                            </div>
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-outline-secondary" id="cancelKbFormBtn">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="saveKbBtn"><i class="bi bi-check2"></i> Save Entry</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="kbListContainer">
                    @include('admin.chatbot.partials.kb-list', ['knowledgeBases' => $knowledgeBases, 'isAdmin' => $isAdmin, 'client' => $client])
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    const IS_ADMIN = {{ $isAdmin ? 'true' : 'false' }};
    const CLIENT_ID = {{ $client->id }};

    const ROUTES = {
        promptStore:  IS_ADMIN ? '{{ route("chatbot.config.prompts.store", $client->id) }}' : '{{ route("client.chatbot.config.prompts.store") }}',
        promptUpdate: IS_ADMIN ? '{{ route("chatbot.config.prompts.update", [$client->id, ":id"]) }}' : '{{ route("client.chatbot.config.prompts.update", ":id") }}',
        promptDestroy:IS_ADMIN ? '{{ route("chatbot.config.prompts.destroy", [$client->id, ":id"]) }}' : '{{ route("client.chatbot.config.prompts.destroy", ":id") }}',
        promptActivate:IS_ADMIN ? '{{ route("chatbot.config.prompts.activate", [$client->id, ":id"]) }}' : '{{ route("client.chatbot.config.prompts.activate", ":id") }}',
        kbStore:      IS_ADMIN ? '{{ route("chatbot.config.kb.store", $client->id) }}' : '{{ route("client.chatbot.config.kb.store") }}',
        kbUpdate:     IS_ADMIN ? '{{ route("chatbot.config.kb.update", [$client->id, ":id"]) }}' : '{{ route("client.chatbot.config.kb.update", ":id") }}',
        kbDestroy:    IS_ADMIN ? '{{ route("chatbot.config.kb.destroy", [$client->id, ":id"]) }}' : '{{ route("client.chatbot.config.kb.destroy", ":id") }}',
        kbToggle:     IS_ADMIN ? '{{ route("chatbot.config.kb.toggle", [$client->id, ":id"]) }}' : '{{ route("client.chatbot.config.kb.toggle", ":id") }}',
    };

    const CSRF_TOKEN = '{{ csrf_token() }}';

    $(document).ready(function () {
        setTimeout(() => $('#ajaxMessage .alert').alert('close'), 4000);

        function showMessage(type, message) {
            $('#ajaxMessage').html(`
                <div class="alert alert-${type} alert-dismissible fade show">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'x-circle'} me-1"></i> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            setTimeout(() => $('#ajaxMessage .alert').alert('close'), 4000);
            $('html, body').animate({ scrollTop: 0 }, 200);
        }

        function renderPromptsList(prompts) {
            let html = '';
            if (!prompts || prompts.length === 0) {
                html = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-chat-left-text fs-4 d-block mb-1"></i>
                        No prompts added yet.
                    </div>`;
            } else {
                prompts.forEach(function (p) {
                    const activeBadge = p.is_active ? '<span class="badge bg-success ms-2">Active</span>' : '';
                    const activateBtn = p.is_active ? '' : `
                        <button type="button" class="btn btn-sm btn-outline-success activate-prompt-btn" data-id="${p.id}">
                            <i class="bi bi-check-circle"></i> Activate
                        </button>`;

                    html += `
                        <div class="card mb-2">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                    <div>
                                        <strong>${escapeHtml(p.title)}</strong>${activeBadge}
                                        <div class="text-muted small mt-1">${escapeHtml(truncate(p.prompt_text, 150))}</div>
                                    </div>
                                    <div class="d-flex gap-1">
                                        ${activateBtn}
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-prompt-btn"
                                            data-id="${p.id}" data-title="${escapeAttr(p.title)}" data-text="${escapeAttr(p.prompt_text)}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-prompt-btn" data-id="${p.id}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
            }
            $('#promptsListContainer').html(html);
        }

        function renderKbList(entries) {
            let html = '';
            if (!entries || entries.length === 0) {
                html = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-journal-text fs-4 d-block mb-1"></i>
                        No knowledge base entries added yet.
                    </div>`;
            } else {
                entries.forEach(function (kb) {
                    const badge = kb.is_active
                        ? '<span class="badge bg-success ms-2">Active</span>'
                        : '<span class="badge bg-secondary ms-2">Inactive</span>';

                    html += `
                        <div class="card mb-2">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                    <div>
                                        <strong>${escapeHtml(kb.title)}</strong>${badge}
                                        <div class="text-muted small mt-1">${escapeHtml(truncate(kb.content, 150))}</div>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary toggle-kb-btn" data-id="${kb.id}"
                                            title="${kb.is_active ? 'Deactivate' : 'Activate'}">
                                            <i class="bi bi-${kb.is_active ? 'toggle-on' : 'toggle-off'}"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-kb-btn"
                                            data-id="${kb.id}" data-title="${escapeAttr(kb.title)}" data-content="${escapeAttr(kb.content)}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-kb-btn" data-id="${kb.id}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
            }
            $('#kbListContainer').html(html);
        }

        function escapeHtml(text) {
            return $('<div>').text(text ?? '').html();
        }
        function escapeAttr(text) {
            return (text ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
        function truncate(text, len) {
            text = text ?? '';
            return text.length > len ? text.substring(0, len) + '...' : text;
        }

        // ══════════ PROMPT FORM ══════════
        $('#showAddPromptBtn').on('click', function (e) {
            e.preventDefault();
            $('#promptFormTitle').text('Add New Prompt');
            $('#promptFormMethod').val('POST');
            $('#promptIdInput').val('');
            $('#promptTitleInput, #promptTextInput').val('').removeClass('is-invalid');
            $('#promptTitleError, #promptTextError').text('');
            $('#promptFormCard').removeClass('d-none');
            $('html, body').animate({ scrollTop: $('#promptFormCard').offset().top - 100 }, 300);
        });

        $('#cancelPromptFormBtn').on('click', function () {
            $('#promptFormCard').addClass('d-none');
        });

        $(document).on('click', '.edit-prompt-btn', function () {
            $('#promptFormTitle').text('Edit Prompt');
            $('#promptFormMethod').val('PUT');
            $('#promptIdInput').val($(this).data('id'));
            $('#promptTitleInput').val($(this).data('title')).removeClass('is-invalid');
            $('#promptTextInput').val($(this).data('text')).removeClass('is-invalid');
            $('#promptTitleError, #promptTextError').text('');
            $('#promptFormCard').removeClass('d-none');
            $('html, body').animate({ scrollTop: $('#promptFormCard').offset().top - 100 }, 300);
        });

        $('#promptForm').on('submit', function (e) {
            e.preventDefault();
            const id = $('#promptIdInput').val();
            const isUpdate = !!id;
            const url = isUpdate ? ROUTES.promptUpdate.replace(':id', id) : ROUTES.promptStore;

            $('#promptTitleInput, #promptTextInput').removeClass('is-invalid');
            $('#promptTitleError, #promptTextError').text('');

            const btn = $('#savePromptBtn').prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _method: isUpdate ? 'PUT' : 'POST',
                    _token: CSRF_TOKEN,
                    title: $('#promptTitleInput').val(),
                    prompt_text: $('#promptTextInput').val(),
                },
                success: function (res) {
                    if (res.status) {
                        showMessage('success', res.message);
                        renderPromptsList(res.prompts);
                        $('#promptFormCard').addClass('d-none');
                    }
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        if (errors.title) { $('#promptTitleInput').addClass('is-invalid'); $('#promptTitleError').text(errors.title[0]); }
                        if (errors.prompt_text) { $('#promptTextInput').addClass('is-invalid'); $('#promptTextError').text(errors.prompt_text[0]); }
                    } else {
                        showMessage('danger', xhr.responseJSON?.message ?? 'Something went wrong.');
                    }
                },
                complete: function () {
                    btn.prop('disabled', false);
                }
            });
        });

        $(document).on('click', '.delete-prompt-btn', function () {
            if (!confirm('Delete this prompt?')) return;
            const id = $(this).data('id');

            $.ajax({
                url: ROUTES.promptDestroy.replace(':id', id),
                type: 'POST',
                data: { _method: 'DELETE', _token: CSRF_TOKEN },
                success: function (res) {
                    showMessage('success', res.message);
                    renderPromptsList(res.prompts);
                },
                error: function (xhr) {
                    showMessage('danger', xhr.responseJSON?.message ?? 'Failed to delete prompt.');
                }
            });
        });

        $(document).on('click', '.activate-prompt-btn', function () {
            const id = $(this).data('id');

            $.ajax({
                url: ROUTES.promptActivate.replace(':id', id),
                type: 'POST',
                data: { _token: CSRF_TOKEN },
                success: function (res) {
                    showMessage('success', res.message);
                    renderPromptsList(res.prompts);
                },
                error: function (xhr) {
                    showMessage('danger', xhr.responseJSON?.message ?? 'Failed to activate prompt.');
                }
            });
        });

        // ══════════ KB FORM ══════════
        $('#showAddKbBtn').on('click', function (e) {
            e.preventDefault();
            $('#kbFormTitle').text('Add New Knowledge Base Entry');
            $('#kbFormMethod').val('POST');
            $('#kbIdInput').val('');
            $('#kbTitleInput, #kbContentInput').val('').removeClass('is-invalid');
            $('#kbTitleError, #kbContentError').text('');
            $('#kbFormCard').removeClass('d-none');
            $('html, body').animate({ scrollTop: $('#kbFormCard').offset().top - 100 }, 300);
        });

        $('#cancelKbFormBtn').on('click', function () {
            $('#kbFormCard').addClass('d-none');
        });

        $(document).on('click', '.edit-kb-btn', function () {
            $('#kbFormTitle').text('Edit Knowledge Base Entry');
            $('#kbFormMethod').val('PUT');
            $('#kbIdInput').val($(this).data('id'));
            $('#kbTitleInput').val($(this).data('title')).removeClass('is-invalid');
            $('#kbContentInput').val($(this).data('content')).removeClass('is-invalid');
            $('#kbTitleError, #kbContentError').text('');
            $('#kbFormCard').removeClass('d-none');
            $('html, body').animate({ scrollTop: $('#kbFormCard').offset().top - 100 }, 300);
        });

        $('#kbForm').on('submit', function (e) {
            e.preventDefault();
            const id = $('#kbIdInput').val();
            const isUpdate = !!id;
            const url = isUpdate ? ROUTES.kbUpdate.replace(':id', id) : ROUTES.kbStore;

            $('#kbTitleInput, #kbContentInput').removeClass('is-invalid');
            $('#kbTitleError, #kbContentError').text('');

            const btn = $('#saveKbBtn').prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _method: isUpdate ? 'PUT' : 'POST',
                    _token: CSRF_TOKEN,
                    title: $('#kbTitleInput').val(),
                    content: $('#kbContentInput').val(),
                },
                success: function (res) {
                    if (res.status) {
                        showMessage('success', res.message);
                        renderKbList(res.knowledgeBases);
                        $('#kbFormCard').addClass('d-none');
                    }
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        if (errors.title) { $('#kbTitleInput').addClass('is-invalid'); $('#kbTitleError').text(errors.title[0]); }
                        if (errors.content) { $('#kbContentInput').addClass('is-invalid'); $('#kbContentError').text(errors.content[0]); }
                    } else {
                        showMessage('danger', xhr.responseJSON?.message ?? 'Something went wrong.');
                    }
                },
                complete: function () {
                    btn.prop('disabled', false);
                }
            });
        });

        $(document).on('click', '.delete-kb-btn', function () {
            if (!confirm('Delete this knowledge base entry?')) return;
            const id = $(this).data('id');

            $.ajax({
                url: ROUTES.kbDestroy.replace(':id', id),
                type: 'POST',
                data: { _method: 'DELETE', _token: CSRF_TOKEN },
                success: function (res) {
                    showMessage('success', res.message);
                    renderKbList(res.knowledgeBases);
                },
                error: function (xhr) {
                    showMessage('danger', xhr.responseJSON?.message ?? 'Failed to delete entry.');
                }
            });
        });

        $(document).on('click', '.toggle-kb-btn', function () {
            const id = $(this).data('id');

            $.ajax({
                url: ROUTES.kbToggle.replace(':id', id),
                type: 'POST',
                data: { _token: CSRF_TOKEN },
                success: function (res) {
                    showMessage('success', res.message);
                    renderKbList(res.knowledgeBases);
                },
                error: function (xhr) {
                    showMessage('danger', xhr.responseJSON?.message ?? 'Failed to update entry.');
                }
            });
        });
    });
</script>
@endpush