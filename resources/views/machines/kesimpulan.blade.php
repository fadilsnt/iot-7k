<x-app-layout>
    @php
        // Mapping 4 sensor per mesin (ubah sesuai data kamu)
        $sensorMeta = [
            ['key' => 'amp', 'label' => 'AMP', 'unit' => 'A', 'decimals' => 2],
            ['key' => 'hm', 'label' => 'HM', 'unit' => '', 'decimals' => 2],
            ['key' => 'temp', 'label' => 'TEMP', 'unit' => '°C', 'decimals' => 0],
            ['key' => 'moist', 'label' => 'MOIST', 'unit' => '%', 'decimals' => 0],
        ];
    @endphp

    @push('head')
        @vite(['resources/css/app.css', 'resources/css/dashboard.css'])
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            #detailTableWrap {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            #detailDataTable {
                width: 100%;
                min-width: 860px;
            }

            @media (max-width: 768px) {
                #detailDataTable {
                    min-width: 100%;
                    border-collapse: separate;
                    border-spacing: 0 10px;
                }

                #detailDataTable thead {
                    display: none;
                }

                #detailDataTable tbody tr {
                    display: block;
                    border: 1px solid #e2e8f0;
                    border-radius: 12px;
                    background: #ffffff;
                    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
                    overflow: hidden;
                }

                #detailDataTable tbody td {
                    display: grid;
                    grid-template-columns: 115px 1fr;
                    gap: 10px;
                    align-items: center;
                    padding: 10px 12px;
                    border-bottom: 1px dashed #e2e8f0;
                    white-space: normal;
                    word-break: break-word;
                }

                #detailDataTable tbody td::before {
                    content: attr(data-label);
                    color: #64748b;
                    font-size: 0.75rem;
                    font-weight: 700;
                    letter-spacing: 0.02em;
                    text-transform: uppercase;
                }

                #detailDataTable tbody tr td:last-child {
                    border-bottom: none;
                }

                #detailDataTable tbody td[colspan] {
                    display: block;
                    text-align: center;
                }

                #detailDataTable tbody td[colspan]::before {
                    content: '';
                }
            }
        </style>
    @endpush

    <header class="dashboard-header"
        style="display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;position:relative;">

        <span class="text-xs uppercase tracking-wide text-white/80 flex items-center gap-2">
            PT. Tuju Kuda Hitam Sakti
        </span>
        <h1 style="font-size: 1.25rem; margin-top: 0.5rem;">Kesimpulan</h1>
    </header>
    <div class="content-container">
        {{-- CUSTOM RANGE MODAL --}}
        <div>
            <div id="customRangeModal" class="modal-overlay custom-range-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><i class="fa-regular fa-calendar-days"></i> Select Custom Date & Time Range</h3>
                        <button type="button" id="btnCloseModal" class="modal-close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="date-picker-group">
                            <div class="date-picker-field">
                                <label><i class="fa-solid fa-calendar-check"></i> Start Date & Time</label>
                                <input type="text" id="startDateTime" class="datetime-input"
                                    placeholder="Select start date & time">
                            </div>
                            <div class="date-picker-field">
                                <label><i class="fa-solid fa-calendar-xmark"></i> End Date & Time</label>
                                <input type="text" id="endDateTime" class="datetime-input"
                                    placeholder="Select end date & time">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" id="btnCancel" class="btn-cancel">
                                <i class="fa-solid fa-ban"></i> Cancel
                            </button>
                            <button type="button" id="btnApply" class="btn-apply">
                                <i class="fa-solid fa-check"></i> Apply Range
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- REPORT DOWNLOAD MODAL --}}
        <div id="reportDownloadModal" class="modal-overlay custom-range-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><i class="fa-solid fa-file-excel"></i> Buat Laporan Mesin Press</h3>
                    <button type="button" id="btnCloseReportModal" class="modal-close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="reportForm" action="{{ route('machines.download-report') }}" method="GET">
                        <div class="date-picker-group">
                            <div class="date-picker-field">
                                <label><i class="fa-solid fa-calendar-check"></i> Start Date & Time</label>
                                <input type="text" id="startDateTimeReport" name="start" class="datetime-input"
                                    placeholder="Select start date & time" required>
                            </div>
                            <div class="date-picker-field">
                                <label><i class="fa-solid fa-calendar-xmark"></i> End Date & Time</label>
                                <input type="text" id="endDateTimeReport" name="end" class="datetime-input"
                                    placeholder="Select end date & time" required>
                            </div>
                        </div>

                        <div class="date-picker-group mt-4">
                            <div class="date-picker-field">
                                <label><i class="fa-solid fa-layer-group"></i> Line</label>
                                <input type="text" id="lineInput" name="line" class="datetime-input"
                                    placeholder="Contoh: I, II, III" required>
                            </div>
                            <div class="date-picker-field">
                                <label><i class="fa-solid fa-calendar-day"></i> Hari Kerja</label>
                                <input type="number" id="hariKerjaInput" name="hari_kerja" class="datetime-input"
                                    placeholder="Jumlah hari kerja" min="1" required>
                            </div>
                        </div>

                        <div class="date-picker-group mt-4">
                            <div class="date-picker-field">
                                <label><i class="fa-solid fa-clock-rotate-left"></i> Total Jam Kerja</label>
                                <input type="number" id="jamKerjaInput" name="jam_kerja" class="datetime-input"
                                    placeholder="Contoh: 40" required min="1">
                            </div>
                            <div class="date-picker-field">
                                <label><i class="fa-solid fa-cubes"></i> Total Briket (Batang)</label>
                                <input type="number" id="totalBriketInput" name="total_briket" class="datetime-input"
                                    placeholder="Jumlah briket" min="0" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" id="btnCancelReport" class="btn-cancel">
                                <i class="fa-solid fa-ban"></i> Cancel
                            </button>
                            <button type="submit" class="btn-download" style="background: #10b981;">
                                <i class="fa-solid fa-file-excel"></i> Download Laporan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="data-row mt-6">
            <div class="table-card" style="grid-column: 1 / -1;">
                <div class="table-header-controls"
                    style="display:flex;flex-wrap:wrap;gap:0.75rem;align-items:center;justify-content:space-between;">
                    <h3>Detail Data Mesin</h3>
                    <div class="table-controls"
                        style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;justify-content:flex-end;flex:1;min-width:260px;">
                        <select class="filter-select filter-pill" id="summaryFilterSelect">
                            <option value="1h" selected>1 Hours</option>
                            <option value="2h">2 Hours</option>
                            <option value="4h">4 Hour</option>
                            <option value="8h">8 Hour</option>
                            <option value="16h">16 Hour</option>
                            <option value="24h">24 Hour</option>
                            <option value="shift1">Shift 1 Terakhir</option>
                            <option value="shift2">Shift 2 Terakhir</option>
                            <option value="shift3">Shift 3 Terakhir</option>
                        </select>
                        <button class="btn-custom-range btn-custom-range-pill" id="btnModeCustom"
                            onclick="switchTableMode('custom')">
                            <i class="fa-regular fa-calendar-days"></i> Custom Range
                        </button>
                        <button class="btn-custom-range btn-custom-range-pill" id="btnBuatLaporan"
                            onclick="openReportModal()">
                            <i class="fa-solid fa-download"></i> Buat Laporan
                        </button>
                    </div>
                </div>

                <div class="table-shell" id="detailTableWrap">
                    <table id="detailDataTable">
                        <thead>
                            <tr>
                                <th class="cursor-pointer hover:bg-slate-50 transition" onclick="toggleSort('name')">
                                    No. Mesin <i class="fa-solid fa-sort text-slate-300 ml-1" id="sort-icon-name"></i>
                                </th>
                                <th class="text-slate-500">Timestamp</th>
                                @foreach ($sensorMeta as $s)
                                    <th class="cursor-pointer hover:bg-slate-50 transition"
                                        onclick="toggleSort('{{ $s['key'] }}')">
                                        {{ $s['label'] }} <i class="fa-solid fa-sort text-slate-300 ml-1"
                                            id="sort-icon-{{ $s['key'] }}"></i>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="machinesTableBody">
                            @foreach ($machines as $m)
                                <tr class="border-b border-slate-100">
                                    <td data-label="No. Mesin" class="px-4 py-3 font-semibold text-slate-700">
                                        {{ $m['name'] ?? '-' }}</td>
                                    <td data-label="Timestamp" class="px-4 py-3 text-slate-600">
                                        {{ $m['last_updated'] ?? '-' }}</td>
                                    @foreach ($sensorMeta as $s)
                                        @php $v = $m[$s['key']] ?? null; @endphp
                                        <td data-label="{{ $s['label'] }}"
                                            class="px-4 py-3 font-semibold text-slate-900">
                                            @if (in_array($s['key'], ['temp', 'moist', 'amp', 'hm']) && (float) $v == 0)
                                                -
                                            @else
                                                {{ is_numeric($v) ? number_format($v, $s['decimals']) : '-' }}
                                            @endif
                                            @if (in_array($s['key'], ['temp', 'moist', 'amp', 'hm']) && (float) $v == 0)
                                                {{-- Hide unit --}}
                                            @elseif($s['unit'] !== '')
                                                <span
                                                    class="text-xs text-slate-500 font-semibold">{{ $s['unit'] }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="table-footer"
                    style="display:flex;flex-wrap:wrap;gap:0.75rem;align-items:center;justify-content:space-between;">
                    <div class="table-footer-left" style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;">
                        <label>Show:</label>
                        <select id="perPageSelect" class="pagination-select">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="pagination-info" id="paginationInfo">Loading summary...</span>
                        <span id="tableLastSync" class="text-[10px] text-slate-400 font-mono ml-3"></span>
                    </div>
                    <div class="table-footer-right"
                        style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;">
                        <button class="btn-pagination" id="btnPagePrev" disabled>
                            <i class="fa-solid fa-chevron-left"></i> Back
                        </button>
                        <button class="btn-pagination" id="btnPageNext" disabled>
                            Next <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <div class="toast-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">Berhasil!</div>
            <div class="toast-message">Data berhasil dimuat</div>
        </div>
        <button id="toastAction" class="toast-close" style="display:none;margin-right:6px;" type="button">
            Reconnect
        </button>
        <button class="toast-close" onclick="closeToast()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    @push('scripts')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

        <script>
            // Endpoint tabel & grid
            const TABLE_ENDPOINT = "{{ route('machines.table-data') }}";
            const GRID_ENDPOINT = "{{ route('machines.get-data') }}";
            let tableStartPicker = null;
            let tableEndPicker = null;
            let currentSortColumn = 'auto'; // Default auto-sort logic
            const tableState = {
                rows: [],
                page: 1,
                perPage: 25
            };

            const gridSensorMeta = [{
                    key: 'amp',
                    label: 'AMP',
                    unit: 'A',
                    decimals: 2
                },
                {
                    key: 'hm',
                    label: 'HM',
                    unit: '',
                    decimals: 2
                },
                {
                    key: 'temp',
                    label: 'TEMP',
                    unit: '°C',
                    decimals: 0
                },
                {
                    key: 'moist',
                    label: 'MOIST',
                    unit: '%',
                    decimals: 0
                },
            ];

            const tableSensorMeta = gridSensorMeta.map(s => ({
                key: s.key,
                decimals: s.decimals,
                suffix: s.unit ? ' ' + s.unit : ''
            }));

            const modal = document.getElementById('customRangeModal');
            // Note: btnCustomRange is now only used for the Chart filter if it exists, or hidden.
            // The table has its own buttons now.

            // --- FLATPICKR SETUP ---
            let startPicker = null;
            let endPicker = null;

            if (typeof flatpickr !== 'undefined') {
                startPicker = flatpickr("#startDateTime", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr: true,
                    maxDate: new Date(),
                    minuteIncrement: 1,
                    onChange: (_, dateStr) => {
                        if (endPicker) endPicker.set('minDate', dateStr)
                    }
                });

                endPicker = flatpickr("#endDateTime", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr: true,
                    maxDate: new Date(),
                    minuteIncrement: 1,
                    onChange: (_, dateStr) => {
                        if (startPicker) startPicker.set('maxDate', dateStr)
                    }
                });
            } else {
                console.error('Flatpickr not loaded - check internet connection');
            }

            function refreshCustomRangeMax() {
                const now = new Date();
                if (startPicker) startPicker.set('maxDate', now);
                if (endPicker) endPicker.set('maxDate', now);
            }

            // --- MODAL FUNCTIONS ---
            window.openModal = function() {
                refreshCustomRangeMax();
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
            window.closeModal = function() {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
                if (startPicker) {
                    startPicker.clear();
                    startPicker.set('maxDate', null);
                }
                if (endPicker) {
                    endPicker.clear();
                    endPicker.set('minDate', null);
                }
            }

            function showToast(type, title, message, action = null) {
                const toast = document.getElementById('toast');
                const icon = toast.querySelector('.toast-icon i');
                const titleEl = toast.querySelector('.toast-title');
                const messageEl = toast.querySelector('.toast-message');
                const actionBtn = document.getElementById('toastAction');

                toast.classList.remove('success', 'error');
                toast.classList.add(type);
                titleEl.textContent = title;
                messageEl.textContent = message;

                if (type === 'success') {
                    icon.className = 'fa-solid fa-circle-check';
                } else if (type === 'error') {
                    icon.className = 'fa-solid fa-circle-exclamation';
                }

                if (actionBtn) {
                    if (typeof action === 'function') {
                        actionBtn.style.display = 'inline-flex';
                        actionBtn.onclick = action;
                    } else {
                        actionBtn.style.display = 'none';
                        actionBtn.onclick = null;
                    }
                }

                toast.classList.add('active');
                setTimeout(closeToast, 5000);
            }

            function closeToast() {
                document.getElementById('toast').classList.remove('active');
            }

            // Bind modal buttons
            document.getElementById('btnCloseModal').onclick = closeModal;
            document.getElementById('btnCancel').onclick = closeModal;
            modal.onclick = (e) => {
                if (e.target === modal) closeModal();
            };

            // Apply Range Action
            document.getElementById('btnApply').onclick = () => {
                const start = document.getElementById('startDateTime').value;
                const end = document.getElementById('endDateTime').value;

                if (!start || !end) {
                    showToast('error', 'Input Tidak Lengkap', 'Silakan pilih tanggal & waktu start dan end');
                    return;
                }
                const now = new Date();
                if (new Date(start) > now) return alert('Start tidak boleh melebihi waktu saat ini');
                if (new Date(end) > now) return alert('End tidak boleh melebihi waktu saat ini');
                if (new Date(start) >= new Date(end)) return alert('Start harus < End');

                closeModal();

                // Trigger Data Fetch for Table
                // Update UI button text if needed
                const btnCustom = document.getElementById('btnModeCustom');
                btnCustom.innerHTML = `<i class="fa-regular fa-calendar-check text-xs mr-1"></i> ${start} - ${end}`;

                // Set active mode
                setActiveMode('custom');

                // Fetch
                currentSortColumn = 'auto';
                tableState.page = 1;
                fetchTableData({
                    start,
                    end,
                    sort_by: 'auto'
                }, {
                    showLoading: true
                });
            };

            // --- TABLE MODE SWITCHING ---
            window.switchTableMode = function(mode) {
                if (mode === 'custom') {
                    openModal();
                } else if (mode === 'latest') {
                    setActiveMode('latest');
                    // Reset custom range text
                    document.getElementById('btnModeCustom').innerHTML =
                        `<i class="fa-regular fa-calendar-days"></i> Custom Range`;
                    currentSortColumn = 'auto'; // Reset to auto-sort on Summary mode
                    tableState.page = 1;
                    const filter = jQuery('#summaryFilterSelect').val();
                    fetchTableData({
                        filter: filter,
                        sort_by: 'auto'
                    }, {
                        showLoading: true
                    });
                }
            };

            // Listen to select change
            jQuery('#summaryFilterSelect').on('change', function() {
                switchTableMode('latest');
            });

            function setActiveMode(mode) {
                jQuery('.btn-table-mode').removeClass('active');
                // Since btnModeLatest is gone and replaced by select, we just manage btnModeCustom
                if (mode === 'latest') {
                    // Highlight nothing or maybe the select container if we had one
                }
                if (mode === 'custom') {
                    jQuery('#btnModeCustom').addClass('active');
                }
            }

            // --- DATA FETCHING ---
            let tableRequestInFlight = false;
            let failureCount = 0;
            let isOffline = false;

            function fetchTableData(params, options = {}) {
                if (tableRequestInFlight) return;
                const showLoading = !!options.showLoading;
                // Always include current sort
                if (!params.sort_by) params.sort_by = currentSortColumn;

                updateSortIcons(params.sort_by);

                tableRequestInFlight = true;
                if (showLoading) {
                    setTableLoading(true);
                }
                jQuery.ajax({
                    url: TABLE_ENDPOINT,
                    method: 'GET',
                    data: params,
                    dataType: 'json',
                    success: function(res) {
                        const rows = res.data || [];
                        renderTableRows(rows);
                        // Success - nothing to reset for infinite retry logic
                        // Reset failure count
                        failureCount = 0;
                        if (isOffline) {
                            isOffline = false;
                            showToast('success', 'Terhubung Kembali', 'Koneksi internet stabil.');
                        }
                    },
                    error: function(xhr) {
                        // Handle Session Timeout (401 / 419)
                        if (xhr.status === 401 || xhr.status === 419) {
                            console.warn('Session expired. Reloading page...');
                            window.location.reload();
                            return;
                        }

                        // Handle Network/Server Error (500, 503, etc.)
                        console.error('Table fetch error:', xhr.responseText || xhr.statusText);

                        // Increment failure count
                        failureCount++;

                        const reconnectAction = () => {
                            // Try immediately
                            tableRequestInFlight = false;
                            fetchTableData(params, {
                                showLoading: true
                            });
                        };

                        // If persistent failures (e.g. 3 times / ~15s), show error toast
                        if (failureCount >= 3 && !isOffline) {
                            isOffline = true;
                            showToast('error', 'Koneksi Terputus',
                                'Gagal menghubungi server. Klik Reconnect untuk mencoba lagi.', reconnectAction);
                        }

                        // Retry indefinitely
                        console.log(`Retrying fetchTableData (${failureCount}) in 5s...`);
                        setTimeout(() => {
                            tableRequestInFlight = false; // Reset flag to allow retry
                            fetchTableData(params, options);
                        }, 5000);
                    },
                    complete: function() {
                        // Only reset flight flag if not retrying immediately
                        // (If retrying, the retry logic handles it)
                        // But to be safe, we reset it here, and the retry logic will call fetchTableData again which checks the flag.
                        // Actually, better to reset it here always.
                        tableRequestInFlight = false;
                        if (showLoading) {
                            setTableLoading(false);
                        }
                    }
                });
            }

            window.toggleSort = function(column) {
                currentSortColumn = column;
                tableState.page = 1;

                // If custom range active, fetch with custom range params
                if (jQuery('#btnModeCustom').hasClass('active')) {
                    const start = document.getElementById('startDateTime').value;
                    const end = document.getElementById('endDateTime').value;
                    if (start && end) {
                        fetchTableData({
                            start,
                            end,
                            sort_by: column
                        }, {
                            showLoading: false
                        });
                        return;
                    }
                }

                // Else fetch with current summary filter
                const filter = jQuery('#summaryFilterSelect').val();
                fetchTableData({
                    filter,
                    sort_by: column
                }, {
                    showLoading: false
                });
            };

            function updateSortIcons(activeCol) {
                // Reset all
                jQuery('thead th i').removeClass('text-indigo-600').addClass('text-slate-300');
                // Set active if exists
                const $target = jQuery(`#sort-icon-${activeCol}`);
                if ($target.length) {
                    $target.removeClass('text-slate-300').addClass('text-indigo-600');
                }
            }

            function renderTableRows(rows) {
                tableState.rows = Array.isArray(rows) ? rows : [];

                const total = tableState.rows.length;
                const perPage = tableState.perPage;
                const totalPages = total > 0 ? Math.ceil(total / perPage) : 1;

                if (tableState.page > totalPages) tableState.page = totalPages;
                if (tableState.page < 1) tableState.page = 1;

                const startIdx = (tableState.page - 1) * perPage;
                const pageRows = tableState.rows.slice(startIdx, startIdx + perPage);

                let html = '';

                pageRows.forEach(m => {
                    const sensorTds = tableSensorMeta.map(s => {
                        const v = Number(m[s.key]);
                        const ok = Number.isFinite(v);
                        let txt = '-';

                        if (ok) {
                            // 0 => '-'
                            if (
                                v === 0 ||
                                ((s.key === 'temp' || s.key === 'moist' || s.key === 'amp' || s.key === 'hm') &&
                                    Math.round(v) === 0)
                            ) {
                                txt = '-';
                            } else {
                                txt = v.toFixed(s.decimals);
                            }
                        }

                        let unitHtml = '';
                        if (txt !== '-' && s.suffix) {
                            unitHtml =
                                ` <span class="text-xs text-slate-500 font-semibold">${esc(s.suffix)}</span>`;
                        }

                        return `<td data-label="${esc(s.key.toUpperCase())}" class="px-4 py-3 font-semibold text-slate-900">${esc(txt)}${unitHtml}</td>`;
                    }).join('');

                    html += `
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td data-label="No. Mesin" class="px-4 py-3 font-semibold text-slate-700">${esc(m.name ?? '-')}</td>
                            <td data-label="Timestamp" class="px-4 py-3 text-slate-600">${esc(m.last_updated ?? '-')}</td>
                            ${sensorTds}
                        </tr>
                    `;
                });

                if (!html) {
                    const colCount = 2 + tableSensorMeta.length;
                    html = `
                        <tr>
                            <td colspan="${colCount}" class="px-4 py-8 text-center text-slate-500">
                                Tidak ada data di range ini
                            </td>
                        </tr>
                    `;
                }

                jQuery('#machinesTableBody').html(html);

                const endIdx = total > 0 ? Math.min(startIdx + pageRows.length, total) : 0;
                const info = total > 0 ?
                    `Menampilkan ${startIdx + 1}-${endIdx} dari ${total} mesin - Halaman ${tableState.page}/${totalPages}` :
                    'Tidak ada data';
                jQuery('#paginationInfo').text(info);

                const now = new Date();
                const timeStr = now.getHours().toString().padStart(2, '0') + ':' +
                    now.getMinutes().toString().padStart(2, '0') + ':' +
                    now.getSeconds().toString().padStart(2, '0');
                jQuery('#tableLastSync').text(`Last Sync: ${timeStr}`);

                updatePaginationControls(totalPages);
            }

            function esc(str) {
                return String(str).replace(/[&<>"']/g, s => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [s]));
            }

            function updatePaginationControls(totalPages) {
                const prevBtn = document.getElementById('btnPagePrev');
                const nextBtn = document.getElementById('btnPageNext');
                if (!prevBtn || !nextBtn) return;

                const hasData = tableState.rows.length > 0;
                prevBtn.disabled = !hasData || tableState.page <= 1;
                nextBtn.disabled = !hasData || tableState.page >= totalPages;
            }

            function setTableLoading(isLoading) {
                const perPageSelect = document.getElementById('perPageSelect');
                const summarySelect = document.getElementById('summaryFilterSelect');
                const customBtn = document.getElementById('btnModeCustom');
                const prevBtn = document.getElementById('btnPagePrev');
                const nextBtn = document.getElementById('btnPageNext');

                if (perPageSelect) perPageSelect.disabled = isLoading;
                if (summarySelect) summarySelect.disabled = isLoading;
                if (customBtn) customBtn.disabled = isLoading;

                if (isLoading) {
                    const colCount = 2 + tableSensorMeta.length;
                    jQuery('#machinesTableBody').html(`
                        <tr>
                            <td colspan="${colCount}" class="px-4 py-8 text-center text-slate-500">
                                Memuat data...
                            </td>
                        </tr>
                    `);
                    jQuery('#paginationInfo').text('Memuat data...');
                    if (prevBtn) prevBtn.disabled = true;
                    if (nextBtn) nextBtn.disabled = true;
                    return;
                }

                const totalPages = tableState.rows.length > 0 ?
                    Math.ceil(tableState.rows.length / tableState.perPage) :
                    1;
                updatePaginationControls(totalPages);
            }

            // Table: setiap 5 detik (Summary)
            setInterval(() => {
                if (!jQuery('#btnModeCustom').hasClass('active')) {
                    const filter = jQuery('#summaryFilterSelect').val();
                    fetchTableData({
                        filter: filter
                    }, {
                        showLoading: false
                    });
                }
            }, 5000);

            // Initial Loads
            const initialFilter = jQuery('#summaryFilterSelect').val();
            fetchTableData({
                filter: initialFilter
            }, {
                showLoading: false
            });

            (function initTablePagination() {
                const perPageSelect = document.getElementById('perPageSelect');
                const prevBtn = document.getElementById('btnPagePrev');
                const nextBtn = document.getElementById('btnPageNext');

                if (perPageSelect) {
                    tableState.perPage = parseInt(perPageSelect.value, 10) || 25;
                    perPageSelect.addEventListener('change', () => {
                        tableState.perPage = parseInt(perPageSelect.value, 10) || 25;
                        tableState.page = 1;
                        renderTableRows(tableState.rows);
                    });
                }

                if (prevBtn) {
                    prevBtn.addEventListener('click', () => {
                        if (tableState.page > 1) {
                            tableState.page -= 1;
                            renderTableRows(tableState.rows);
                        }
                    });
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', () => {
                        const totalPages = tableState.rows.length > 0 ?
                            Math.ceil(tableState.rows.length / tableState.perPage) :
                            1;
                        if (tableState.page < totalPages) {
                            tableState.page += 1;
                            renderTableRows(tableState.rows);
                        }
                    });
                }
            })();

            // ========== REPORT MODAL FUNCTIONS ==========
            let reportStartPicker = null;
            let reportEndPicker = null;

            if (typeof flatpickr !== 'undefined') {
                reportStartPicker = flatpickr("#startDateTimeReport", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr: true,
                    defaultHour: 7,
                    defaultMinute: 0,
                    maxDate: new Date(),
                    minuteIncrement: 1,
                    onChange: function(selectedDates, dateStr) {
                        if (reportEndPicker) {
                            reportEndPicker.set('minDate', dateStr);
                        }
                    }
                });

                reportEndPicker = flatpickr("#endDateTimeReport", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr: true,
                    defaultHour: 7,
                    defaultMinute: 0,
                    maxDate: new Date(),
                    minuteIncrement: 1,
                    onChange: function(selectedDates, dateStr) {
                        if (reportStartPicker) {
                            reportStartPicker.set('maxDate', dateStr);
                        }
                    }
                });
            }

            // Open Report Modal
            window.openReportModal = function() {
                const modal = document.getElementById('reportDownloadModal');
                const now = new Date();
                if (reportStartPicker) reportStartPicker.set('maxDate', now);
                if (reportEndPicker) reportEndPicker.set('maxDate', now);
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            };

            // Close Report Modal
            window.closeReportModal = function() {
                const modal = document.getElementById('reportDownloadModal');
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';

                // Reset form
                document.getElementById('reportForm').reset();
                if (reportStartPicker) {
                    reportStartPicker.clear();
                    reportStartPicker.set('maxDate', null);
                }
                if (reportEndPicker) {
                    reportEndPicker.clear();
                    reportEndPicker.set('minDate', null);
                }
                document.getElementById('hariKerjaInput').value = '';
            };



            // Bind modal close buttons
            document.getElementById('btnCloseReportModal').onclick = closeReportModal;
            document.getElementById('btnCancelReport').onclick = closeReportModal;
            document.getElementById('reportDownloadModal').onclick = function(e) {
                if (e.target === this) closeReportModal();
            };

            // Form Validation
            document.getElementById('reportForm').onsubmit = function(e) {
                const fields = [{
                        id: 'startDateTimeReport',
                        name: 'Start Date'
                    },
                    {
                        id: 'endDateTimeReport',
                        name: 'End Date'
                    },
                    {
                        id: 'lineInput',
                        name: 'Line'
                    },
                    {
                        id: 'hariKerjaInput',
                        name: 'Hari Kerja'
                    },
                    {
                        id: 'jamKerjaInput',
                        name: 'Jam Kerja'
                    },
                    {
                        id: 'totalBriketInput',
                        name: 'Total Briket'
                    }
                ];

                for (let field of fields) {
                    const value = document.getElementById(field.id).value;
                    if (!value || value.trim() === '') {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Oops...',
                                text: 'Mohon isi field ' + field.name + ' terlebih dahulu!',
                                confirmButtonColor: '#10b981',
                            });
                        } else {
                            alert('Mohon isi field ' + field.name + ' terlebih dahulu!');
                        }
                        e.preventDefault();
                        return false;
                    }
                }
                return true;
            };
        </script>
    @endpush
</x-app-layout>
