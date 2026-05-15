@extends('layouts.app')

@section('title', 'Add Template')
@section('page-title', 'Add Template')

@section('content')
    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Add New Template</h4>
            <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary px-2">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form id="createTemplateForm" novalidate>
                    @csrf
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Client <span class="text-danger">*</span></label>
                            <select name="client_id" class="form-select">
                                <option value="">— Select Client —</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->company }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-block" id="err_client_id"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. order_confirmation">
                            <div class="invalid-feedback" id="err_name"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select">
                                <option value="pending" selected>Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <div class="invalid-feedback" id="err_status"></div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="5" placeholder="Enter your message here..."></textarea>
                            <div class="invalid-feedback" id="err_message"></div>
                        </div>

                    </div>

                    <hr class="mt-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                            <i class="bi bi-save me-1"></i> Save Template
                        </button>
                        <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

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

            $('#createTemplateForm').on('submit', function(e) {
                e.preventDefault();
                clearErrors();

                const btn = $('#submitBtn');
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Saving...'
                );

                $.ajax({
                    url: '{{ route('templates.store') }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.status) {
                            window.location.href = '{{ route('templates.index') }}?success=' +
                                encodeURIComponent(res.message ??
                                    'Template created successfully!');
                        } else {
                            btn.prop('disabled', false).html(
                                '<i class="bi bi-save me-1"></i> Save Template'
                            );
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            showErrors(xhr.responseJSON.errors);
                        } else {
                            alert('Something went wrong. Please try again.');
                        }
                        btn.prop('disabled', false).html(
                            '<i class="bi bi-save me-1"></i> Save Template'
                        );
                    }
                });
            });

        });
    </script>
@endpush
