@extends('layouts.app')

@section('title', 'Templates')
@section('page-title', 'Templates')

@section('content')
    <div class="container mt-4">

        {{-- ALERTS --}}
        <div id="ajaxMessage">
            @if (session('success') || request('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-1"></i>
                    {{ session('success') ?? request('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error') || request('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-x-circle me-1"></i>
                    {{ session('error') ?? request('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Template List</h4>
            <a href="{{ route('templates.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Template
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Template Name</th>
                                <th>Client</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="templateTableBody">
                            @forelse ($templates as $template)
                                <tr id="row-{{ $template->id }}">
                                    <td>{{ $loop->iteration + ($templates->currentPage() - 1) * $templates->perPage() }}
                                    </td>
                                    <td class="fw-semibold">{{ $template->name }}</td>
                                    <td>
                                        @if ($template->client)
                                            {{ $template->client->name }}
                                            <div class="text-muted small">{{ $template->client->company }}</div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
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
                                    <td class="text-muted small">{{ $template->created_at->format('d M Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('templates.show', $template) }}"
                                            class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('templates.edit', $template) }}"
                                            class="btn btn-sm btn-outline-primary ms-1" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger ms-1 deleteTemplate" title="Delete"
                                            data-id="{{ $template->id }}" data-name="{{ $template->name }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi bi-file-earmark-text fs-4 d-block mb-1"></i>
                                        No templates found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($templates->hasPages())
                <div class="card-footer bg-white">
                    {{ $templates->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            function showAlert(type, message) {
                const icon = type === 'success' ? 'check-circle' : 'x-circle';
                $('#ajaxMessage').html(`
                    <div class="alert alert-${type} alert-dismissible fade show">
                        <i class="bi bi-${icon} me-1"></i> ${message}
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 3000);
            }

            $(document).on('click', '.deleteTemplate', function() {

                if (!confirm('Are you sure you want to delete this template?')) return;

                const id = $(this).data('id');
                const btn = $(this);

                btn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span>');

                $.ajax({
                    url: '/wa/templates/' + id,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.status) {
                            $('#row-' + id).remove();
                            showAlert('success', res.message);

                            // re-number rows
                            $('#templateTableBody tr').each(function(i) {
                                $(this).find('td:first').text(i + 1);
                            });

                        } else {
                            showAlert('danger', res.message);
                            btn.prop('disabled', false)
                                .html('<i class="bi bi-trash"></i>');
                        }
                    },
                    error: function(xhr) {

                        let message = 'Delete failed.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        showAlert('danger', message);

                        btn.prop('disabled', false)
                            .html('<i class="bi bi-trash"></i>');
                    }
                });
            });

        });
    </script>
@endpush
