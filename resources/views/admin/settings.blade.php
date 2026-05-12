@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')

    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Profile Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex align-items-center gap-3 py-3">
                    <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                        style="width:42px; height:42px; flex-shrink:0; font-size:15px;">
                       {{ strtoupper(substr(explode(' ', auth()->user()->name ?? 'A A')[0], 0, 1) . substr(explode(' ', auth()->user()->name ?? 'A A')[1] ?? '', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-semibold">{{ auth()->user()->name }}</div>
                        <span
                            class="badge {{ auth()->user()->status === 'active' ? 'bg-success' : 'bg-secondary' }} rounded-pill"
                            style="font-size:11px;">
                            {{ ucfirst(auth()->user()->status) }}
                        </span>
                    </div>

                </div>

                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted mb-3" style="font-size:11px; letter-spacing:1px;">
                        <i class="bi bi-person me-1"></i> Profile Information
                    </h6>

                    <form action="{{ route('settings.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted" for="name">Name</label>
                                <input type="text" id="name" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', auth()->user()->name) }}" required />
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small text-muted" for="email">Email</label>
                                <input type="email" id="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', auth()->user()->email) }}" required />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small text-muted" for="whatsapp_number">
                                    <i class="bi bi-whatsapp text-success me-1"></i> WhatsApp Number
                                </label>
                                <input type="text" id="whatsapp_number" name="whatsapp_number"
                                    class="form-control @error('whatsapp_number') is-invalid @enderror"
                                    value="{{ old('whatsapp_number', auth()->user()->whatsapp_number) }}"
                                    placeholder="+91 98765 43210" />
                                @error('whatsapp_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 "></i> Save Profile
                            </button>
                        </div>
                    </form>

                </div>
            </div>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted mb-3" style="font-size:11px; letter-spacing:1px;">
                        <i class="bi bi-gear me-1"></i> App Settings
                    </h6>

                    <form action="{{ route('settings.app.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label small text-muted" for="site_name">Site Name</label>
                                <input type="text" id="site_name" name="site_name"
                                    class="form-control @error('site_name') is-invalid @enderror"
                                    value="{{ old('site_name', $settings->site_name ?? '') }}" placeholder="Enter app name"
                                    required />

                                @error('site_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Site Icon --}}
                            <div class="col-md-6">
                                <label class="form-label small text-muted" for="site_icon">
                                    <i class="bi bi-stars me-1"></i> Site Icon
                                </label>
                                <input type="text" id="site_icon" name="site_icon"
                                    class="form-control @error('site_icon') is-invalid @enderror"
                                    value="{{ old('site_icon', $settings->site_icon ?? '') }}" placeholder="bi bi-whatsapp"
                                    required />

                                @error('site_icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary px-1">
                                <i class="bi bi-check2 me-"></i> Save App Settings
                            </button>
                        </div>

                    </form>

                </div>
            </div>

            {{-- Change Password Card --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted mb-3" style="font-size:11px; letter-spacing:1px;">
                        <i class="bi bi-shield-lock me-1"></i> Change Password
                    </h6>

                    <form action="{{ route('settings.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label small text-muted" for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password"
                                class="form-control @error('current_password') is-invalid @enderror" placeholder="••••••••"
                                required />
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted" for="password">New Password</label>
                                <input type="password" id="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" placeholder="••••••••"
                                    required />
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small text-muted" for="password_confirmation">Confirm New
                                    Password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="form-control" placeholder="••••••••" required />
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary px-1">
                                <i class="bi bi-lock"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

@endsection
