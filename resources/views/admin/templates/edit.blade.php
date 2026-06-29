@extends('layouts.app')

@section('title', 'Edit Template')
@section('page-title', 'Edit Template')

@section('content')
    <div class="container mt-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h4 class="mb-0">Edit Template</h4>
            @if (Auth::guard('web')->check())
                <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary px-2">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            @else
                <a href="{{ route('client.templates.index') }}" class="btn btn-outline-secondary px-2">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            @endif
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form id="editTemplateForm">
                    @csrf
                    <div class="row g-3">

                        {{-- Client Dropdown — sirf admin ke liye --}}
                        @if (Auth::guard('web')->check())
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Client <span class="text-danger">*</span></label>
                                <select name="client_id" class="form-select">
                                    <option value="">— Select Client —</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}"
                                            {{ $template->client_id == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }} ({{ $client->company }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback d-block" id="err_client_id"></div>
                            </div>
                        @endif

                        <div class="{{ Auth::guard('web')->check() ? 'col-12 col-md-6' : 'col-12' }}">
                            <label class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $template->name }}"
                                placeholder="e.g. order_confirmation">
                            <small class="text-muted">Sirf lowercase, numbers aur underscore allowed</small>
                            <div class="invalid-feedback d-block" id="err_name"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select">
                                <option value="">— Select Category —</option>
                                <option value="MARKETING" {{ $template->category == 'MARKETING' ? 'selected' : '' }}>
                                    Marketing</option>
                                <option value="UTILITY" {{ $template->category == 'UTILITY' ? 'selected' : '' }}>Utility
                                </option>
                                <option value="AUTHENTICATION"
                                    {{ $template->category == 'AUTHENTICATION' ? 'selected' : '' }}>Authentication</option>
                            </select>
                            <div class="invalid-feedback d-block" id="err_category"></div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Language <span class="text-danger">*</span></label>
                            <select name="language" class="form-select">
                                <option value="en" {{ $template->language == 'en' ? 'selected' : '' }}>English</option>
                                <option value="en_US" {{ $template->language == 'en_US' ? 'selected' : '' }}>English (US)
                                </option>
                                <option value="hi" {{ $template->language == 'hi' ? 'selected' : '' }}>Hindi</option>
                            </select>
                            <div class="invalid-feedback d-block" id="err_language"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                            <small class="text-muted d-block mb-1">
                                Use <code>{name}</code> for recipient's name — automatically replaced during campaign.
                            </small>
                            <textarea name="message" rows="5" class="form-control"
                                placeholder="e.g. Hello {name}, your request has been received.">{{ $template->message }}</textarea>
                            <div class="invalid-feedback d-block" id="err_message"></div>
                        </div>

                    </div>

                    <hr class="mt-4">

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                            <i class="bi bi-save me-1"></i> Update Template
                        </button>
                        @if (Auth::guard('web')->check())
                            <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        @else
                            <a href="{{ route('client.templates.index') }}"
                                class="btn btn-outline-secondary px-4">Cancel</a>
                        @endif
                    </div>

                </form>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function clearErrors() {
                $('.form-control, .form-select').removeClass('is-invalid');
                $('[id^="err_"]').text('');
            }

            function showErrors(errors) {
                $.each(errors, function(field, messages) {
                    $('[name="' + field + '"]').addClass('is-invalid');
                    $('#err_' + field).text(messages[0]);
                });
            }

            @if (Auth::guard('web')->check())
                const updateUrl = '{{ route('templates.update', $template) }}';
                const redirectUrl = '{{ route('templates.index') }}';
            @else
                const updateUrl = '{{ route('client.templates.update', $template) }}';
                const redirectUrl = '{{ route('client.templates.index') }}';
            @endif

            $('#editTemplateForm').on('submit', function(e) {
                e.preventDefault();
                clearErrors();

                const btn = $('#submitBtn');
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Updating...'
                );

                $.ajax({
                    url: updateUrl,
                    type: 'POST',
                    data: $(this).serialize() + '&_method=PUT',
                    success: function(res) {
                        if (res.status) {
                            // ✅ sessionStorage use karo
                            sessionStorage.setItem('flashMessage', res.message ??
                                'Template updated successfully!');
                            window.location.href = redirectUrl;
                        }
                    },
                    error: function(xhr) {
                        const res = xhr.responseJSON;
                        if (xhr.status === 422 && res.errors) {
                            showErrors(res.errors);
                        } else {
                            alert(res.message ?? 'Something went wrong.');
                        }
                        btn.prop('disabled', false).html(
                            '<i class="bi bi-save me-1"></i> Update Template'
                        );
                    }
                });
            });

        });
    </script>
@endpush
