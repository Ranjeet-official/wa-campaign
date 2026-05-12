@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    <div class="container mt-4">

        {{-- STATS CARDS --}}
        <div class="row g-3 mb-4">

            {{-- Total Clients --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">

                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="bi bi-person-badge-fill text-success fs-4"></i>
                        </div>

                        <div>
                            <div class="text-muted small">Total Clients</div>
                            <div class="fw-bold fs-4">{{ $totalClients }}</div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Total Campaigns --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">

                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="bi bi-megaphone-fill text-primary fs-4"></i>
                        </div>

                        <div>
                            <div class="text-muted small">Total Campaigns</div>
                            <div class="fw-bold fs-4">{{ $totalCampaigns }}</div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Running Campaigns --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">

                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                            <i class="bi bi-lightning-fill text-warning fs-4"></i>
                        </div>

                        <div>
                            <div class="text-muted small">Running Campaigns</div>
                            <div class="fw-bold fs-4">{{ $activeCampaigns }}</div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection
