@extends('layouts.app')

@section('title', 'Campaigns')
@section('page-title', 'Campaigns')

@section('content')

    <div class="container mt-4">


        <div id="ajaxMessage"></div>
        @if (request()->has('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ request('success') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif


        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted mb-0">Total: {{ $campaigns->total() }} campaigns</p>

            <a href="{{ route('campaigns.create', [
                'client_id' => request('client_id'),
            ]) }}"
                class="btn btn-primary px-1">
                <i class="bi bi-plus-lg"></i>Add Campaigns
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Name</th>
                            <th>Message</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Clients</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $campaign)
                            <tr>
                                <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $campaign->name }}</td>
                                <td class="text-muted small" style="max-width:180px;">
                                    {{ $campaign->message ? Str::limit($campaign->message, 50) : '-' }}
                                </td>
                                <td class="small">{{ $campaign->start_date->format('d M Y') }}</td>
                                <td class="small">{{ $campaign->end_date->format('d M Y') }}</td>
                                <td>
                                    <span class="badge bg-secondary rounded-pill">
                                        {{ $campaign->client->name ?? 'N/A' }}
                                    </span>
                                </td>

                                <td class="text-end pe-3">

                                    <button type="button" class="btn btn-sm btn-outline-warning sendCampaign"
                                        data-id="{{ $campaign->id }}" title="Send Campaign">
                                        <i class="bi bi-send"></i>
                                    </button>
                                    <a href="{{ route('campaigns.show', ['campaign' => $campaign->id, 'client_id' => request('client_id')]) }}"
                                        class="btn btn-sm btn-outline-secondary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('campaigns.export', $campaign->id) }}"
                                        class="btn btn-sm btn-success">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <a href="{{ route('campaigns.edit', [
                                        'campaign' => $campaign->id,
                                        'client_id' => request('client_id'),
                                    ]) }}"
                                        class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form
                                        action="{{ route('campaigns.destroy', [
                                            'campaign' => $campaign->id,
                                            'client_id' => request('client_id'),
                                        ]) }}"
                                        method="POST" class="d-inline" onsubmit="return confirm('Delete this campaign?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger ms-1" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-megaphone fs-3 d-block mb-2"></i>
                                    No campaigns found. <a href="{{ route('campaigns.create') }}">Create one</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($campaigns->hasPages())
            <div class="mt-3 d-flex justify-content-end">
                {{ $campaigns->links() }}
            </div>
        @endif

    @endsection



    @push('scripts')
        <script>
            $(document).ready(function() {

                let flash = sessionStorage.getItem('flashMessage');

                if (flash) {

                    $('#ajaxMessage').html(`
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-1"></i>
                ${flash}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
                    setTimeout(() => {
                        $('.alert').alert('close');
                    }, 4000);

                    sessionStorage.removeItem('flashMessage');
                }

            });

            function showAlert(type, message) {
                $('#ajaxMessage').html(`
        <div class="alert alert-${type} alert-dismissible fade show">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-1"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
                setTimeout(() => $('.alert').alert('close'), 5000);
            }

            $(document).on('click', '.sendCampaign', function() {
                if (!confirm('Are you sure you want to send this campaign?')) return;

                const id = $(this).data('id');
                const btn = $(this);

                btn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span>');

                $.ajax({
                    url: '/wa/campaigns/' + id + '/send',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        btn.prop('disabled', false)
                            .html('<i class="bi bi-send"></i>');

                        if (res.status) {
                            showAlert('success', res.message);
                            $('#status-' + id)
                                .removeClass()
                                .addClass('badge bg-success')
                                .text('Running');
                        } else {
                            showAlert('danger', res.message);
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false)
                            .html('<i class="bi bi-send"></i>');
                        showAlert('danger', 'Something went wrong. Please try again.');
                    }
                });
            });
        </script>
    @endpush
