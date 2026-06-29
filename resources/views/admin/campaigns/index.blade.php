@extends('layouts.app')

@if (Auth::guard('web')->check())
    @section('title', 'Campaigns')
    @section('page-title', 'Campaigns')
@else
    @section('title', 'My Campaigns')
    @section('page-title', 'My Campaigns')
@endif

@section('content')
    <div class="container mt-4">

        <div id="ajaxMessage"></div>
        @if (request()->has('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ request('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <p class="text-muted mb-0">Total: {{ $campaigns->total() }} campaigns</p>
            @if (Auth::guard('web')->check())
                <a href="{{ route('campaigns.create', ['client_id' => request('client_id')]) }}" class="btn btn-primary px-1">
                    <i class="bi bi-plus-lg"></i> Add Campaign
                </a>
            @else
                <a href="{{ route('client.campaigns.create') }}" class="btn btn-primary px-1">
                    <i class="bi bi-plus-lg"></i> Add Campaign
                </a>
            @endif
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Name</th>
                                <th>Message</th>
                                <th style="white-space: nowrap">Start Date</th>
                                <th style="white-space: nowrap">End Date</th>
                                @if (Auth::guard('web')->check())
                                    <th>Client</th>
                                @endif
                                <th>Contacts</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $campaign)
                                @php
                                    $isDisabled = in_array($campaign->status, ['running', 'completed', 'partial']);
                                @endphp
                                <tr>
                                    <td class="ps-3 text-muted small">
                                        {{ ($campaigns->currentPage() - 1) * $campaigns->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="fw-semibold">{{ $campaign->name }}</td>




                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 250px;"
                                            title="{{ $campaign->message }}">
                                            {{ $campaign->message ? Str::limit($campaign->message, 50) : '-' }}
                                        </span>
                                    </td>
                                    <td class="small" style="white-space: nowrap">
                                        {{ $campaign->start_date->format('d M Y') }}</td>
                                    <td class="small" style="white-space: nowrap">
                                        {{ $campaign->end_date->format('d M Y') }}</td>

                                    @if (Auth::guard('web')->check())
                                        <td>
                                            <span class="badge bg-secondary rounded-pill">
                                                {{ $campaign->client->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                    @endif

                                    <td class="small text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            {{ $campaign->total_contacts ?? 0 }}
                                        </span>
                                    </td>

                                    <td>
                                        @php
                                            $statusConfig = match ($campaign->status) {
                                                'completed' => [
                                                    'class' =>
                                                        'bg-success-subtle text-success border border-success-subtle',
                                                    'label' => 'Completed',
                                                ],
                                                'running' => [
                                                    'class' =>
                                                        'bg-warning-subtle text-warning border border-warning-subtle',
                                                    'label' => 'Running',
                                                ],
                                                'partial' => [
                                                    'class' => 'bg-info-subtle text-info border border-info-subtle',
                                                    'label' => 'Partial',
                                                ],
                                                'failed' => [
                                                    'class' =>
                                                        'bg-danger-subtle text-danger border border-danger-subtle',
                                                    'label' => 'Failed',
                                                ],
                                                default => [
                                                    'class' =>
                                                        'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                                    'label' => 'Draft',
                                                ],
                                            };
                                        @endphp
                                        <span class="badge {{ $statusConfig['class'] }}" id="status-{{ $campaign->id }}">
                                            {{ $statusConfig['label'] }}
                                        </span>
                                    </td>

                                    <td class="text-end pe-3" id="actions-{{ $campaign->id }}">
                                        <div class="d-flex gap-1 justify-content-end flex-wrap">
                                            @php
                                                $isCompleted = $campaign->status === 'completed';
                                            @endphp
                                            {{-- Run --}}
                                            <button type="button" class="btn btn-sm btn-outline-warning sendCampaign"
                                                data-id="{{ $campaign->id }}"
                                                data-url="{{ Auth::guard('web')->check()
                                                    ? route('campaigns.send', $campaign->id)
                                                    : route('client.campaigns.send', $campaign->id) }}"
                                                title="Run Campaign" {{ $isCompleted ? 'disabled' : '' }}>
                                                <i class="bi bi-send"></i>
                                            </button>

                                            {{-- View --}}
                                            @if (Auth::guard('web')->check())
                                                <a href="{{ route('campaigns.show', ['campaign' => $campaign->id, 'client_id' => request('client_id')]) }}"
                                                    class="btn btn-sm btn-outline-secondary" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('client.campaigns.show', $campaign->id) }}"
                                                    class="btn btn-sm btn-outline-secondary" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endif

                                            {{-- Export --}}
                                            @if (Auth::guard('web')->check())
                                                <a href="{{ route('campaigns.export', $campaign->id) }}"
                                                    class="btn btn-sm btn-success {{ $isDisabled ? 'disabled pe-none' : '' }}">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('client.campaigns.export', $campaign->id) }}"
                                                    class="btn btn-sm btn-success {{ $isDisabled ? 'disabled pe-none' : '' }}">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            @endif

                                            {{-- Edit --}}
                                            @if (Auth::guard('web')->check())
                                                <a href="{{ route('campaigns.edit', ['campaign' => $campaign->id, 'client_id' => request('client_id')]) }}"
                                                    class="btn btn-sm btn-outline-primary editCampaign {{ $isDisabled ? 'disabled pe-none' : '' }}"
                                                    title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('client.campaigns.edit', $campaign->id) }}"
                                                    class="btn btn-sm btn-outline-primary editCampaign {{ $isDisabled ? 'disabled pe-none' : '' }}"
                                                    title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif

                                            {{-- Delete --}}
                                            @if (Auth::guard('web')->check())
                                                <form
                                                    action="{{ route('campaigns.destroy', ['campaign' => $campaign->id, 'client_id' => request('client_id')]) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Delete this campaign?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        {{ $isDisabled ? 'disabled' : '' }} title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('client.campaigns.destroy', $campaign->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Delete this campaign?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        {{ $isDisabled ? 'disabled' : '' }} title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="bi bi-megaphone fs-3 d-block mb-2"></i>
                                        No campaigns found.
                                        @if (Auth::guard('web')->check())
                                            <a href="{{ route('campaigns.create') }}">Create one</a>
                                        @else
                                            <a href="{{ route('client.campaigns.create') }}">Create one</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
