@extends('layouts.app')

@section('title', $campaign->name)
@section('page-title', $campaign->name)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- Campaign Info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start mb-3">
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
                            $filename = basename($campaign->media_file);
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                            $isVideo = in_array($ext, ['mp4', '3gp', 'mov', 'webm']);
                            $isAudio = in_array($ext, ['mp3', 'ogg', 'aac', 'wav']);
                        @endphp
                        <div class="card border-0 shadow-sm mb-4">
                            <div
                                class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="bi bi-paperclip me-2"></i>Media File
                                </h6>
                                <a href="{{ $mediaUrl }}" download target="_blank"
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



            {{-- Contacts (Excel Data) --}}
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
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaign->contacts as $contact)
                                <tr>
                                    <td class="text-muted small">{{ $loop->iteration }}</td>
                                    <td>{{ $contact->name }}</td>
                                    <td>{{ $contact->phone }}</td>
                                    <td>
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No contacts uploaded.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('campaigns.index', ['client_id' => $client_id]) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>

                <a href="{{ route('campaigns.edit', [
                    'campaign' => $campaign->id,
                    'client_id' => request('client_id'),
                ]) }}"
                    class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit Campaign
                </a>
            </div>

        </div>
    </div>
@endsection
