@extends('layouts.app')

@section('title', 'Edit Client')
@section('page-title', 'Edit Client')

@section('content')
    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Edit Client</h4>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary px-2">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form id="editClientForm" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $client->name) }}" placeholder="Enter full name">
                            <div class="invalid-feedback" id="err_name"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control"
                                value="{{ old('email', $client->email) }}" placeholder="client@example.com">
                            <div class="invalid-feedback" id="err_email"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                value="{{ old('phone', $client->phone) }}" placeholder="+91XXXXXXXXXX">
                            <div class="invalid-feedback" id="err_phone"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company <span class="text-danger">*</span></label>
                            <input type="text" name="company" class="form-control"
                                value="{{ old('company', $client->company) }}" placeholder="Company name">
                            <div class="invalid-feedback" id="err_company"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">WhatsApp Sender Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-whatsapp text-success"></i></span>
                                <input type="text" name="wa_sender_number" class="form-control"
                                    value="{{ old('wa_sender_number', $client->wa_sender_number) }}" placeholder="+91XXXXXXXXXX">
                            </div>
                            <div class="invalid-feedback d-block" id="err_wa_sender_number"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">WhatsApp API Key</label>
                            <input type="text" name="wa_api_key" class="form-control"
                                value="{{ old('wa_api_key', $client->wa_api_key) }}" placeholder="Enter API key">
                            <div class="invalid-feedback" id="err_wa_api_key"></div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">WhatsApp API URL</label>
                            <input type="url" name="wa_api_url" class="form-control"
                                value="{{ old('wa_api_url', $client->wa_api_url) }}" placeholder="https://api.example.com">
                            <div class="invalid-feedback" id="err_wa_api_url"></div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" class="form-control" rows="2"
                                placeholder="Street address">{{ old('address', $client->address) }}</textarea>
                            <div class="invalid-feedback" id="err_address"></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">City</label>
                            <input type="text" name="city" class="form-control"
                                value="{{ old('city', $client->city) }}" placeholder="City">
                            <div class="invalid-feedback" id="err_city"></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">State</label>
                            <input type="text" name="state" class="form-control"
                                value="{{ old('state', $client->state) }}" placeholder="State">
                            <div class="invalid-feedback" id="err_state"></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pincode</label>
                            <input type="text" name="pincode" class="form-control"
                                value="{{ old('pincode', $client->pincode) }}" placeholder="Pincode">
                            <div class="invalid-feedback" id="err_pincode"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select">
                                <option value="">— Select —</option>
                                <option value="active"    {{ old('status', $client->status) == 'active'    ? 'selected' : '' }}>Active</option>
                                <option value="inactive"  {{ old('status', $client->status) == 'inactive'  ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status', $client->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            <div class="invalid-feedback" id="err_status"></div>
                        </div>

                    </div>

                    <hr class="mt-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                            <i class="bi bi-save me-1"></i> Update Client
                        </button>
                        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    function clearErrors() {
        $('.form-control, .form-select').removeClass('is-invalid');
        $('[id^="err_"]').text('');
    }

    function showErrors(errors) {
        $.each(errors, function (field, messages) {
            $('[name="' + field + '"]').addClass('is-invalid');
            $('#err_' + field).text(messages[0]);
        });
    }

    $('#editClientForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const btn = $('#submitBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: '{{ route('clients.update', $client->id) }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.status) {
                    window.location.href = '{{ route('clients.index') }}?success=' +
                        encodeURIComponent(res.message ?? 'Client updated successfully!');
                } else {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Update Client');
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    showErrors(xhr.responseJSON.errors);
                } else {
                    alert('Something went wrong. Please try again.');
                }
                btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Update Client');
            }
        });
    });

});
</script>
@endpush