<br>
        <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-4">
            <small class="text-muted">
                Showing {{ $campaigns->firstItem() ?? 0 }}–{{ $campaigns->lastItem() ?? 0 }} of
                {{ $campaigns->total() }} campaigns
            </small>
            @if ($campaigns->hasPages())
                {{ $campaigns->withQueryString()->links('vendor.pagination.simple-bootstrap-5') }}
            @endif
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
                        <i class="bi bi-check-circle me-1"></i>
                        ${flash}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                setTimeout(() => $('.alert').alert('close'), 4000);
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

        function disableAllButtons(id) {
            const actions = $('#actions-' + id);
            actions.find('.sendCampaign').prop('disabled', true);
            actions.find('a:not(.btn-outline-secondary)').addClass('disabled pe-none');
            actions.find('button.btn-outline-danger').prop('disabled', true);
        }

        function disablePartial(id) {
            const actions = $('#actions-' + id);
            actions.find('a:not(.btn-outline-secondary)').addClass('disabled pe-none'); // Edit + Export
            actions.find('button.btn-outline-danger').prop('disabled', true); // Delete
            // Run intentionally chhod diya — enabled rahega
        }


        function enableEditDelete(id) {
            const actions = $('#actions-' + id);
            actions.find('.btn-outline-primary').removeClass('disabled pe-none');
            actions.find('.editCampaign').removeClass('disabled pe-none');
            actions.find('button.btn-outline-danger').prop('disabled', false);
        }

        $(document).on('click', '.sendCampaign', function() {
            if (!confirm('Are you sure you want to send this campaign?')) return;

            const id = $(this).data('id');
            const url = $(this).data('url');
            const btn = $(this);

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {

                    btn.prop('disabled', true);

                    // Edit disable
                    $('#actions-' + id)
                        .find('.btn-outline-primary')
                        .addClass('disabled pe-none');
                    $('#actions-' + id)
                        .find('.editCampaign')
                        .addClass('disabled pe-none');

                    // ✅ Delete disable (running ke time bhi delete na ho paaye)
                    $('#actions-' + id)
                        .find('button.btn-outline-danger')
                        .prop('disabled', true);

                    $('#status-' + id)
                        .removeClass()
                        .addClass('badge bg-warning-subtle text-warning border border-warning-subtle')
                        .text('Running...');
                },
                success: function(res) {
                    btn.html('<i class="bi bi-send"></i>');
                    if (res.status) {
                        showAlert('success', res.message);
                        const badgeColors = {
                            'completed': 'bg-success-subtle text-success border border-success-subtle',
                            'failed': 'bg-danger-subtle text-danger border border-danger-subtle',
                            'partial': 'bg-info-subtle text-info border border-info-subtle',
                            'draft': 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                        };
                        $('#status-' + id)
                            .removeClass()
                            .addClass('badge ' + (badgeColors[res.campaign_status] ??
                                'bg-secondary-subtle text-secondary border border-secondary-subtle'
                            ))
                            .text(res.campaign_status.charAt(0).toUpperCase() + res.campaign_status
                                .slice(1));

                        if (res.campaign_status === 'completed') {
                            disableAllButtons(id); // Run bhi disable
                        } else if (res.campaign_status === 'partial') {
                            disablePartial(id); // Edit/Delete/Export disable, Run enabled
                            btn.prop('disabled', false);
                        } else {
                            btn.prop('disabled', false);
                            enableEditDelete(id);
                        }
                    } else {
                        btn.prop('disabled', false);
                        enableEditDelete(id);
                        showAlert('danger', res.message);
                        $('#status-' + id)
                            .removeClass()
                            .addClass(
                                'badge bg-secondary-subtle text-secondary border border-secondary-subtle'
                            )
                            .text('Draft');
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-send"></i>');
                    showAlert('danger', 'Something went wrong. Please try again.');

                    enableEditDelete(id); // ✅ Edit + Delete dono enable (pehle sirf Delete hota tha)

                    $('#status-' + id)
                        .removeClass()
                        .addClass(
                            'badge bg-secondary-subtle text-secondary border border-secondary-subtle')
                        .text('Draft');
                }
            });
        });
    </script>
@endpush
