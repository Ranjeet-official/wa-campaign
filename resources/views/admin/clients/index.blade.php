    @extends('layouts.app')

    @section('title', 'Clients')
    @section('page-title', 'Clients')

    @section('content')
        <div class="container mt-4">

            {{-- Alerts --}}
            <div id="ajaxMessage">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-x-circle me-1"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h4 class="mb-0">Client List</h4>
                <a href="{{ route('clients.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Add Client
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Client</th>
                                    <th>Phone</th>
                                    <th>Company</th>
                                    <th>WA Sender</th>
                                    <th>Status</th>
                                    <th>Services</th>
                                    <th>Campaigns</th>
                                    <th>Joined</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="clientTableBody">
                                @forelse($clients as $client)
                                    <tr id="row-{{ $client->id }}">
                                        <td class="text-muted small">
                                            {{ ($clients->currentPage() - 1) * $clients->perPage() + $loop->iteration }}
                                        </td>

                                        <td>
                                            <div class="fw-semibold small">{{ $client->name }}</div>
                                            <div class="text-muted" style="font-size:12px;">{{ $client->email ?? '—' }}
                                            </div>
                                        </td>

                                        <td class="small">{{ $client->phone ?? '-' }}</td>
                                        <td class="small">{{ $client->company ?? '-' }}</td>

                                        <td class="small">
                                            @if ($client->wa_sender_number)
                                                <span
                                                    class="badge bg-success-subtle text-success border border-success-subtle">
                                                    <i class="bi bi-whatsapp me-1"></i>{{ $client->wa_sender_number }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($client->status === 'active')
                                                <span
                                                    class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                            @elseif($client->status === 'inactive')
                                                <span
                                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                                            @else
                                                <span
                                                    class="badge bg-danger-subtle text-danger border border-danger-subtle">Suspended</span>
                                            @endif
                                        </td>
                                        <td class="small">
                                            @if ($client->whatsapp_enabled)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-whatsapp"></i> WhatsApp
                                                </span>
                                            @endif
                                            @if ($client->chatbot_enabled)
                                                <span class="badge bg-primary">
                                                    <i class="bi bi-robot"></i> Chatbot
                                                </span>
                                            @endif
                                            @if (!$client->whatsapp_enabled && !$client->chatbot_enabled)
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td class="small text-center">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                {{ $client->campaigns_count ?? 0 }}
                                            </span>
                                        </td>

                                        <td class="small text-muted">{{ $client->created_at->format('d M Y') }}</td>

                                        <td class="text-end">
                                            <div class="d-flex gap-1 justify-content-end">
                                                @if ($client->campaigns->count() > 0)
                                                    <a href="{{ route('campaigns.index', ['client_id' => $client->id]) }}"
                                                        class="btn btn-sm btn-outline-info" title="View Campaigns">
                                                        <i class="bi bi-megaphone"></i>
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-outline-secondary" disabled
                                                        title="No Campaign Found">
                                                        <i class="bi bi-megaphone"></i>
                                                    </button>
                                                @endif
                                                <a href="{{ route('clients.edit', $client->id) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger deleteClient"
                                                    data-id="{{ $client->id }}" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>

                                        @if ($client->chatbot_enabled)
                                            <a href="{{ route('admin.chatbot.history', $client->id) }}"
                                                class="btn btn-sm btn-outline-dark" title="Chat History">
                                                <i class="bi bi-chat-dots"></i>
                                            </a>
                                            {{-- ✅ Chatbot link copy button --}}
                                            <button class="btn btn-sm btn-outline-secondary copyChatbotLink"
                                                data-link="http://10.16.16.29:1060/{{ $client->chatbot_slug }}"
                                                title="Copy Chatbot Link">
                                                <i class="bi bi-link-45deg"></i>
                                            </button>
                                        @endif
                                     </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="noClientsRow">
                                        <td colspan="9" class="text-center text-muted py-5">
                                            <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
                                            No clients found.
                                            <a href="{{ route('clients.create') }}">Add one?</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ✅ SIRF YE RAKHO --}}
                <div
                    class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <small class="text-muted">
                        Showing {{ $clients->firstItem() ?? 0 }}–{{ $clients->lastItem() ?? 0 }} of
                        {{ $clients->total() }} clients
                    </small>
                    @if ($clients->hasPages())
                        {{ $clients->withQueryString()->links('vendor.pagination.simple-bootstrap-5') }}
                    @endif
                </div>
            </div>

        </div>
    @endsection

    @push('scripts')
        <script>
            $(document).ready(function() {



                let flash = sessionStorage.getItem('flashMessage');
                if (flash) {
                    $('#ajaxMessage').html(`
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-1"></i> ${flash}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
                    setTimeout(() => $('#ajaxMessage .alert').alert('close'), 4000);
                    sessionStorage.removeItem('flashMessage');
                }

                setTimeout(() => {
                    $('#ajaxMessage .alert').alert('close');
                }, 4000);

                function showAlert(type, message) {
                    const icon = type === 'success' ? 'check-circle' : 'x-circle';
                    $('#ajaxMessage').html(`
                        <div class="alert alert-${type} alert-dismissible fade show">
                            <i class="bi bi-${icon} me-1"></i> ${message}
                            <button class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                    setTimeout(() => {
                        $('#ajaxMessage .alert').alert('close');
                    }, 4000);
                }



          $(document).on('click', '.copyChatbotLink', function() {
    const link = $(this).data('link');

    // Pehle modern clipboard API try karo (sirf HTTPS/localhost pe kaam karega)
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(link).then(() => {
            showAlert('success', 'Chatbot link copied: ' + link);
        }).catch(() => {
            fallbackCopy(link);
        });
    } else {
        // HTTP/IP address pe seedha fallback use karo
        fallbackCopy(link);
    }
});

function fallbackCopy(link) {
    const el = document.createElement('textarea');
    el.value = link;
    el.style.position = 'fixed';
    el.style.left = '-9999px';
    el.style.top = '0';
    document.body.appendChild(el);
    el.focus();
    el.select();

    try {
        const success = document.execCommand('copy');
        if (success) {
            showAlert('success', 'Chatbot link copied: ' + link);
        } else {
            showAlert('danger', 'Copy failed. Please copy manually: ' + link);
        }
    } catch (err) {
        showAlert('danger', 'Copy failed. Please copy manually: ' + link);
    }

    document.body.removeChild(el);
}
                $(document).on('click', '.deleteClient', function() {
                    if (!confirm('Are you sure you want to delete this client?')) return;

                    const id = $(this).data('id');
                    const btn = $(this);
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                    $.ajax({
                        url: BASE_URL + '/wa/clients/' + id,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.status) {
                                $('#row-' + id).fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('#clientTableBody tr').length === 0) {
                                        $('#clientTableBody').append(`
                                            <tr id="noClientsRow">
                                                <td colspan="9" class="text-center text-muted py-5">
                                                    <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
                                                    No clients found.
                                                </td>
                                            </tr>
                                        `);
                                    }
                                    const offset =
                                        {{ ($clients->currentPage() - 1) * $clients->perPage() }};
                                    $('#clientTableBody tr').each(function(i) {
                                        $(this).find('td:first').text(offset + i +
                                            1);
                                    });
                                });
                                showAlert('success', res.message);
                            } else {
                                showAlert('danger', res.message);
                                btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                            }
                        },
                        error: function(xhr) {
                            const msg = xhr.status === 422 ? xhr.responseJSON.message :
                                'Delete failed. Please try again.';
                            showAlert('danger', msg);
                            btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                        }
                    });
                });

            });
        </script>
    @endpush
