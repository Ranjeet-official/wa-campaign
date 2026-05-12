@extends('layouts.app')

@section('title', 'Edit Campaign')
@section('page-title', 'Edit Campaign')

@section('content')

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <form id="campaignForm">
                        @csrf

                        <input type="hidden" name="client_filter" value="{{ request('client_id') }}">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Campaign Name</label>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="e.g. Diwali Offer" value="{{ $campaign->name }}">
                            <div class="text-danger small mt-1" id="error-name"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Message</label>
                            <textarea name="message" id="message" rows="4" class="form-control"
                                placeholder="Type your WhatsApp message here...">{{ $campaign->message }}</textarea>
                            <div class="text-danger small mt-1" id="error-message"></div>
                        </div>
                        <div class="mb-4">

                            {{-- Label --}}
                            <label for="media_file" class="form-label fw-semibold text-muted">
                                <i class="bi bi-paperclip me-1"></i>
                                Update Media File
                                <span class="text-secondary small">(Optional)</span>
                            </label>

                            {{-- File Input --}}
                            <input type="file" name="media_file" id="media_file" class="form-control"
                                accept="image/*,video/*,.pdf,.doc,.docx">

                            {{-- Validation Error --}}
                            <div class="invalid-feedback d-block" id="error-media_file"></div>

                            {{-- Current File --}}
                            @if ($campaign->media_file)
                                @php
                                    $ext = strtolower(pathinfo($campaign->media_file, PATHINFO_EXTENSION));

                                    $icon = match (true) {
                                        in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])
                                            => 'bi-file-image text-success',

                                        in_array($ext, ['mp4', 'mov', 'avi', 'mkv']) => 'bi-file-play text-danger',

                                        $ext === 'pdf' => 'bi-file-pdf text-danger',

                                        in_array($ext, ['doc', 'docx']) => 'bi-file-word text-primary',

                                        default => 'bi-file-earmark text-secondary',
                                    };
                                @endphp

                                <div class="border rounded p-3 mt-3 bg-light">

                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                                        {{-- File Info --}}
                                        <div class="d-flex align-items-center gap-2">

                                            <i class="bi {{ $icon }} fs-3"></i>

                                            <div>

                                                <div class="fw-semibold text-dark small">
                                                    Current Uploaded File
                                                </div>

                                                <small class="text-muted">
                                                    {{ basename($campaign->media_file) }}
                                                </small>

                                            </div>

                                        </div>

                                        {{-- Actions --}}
                                        <div class="d-flex align-items-center gap-2">

                                            {{-- View --}}
                                            <a href="{{ asset('storage/' . $campaign->media_file) }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">

                                                <i class="bi bi-eye me-1"></i>
                                                View

                                            </a>

                                            {{-- Remove --}}
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                id="removeMediaBtn">

                                                <i class="bi bi-trash me-1"></i>
                                                Remove

                                            </button>

                                            <input type="hidden" name="remove_media" id="remove_media" value="0">

                                        </div>

                                    </div>

                                    {{-- Remove Alert --}}
                                    <div class="alert alert-warning py-2 px-3 mt-3 mb-0 d-none small" id="removeAlert">

                                        <i class="bi bi-exclamation-triangle me-1"></i>

                                        File will be removed after update.

                                        <a href="#" class="ms-2" id="undoRemove">

                                            Undo

                                        </a>

                                    </div>

                                </div>
                            @endif

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
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                    value="{{ \Carbon\Carbon::parse($campaign->start_date)->format('Y-m-d') }}">
                                <div class="text-danger small mt-1" id="error-start_date"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control"
                                    value="{{ \Carbon\Carbon::parse($campaign->end_date)->format('Y-m-d') }}">
                                <div class="text-danger small mt-1" id="error-end_date"></div>
                            </div>
                        </div>

                        {{-- Client --}}
                        <div class="mb-3">
                            <label class="form-label small text-muted">Select Client</label>
                            <select name="client_id" id="client_id" class="form-select">
                                <option value="">-- Select Client --</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}"
                                        {{ $campaign->client_id == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-danger small mt-1" id="error-client_id"></div>
                        </div>

                        {{-- <div class="mb-3">
                            <label class="form-label small text-muted">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="draft" {{ $campaign->status == 'draft' ? 'selected' : '' }}>Draft
                                </option>
                                <option value="active" {{ $campaign->status == 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="paused" {{ $campaign->status == 'paused' ? 'selected' : '' }}>Paused
                                </option>
                                <option value="completed" {{ $campaign->status == 'completed' ? 'selected' : '' }}>
                                    Completed</option>
                            </select>
                            <div class="text-danger small mt-1" id="error-status"></div>
                        </div> --}}

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
                                <tbody id="previewBody">
                                    {{-- Existing contacts preload --}}
                                    @foreach ($campaign->contacts as $contact)
                                        <tr>
                                            <td>{{ $contact->name }}</td>
                                            <td>{{ $contact->phone }}</td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm deleteRow">❌</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Buttons --}}
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('campaigns.index', ['client_id' => $client_id]) }}"
                                class="btn btn-outline-secondary">
                                Back
                            </a>
                            <button type="submit" id="submitBtn" class="btn btn-primary">
                                <i class="bi bi-check2 me-1"></i> Update Campaign
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

            // ─── Excel Upload ────────────────────────────────────────────────
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
                        html += `
                    <tr>
                        <td>${row.name ?? ''}</td>
                        <td>${row.whatsapp_number ?? ''}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm deleteRow">❌</button>
                        </td>
                    </tr>`;
                    });

                    $('#previewBody').html(html);
                    $('#error-contacts').text('');
                };
                reader.readAsArrayBuffer(file);
            });

            // ─── Delete Row ──────────────────────────────────────────────────
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

                // Button disable + spinner
                const btn = $('#submitBtn');
                btn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                $.ajax({
                    url: '{{ route('campaigns.update', $campaign->id) }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function(res) {

                        if (res.success) {

                            sessionStorage.setItem('flashMessage', res.message);

                            window.location.href = res.redirect;
                        }

                    },
                    error: function(xhr) {
                        // Re-enable button
                        btn.prop('disabled', false)
                            .html('<i class="bi bi-check2 me-1"></i> Update Campaign');

                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, function(field, messages) {
                                // Error text field ke niche dikhao
                                $('#error-' + field).text(messages[0]);
                                // Field pe red border lagao
                                $('[name="' + field + '"]').addClass('is-invalid');
                            });

                            // Pehli error field pe scroll karo
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

            $(document).on('input change', 'input, select, textarea', function() {
                const name = $(this).attr('name');
                if (name) {
                    $('#error-' + name).text('');
                    $(this).removeClass('is-invalid');
                }
            });

            $('#removeMediaBtn').on('click', function() {

                $('#remove_media').val(1);

                $('#removeAlert').removeClass('d-none');

                $(this)
                    .removeClass('btn-outline-danger')
                    .addClass('btn-danger')
                    .html('<i class="bi bi-check-circle me-1"></i> Removed');

            });

            // Undo Remove
            $('#undoRemove').on('click', function(e) {

                e.preventDefault();

                $('#remove_media').val(0);

                $('#removeAlert').addClass('d-none');

                $('#removeMediaBtn')
                    .removeClass('btn-danger')
                    .addClass('btn-outline-danger')
                    .html('<i class="bi bi-trash me-1"></i> Remove');

            });

        });
    </script>
@endpush
