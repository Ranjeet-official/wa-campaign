@extends('layouts.app')
@section('title', $template->name)
@section('page-title', $template->name)

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3 p-md-4">

                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                        <div>
                            <h4 class="fw-bold mb-1">{{ $template->name }}</h4>
                            <small class="text-muted">Template Overview</small>
                        </div>
                        @if ($template->status === 'approved')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Approved</span>
                        @elseif ($template->status === 'rejected')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                        @endif
                    </div>

                    <div class="row g-3 mb-3">

                        {{-- Client — sirf admin ko dikhao --}}
                        @if (Auth::guard('web')->check())
                            <div class="col-12 col-md-6">
                                <p class="text-muted small mb-1 fw-semibold text-uppercase">Client</p>
                                @if ($template->client)
                                    <p class="mb-0 fw-semibold">{{ $template->client->name }}</p>
                                    <p class="mb-0 text-muted small">{{ $template->client->company }}</p>
                                @else
                                    <p class="mb-0 text-muted">—</p>
                                @endif
                            </div>
                        @endif

                        <div class="col-12 col-md-6">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">Category</p>
                            <p class="mb-0 fw-semibold">{{ ucfirst(strtolower($template->category)) }}</p>
                        </div>

                        <div class="col-12 col-md-6">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">Language</p>
                            <p class="mb-0 fw-semibold">{{ $template->language }}</p>
                        </div>

                        <div class="col-12 col-md-6">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">Created At</p>
                            <p class="mb-0">{{ $template->created_at->format('d M Y, h:i A') }}</p>
                        </div>

                        @if ($template->approved_at)
                            <div class="col-12 col-md-6">
                                <p class="text-muted small mb-1 fw-semibold text-uppercase">Approved At</p>
                                <p class="mb-0 text-success">
                                    {{ \Carbon\Carbon::parse($template->approved_at)->format('d M Y, h:i A') }}
                                </p>
                            </div>
                        @endif

                    </div>

                    <hr>

                    <p class="text-muted small mb-1 fw-semibold text-uppercase">Message</p>
                    <div class="bg-light rounded p-3" style="font-size: 0.95rem; line-height: 1.6;">
                        {!! nl2br(e(trim($template->message))) !!}
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4">

                {{-- Back button --}}
                @if (Auth::guard('web')->check())
                    <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                @else
                    <a href="{{ route('client.templates.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                @endif

                {{-- Edit button — sirf admin --}}
                @if (Auth::guard('web')->check())
                    @if ($template->status == 'approved')
                        <button class="btn btn-primary" disabled title="Approved templates cannot be edited">
                            <i class="bi bi-pencil me-1"></i> Edit Template
                        </button>
                    @else
                        <a href="{{ route('templates.edit', $template) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i> Edit Template
                        </a>
                    @endif
                @endif

            </div>

        </div>
    </div>
@endsection
