@extends('layouts.app')

@if (Auth::guard('web')->check())
    @section('title', 'Templates')
    @section('page-title', 'Templates')
@else
    @section('title', 'My Templates')
    @section('page-title', 'My Templates')
@endif

@section('content')
    <div class="container mt-4">
        {{-- ALERTS --}}
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

        {{-- HEADER --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0">Template List</h4>
            @if (Auth::guard('web')->check())
                <a href="{{ route('templates.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Add Template
                </a>
            @else
                <a href="{{ route('client.templates.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Add Template
                </a>
            @endif
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Template Name</th>
                                @if (Auth::guard('web')->check())
                                    <th>Client</th>
                                @endif
                                <th>Message</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Approved At</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="templateTableBody">
                            @forelse ($templates as $template)
                                <tr id="row-{{ $template->id }}">
                                    <td>{{ $loop->iteration + ($templates->currentPage() - 1) * $templates->perPage() }}
                                    </td>
                                    <td class="fw-semibold">{{ $template->name }}</td>

                                    @if (Auth::guard('web')->check())
                                        <td>
                                            @if ($template->client)
                                                {{ $template->client->name }}
                                                <div class="text-muted small">{{ $template->client->company }}</div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endif

                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 250px;"
                                            title="{{ $template->message }}">
                                            {{ $template->message }}
                                        </span>
                                    </td>

                                    <td>
                                        @php
                                            $badge = match ($template->status) {
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                default => 'warning',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badge }}">{{ ucfirst($template->status) }}</span>
                                    </td>

                                    <td class="text-muted small">{{ $template->created_at->format('d M Y, h:i A') }}</td>

                                    <td>
                                        @if ($template->approved_at)
                                            <span class="text-success small">
                                                {{ \Carbon\Carbon::parse($template->approved_at)->format('d M Y, h:i A') }}
                                            </span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            {{-- View --}}
                                            @if (Auth::guard('web')->check())
                                                <a href="{{ route('templates.show', $template) }}"
                                                    class="btn btn-sm btn-outline-secondary" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('client.templates.show', $template) }}"
                                                    class="btn btn-sm btn-outline-secondary" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endif

                                            {{-- Edit --}}
                                            @if ($template->status == 'approved')
                                                <button class="btn btn-sm btn-outline-primary" disabled
                                                    title="Approved templates cannot be edited">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            @else
                                                @if (Auth::guard('web')->check())
                                                    <a href="{{ route('templates.edit', $template) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('client.templates.edit', $template) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endif
                                            @endif

                                            {{-- Delete --}}
                                            @if (Auth::guard('web')->check())
                                                <button class="btn btn-sm btn-outline-danger deleteTemplate" title="Delete"
                                                    data-id="{{ $template->id }}"
                                                    data-url="{{ url('/wa/templates/' . $template->id) }}"
                                                    data-name="{{ $template->name }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline-danger deleteTemplate" title="Delete"
                                                    data-id="{{ $template->id }}"
                                                    data-url="{{ url('/wa/client/templates/' . $template->id) }}"
                                                    data-name="{{ $template->name }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::guard('web')->check() ? 8 : 7 }}"
                                        class="text-center text-muted py-4">
                                        <i class="bi bi-file-earmark-text fs-4 d-block mb-1"></i>
                                        No templates found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-4">
                <small class="text-muted">
                    Showing {{ $templates->firstItem() ?? 0 }}–{{ $templates->lastItem() ?? 0 }} of
                    {{ $templates->total() }} templates
                </small>
                @if ($templates->hasPages())
                    {{ $templates->withQueryString()->links('vendor.pagination.simple-bootstrap-5') }}
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

            // ✅ YE ADD KARO — missing tha
            function showAlert(type, message) {
                const icon = type === 'success' ? 'check-circle' : 'x-circle';
                $('#ajaxMessage').html(`
            <div class="alert alert-${type} alert-dismissible fade show">
                <i class="bi bi-${icon} me-1"></i> ${message}
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
                setTimeout(() => {
                    $('#ajaxMessage .alert').alert('close');
                }, 4000);
            }

            $(document).on('click', '.deleteTemplate', function() {
                if (!confirm('Are you sure you want to delete this template?')) return;

                const id = $(this).data('id');
                const url = $(this).data('url');
                const btn = $(this);

                btn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span>');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.status) {
                            $('#row-' + id).remove();
                            showAlert('success', res.message);
                            const offset =
                                {{ ($templates->currentPage() - 1) * $templates->perPage() }};
                            $('#templateTableBody tr').each(function(i) {
                                $(this).find('td:first').text(offset + i + 1);
                            });
                        } else {
                            showAlert('danger', res.message);
                            btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                        }
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON?.message ?? 'Delete failed.';
                        showAlert('danger', message);
                        btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                    }
                });
            });

        });
    </script>
@endpush
