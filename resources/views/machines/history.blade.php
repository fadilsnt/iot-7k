<x-app-layout>
    @push('head')
        @vite(['resources/css/app.css', 'resources/css/dashboard.css'])
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            #historyTableWrap {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            #historyDataTable {
                width: 100%;
                min-width: 760px;
            }

            @media (max-width: 768px) {
                #historyDataTable {
                    min-width: 100%;
                    border-collapse: separate;
                    border-spacing: 0 10px;
                }

                #historyDataTable thead {
                    display: none;
                }

                #historyDataTable tbody tr {
                    display: block;
                    border: 1px solid #e2e8f0;
                    border-radius: 12px;
                    background: #ffffff;
                    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
                    overflow: hidden;
                }

                #historyDataTable tbody td {
                    display: grid;
                    grid-template-columns: 110px 1fr;
                    gap: 10px;
                    align-items: center;
                    padding: 10px 12px;
                    border-bottom: 1px dashed #e2e8f0;
                    white-space: normal;
                    word-break: break-word;
                }

                #historyDataTable tbody td::before {
                    content: attr(data-label);
                    color: #64748b;
                    font-size: 0.75rem;
                    font-weight: 700;
                    letter-spacing: 0.02em;
                    text-transform: uppercase;
                }

                #historyDataTable tbody tr td:last-child {
                    border-bottom: none;
                }

                #historyDataTable tbody td[colspan] {
                    display: block;
                    text-align: center;
                }

                #historyDataTable tbody td[colspan]::before {
                    content: '';
                }

                #historyPagination {
                    flex-direction: column;
                    align-items: flex-start;
                }

                #historyPagination > div:last-child {
                    width: 100%;
                    display: flex;
                    justify-content: space-between;
                }

                #historyPagination button {
                    min-width: 90px;
                }
            }
        </style>
    @endpush

    <header class="dashboard-header"
        style="display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;position:relative;">
        <span class="text-xs uppercase tracking-wide text-white/80 flex items-center gap-2">
            PT. Tuju Kuda Hitam Sakti
        </span>
        <h1 style="font-size: 1.25rem; margin-top: 0.5rem;">Riwayat Performa {{ $machine_id }}</h1>
        <a href="{{ route('machines.index') }}" class="absolute left-6 top-1/2 -translate-y-1/2 flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white hover:bg-white/30 transition">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
    </header>

    <div class="content-container" style="padding-top: 0;">
        <div class="data-row">
            <div class="table-card" style="grid-column: 1 / -1;">
                <div class="table-header-controls" style="display:flex;flex-wrap:wrap;gap:0.75rem;align-items:center;justify-content:space-between;">
                    <h3>Ringkasan Riwayat Mesin</h3>
                    <div class="table-controls" style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;justify-content:flex-end;flex:1;min-width:260px;">
                        <select class="filter-select filter-pill" id="historyFilterSelect">
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
                        <select class="filter-select filter-pill" id="historyPerPageSelect">
                            <option value="50" selected>50</option>
                            <option value="100">100</option>
                            <option value="200">200</option>
                            <option value="300">300</option>
                            <option value="400">400</option>
                            <option value="500">500</option>
                            <option value="all">All</option>
                        </select>
                        <button class="btn-custom-range btn-custom-range-pill" id="btnHistoryCustom">
                            <i class="fa-regular fa-calendar-days"></i> Custom Range
                        </button>
                        <button class="btn-custom-range btn-custom-range-pill" id="btnDownloadExcel" style="border-color: #4f76ff; color: #4f76ff; box-shadow: 0 8px 18px rgba(79, 118, 255, 0.12);">
                            <i class="fa-solid fa-file-excel"></i> Download Excel
                        </button>
                    </div>
                </div>

                <div class="table-shell" id="historyTableWrap">
                    <table id="historyDataTable">
                        <thead>
                            <tr>
                                <th class="text-slate-500">Timestamp</th>
                                <th class="text-slate-500">AMP</th>
                                <th class="text-slate-500">HM</th>
                                <th class="text-slate-500">TEMP</th>
                                <th class="text-slate-500">MOIST</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                    Memuat data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="historyPagination" class="px-4 py-3 text-sm text-slate-600 flex items-center justify-between gap-3" style="flex-wrap:wrap;"></div>
            </div>
        </div>
    </div>

    {{-- Custom Range Modal --}}
    <div id="customRangeModal" class="modal-overlay custom-range-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-regular fa-calendar-days"></i> Select Custom Date & Time Range</h3>
                <button type="button" id="btnCloseModal" class="modal-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="date-picker-group">
                    <div class="date-picker-field">
                        <label><i class="fa-solid fa-calendar-check"></i> Start Date & Time</label>
                        <input type="text" id="startDateTime" class="datetime-input" placeholder="Select start date & time">
                    </div>
                    <div class="date-picker-field">
                        <label><i class="fa-solid fa-calendar-check"></i> End Date & Time</label>
                        <input type="text" id="endDateTime" class="datetime-input" placeholder="Select end date & time">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: transparent; border-top: none; padding-top: 0;">
                <button type="button" id="btnCancel" class="btn-cancel">Cancel</button>
                <button type="button" id="btnApply" class="btn-apply">Terapkan Range</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        const DATA_ENDPOINT = "{{ route('machines.history-data', ['machine_id' => $machine_id]) }}";
        const EXPORT_ENDPOINT = "{{ route('machines.history-export', ['machine_id' => $machine_id]) }}";
        
        const tableSensorMeta = [
            { key: 'amp',   decimals: 2, suffix: ' A' },
            { key: 'hm',    decimals: 2, suffix: '' },
            { key: 'temp',  decimals: 0, suffix: '&deg; C' },
            { key: 'moist', decimals: 0, suffix: '%' }
        ];

        let currentParams = { filter: '1h' };
        let currentPage = 1;
        let currentPerPage = '50';
        let startPicker = null, endPicker = null;

        function esc(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // --- INITIALIZATION ---
        jQuery(document).ready(() => {
            initPickers();
            fetchHistory();

            jQuery('#historyFilterSelect').on('change', function() {
                currentParams = { filter: jQuery(this).val() };
                currentPage = 1;
                jQuery('#btnHistoryCustom').html('<i class="fa-regular fa-calendar-days"></i> Custom Range');
                fetchHistory();
            });

            jQuery('#historyPerPageSelect').on('change', function() {
                currentPerPage = jQuery(this).val();
                currentPage = 1;
                fetchHistory();
            });

            jQuery('#btnHistoryCustom').on('click', () => jQuery('#customRangeModal').addClass('active'));
            jQuery('#btnCloseModal, #btnCancel').on('click', () => jQuery('#customRangeModal').removeClass('active'));
            
            jQuery('#btnApply').on('click', () => {
                const start = jQuery('#startDateTime').val();
                const end = jQuery('#endDateTime').val();
                if(!start || !end) return alert('Pilih rentang waktu!');
                
                currentParams = { start, end };
                currentPage = 1;
                jQuery('#btnHistoryCustom').html(`<i class="fa-regular fa-calendar-check text-xs mr-1"></i> ${start} - ${end}`);
                jQuery('#customRangeModal').removeClass('active');
                fetchHistory();
            });

            jQuery(document).on('click', '.history-page-btn', function() {
                const targetPage = Number(jQuery(this).data('page'));
                if (!targetPage || jQuery(this).prop('disabled')) return;

                currentPage = targetPage;
                fetchHistory();
            });

            jQuery('#btnDownloadExcel').on('click', () => {
                const url = new URL(EXPORT_ENDPOINT);
                Object.keys(currentParams).forEach(key => url.searchParams.append(key, currentParams[key]));
                window.location.href = url.toString();
            });
        });

        function initPickers() {
            startPicker = flatpickr("#startDateTime", {
                enableTime: true, dateFormat: "Y-m-d H:i", time_24hr: true, maxDate: new Date(),
                onChange: (_, dateStr) => { if(endPicker) endPicker.set('minDate', dateStr) }
            });
            endPicker = flatpickr("#endDateTime", {
                enableTime: true, dateFormat: "Y-m-d H:i", time_24hr: true, maxDate: new Date(),
                onChange: (_, dateStr) => { if(startPicker) startPicker.set('maxDate', dateStr) }
            });
        }

        function fetchHistory() {
            setTableLoading(true);
            console.log('fetchHistory start', currentParams);

            const requestParams = {
                ...currentParams,
                page: currentPage,
                per_page: currentPerPage
            };

            jQuery.ajax({
                url: DATA_ENDPOINT,
                data: requestParams,
                method: 'GET',
                dataType: 'json',
                timeout: 10000 
            })
            .done((res) => {
                console.log('fetchHistory success', res);
                renderTable(res.data);
                renderPagination(res.pagination);
            })
            .fail((xhr) => {
                console.error('fetchHistory fail', xhr);
                const msg = xhr.responseJSON ? (xhr.responseJSON.message || xhr.responseJSON.error) : 'Gagal memuat data (Timeout/Jaringan)';
                jQuery('#historyTableBody').html(`<tr><td colspan="5" class="px-4 py-8 text-center text-red-500 font-semibold">${msg}</td></tr>`);
                jQuery('#historyPagination').html('');
            })
            .always(() => {
                console.log('fetchHistory complete');
                setTableLoading(false);
            });
        }

        function renderPagination(pagination) {
            if (!pagination) {
                jQuery('#historyPagination').html('');
                return;
            }

            const currentPageNum = Number(pagination.current_page) || 1;
            const lastPageNum = Number(pagination.last_page) || 1;
            const prevDisabled = currentPageNum <= 1 ? 'disabled' : '';
            const nextDisabled = currentPageNum >= lastPageNum ? 'disabled' : '';
            const from = Number(pagination.from) || 0;
            const to = Number(pagination.to) || 0;
            const total = Number(pagination.total) || 0;

            let pagerHtml = '';
            if (lastPageNum > 1) {
                pagerHtml = `
                    <div class="flex items-center gap-2">
                        <button type="button" class="history-page-btn px-3 py-1 rounded border border-slate-300 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed" data-page="${currentPageNum - 1}" ${prevDisabled}>Prev</button>
                        <span>Hal. ${currentPageNum} / ${lastPageNum}</span>
                        <button type="button" class="history-page-btn px-3 py-1 rounded border border-slate-300 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed" data-page="${currentPageNum + 1}" ${nextDisabled}>Next</button>
                    </div>
                `;
            }

            jQuery('#historyPagination').html(`
                <div>Menampilkan ${from} - ${to} dari ${total} data</div>
                ${pagerHtml}
            `);
        }

        function renderTable(rows) {
            let html = '';
            if (!rows || rows.length === 0 || (rows.length === 1 && rows[0].last_updated === '-')) {
                html = '<tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Tidak ada data untuk rentang waktu ini</td></tr>';
            } else {
                rows.forEach(m => {
                    html += `
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td data-label="Timestamp" class="px-4 py-3 text-slate-600">${esc(m.last_updated)}</td>
                            ${tableSensorMeta.map(s => {
                                const v = Number(m[s.key]);
                                const isFinite = Number.isFinite(v);
                                let txt = '-';
                                
                                if (isFinite) {
                                    if (v === 0 || Math.round(v) === 0) {
                                        txt = '-';
                                    } else {
                                        txt = v.toFixed(s.decimals) + '<span class="text-xs text-slate-500 font-semibold">' + s.suffix + '</span>';
                                    }
                                }
                                return `<td data-label="${s.key.toUpperCase()}" class="px-4 py-3 font-semibold text-slate-900">${txt}</td>`;
                            }).join('')}
                        </tr>`;
                });
            }
            jQuery('#historyTableBody').html(html);
        }

        function setTableLoading(isLoading) {
            if (isLoading) {
                jQuery('#historyTableBody').html('<tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Memuat data...</td></tr>');
            }
        }
    </script>
    @endpush
</x-app-layout>
