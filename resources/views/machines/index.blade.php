<x-app-layout>
    @php
        // Mapping 4 sensor per mesin (ubah sesuai data kamu)
        $sensorMeta = [
            ['key' => 'amp', 'label' => 'AMP', 'unit' => 'A', 'decimals' => 2],
            ['key' => 'hm', 'label' => 'HM', 'unit' => '', 'decimals' => 2],
            ['key' => 'temp', 'label' => 'TEMP', 'unit' => '째 C', 'decimals' => 0],
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
            <!-- <i class="fa-solid fa-industry"></i> -->
        </span>
        <h1 style="font-size: 1.25rem; margin-top: 0.5rem;">Monitoring Mesin</h1>
    </header>

    <div class="content-container">

        {{-- GRID 4 x N --}}
        <div id="machineGrid" class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-2">
            @foreach ($machines as $m)
                @php
                    $rawName = $m['name'] ?? 'MESIN';
                    $slug = preg_replace('/[^A-Za-z0-9_-]+/', '-', $rawName);
                    $slug = trim($slug, '-');
                    $cardId = 'card-' . ($slug !== '' ? $slug : $m['id'] ?? 'unknown');
                @endphp
                <div id="{{ $cardId }}"
                    class="rounded-xl border border-[#4f76ff] bg-white shadow-sm hover:shadow-md transition overflow-hidden">
                    <div class="p-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-[13px] font-extrabold text-slate-900 leading-tight">
                                    {{ $m['name'] ?? 'MESIN' }}
                                </div>
                                <div class="mt-0.5 text-[9px] font-mono text-slate-500 last-updated">
                                    {{ $m['last_updated'] ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 sensor-values">
                            @foreach ($sensorMeta as $s)
                                @php
                                    $val = $m[$s['key']] ?? null;
                                @endphp
                                <div class="rounded-xl bg-slate-50 p-3">
                                    <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">
                                        {{ $s['label'] }}
                                    </div>
                                    <div class="mt-1 text-base font-black text-slate-900">
                                        <span class="sensor-value" data-sensor="{{ $s['key'] }}">
                                            @if (in_array($s['key'], ['temp', 'moist', 'amp', 'hm']) && (float) $val == 0)
                                                -
                                            @else
                                                {{ is_numeric($val) ? number_format($val, $s['decimals']) : '-' }}
                                            @endif
                                        </span>
                                        @if (in_array($s['key'], ['temp', 'moist', 'amp', 'hm']) && (float) $val == 0)
                                            {{-- Hide unit --}}
                                        @elseif($s['unit'] !== '')
                                            <span
                                                class="text-xs font-bold text-slate-500 sensor-unit">{{ $s['unit'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <a href="{{ route('machines.history', ['machine_id' => $m['name'] ?? 'MESIN']) }}"
                        class="block w-full text-center border-t border-[#4f76ff] py-1.5 text-[11px] font-semibold text-[#4f76ff] hover:bg-[#4f76ff] hover:text-white transition">
                        Riwayat Mesin
                    </a>
                </div>
            @endforeach
        </div>
        <br>
        <div class="mt-5 flex justify-center">
            <a href="{{ route('machines.kesimpulan') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#4f76ff] px-5 py-3 text-sm font-extrabold text-black shadow-sm hover:shadow-md hover:bg-[#3f66ff] transition w-full sm:w-auto border-2 border-[#4f76ff]">
                <i class="fa-solid fa-chart-line"></i>
                Lihat Kesimpulan
            </a>
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
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>

        <script>
            const GRID_ENDPOINT = "{{ route('machines.get-data') }}";

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
                    unit: '째  C',
                    decimals: 0
                },
                {
                    key: 'moist',
                    label: 'MOIST',
                    unit: '%',
                    decimals: 0
                },
            ];

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

            function esc(str) {
                return String(str).replace(/[&<>"']/g, s => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [s]));
            }

            // --- AUTO REFRESH LOGIC ---
            let gridRequestInFlight = false;
            let gridFailureCount = 0;
            let gridIsOffline = false;

            function fetchGridData() {
                if (gridRequestInFlight) return;
                gridRequestInFlight = true;
                jQuery.ajax({
                    url: GRID_ENDPOINT,
                    method: 'GET',
                    data: {
                        _t: Date.now()
                    }, // Cache busting
                    dataType: 'json',
                    success: function(res) {
                        renderGridCards(res.data || []);
                        gridFailureCount = 0;
                        if (gridIsOffline) {
                            gridIsOffline = false;
                            showToast('success', 'Terhubung Kembali', 'Koneksi internet stabil.');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 401 || xhr.status === 419) {
                            window.location.reload();
                            return;
                        }
                        console.error("Grid fetch failed", xhr.statusText);

                        gridFailureCount++;
                        const reconnectAction = () => {
                            gridRequestInFlight = false;
                            fetchGridData();
                        };
                        if (gridFailureCount >= 3 && !gridIsOffline) {
                            gridIsOffline = true;
                            showToast('error', 'Koneksi Terputus',
                                'Gagal menghubungi server. Klik Reconnect untuk mencoba lagi.', reconnectAction);
                        }
                    },
                    complete: function() {
                        gridRequestInFlight = false;
                    }
                });
            }

            function toCardId(name, id) {
                const raw = (name ?? '').toString();
                const slug = raw.replace(/[^A-Za-z0-9_-]+/g, '-').replace(/^-+|-+$/g, '');
                const safe = slug || (id !== undefined && id !== null ? String(id) : 'unknown');
                return `card-${safe}`;
            }

            function cssEscape(value) {
                if (window.CSS && typeof window.CSS.escape === 'function') {
                    return window.CSS.escape(value);
                }
                return String(value).replace(/[^a-zA-Z0-9_-]/g, '\\$&');
            }

            function renderGridCards(machines) {
                const $grid = jQuery('#machineGrid');
                const seen = new Set();

                machines.forEach(m => {
                    const cardId = toCardId(m.name, m.id);
                    const selector = `#${cssEscape(cardId)}`;
                    let $card = jQuery(selector);
                    seen.add(cardId);

                    if (!$card.length) {
                        // CREATE CARD ONCE
                        let sensorsHtml = gridSensorMeta.map(s => `
                            <div class="rounded-xl bg-slate-50 p-3">
                                <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">
                                    ${s.label}
                                </div>
                                <div class="mt-1 text-base font-black text-slate-900">
                                    <span class="sensor-value" data-sensor="${s.key}">-</span>
                                    ${s.unit ? `<span class="text-xs font-bold text-slate-500 sensor-unit" style="display:none;">${s.unit}</span>` : ''}
                                </div>
                            </div>
                        `).join('');

                        $grid.append(`
                            <div id="${cardId}" class="rounded-xl border border-[#4f76ff] bg-white shadow-sm hover:shadow-md transition overflow-hidden">
                                <div class="p-3">
                                    <div class="text-[13px] font-extrabold text-slate-900 leading-tight">${esc(m.name ?? 'MESIN')}</div>
                                    <div class="mt-0.5 text-[9px] font-mono text-slate-500 last-updated">-</div>
                                    <div class="mt-3 grid grid-cols-2 gap-2 sensor-values">
                                        ${sensorsHtml}
                                    </div>
                                </div>
                                <a href="/machines/${encodeURIComponent(m.name ?? 'MESIN')}/history" 
                                   class="block w-full text-center border-t border-[#4f76ff] py-1.5 text-[11px] font-semibold text-[#4f76ff] hover:bg-[#4f76ff] hover:text-white transition">
                                    Riwayat Mesin
                                </a>
                            </div>
                        `);

                        $card = jQuery(selector);
                    }

                    // UPDATE VALUES ONLY
                    $card.find('.last-updated').text(m.last_updated ?? '-');

                    gridSensorMeta.forEach(s => {
                        const v = Number(m[s.key]);
                        const isFinite = Number.isFinite(v);

                        let txt = '-';
                        if (isFinite) {
                            if (v === 0 || ((s.key === 'temp' || s.key === 'moist' || s.key === 'amp' || s
                                    .key === 'hm') && Math.round(v) === 0)) {
                                txt = '-';
                            } else {
                                txt = v.toFixed(s.decimals);
                            }
                        }

                        const $val = $card.find(`.sensor-value[data-sensor="${s.key}"]`);
                        $val.text(txt);

                        // Toggle unit visibility
                        const $unit = $val.next('.sensor-unit');
                        if (txt === '-') {
                            $unit.hide();
                        } else {
                            $unit.show();
                        }

                        // Highlighting grid box
                        const $box = $val.closest('.rounded-xl');
                        let isCritical = false;
                        /* if (isFinite) {
                            if (s.key === 'amp' && v < 14) isCritical = true;
                            if (s.key === 'temp' && (v < 55 || v > 65)) isCritical = true;
                            if (s.key === 'moist' && (v < 15 || v > 25)) isCritical = true;
                        } */

                        if (isCritical) {
                            $box.removeClass('bg-slate-50').addClass('bg-red-50 border border-red-100');
                            $val.removeClass('text-slate-900').addClass('text-red-700');
                        } else {
                            $box.removeClass('bg-red-50 border border-red-100').addClass('bg-slate-50');
                            $val.removeClass('text-red-700').addClass('text-slate-900');
                        }
                    });
                });

                $grid.children('[id^="card-"]').each(function() {
                    if (!seen.has(this.id)) {
                        jQuery(this).remove();
                    }
                });
            }

            // Polling setiap 10 detik
            // Grid: realtime
            setInterval(fetchGridData, 10000);

            // Initial Loads
            fetchGridData();
        </script>
    @endpush
</x-app-layout>
