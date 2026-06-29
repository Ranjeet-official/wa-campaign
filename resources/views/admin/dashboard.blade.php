@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="container mt-4">

        {{-- ✅ CAMPAIGN STATS — sirf jab WhatsApp enabled (totalCampaigns set hai) --}}
        @if(isset($totalCampaigns) && $totalCampaigns !== null)
        <div class="row g-3 mb-4">

            {{-- Total Clients — sirf admin ko dikhao --}}
            @if (Auth::guard('web')->check())
                <div class="col-6 col-md-4">
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
            @endif

            {{-- Total Campaigns --}}
            <div class="col-6 col-md-4">
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
            <div class="col-6 col-md-4">
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

            {{-- Completed --}}
            <div class="col-6 col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Completed Campaigns</div>
                            <div class="fw-bold fs-4">{{ $completedCampaigns }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Failed --}}
            <div class="col-6 col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                            <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Failed Campaigns</div>
                            <div class="fw-bold fs-4">{{ $failedCampaigns }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        @endif

        {{-- ✅ CHATBOT STATS — sirf jab Chatbot enabled (totalChatSessions set hai) --}}
        @if(isset($totalChatSessions) && $totalChatSessions !== null)
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3">
                            <i class="bi bi-chat-dots-fill text-info fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Chat Sessions</div>
                            <div class="fw-bold fs-4">{{ $totalChatSessions }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="bi bi-envelope-fill text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Messages</div>
                            <div class="fw-bold fs-4">{{ $totalChatMessages }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="bi bi-calendar-day-fill text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Today's Chats</div>
                            <div class="fw-bold fs-4">{{ $todayChatSessions }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
@endsection