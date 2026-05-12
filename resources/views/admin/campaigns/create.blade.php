@extends('layouts.app')

@section('title', 'New Campaign')
@section('page-title', 'New Campaign')

@section('content')

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <form id="campaignForm" method="POST" action="{{ route('campaigns.store') }}"
                        enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="client_filter" value="{{ request('client_id') }}">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Campaign Name</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Diwali Offer"
                                value="{{ old('name') }}">
                            <div class="text-danger small mt-1" id="error-name">
                                @error('name')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>

                        {{-- Message --}}
                        <div class="mb-3">
                            <label class="form-label small text-muted">Message</label>
                            <textarea name="message" id="message" rows="4" class="form-control @error('message') is-invalid @enderror"
                                placeholder="Type your WhatsApp message here...">{{ old('message') }}</textarea>
                            <div class="text-danger small mt-1" id="error-message">
                                @error('message')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>


                        {{-- Media File Upload --}}
                        <div class="mb-4">

                            <label for="media_file" class="form-label fw-semibold text-muted">
                                <i class="bi bi-paperclip me-1"></i>
                                Attach Media File
                                <span class="text-secondary small">(Optional)</span>
                            </label>

                            <input type="file" name="media_file" id="media_file" class="form-control"
                                accept="image/*,video/*,.pdf,.doc,.docx">
                            <div class="invalid-feedback d-block" id="error-media_file"></div>

                            {{-- Supported Formats --}}
                            <div class="form-text mt-3">

                                <div class="d-flex flex-wrap gap-2 align-items-center">

                                    <span class="text-muted small">
                                        Supported formats:
                                    </span>

                                    <span class="badge bg-light text-dark border">
                                        JPG / PNG
                                    </span>

                                    <span class="badge bg-light text-dark border">
                                        MP4
                                    </span>

                                    <span class="badge bg-light text-dark border">
                                        PDF
                                    </span>

                                    <span class="badge bg-light text-dark border">
                                        DOC / DOCX
                                    </span>

                                    <span class="badge bg-light text-dark border">
                                        Max: 20MB
                                    </span>

                                </div>

                            </div>

                        </div>
                        {{-- Dates --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Start Date</label>
                                <input type="date" name="start_date" id="start_date"
                                    class="form-control @error('start_date') is-invalid @enderror"
                                    value="{{ old('start_date') }}">
                                <div class="text-danger small mt-1" id="error-start_date">
                                    @error('start_date')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">End Date</label>
                                <input type="date" name="end_date" id="end_date"
                                    class="form-control @error('end_date') is-invalid @enderror"
                                    value="{{ old('end_date') }}">
                                <div class="text-danger small mt-1" id="error-end_date">
                                    @error('end_date')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Client --}}
                        <div class="mb-3">
                            <label class="form-label small text-muted">Select Client</label>
                            <select name="client_id" id="client_id"
                                class="form-select @error('client_id') is-invalid @enderror">

                                <option value="">-- Select Client --</option>

                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}"
                                        {{ old('client_id', $client_id) == $client->id ? 'selected' : '' }}>

                                        {{ $client->name }}

                                    </option>
                                @endforeach

                            </select>
                            <div class="text-danger small mt-1" id="error-client_id">
                                @error('client_id')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>

                        {{-- Excel Upload --}}
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label small text-muted mb-0">
                                    Upload Excel Sheet &nbsp;
                                    <a href="{{ asset('sample/campaign_contacts_sample.xlsx') }}"
                                        class="btn btn-sm btn-outline-primary" download>
                                        ⬇ Demo
                                    </a>
                                </label>
                            </div>

                            <div class="alert alert-info py-2 px-3 mb-2" style="font-size:12px;">
                                Required columns: <code>name</code> &amp; <code>whatsapp_number</code>
                            </div>

                            <input type="file" id="sheet_file" class="form-control" accept=".xlsx,.xls">
                            <input type="hidden" name="contacts" id="contactsInput">

                            <div class="text-danger small mt-1" id="error-contacts"></div>

                            <table class="table table-bordered mt-3">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th width="80">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="previewBody"></tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('campaigns.index', ['client_id' => $client_id]) }}"
                                class="btn btn-outline-secondary">
                                Back
                            </a>
                            <button type="submit" id="submitBtn" class="btn btn-primary">
                                <i class="bi bi-check2 me-1"></i> Create Campaign
                            </button>
                        </div>

                    </form>
                </div>
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

            // ─── Excel Upload ───────────────────────────────────────────────
            $('#sheet_file').on('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function(e) {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, {
                        type: 'array'
                    });
                    const sheet = workbook.Sheets[workbook.SheetNames[0]];
                    const jsonData = XLSX.utils.sheet_to_json(sheet);

                    let html = '';
                    jsonData.forEach(row => {

                        const name = row.name || row.Name || '';
                        const phone = row.phone || row.Phone || row.mobile || row
                            .whatsapp_number || row.Whatsapp_Number || '';

                        html += `
        <tr>
            <td>${name}</td>
            <td>${phone}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm deleteRow">❌</button>
            </td>
        </tr>`;
                    });
                    $('#previewBody').html(html);
                    $('#error-contacts').text(''); // clear error on new upload
                };
                reader.readAsArrayBuffer(file);
            });

            // ─── Delete Row ─────────────────────────────────────────────────
            $(document).on('click', '.deleteRow', function() {
                $(this).closest('tr').remove();
            });

            // ─── Form Submit ─────────────────────────────────────────────────
            $('#campaignForm').on('submit', function(e) {
                e.preventDefault();

                // Clear all previous errors
                $('[id^="error-"]').text('');
                $('input, select, textarea').removeClass('is-invalid');

                // Build contacts JSON from table
                let contacts = [];
                $('#previewBody tr').each(function() {
                    const name = $(this).find('td:eq(0)').text().trim();
                    const phone = $(this).find('td:eq(1)').text().trim();
                    if (name && phone) {
                        contacts.push({
                            name,
                            phone
                        });
                    }
                });
                $('#contactsInput').val(JSON.stringify(contacts));

                // Disable button + show spinner
                const btn = $('#submitBtn');
                btn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                $.ajax({
                    url: '{{ route('campaigns.store') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function(res) {

                        if (res.success) {

                            // ✅ better than localStorage for redirect use case
                            sessionStorage.setItem('flashMessage', res.message);

                            window.location.href = res.redirect;
                        }

                    },
                    error: function(xhr) {
                        // Re-enable button
                        btn.prop('disabled', false)
                            .html('<i class="bi bi-check2 me-1"></i> Create Campaign');

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;

                            $.each(errors, function(field, messages) {
                                // Show error text below field
                                $('#error-' + field).text(messages[0]);

                                // Add red border on the field
                                $('[name="' + field + '"]').addClass('is-invalid');
                            });

                            // Scroll to first error
                            const firstError = $('.is-invalid').first();
                            if (firstError.length) {
                                $('html, body').animate({
                                    scrollTop: firstError.offset().top - 100
                                }, 400);
                            }

                        } else {
                            alert('Something went wrong. Please try again.');
                        }
                    }
                });
            });

            // Clear individual field error on input/change
            $(document).on('input change', 'input, select, textarea', function() {
                const name = $(this).attr('name');
                if (name) {
                    $('#error-' + name).text('');
                    $(this).removeClass('is-invalid');
                }
            });

        });
    </script>
@endpush
