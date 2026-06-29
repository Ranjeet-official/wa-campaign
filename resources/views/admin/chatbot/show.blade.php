@extends('layouts.app')

@if (session('role') === 'admin')
    @section('title', 'Chat Session')
    @section('page-title', 'Chat Session')
@else
    @section('title', 'Conversation')
    @section('page-title', 'Conversation')
@endif

@section('content')
    <div class="container mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0">
                @if (session('role') === 'admin')
                    Chat Session
                @else
                    {{ $userInfo->user_name ?? 'Guest Visitor' }}
                    @if (!empty($userInfo->user_email))
                        <small class="text-muted">({{ $userInfo->user_email }})</small>
                    @endif
                @endif
            </h4>

            @if (session('role') === 'admin')
                <a href="{{ route('admin.chatbot.history', $client_id) }}" class="btn btn-outline-secondary px-3">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            @else
                <a href="{{ route('client.chatbot.index') }}" class="btn btn-outline-secondary px-3">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            @endif
        </div>

        <div class="card shadow-sm">
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                @foreach ($conversations as $msg)
                    <div class="d-flex {{ $msg->sender === 'user' ? 'justify-content-end' : 'justify-content-start' }} mb-2">
                        <div class="px-3 py-2 rounded-3" style="
                            max-width: 70%;
                            background: {{ $msg->sender === 'user' ? '#007aff' : '#e9ecef' }};
                            color: {{ $msg->sender === 'user' ? '#fff' : '#333' }};
                            font-size: 14px;
                        ">
                            <div>{{ $msg->message }}</div>
                            <div style="font-size: 11px; opacity: 0.7; margin-top: 4px; text-align: right;">
                                {{ \Carbon\Carbon::parse($msg->created_at)->format('h:i A') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection