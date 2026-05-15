@extends('layouts.app')


@section('title', 'Templates')
@section('page-title', 'Templates')

@section('content')
    <div class="container mt-4">

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

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            {{-- <p class="text-muted mb-0">Total: {{ $templates->total() }} templates</p> --}}
            <a href="{{ route('templates.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Template
            </a>
        </div>

        {{-- Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Message</th>
                            <th>Media</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="templateTableBody">
                        @forelse($templates as $template)
                            <tr id="row-{{ $template->id }}">
                                <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $template->name }}</td>
                                <td>
                                    @php
                                        $typeIcons = [
                                            'text' => 'bi-chat-text text-primary',
                                            'image' => 'bi-image text-success',
                                            'video' => 'bi-camera-video text-danger',
                                            'document' => 'bi-file-earmark text-warning',
                                            'audio' => 'bi-music-note text-info',
                                        ];
                                    @endphp
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi {{ $typeIcons[$template->type] ?? 'bi-file' }} me-1"></i>
                                        {{ ucfirst($template->type) }}
                                    </span>
                                </td>
                                <td class="text-muted small" style="max-width:200px;">
                                    {{ $template->message ? Str::limit($template->message, 50) : '-' }}
                                </td>
                                <td>
                                    @if ($template->media_file)
                                        <a href="{{ asset('storage/' . $template->media_file) }}" target="_blank"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($template->status === 'active')
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                    @else
                                        <span
                                            class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('templates.edit', $template->id) }}"
                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger deleteTemplate"
                                            data-id="{{ $template->id }}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="noTemplatesRow">
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-file-earmark-text fs-1 d-block mb-2 opacity-25"></i>
                                    No templates found.
                                    <a href="{{ route('templates.create') }}">Add one?</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($templates->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Showing {{ $templates->firstItem() }}–{{ $templates->lastItem() }} of {{ $templates->total() }}
                    </small>
                    {{ $templates->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            setTimeout(() => {
                $('#ajaxMessage .alert').alert('close');
            }, 4000);

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
                const btn = $(this);

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                $.ajax({
                    url: '/wa/templates/' + id,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.status) {
                            $('#row-' + id).fadeOut(300, function() {
                                $(this).remove();
                                if ($('#templateTableBody tr').length === 0) {
                                    $('#templateTableBody').append(`
                                <tr id="noTemplatesRow">
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-file-earmark-text fs-1 d-block mb-2 opacity-25"></i>
                                        No templates found.
                                    </td>
                                </tr>
                            `);
                                }
                                $('#templateTableBody tr').each(function(i) {
                                    $(this).find('td:first').text(i + 1);
                                });
                            });
                            showAlert('success', res.message);
                        } else {
                            showAlert('danger', res.message);
                            btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                        }
                    },
                    error: function() {
                        showAlert('danger', 'Delete failed. Please try again.');
                        btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                    }
                });
            });

        });
    </script>
