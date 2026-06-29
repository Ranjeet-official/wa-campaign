@extends('layouts.app')

@if (Auth::guard('web')->check())
    @section('title', 'Chatbot History')
    @section('page-title', 'Chatbot History')
@else
    @section('title', 'My Chatbot Conversations')
    @section('page-title', 'My Chatbot Conversations')
@endif

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
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0">{{ Auth::guard('web')->check() ? 'Chatbot Conversations' : 'My Chatbot Conversations' }}</h4>
            @if (Auth::guard('web')->check())
                <div class="d-flex gap-2">
                    <a href="{{ route('chatbot.config.edit', $client_id) }}" class="btn btn-primary px-3">
                        <i class="bi bi-sliders"></i> Chatbot Config
                    </a>
                    <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary px-3">
                        <i class="bi bi-arrow-left"></i> Back to Clients
                    </a>
                </div>
            @endif
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ Auth::guard('web')->check() ? 'Session ID' : 'Visitor' }}</th>
                                <th>Messages</th>
                                <th>Last Activity</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sessionTableBody">
                            @forelse ($sessions as $session)
                                <tr id="row-{{ $session->session_id }}">
                                    <td>{{ $loop->iteration + ($sessions->currentPage() - 1) * $sessions->perPage() }}</td>
                                    <td>
                                        @if (Auth::guard('web')->check())
                                            <code>{{ Str::limit($session->session_id, 25) }}</code>
                                        @else
                                            <span class="fw-semibold">{{ $session->user_name ?? 'Guest Visitor' }}</span>
                                            @if ($session->user_email)
                                                <div class="text-muted small">{{ $session->user_email }}</div>
                                            @endif
                                        @endif
                                    </td>
                                    <td><span class="badge bg-primary">{{ $session->message_count }}</span></td>
                                    <td class="text-muted small">
                                        {{ \Carbon\Carbon::parse($session->last_message_at)->format('d M Y, h:i A') }}
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            @if (Auth::guard('web')->check())
                                                <a href="{{ route('admin.chatbot.history.show', [$client_id, $session->session_id]) }}"
                                                    class="btn btn-sm btn-outline-primary" title="View">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="{{ route('admin.chatbot.history.download', [$client_id, $session->session_id]) }}"
                                                    class="btn btn-sm" title="Download Chat"
                                                    style="background-color: #6c5ce7; border-color: #6c5ce7; color: #fff;">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('client.chatbot.show', $session->session_id) }}"
                                                    class="btn btn-sm btn-outline-primary" title="View">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="{{ route('client.chatbot.download', $session->session_id) }}"
                                                    class="btn btn-sm" title="Download Chat"
                                                    style="background-color: #6c5ce7; border-color: #6c5ce7; color: #fff;">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-chat-square-text fs-4 d-block mb-1"></i>
                                        No conversations found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted">
                    Showing {{ $sessions->firstItem() ?? 0 }}–{{ $sessions->lastItem() ?? 0 }} of
                    {{ $sessions->total() }} conversations
                </small>
                @if ($sessions->hasPages())
                    {{ $sessions->withQueryString()->links('vendor.pagination.simple-bootstrap-5') }}
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let flash = sessionStorage.getItem('flashMessage');
            if (flash) {
                $('#ajaxMessage').html(`
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-1"></i> ${flash}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                setTimeout(() => $('#ajaxMessage .alert').alert('close'), 4000);
                sessionStorage.removeItem('flashMessage');
            }

            setTimeout(() => {
                $('#ajaxMessage .alert').alert('close');
            }, 4000);
        });
    </script>
@endpush