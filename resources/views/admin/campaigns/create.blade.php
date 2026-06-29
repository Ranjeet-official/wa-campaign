@extends('layouts.app')
@section('title', 'New Campaign')
@section('page-title', 'New Campaign')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 p-md-4">

                    <form id="campaignForm" enctype="multipart/form-data">
                        @csrf

                        {{-- ✅ Error Alert --}}
                        <div id="formError" class="alert alert-danger d-none mb-3"></div>

                        @if (Auth::guard('web')->check())
                            <input type="hidden" name="client_filter" value="{{ request('client_id') }}">
                        @endif

                        {{-- Campaign Name --}}
                        <div class="mb-3">
                            <label class="form-label small text-muted">Campaign Name</label>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="e.g. Diwali Offer" value="{{ old('name') }}">
                            <div class="text-danger small mt-1" id="error-name"></div>
                        </div>

                        {{-- Client dropdown — sirf admin ko --}}
                        @if (Auth::guard('web')->check())
                            <div class="mb-3">
                                <label class="form-label small text-muted">Select Client</label>
                                <select name="client_id" id="client_id" class="form-select">
                                    <option value="">-- Select Client --</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}"
                                            {{ old('client_id', $client_id ?? '') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="text-danger small mt-1" id="error-client_id"></div>
                            </div>
                        @endif

                        {{-- Template --}}
                        <div class="mb-3">
                            <label class="form-label small text-muted">Select Template
                                <span class="text-secondary">(Optional)</span>
                            </label>
                            @if (Auth::guard('web')->check())
                                <select name="template_id" id="template_id" class="form-select" disabled>
                                    <option value="">-- Select Client First --</option>
                                </select>
                            @else
                                <select name="template_id" id="template_id" class="form-select">
                                    <option value="">-- Select Template --</option>
                                    @foreach ($templates as $template)
                                        <option value="{{ $template->id }}" data-message="{{ $template->message }}">
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            <div class="text-danger small mt-1" id="error-template_id"></div>
                        </div>

<!--                         {{-- Message --}}
                        <div class="mb-3">
                            <label class="form-label small text-muted">Message</label>
                            <textarea name="message" id="message" rows="4" class="form-control"
                                placeholder="Type your WhatsApp message here...">{{ old('message') }}</textarea>
                            <div class="text-danger small mt-1" id="error-message"></div>
                        </div> -->

                        <div class="mb-3">
    <label class="form-label small text-muted">Message</label>
    <textarea
        name="message"
        id="message"
        rows="4"
        class="form-control"
        placeholder="Message will be loaded from the selected template..."
        readonly>{{ old('message') }}</textarea>

    <div class="text-danger small mt-1" id="error-message"></div>
</div>

                        {{-- Media File --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-muted">
                                <i class="bi bi-paperclip me-1"></i> Attach Media File
                                <span class="text-secondary small">(Optional)</span>
                            </label>
                            <input type="file" name="media_file" id="media_file" class="form-control"
                                accept="image/*,video/*,.pdf,.doc,.docx">
                            <div class="invalid-feedback d-block" id="error-media_file"></div>
                            <div class="form-text mt-3">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span class="text-muted small">Supported formats:</span>
                                    <span class="badge bg-light text-dark border">JPG / PNG</span>
                                    <span class="badge bg-light text-dark border">MP4</span>
                                    <span class="badge bg-light text-dark border">PDF</span>
                                    <span class="badge bg-light text-dark border">DOC / DOCX</span>
                                    <span class="badge bg-light text-dark border">Max: 20MB</span>
                                </div>
                            </div>
                        </div>

                        {{-- Dates --}}
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small text-muted">Start Date</label>
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ old('start_date') }}">
                                <div class="text-danger small mt-1" id="error-start_date"></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small text-muted">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                                <div class="text-danger small mt-1" id="error-end_date"></div>
                            </div>
                        </div>

                        {{-- Excel Upload --}}
                        <div class="mb-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                <label class="form-label small text-muted mb-0">
                                    Upload Excel Sheet &nbsp;
                                    <a href="{{ asset('sample/campaign_contacts_sample.xlsx') }}"
                                        class="btn btn-sm btn-outline-primary" download>⬇ Demo</a>
                                </label>
                            </div>
                            <div class="alert alert-info py-2 px-3 mb-2" style="font-size:12px;">
                                Required columns: <code>name</code> &amp; <code>whatsapp_number</code>
                            </div>
                            <input type="file" id="sheet_file" class="form-control" accept=".xlsx,.xls">
                            <input type="hidden" name="contacts" id="contactsInput">
                            <div class="text-danger small mt-1" id="error-contacts"></div>
                            <div class="table-responsive">
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
                        </div>

                        {{-- Buttons --}}
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            @if (Auth::guard('web')->check())
                                <a href="{{ route('campaigns.index', ['client_id' => $client_id ?? '']) }}"
                                    class="btn btn-outline-secondary">Back</a>
                            @else
                                <a href="{{ route('client.campaigns.index') }}"
                                    class="btn btn-outline-secondary">Back</a>
                            @endif
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

            @if (Auth::guard('web')->check())
                $('#client_id').on('change', function() {
                    const clientId = $(this).val();
                    const templateSelect = $('#template_id');

                    templateSelect.html('<option value="">-- Loading... --</option>').prop('disabled',
                        true);
                    $('#message').val('');

                    if (!clientId) {
                        templateSelect.html('<option value="">-- Select Client First --</option>');
                        return;
                    }

                    $.ajax({
                        url: BASE_URL + '/wa/templates/by-client/' + clientId,
                        type: 'GET',
                        success: function(res) {
                            if (res.status && res.templates.length > 0) {
                                let options = '<option value="">-- Select Template --</option>';
                                res.templates.forEach(t => {
                                    options +=
                                        `<option value="${t.id}" data-message="${t.message}">${t.name}</option>`;
                                });
                                templateSelect.html(options).prop('disabled', false);
                            } else {
                                templateSelect.html(
                                    '<option value="">-- No Templates Found --</option>');
                            }
                        },
                        error: function() {
                            templateSelect.html(
                                '<option value="">-- Failed to Load --</option>');
                        }
                    });
                });
            @endif

            $('#template_id').on('change', function() {
                const message = $(this).find(':selected').data('message');
                $('#message').val(message || '');
            });

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
                        html += `<tr>
                            <td>${name}</td>
                            <td>${phone}</td>
                            <td><button type="button" class="btn btn-danger btn-sm deleteRow">❌</button></td>
                        </tr>`;
                    });
                    $('#previewBody').html(html);
                    $('#error-contacts').text('');
                };
                reader.readAsArrayBuffer(file);
            });

            // ✅ File size check before upload
            $('#media_file').on('change', function() {
                const file = this.files[0];
                if (file && file.size > 20 * 1024 * 1024) {
                    $('#error-media_file').text('File size must not exceed 20MB.');
                    $(this).val('');
                } else {
                    $('#error-media_file').text('');
                }
            });

            $(document).on('click', '.deleteRow', function() {
                $(this).closest('tr').remove();
            });

            $('#campaignForm').on('submit', function(e) {
                e.preventDefault();
                $('[id^="error-"]').text('');
                $('#formError').addClass('d-none').text('');
                $('input, select, textarea').removeClass('is-invalid');

                // ✅ File size check on submit
                const mediaFile = $('#media_file')[0].files[0];
                if (mediaFile && mediaFile.size > 20 * 1024 * 1024) {
                    $('#error-media_file').text('File size must not exceed 20MB.');
                    $('html, body').animate({
                        scrollTop: $('#media_file').offset().top - 100
                    }, 400);
                    return;
                }

                let contacts = [];
                $('#previewBody tr').each(function() {
                    const name = $(this).find('td:eq(0)').text().trim();
                    const phone = $(this).find('td:eq(1)').text().trim();
                    if (name && phone) contacts.push({
                        name,
                        phone
                    });
                });
                $('#contactsInput').val(JSON.stringify(contacts));

                const btn = $('#submitBtn');
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                const formData = new FormData(this);

                $.ajax({
                    url: '{{ Auth::guard('web')->check() ? route('campaigns.store') : route('client.campaigns.store') }}',
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
                        btn.prop('disabled', false).html(
                            '<i class="bi bi-check2 me-1"></i> Create Campaign');

                        const res = xhr.responseJSON;

                        if (xhr.status === 422) {
                            // ✅ Generic message (PostTooLarge ya koi aur)
                            if (res.message && !res.errors) {
                                $('#formError').removeClass('d-none').text(res.message);
                                $('html, body').animate({
                                    scrollTop: 0
                                }, 400);
                                return;
                            }

                            // Validation errors
                            if (res.errors) {
                                $.each(res.errors, function(field, messages) {
                                    $('#error-' + field).text(messages[0]);
                                    $('[name="' + field + '"]').addClass('is-invalid');
                                });
                                const firstError = $('.is-invalid').first();
                                if (firstError.length) {
                                    $('html, body').animate({
                                        scrollTop: firstError.offset().top - 100
                                    }, 400);
                                }
                            }
                        } else {
                            // ✅ Server error
                            $('#formError').removeClass('d-none').text(
                                res?.message ?? 'Something went wrong. Please try again.'
                            );
                            $('html, body').animate({
                                scrollTop: 0
                            }, 400);
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

        });
    </script>
@endpush
