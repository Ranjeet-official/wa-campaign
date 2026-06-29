@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            {{-- Profile Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex align-items-center gap-3 py-3">
                    @php
                        $authUser = Auth::guard('web')->check() ? auth()->user() : auth()->guard('client')->user();
                        $nameParts = explode(' ', $authUser->name ?? 'A A');
                        $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1] ?? '', 0, 1));
                    @endphp
                    <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                        style="width:42px;height:42px;flex-shrink:0;font-size:15px;">
                        {{ $initials }}
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $authUser->name }}</div>
                        <span class="badge {{ $authUser->status === 'active' ? 'bg-success' : 'bg-secondary' }} rounded-pill"
                            style="font-size:11px;">
                            {{ ucfirst($authUser->status) }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <h6 class="text-uppercase text-muted mb-3" style="font-size:11px;letter-spacing:1px;">
                        <i class="bi bi-person me-1"></i> Profile Information
                    </h6>

                    <form id="profileForm"
                        action="{{ Auth::guard('web')->check() ? route('settings.profile.update') : route('client.settings.profile.update') }}"
                        method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small text-muted">Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $authUser->name) }}" required />
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small text-muted">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ old('email', $authUser->email) }}" required />
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small text-muted">
                                    <i class="bi bi-whatsapp text-success me-1"></i> WhatsApp Number
                                </label>
                               <input type="text" name="wa_sender_number" class="form-control"
    value="{{ old('wa_sender_number', $authUser->wa_sender_number ?? '') }}"
    placeholder="+91 98765 43210" />
<div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2"></i> Save Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- App Settings — sirf admin --}}
            @if (Auth::guard('web')->check())
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3 p-md-4">
                        <h6 class="text-uppercase text-muted mb-3" style="font-size:11px;letter-spacing:1px;">
                            <i class="bi bi-gear me-1"></i> App Settings
                        </h6>

                        <form id="appSettingsForm" action="{{ route('settings.app.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small text-muted">Site Name</label>
                                    <input type="text" name="site_name" class="form-control"
                                        value="{{ old('site_name', $settings->site_name ?? '') }}"
                                        placeholder="Enter app name" required />
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label small text-muted">
                                        <i class="bi bi-stars me-1"></i> Site Icon
                                    </label>
                                    <input type="text" name="site_icon" class="form-control"
                                        value="{{ old('site_icon', $settings->site_icon ?? '') }}"
                                        placeholder="bi bi-whatsapp" required />
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check2"></i> Save App Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Change Password --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 p-md-4">
                    <h6 class="text-uppercase text-muted mb-3" style="font-size:11px;letter-spacing:1px;">
                        <i class="bi bi-shield-lock me-1"></i> Change Password
                    </h6>

                    <form id="passwordForm"
                        action="{{ Auth::guard('web')->check() ? route('settings.password.update') : route('client.settings.password.update') }}"
                        method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label small text-muted">Current Password</label>
                            <input type="password" name="current_password" class="form-control" placeholder="••••••••"
                                required />
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small text-muted">New Password</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••"
                                    required />
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small text-muted">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control"
                                    placeholder="••••••••" required />
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-lock"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- Toast Container --}}
    <div id="toastContainer" style="position:fixed;bottom:24px;right:24px;z-index:9999;min-width:280px;"></div>

@endsection

@push('scripts')
    <script>
        function handleForm(formId) {
            $('#' + formId).on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const btn = form.find('[type=submit]');

                // Clear previous errors
                form.find('input').removeClass('is-invalid');
                form.find('.invalid-feedback').text('');

                // Loading state
                btn.prop('disabled', true)
                    .prepend('<span class="spinner-border spinner-border-sm me-1"></span>');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        if (res.status) {
                            showToast('success', res.message);

                            // Password form reset karo after success
                            if (formId === 'passwordForm') {
                                form[0].reset();
                            }
                        }
                    },
                    error: function(xhr) {
                        const json = xhr.responseJSON;
                        const errors = json?.errors;

                        if (errors) {
                            $.each(errors, function(field, messages) {
                                const input = form.find('[name="' + field + '"]');
                                input.addClass('is-invalid');
                                input.siblings('.invalid-feedback').text(messages[0]);
                            });
                        } else {
                            showToast('danger', json?.message ?? 'Something went wrong.');
                        }
                    },
                    complete: function() {
                        btn.prop('disabled', false).find('.spinner-border').remove();
                    }
                });
            });

            // Input change pe error clear
            $('#' + formId + ' input').on('input', function() {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').text('');
            });
        }

        handleForm('profileForm');
        handleForm('passwordForm');
        @if (Auth::guard('web')->check())
            handleForm('appSettingsForm');
        @endif

        function showToast(type, message) {
            const id = 'toast_' + Date.now();
            const icon = type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill';
            const html = `
            <div id="${id}" class="toast align-items-center text-bg-${type} border-0 show mb-2 shadow" role="alert">
                <div class="d-flex">
                    <div class="toast-body fw-semibold">
                        <i class="bi bi-${icon} me-1"></i> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        onclick="$('#${id}').remove()"></button>
                </div>
            </div>`;

            $('#toastContainer').append(html);
            setTimeout(() => $('#' + id).fadeOut(400, function() {
                $(this).remove();
            }), 4000);
        }
    </script>
@endpush
