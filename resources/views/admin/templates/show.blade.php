@extends('layouts.app')

@section('title', $template->name)
@section('page-title', $template->name)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- Template Info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start mb-3">
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

                    {{-- Meta Info --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">Client</p>
                            @if ($template->client)
                                <p class="mb-0 fw-semibold">{{ $template->client->name }}</p>
                                <p class="mb-0 text-muted small">{{ $template->client->company }}</p>
                            @else
                                <p class="mb-0 text-muted">—</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">Created At</p>
                            <p class="mb-0">{{ $template->created_at->format('d M Y, h:i A') }}</p>
                        </div>
                    </div>

                    <hr>

                    {{-- Message --}}
                    <p class="text-muted small mb-1 fw-semibold text-uppercase">Message</p>
                    <div class="bg-light rounded p-3" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.6;">{{ $template->message }}</div>

                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
                <a href="{{ route('templates.edit', $template) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit Template
                </a>
            </div>

        </div>
    </div>
@endsection
