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

                        @php
                            $badges = [
                                'pending' => 'warning',
                                'active' => 'success',
                                'completed' => 'primary',
                                'cancelled' => 'danger',
                            ];
                        @endphp

                        <span class="badge bg-{{ $badges[$campaign->status] ?? 'secondary' }} fs-6 px-3 py-2">
                            {{ ucfirst($campaign->status) }}
                        </span>
                    </div>

                    <p class="mb-0 text-muted">
                        {!! $campaign->message ? nl2br(e($campaign->message)) : 'No message' !!}
                    </p>



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

                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaign->contacts as $contact)
                                <tr>
                                    <td class="text-muted small">{{ $loop->iteration }}</td>
                                    <td>{{ $contact->name }}</td>
                                    <td>{{ $contact->phone }}</td>

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
