    @extends('layouts.app')

    @section('title', 'Edit Client')
    @section('page-title', 'Edit Client')

    @section('content')
        <div class="container mt-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h4 class="mb-0">Edit Client</h4>
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

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $client->name) }}" placeholder="Enter full name">
                                <div class="invalid-feedback" id="err_name"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ old('email', $client->email) }}" placeholder="client@example.com">
                                <div class="invalid-feedback" id="err_email"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control"
                                    value="{{ old('phone', $client->phone) }}" placeholder="+91XXXXXXXXXX">
                                <div class="invalid-feedback" id="err_phone"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Company <span class="text-danger">*</span></label>
                                <input type="text" name="company" class="form-control"
                                    value="{{ old('company', $client->company) }}" placeholder="Company name">
                                <div class="invalid-feedback" id="err_company"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">WhatsApp Sender Number <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-whatsapp text-success"></i></span>
                                    <input type="text" name="wa_sender_number" class="form-control"
                                        value="{{ old('wa_sender_number', $client->wa_sender_number) }}"
                                        placeholder="+91XXXXXXXXXX">
                                </div>
                                <div class="invalid-feedback d-block" id="err_wa_sender_number"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" name="password" class="form-control"
                                        placeholder="Leave blank to keep current password">
                                </div>
                                <div class="invalid-feedback" id="err_password"></div>
                                <small class="text-muted">Leave empty if you do not want to change password.</small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Phone Number ID</label>
                                <input type="text" name="wa_phone_number_id" class="form-control"
                                    value="{{ old('wa_phone_number_id', $client->wa_phone_number_id) }}"
                                    placeholder="e.g. 1234567890">
                                <div class="invalid-feedback" id="err_wa_phone_number_id"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Access Token</label>
                                <input type="password" name="wa_access_token" class="form-control"
                                    value="{{ old('wa_access_token', $client->wa_access_token) }}"
                                    placeholder="EAAxxxxxxx...">
                                <div class="invalid-feedback" id="err_wa_access_token"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">WABA ID</label>
                                <input type="text" name="wa_waba_id" class="form-control"
                                    value="{{ old('wa_waba_id', $client->wa_waba_id) }}"
                                    placeholder="WhatsApp Business Account ID">
                                <div class="invalid-feedback" id="err_wa_waba_id"></div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Address</label>
                                <textarea name="address" class="form-control" rows="2" placeholder="Street address">{{ old('address', $client->address) }}</textarea>
                                <div class="invalid-feedback" id="err_address"></div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">City</label>
                                <input type="text" name="city" class="form-control"
                                    value="{{ old('city', $client->city) }}" placeholder="City">
                                <div class="invalid-feedback" id="err_city"></div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">State</label>
                                <input type="text" name="state" class="form-control"
                                    value="{{ old('state', $client->state) }}" placeholder="State">
                                <div class="invalid-feedback" id="err_state"></div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Pincode</label>
                                <input type="text" name="pincode" class="form-control"
                                    value="{{ old('pincode', $client->pincode) }}" placeholder="Pincode">
                                <div class="invalid-feedback" id="err_pincode"></div>
                            </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select">
                                <option value="">— Select —</option>
                                <option value="active"
                                    {{ old('status', $client->status) == 'active' ? 'selected' : '' }}>
                                    Active</option>
                                <option value="inactive"
                                    {{ old('status', $client->status) == 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                                <option value="suspended"
                                    {{ old('status', $client->status) == 'suspended' ? 'selected' : '' }}>Suspended
                                </option>
                            </select>
                            <div class="invalid-feedback" id="err_status"></div>
                        </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Services</label>
                                <div class="d-flex gap-4 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="whatsapp_enabled"
                                            id="whatsapp_enabled" value="1"
                                            {{ old('whatsapp_enabled', $client->whatsapp_enabled) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="whatsapp_enabled">
                                            WhatsApp Campaign
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="chatbot_enabled"
                                            id="chatbot_enabled" value="1"
                                            {{ old('chatbot_enabled', $client->chatbot_enabled) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="chatbot_enabled">
                                            Chatbot
                                        </label>
                                    </div>
                                </div>
                                <div class="text-danger small mt-1" id="err_services"></div>
                            </div>

                        </div>

                        <hr class="mt-4">

                        <div class="d-flex flex-wrap gap-2">
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

                $('#editClientForm').on('submit', function(e) {
                    e.preventDefault();
                    clearErrors();

                    const btn = $('#submitBtn');
                    btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

                    $.ajax({
                        url: '{{ route('clients.update', $client->id) }}',
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(res) {
                            if (res.status) {
                                // ✅ sessionStorage use karo — URL me ?success= nahi
                                sessionStorage.setItem('flashMessage', res.message ??
                                    'Client updated successfully!');
                                window.location.href = '{{ route('clients.index') }}';
                            } else {
                                btn.prop('disabled', false).html(
                                    '<i class="bi bi-save me-1"></i> Update Client');
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                showErrors(xhr.responseJSON.errors);
                            } else {
                                alert('Something went wrong. Please try again.');
                            }
                            btn.prop('disabled', false).html(
                                '<i class="bi bi-save me-1"></i> Update Client');
                        }
                    });
                });

            });
        </script>
    @endpush
