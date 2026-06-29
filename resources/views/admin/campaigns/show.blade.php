@extends('layouts.app')

@section('title', $campaign->name)
@section('page-title', $campaign->name)

@section('content')

    <div id="ajaxMessage"></div>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">

            {{-- Campaign Info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3 p-md-4">

                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                        <div>
                            <h4 class="fw-bold mb-1">{{ $campaign->name }}</h4>
                            <small class="text-muted">Campaign Overview</small>
                        </div>
                        @if ($campaign->status === 'completed')
                            <span class="badge bg-success-subtle text-success border border-success-subtle"
                                id="status-{{ $campaign->id }}">Completed</span>
                        @elseif($campaign->status === 'running')
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle"
                                id="status-{{ $campaign->id }}">Running</span>
                        @elseif($campaign->status === 'partial')
                            <span class="badge bg-info-subtle text-info border border-info-subtle"
                                id="status-{{ $campaign->id }}">Partial</span>
                        @elseif($campaign->status === 'failed')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"
                                id="status-{{ $campaign->id }}">Failed</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"
                                id="status-{{ $campaign->id }}">Draft</span>
                        @endif
                    </div>

                    <p class="mb-0 text-muted">
                        {!! $campaign->message ? nl2br(e($campaign->message)) : 'No message' !!}
                    </p>

                    {{-- Media File --}}
                    @if ($campaign->media_file)
                        @php
                            $ext = strtolower(pathinfo($campaign->media_file, PATHINFO_EXTENSION));
                            $mediaUrl = asset('storage/' . $campaign->media_file);
                            $filename = $campaign->media_original_name ?? basename($campaign->media_file);
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                            $isVideo = in_array($ext, ['mp4', '3gp', 'mov', 'webm']);
                            $isAudio = in_array($ext, ['mp3', 'ogg', 'aac', 'wav']);
                        @endphp
                        <div class="card border-0 shadow-sm mb-4 mt-3">
                            <div
                                class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="bi bi-paperclip me-2"></i>Media File
                                </h6>
                                <a href="{{ $mediaUrl }}" download="{{ $filename }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            </div>
                            <div class="card-body p-0">
                                @if ($isImage)
                                    <img src="{{ $mediaUrl }}" alt="Campaign Media" class="img-fluid w-100"
                                        style="max-height:340px; object-fit:contain; background:#f8fafc;">
                                @elseif($isVideo)
                                    <video controls class="w-100"
                                        style="max-height:340px; background:#000; display:block;">
                                        <source src="{{ $mediaUrl }}" type="video/{{ $ext }}">
                                        Your browser does not support video playback.
                                    </video>
                                @elseif($isAudio)
                                    <div class="p-4 bg-light">
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <i class="bi bi-music-note-beamed fs-3 text-info"></i>
                                            <div>
                                                <div class="fw-semibold">{{ $filename }}</div>
                                                <small class="text-muted">{{ strtoupper($ext) }} Audio File</small>
                                            </div>
                                        </div>
                                        <audio controls class="w-100">
                                            <source src="{{ $mediaUrl }}" type="audio/{{ $ext }}">
                                            Your browser does not support audio playback.
                                        </audio>
                                    </div>
                                @else
                                    <div class="p-4 bg-light text-center">
                                        @if ($ext === 'pdf')
                                            <i class="bi bi-file-earmark-pdf text-danger" style="font-size:3rem;"></i>
                                        @elseif(in_array($ext, ['xls', 'xlsx']))
                                            <i class="bi bi-file-earmark-excel text-success" style="font-size:3rem;"></i>
                                        @elseif(in_array($ext, ['doc', 'docx']))
                                            <i class="bi bi-file-earmark-word text-primary" style="font-size:3rem;"></i>
                                        @else
                                            <i class="bi bi-file-earmark-arrow-down text-secondary"
                                                style="font-size:3rem;"></i>
                                        @endif
                                        <div class="fw-semibold mt-2">{{ $filename }}</div>
                                        <small class="text-muted">{{ strtoupper($ext) }} File</small>
                                        <div class="mt-3">
                                            <a href="{{ $mediaUrl }}" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>Open File
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            @php
                $sentCount = $campaign->contacts->where('status', 'sent')->count();
                $failedCount = $campaign->contacts->where('status', 'failed')->count();
                $pendingCount = $campaign->contacts->where('status', 'pending')->count();
                $totalCount = $campaign->contacts->count();
            @endphp

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <div class="fs-3 fw-bold text-secondary">{{ $totalCount }}</div>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <div class="fs-3 fw-bold text-success">{{ $sentCount }}</div>
                        <small class="text-muted">Sent</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <div class="fs-3 fw-bold text-danger">{{ $failedCount }}</div>
                        <small class="text-muted">Failed</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <div class="fs-3 fw-bold text-warning">{{ $pendingCount }}</div>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>

            {{-- Contacts --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i>Campaign Contacts
                    </h6>
                    <span class="badge bg-secondary">{{ $campaign->contacts->count() ?? 0 }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>WhatsApp</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaign->contacts as $contact)
                                <tr>
                                    <td class="text-muted small">{{ $loop->iteration }}</td>
                                    <td>{{ $contact->name }}</td>
                                    <td>{{ $contact->phone }}</td>
                                    <td id="contact-status-{{ $contact->id }}">
                                        @if (($contact->status ?? 'pending') === 'sent')
                                            <span
                                                class="badge bg-success-subtle text-success border border-success-subtle">Sent</span>
                                        @elseif(($contact->status ?? 'pending') === 'failed')
                                            <span
                                                class="badge bg-danger-subtle text-danger border border-danger-subtle">Failed</span>
                                        @else
                                            <span
                                                class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($campaign->status !== 'draft' && ($contact->status ?? 'pending') !== 'sent')
                                            <button class="btn btn-sm btn-outline-warning retrySend"
                                                data-campaign="{{ $campaign->id }}" data-contact="{{ $contact->id }}"
                                                data-url="{{ Auth::guard('web')->check()
                                                    ? route('campaigns.sendSingle', [$campaign->id, $contact->id])
                                                    : route('client.campaigns.sendSingle', [$campaign->id, $contact->id]) }}">
                                                <i class="bi bi-arrow-clockwise"></i> Retry
                                            </button>
                                        @elseif ($campaign->status === 'draft')
                                            <span class="text-muted small">Not sent yet</span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No contacts uploaded.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4">

                {{-- Back Button --}}
                @if (Auth::guard('web')->check())
                    <a href="{{ route('campaigns.index', ['client_id' => $client_id]) }}"
                        class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                @else
                    <a href="{{ route('client.campaigns.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                @endif

                {{-- Edit Button --}}
                @if (in_array(strtolower($campaign->status), ['completed', 'partial', 'running']))
                    <button class="btn btn-primary" disabled title="Completed or partial campaigns cannot be edited">
                        <i class="bi bi-pencil me-1"></i> Edit Campaign
                    </button>
                @else
                    @if (Auth::guard('web')->check())
                        <a href="{{ route('campaigns.edit', ['campaign' => $campaign->id, 'client_id' => request('client_id')]) }}"
                            class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i> Edit Campaign
                        </a>
                    @else
                        <a href="{{ route('client.campaigns.edit', $campaign->id) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i> Edit Campaign
                        </a>
                    @endif
                @endif

            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).on('click', '.retrySend', function() {
            const btn = $(this);
            const contactId = btn.data('contact');
            const url = btn.data('url');

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.status) {
                        $('#contact-status-' + contactId).html(
                            '<span class="badge bg-success-subtle text-success border border-success-subtle">Sent</span>'
                        );
                        btn.closest('td').html('<span class="text-muted small">—</span>');
                        showAlert('success', res.message);
                    } else {
                        btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i> Retry');
                        showAlert('danger', res.message);
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i> Retry');
                    showAlert('danger', 'Something went wrong. Please try again.');
                }
            });
        });

        function showAlert(type, message) {
            $('#ajaxMessage').html(`
                <div class="alert alert-${type} alert-dismissible fade show">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-1"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            setTimeout(() => $('.alert').alert('close'), 4000);
        }
    </script>
@endpush
