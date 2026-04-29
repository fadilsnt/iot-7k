<x-app-layout>
    @push('head')
        @vite(['resources/css/dashboard.css'])
    @endpush

    <header class="dashboard-header"
        style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; position: relative;">

        <span class="text-xs uppercase tracking-wide text-white/80 flex items-center gap-2">
            <i class="fa-solid fa-temperature-high"></i>
            PT. Tuju Kuda Hitam Sakti
        </span>
        <h1 style="font-size: 1.25rem; margin-top: 0.5rem;">IoT Dashboard Suhu & Kadar Air</h1>
        <!-- Notification Bell -->
        <div class="notification-bell" id="notificationBell">
            <div class="bell-icon" id="bellIcon">
                <i class="fa-solid fa-bell"></i>
                <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
            </div>
        </div>

        <!-- Notification Panel -->
        <div class="notification-panel" id="notificationPanel">
            <div class="notification-header">
                <h3><i class="fa-solid fa-bell"></i> Notifikasi Sensor</h3>
            </div>
            <div class="notification-list" id="notificationList">
                <div class="notification-empty">
                    <i class="fa-solid fa-bell-slash"></i>
                    <p>Tidak ada notifikasi</p>
                </div>
            </div>
            <button class="clear-all-btn" id="clearAllBtn" style="display: none;">
                <i class="fa-solid fa-check-double"></i> Tandai Semua Sudah Dibaca
            </button>
        </div>
    </header>
    <div class="content-container">
        <div class="stats-row">
            <div class="gauge-card">
                <h3>Suhu</h3>
                <div class="gauge-area">
                    <canvas id="tempGauge"></canvas>
                </div>
                <span id="tempStatus" class="status-chip normal">Normal</span>
            </div>
            <div class="chart-card">
                <div class="chart-header-with-controls">
                    <div>
                        <p class="chart-heading">Tren Suhu</p>
                    </div>
                    <div class="chart-controls">
                        <select class="filter-select" data-chart="temp" id="tempFilterSelect">
                            <option value="24h">24 Hours</option>
                            <option value="8h">8 Hours</option>
                            <option value="1h" selected>1 Hour</option>
                            <option value="30m">30 Minutes</option>
                            <option value="15m">15 Minutes</option>
                            <option value="5m">5 Minutes</option>
                        </select>
                        <button class="btn-custom-range" data-chart="temp">
                            <i class="fa-regular fa-calendar-days"></i> Custom Range
                        </button>
                    </div>
                </div>
                <div class="chart-wrapper" style="height:300px;">
                    <canvas id="tempLine"></canvas>
                </div>
            </div>
        </div>
        <div class="stats-row" style="margin-top:1rem;">
            <div class="gauge-card">
                <h3>Kadar Air</h3>
                <div class="gauge-area">
                    <canvas id="moistGauge"></canvas>
                </div>
                <span id="moistStatus" class="status-chip normal">Normal</span>
            </div>
            <div class="chart-card">
                <div class="chart-header-with-controls">
                    <div>
                        <p class="chart-heading">Tren Kadar Air</p>
                    </div>
                    <div class="chart-controls">
                        <select class="filter-select" data-chart="moist" id="moistFilterSelect">
                            <option value="24h">24 Hours</option>
                            <option value="8h">8 Hours</option>
                            <option value="1h" selected>1 Hour</option>
                            <option value="30m">30 Minutes</option>
                            <option value="15m">15 Minutes</option>
                            <option value="5m">5 Minutes</option>
                        </select>
                        <button class="btn-custom-range" data-chart="moist">
                            <i class="fa-regular fa-calendar-days"></i> Custom Range
                        </button>
                    </div>
                </div>
                <div class="chart-wrapper" style="height:300px;">
                    <canvas id="moistLine"></canvas>
                </div>
            </div>
        </div>
        <div class="data-row">
            <div class="table-card">
                <div class="table-header-controls">
                    <h3>Data Suhu</h3>
                    <div class="table-controls">
                        <button class="btn-table-mode active" data-mode="latest" data-table="temp">
                            <i class="fa-solid fa-clock"></i> Last Data
                        </button>
                        <button class="btn-table-mode" data-mode="custom" data-table="temp">
                            <i class="fa-regular fa-calendar-days"></i> Custom Range
                        </button>
                        <button class="btn-table-mode" data-mode="download" data-table="temp"
                            onclick="openDownloadModal('temp')">
                            <i class="fa-solid fa-download"></i> Download Data
                        </button>
                    </div>
                </div>
                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Temperature (°C)</th>
                            </tr>
                        </thead>
                        <tbody id="tempTableBody"></tbody>
                    </table>
                </div>
                <div class="table-footer">
                    <div class="table-footer-left">
                        <label for="tempPerPage">Show:</label>
                        <select id="tempPerPage" class="pagination-select">
                            <option value="10">10</option>
                            <option value="20" selected>20</option>
                            <option value="30">30</option>
                            <option value="100">100</option>
                        </select>
                        <span class="pagination-info" id="tempPaginationInfo">Showing 1-20 of 100</span>
                    </div>
                    <div class="table-footer-right">
                        <button class="btn-pagination" id="tempPrevBtn" disabled>
                            <i class="fa-solid fa-chevron-left"></i> Back
                        </button>
                        <button class="btn-pagination" id="tempNextBtn">
                            Next <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="table-card">
                <div class="table-header-controls">
                    <h3>Data K.Air</h3>
                    <div class="table-controls">
                        <button class="btn-table-mode active" data-mode="latest" data-table="moist">
                            <i class="fa-solid fa-clock"></i> Last Data
                        </button>
                        <button class="btn-table-mode" data-mode="custom" data-table="moist">
                            <i class="fa-regular fa-calendar-days"></i> Custom Range
                        </button>
                        <button class="btn-table-mode" data-mode="download" data-table="moist"
                            onclick="openDownloadModal('moist')">
                            <i class="fa-solid fa-download"></i> Download Data
                        </button>
                    </div>
                </div>
                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Moisture (%)</th>
                            </tr>
                        </thead>
                        <tbody id="moistTableBody"></tbody>
                    </table>
                </div>
                <div class="table-footer">
                    <div class="table-footer-left">
                        <label for="moistPerPage">Show:</label>
                        <select id="moistPerPage" class="pagination-select">
                            <option value="10">10</option>
                            <option value="20" selected>20</option>
                            <option value="30">30</option>
                            <option value="100">100</option>
                        </select>
                        <span class="pagination-info" id="moistPaginationInfo">Showing 1-20 of 100</span>
                    </div>
                    <div class="table-footer-right">
                        <button class="btn-pagination" id="moistPrevBtn" disabled>
                            <i class="fa-solid fa-chevron-left"></i> Back
                        </button>
                        <button class="btn-pagination" id="moistNextBtn">
                            Next <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">Memuat Data...</div>
            <div class="loading-subtext">Mohon tunggu sebentar</div>
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
        <button class="toast-close" onclick="closeToast()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Custom Range Modal -->
    <div id="customRangeModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-regular fa-calendar-days"></i> Select Custom Date & Time Range</h3>
                <button class="modal-close" onclick="closeCustomRangeModal()">
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
                    <button class="btn-cancel" onclick="closeCustomRangeModal()">
                        <i class="fa-solid fa-ban"></i> Cancel
                    </button>
                    <button class="btn-apply" onclick="applyCustomRange()">
                        <i class="fa-solid fa-check"></i> Apply Range
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Range Modal for Table -->
    <div id="customRangeTableModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-regular fa-calendar-days"></i> Select Date & Time Range for Table</h3>
                <button class="modal-close" onclick="closeCustomRangeTableModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="date-picker-group">
                    <div class="date-picker-field">
                        <label><i class="fa-solid fa-calendar-check"></i> Start Date & Time</label>
                        <input type="text" id="startDateTimeTable" class="datetime-input"
                            placeholder="Select start date & time">
                    </div>
                    <div class="date-picker-field">
                        <label><i class="fa-solid fa-calendar-xmark"></i> End Date & Time</label>
                        <input type="text" id="endDateTimeTable" class="datetime-input"
                            placeholder="Select end date & time">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" onclick="closeCustomRangeTableModal()">
                        <i class="fa-solid fa-ban"></i> Cancel
                    </button>
                    <button class="btn-apply" onclick="applyCustomRangeTable()">
                        <i class="fa-solid fa-check"></i> Apply Range
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Download Data Modal -->
    <div id="downloadModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-download"></i> Download Data Excel</h3>
                <button class="modal-close" onclick="closeDownloadModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="date-picker-group">
                    <div class="date-picker-field">
                        <label><i class="fa-solid fa-calendar-check"></i> Start Date & Time</label>
                        <input type="text" id="startDateTimeDownload" class="datetime-input"
                            placeholder="Select start date & time">
                    </div>
                    <div class="date-picker-field">
                        <label><i class="fa-solid fa-calendar-xmark"></i> End Date & Time</label>
                        <input type="text" id="endDateTimeDownload" class="datetime-input"
                            placeholder="Select end date & time">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" onclick="closeDownloadModal()">
                        <i class="fa-solid fa-ban"></i> Cancel
                    </button>
                    <button class="btn-download" onclick="downloadData()">
                        <i class="fa-solid fa-file-excel"></i> Download Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer class="ottoman-footer">
        <p>&copy; <span id="footerYear">{{ date('Y') }}</span> Thermocouple Monitoring. All rights reserved.</p>
    </footer>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script>
            // Global variables
            window.currentChartForModal = null;
            window.startPicker = null;
            window.endPicker = null;

            jQuery(document).ready(function($) {
                const TEMP_LIMITS = {
                    warn: 32,
                    alert: 35,
                    max: 100
                };
                const MOIST_LIMITS = {
                    dry: 40,
                    humid: 75,
                    max: 100
                };
                const REFRESH_INTERVAL = 1000;

                // Expose limits to window for global access
                window.TEMP_LIMITS = TEMP_LIMITS;
                window.MOIST_LIMITS = MOIST_LIMITS;

                // Deklarasi variabel untuk filter & custom range mode
                let currentTempFilter = '24h';
                let currentMoistFilter = '24h';
                let isCustomRangeActive = false; // Flag untuk mode custom range
                let autoRefreshIntervalId = null;

                // Table state management - MOVED TO TOP
                const tableState = {
                    temp: {
                        mode: 'latest', // 'latest' or 'custom'
                        currentPage: 1,
                        perPage: 20,
                        totalData: 0,
                        allData: [],
                        customStart: null,
                        customEnd: null
                    },
                    moist: {
                        mode: 'latest',
                        currentPage: 1,
                        perPage: 20,
                        totalData: 0,
                        allData: [],
                        customStart: null,
                        customEnd: null
                    }
                };

                let tempLatest = @json($tempLatest);
                let moistLatest = @json($moistLatest);

                // Threshold data from sensor
                const tempThreshold = {
                    plus_1: {{ $tempSensor->threshold_plus_1 ?? 'null' }},
                    plus_2: {{ $tempSensor->threshold_plus_2 ?? 'null' }},
                    min_1: {{ $tempSensor->threshold_min_1 ?? 'null' }},
                    min_2: {{ $tempSensor->threshold_min_2 ?? 'null' }}
                };

                const moistThreshold = {
                    plus_1: {{ $moistSensor->threshold_plus_1 ?? 'null' }},
                    plus_2: {{ $moistSensor->threshold_plus_2 ?? 'null' }},
                    min_1: {{ $moistSensor->threshold_min_1 ?? 'null' }},
                    min_2: {{ $moistSensor->threshold_min_2 ?? 'null' }}
                };

                // Expose thresholds to window for global access
                window.tempThreshold = tempThreshold;
                window.moistThreshold = moistThreshold;
                let tempChartData = @json($tempChartData);
                let moistChartData = @json($moistChartData);

                // Expose tableState to window for modal functions
                window.tableState = tableState;

                const elements = {
                    tempStatus: document.getElementById('tempStatus'),
                    moistStatus: document.getElementById('moistStatus'),
                };

                const singleGauge = {
                    id: 'singleGauge',
                    beforeDatasetsDraw(chart) {
                        if (chart.config.type !== 'doughnut') return;
                        const pluginOpts = chart.options.plugins?.singleGauge;
                        if (!pluginOpts || pluginOpts.enabled !== true) return;
                        const area = chart.chartArea || {};
                        const {
                            left,
                            right,
                            top,
                            bottom,
                            width,
                            height
                        } = area;
                        if (width === undefined) return;
                        const ctx = chart.ctx;
                        const cx = (left + right) / 2;
                        const cy = (top + bottom) / 2;
                        const padding = 10;
                        const radius = Math.min(width / 2, height) - padding;
                        const cutoutRatio = 0.70;
                        const thickness = Math.max(6, radius * (1 - cutoutRatio));
                        const r = radius - thickness / 2;
                        const start = Math.PI;
                        const endFull = start + Math.PI;
                        ctx.save();
                        ctx.beginPath();
                        ctx.lineWidth = thickness;
                        ctx.strokeStyle = '#e5e7eb';
                        ctx.lineCap = 'round';
                        ctx.arc(cx, cy, r, start, endFull);
                        ctx.stroke();
                        ctx.restore();
                        const ratio = pluginOpts.value || 0;
                        const color = pluginOpts.color || '#10b981';
                        const end = start + Math.PI * Math.max(0, Math.min(1, ratio));
                        ctx.save();
                        ctx.beginPath();
                        ctx.lineWidth = thickness;
                        ctx.strokeStyle = color;
                        ctx.lineCap = 'round';
                        ctx.arc(cx, cy, r, start, end);
                        ctx.stroke();
                        ctx.restore();
                    },
                    afterDraw(chart) {
                        if (chart.config.type !== 'doughnut') return;
                        const pluginOpts = chart.options.plugins?.singleGauge;
                        if (!pluginOpts || pluginOpts.enabled !== true) return;
                        const area = chart.chartArea || {};
                        const {
                            left,
                            right,
                            top,
                            bottom,
                            width,
                            height
                        } = area;
                        if (width === undefined) return;
                        const ctx = chart.ctx;
                        const cx = (left + right) / 2;
                        const cy = (top + bottom) / 2;
                        const label = pluginOpts.valueText || '';
                        ctx.save();
                        ctx.fillStyle = '#1f2937';
                        ctx.font = '600 20px Poppins, sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(label, cx, cy);
                        ctx.restore();
                    }
                };
                Chart.register(singleGauge);

                if (!Chart.Tooltip.positioners.centerTop) {
                    Chart.Tooltip.positioners.centerTop = (items) => {
                        if (!items || !items.length) return {
                            x: 0,
                            y: 0
                        };
                        const chart = items[0].element.$context.chart;
                        const area = chart?.chartArea;
                        if (!area) return {
                            x: 0,
                            y: 0
                        };
                        return {
                            x: (area.left + area.right) / 2,
                            y: area.top + 8
                        };
                    };
                }

                function createGauge(id, color) {
                    const ctx = document.getElementById(id);
                    return new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            datasets: [{
                                data: [1],
                                backgroundColor: ['rgba(0,0,0,0)'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            rotation: -90,
                            circumference: Math.PI,
                            cutout: '70%',
                            plugins: {
                                legend: {
                                    display: false
                                },
                                singleGauge: {
                                    enabled: true,
                                    valueText: '',
                                    value: 0,
                                    valueRaw: 0,
                                    color,
                                    unit: ''
                                },
                                tooltip: {
                                    enabled: true,
                                    position: 'centerTop',
                                    callbacks: {
                                        label: (ctx) => {
                                            const opt = ctx.chart.options.plugins.singleGauge || {};
                                            const v = opt.valueRaw ?? 0;
                                            const u = opt.unit ?? '';
                                            return `${v.toFixed(2)} ${u}`;
                                        }
                                    },
                                    displayColors: false
                                }
                            },
                            interaction: {
                                mode: 'nearest',
                                intersect: true
                            },
                            events: ['mousemove', 'mouseleave', 'touchstart', 'touchmove', 'touchend']
                        },
                    });
                }

                // Function to determine sensor status based on threshold
                function getSensorStatus(value, threshold, sensorType) {
                    // Default color based on sensor type
                    const defaultColor = sensorType === 'moist' ? '#10b981' :
                        '#3b82f6'; // green for moisture, blue for temp
                    const defaultBgColor = sensorType === 'moist' ? '#d1fae5' : '#dbeafe';

                    // If no threshold set, return normal
                    if (!threshold.plus_1 && !threshold.plus_2 && !threshold.min_1 && !threshold.min_2) {
                        return {
                            status: 'normal',
                            text: 'Normal',
                            color: defaultColor,
                            bgColor: defaultBgColor
                        };
                    }

                    // Check danger conditions
                    if ((threshold.plus_2 !== null && value > threshold.plus_2) ||
                        (threshold.min_2 !== null && value < threshold.min_2)) {
                        return {
                            status: 'danger',
                            text: 'Bahaya',
                            color: '#ef4444', // red
                            bgColor: '#fee2e2'
                        };
                    }

                    // Check warning conditions
                    if ((threshold.plus_1 !== null && threshold.plus_2 !== null && value > threshold.plus_1 && value <=
                            threshold.plus_2) ||
                        (threshold.min_1 !== null && threshold.min_2 !== null && value < threshold.min_1 && value >=
                            threshold.min_2)) {
                        return {
                            status: 'warning',
                            text: 'Waspada',
                            color: '#f59e0b', // yellow/orange
                            bgColor: '#fef3c7'
                        };
                    }

                    // Normal condition
                    return {
                        status: 'normal',
                        text: 'Normal',
                        color: defaultColor,
                        bgColor: defaultBgColor
                    };
                }

                function setGaugeValue(chart, value, max, unit, threshold, statusElementId, sensorType) {
                    // Determine status based on threshold
                    const sensorStatus = getSensorStatus(value, threshold, sensorType);

                    const ratio = Math.max(0, Math.min(1, value / max));
                    const plug = chart.options.plugins.singleGauge;
                    plug.value = ratio;
                    plug.color = sensorStatus.color;
                    plug.valueRaw = value;
                    plug.unit = unit;
                    plug.valueText = `${value.toFixed(2)} ${unit}`;
                    chart.update('none');

                    // Update status chip
                    const statusElement = document.getElementById(statusElementId);
                    if (statusElement) {
                        statusElement.textContent = sensorStatus.text;
                        statusElement.className = 'status-chip ' + sensorStatus.status;
                        statusElement.style.backgroundColor = sensorStatus.bgColor;
                        statusElement.style.color = sensorStatus.color;
                        statusElement.style.borderColor = sensorStatus.color;
                    }
                }

                function createLineChart(id, color, unit = '') {
                    const ctx = document.getElementById(id);
                    return new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: [],
                            fullDates: [], // Store full datetime for tooltip
                            datasets: [{
                                data: [],
                                borderColor: color,
                                backgroundColor: color + '33',
                                borderWidth: 3,
                                pointRadius: 0,
                                tension: 0.35,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        title: (context) => {
                                            // Use fullDates if available, otherwise use label
                                            const index = context[0].dataIndex;
                                            const chart = context[0].chart;
                                            if (chart.data.fullDates && chart.data.fullDates[index]) {
                                                return chart.data.fullDates[index];
                                            }
                                            return context[0].label;
                                        },
                                        label: (ctx) => `${ctx.parsed.y.toFixed(2)} ${unit}`
                                    }
                                }
                            },
                            interaction: {
                                mode: 'nearest',
                                intersect: false
                            },
                            scales: {
                                y: {
                                    ticks: {
                                        color: '#94a3b8'
                                    },
                                    grid: {
                                        color: 'rgba(148,163,184,0.25)'
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: '#94a3b8',
                                        maxTicksLimit: 8
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                        },
                    });
                }

                function setStatus(el, tone) {
                    el.textContent = tone.label;
                    el.className = `status-chip ${tone.level}`;
                }

                function tempTone(value) {
                    if (value >= TEMP_LIMITS.alert) return {
                        label: 'Alert',
                        level: 'alert'
                    };
                    if (value >= TEMP_LIMITS.warn) return {
                        label: 'Warning',
                        level: 'warning'
                    };
                    return {
                        label: 'Normal',
                        level: 'normal'
                    };
                }

                function moistTone(value) {
                    if (value <= MOIST_LIMITS.dry) return {
                        label: 'Dry',
                        level: 'alert'
                    };
                    if (value >= MOIST_LIMITS.humid) return {
                        label: 'Humid',
                        level: 'warning'
                    };
                    return {
                        label: 'Normal',
                        level: 'normal'
                    };
                }

                const tempGauge = createGauge('tempGauge', '#38bdf8');
                const moistGauge = createGauge('moistGauge', '#34d399');
                const tempLine = createLineChart('tempLine', '#38bdf8', '°C');
                const moistLine = createLineChart('moistLine', '#34d399', '%');

                // Expose to window for modal access
                window.tempGauge = tempGauge;
                window.moistGauge = moistGauge;
                window.tempLine = tempLine;
                window.moistLine = moistLine;
                window.tempChartData = tempChartData;
                window.moistChartData = moistChartData;
                window.tempLatest = tempLatest;
                window.moistLatest = moistLatest;
                window.isCustomRangeActive = isCustomRangeActive;

                updateDashboard();

                // Initialize active state for filter selects (realtime mode)
                console.log('Initializing filter selects active state...');
                $('.filter-select').each(function() {
                    console.log('Adding active class to:', $(this).attr('id'), 'data-chart:', $(this).data(
                        'chart'));
                    $(this).addClass('active');
                });
                console.log('Active classes added. Checking:', $('.filter-select.active').length);

                // ========== OPTIMIZED AUTO-REFRESH SYSTEM ==========
                // Variables for optimized polling
                let lastServerTimestamp = null;
                let isPolling = false;
                let consecutiveNoChanges = 0;
                let currentInterval = 1000; // Start with 1 second (faster)
                const MIN_INTERVAL = 1000; // Minimum 1 second
                const MAX_INTERVAL = 5000; // Maximum 5 seconds when idle (reduced from 10s)
                const BACKOFF_MULTIPLIER = 1.3; // Increase interval by 30% when no changes

                // Start optimized polling
                startOptimizedPolling();

                function startOptimizedPolling() {
                    if (isPolling) return; // Prevent multiple polling loops
                    isPolling = true;
                    fetchDashboardDataOptimized();
                }

                function stopOptimizedPolling() {
                    isPolling = false;
                }

                function fetchDashboardDataOptimized() {
                    // Don't poll if in custom range mode
                    if (isCustomRangeActive) {
                        setTimeout(function() {
                            if (isPolling) fetchDashboardDataOptimized();
                        }, currentInterval);
                        return;
                    }

                    const requestParams = {
                        filter: currentTempFilter // Use current filter
                    };

                    // Include last timestamp for long polling
                    if (lastServerTimestamp) {
                        requestParams.last_timestamp = lastServerTimestamp;
                    }

                    $.ajax({
                        url: '{{ route('dashboard.data') }}',
                        method: 'GET',
                        data: requestParams,
                        dataType: 'json',
                        timeout: 12000, // 12 second timeout (server waits max 10s)
                        success: function(data) {
                            console.log('✅ Data received:', {
                                has_changes: data.has_changes,
                                timestamp: data.timestamp,
                                consecutive_no_changes: consecutiveNoChanges,
                                current_interval: currentInterval
                            });

                            // Update timestamp
                            lastServerTimestamp = data.timestamp;

                            // Check if there are actual changes
                            if (data.has_changes) {
                                // New data available
                                consecutiveNoChanges = 0;
                                currentInterval = MIN_INTERVAL; // Reset to minimum interval

                                // Update dashboard
                                tempLatest = data.tempLatest;
                                moistLatest = data.moistLatest;
                                tempChartData = data.tempChartData;
                                moistChartData = data.moistChartData;
                                updateDashboard();

                                console.log('📊 Dashboard updated with new data');
                            } else {
                                // No changes - increase interval (backoff)
                                consecutiveNoChanges++;
                                if (consecutiveNoChanges >= 2) { // Reduced from 3 to 2
                                    currentInterval = Math.min(currentInterval * BACKOFF_MULTIPLIER,
                                        MAX_INTERVAL);
                                    console.log('⏸️ No changes detected, increasing interval to:',
                                        currentInterval + 'ms');
                                }
                            }

                            // Continue polling immediately (no additional delay - server already waited)
                            if (isPolling) {
                                setTimeout(fetchDashboardDataOptimized, 100); // Minimal delay
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('❌ Error fetching data:', error, 'Status:', status);

                            // On error, use backoff strategy
                            consecutiveNoChanges++;
                            currentInterval = Math.min(currentInterval * BACKOFF_MULTIPLIER, MAX_INTERVAL);

                            // Continue polling even on error (with increased interval)
                            if (isPolling) {
                                setTimeout(fetchDashboardDataOptimized, currentInterval);
                            }
                        }
                    });
                }

                // Legacy function - kept for compatibility with filter changes
                function fetchDashboardData() {
                    $.ajax({
                        url: '{{ route('dashboard.data') }}',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            filter: currentTempFilter
                        },
                        success: function(data) {
                            console.log('Data updated:', data);
                            lastServerTimestamp = data.timestamp; // Update timestamp
                            tempLatest = data.tempLatest;
                            moistLatest = data.moistLatest;
                            tempChartData = data.tempChartData;
                            moistChartData = data.moistChartData;
                            updateDashboard();
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching dashboard data:', error);
                        }
                    });
                }

                function updateDashboard() {
                    if (tempLatest) {
                        const tempValue = parseFloat(tempLatest.value);
                        setGaugeValue(tempGauge, tempValue, TEMP_LIMITS.max, '°C', tempThreshold, 'tempStatus', 'temp');
                    }
                    if (moistLatest) {
                        const moistValue = parseFloat(moistLatest.value);
                        setGaugeValue(moistGauge, moistValue, MOIST_LIMITS.max, '%', moistThreshold, 'moistStatus',
                            'moist');
                    }
                    if (tempChartData && tempChartData.labels && tempChartData.labels.length > 0) {
                        tempLine.data.labels = tempChartData.labels;
                        tempLine.data.fullDates = tempChartData.fullDates || [];
                        tempLine.data.datasets[0].data = tempChartData.values;
                        tempLine.update('none');
                    }
                    if (moistChartData && moistChartData.labels && moistChartData.labels.length > 0) {
                        moistLine.data.labels = moistChartData.labels;
                        moistLine.data.fullDates = moistChartData.fullDates || [];
                        moistLine.data.datasets[0].data = moistChartData.values;
                        moistLine.update('none');
                    }
                    renderLatestTables();
                }

                // ========== TABLE PAGINATION & MANAGEMENT ==========

                function renderLatestTables() {
                    // Render Temperature Table (ONLY in latest mode)
                    if (tableState.temp.mode === 'latest') {
                        if (tempChartData && tempChartData.values && tempChartData.values.length > 0) {
                            tableState.temp.allData = tempChartData.values.map((val, idx) => ({
                                label: tempChartData.fullDates ? tempChartData.fullDates[idx] :
                                    tempChartData.labels[idx],
                                value: val
                            })).reverse(); // Latest first
                            tableState.temp.totalData = tableState.temp.allData.length;
                            renderTablePage('temp');
                        } else {
                            $('#tempTableBody').html('<tr><td colspan="2">No data available</td></tr>');
                        }
                    }
                    // SKIP if in custom range mode (static data)

                    // Render Moisture Table (ONLY in latest mode)
                    if (tableState.moist.mode === 'latest') {
                        if (moistChartData && moistChartData.values && moistChartData.values.length > 0) {
                            tableState.moist.allData = moistChartData.values.map((val, idx) => ({
                                label: moistChartData.fullDates ? moistChartData.fullDates[idx] :
                                    moistChartData.labels[idx],
                                value: val
                            })).reverse(); // Latest first
                            tableState.moist.totalData = tableState.moist.allData.length;
                            renderTablePage('moist');
                        } else {
                            $('#moistTableBody').html('<tr><td colspan="2">No data available</td></tr>');
                        }
                    }
                    // SKIP if in custom range mode (static data)
                }

                function renderTablePage(table) {
                    const state = window.tableState[table];
                    const start = (state.currentPage - 1) * state.perPage;
                    const end = start + state.perPage;
                    const pageData = state.allData.slice(start, end);

                    const unit = table === 'temp' ? '°C' : '%';
                    const tbody = $(`#${table}TableBody`);

                    if (pageData.length > 0) {
                        const rows = pageData.map(item => {
                            return `<tr><td>${item.label}</td><td class="table-value">${parseFloat(item.value).toFixed(2)} ${unit}</td></tr>`;
                        }).join('');
                        tbody.html(rows);
                    } else {
                        tbody.html('<tr><td colspan="2">No data available</td></tr>');
                    }

                    updatePaginationUI(table);
                }

                // Render table directly (for lazy-loaded data from server)
                function renderTableDirectly(table) {
                    const state = window.tableState[table];
                    const unit = table === 'temp' ? '°C' : '%';
                    const tbody = $(`#${table}TableBody`);

                    // Data from server is already paginated - render directly
                    if (state.allData && state.allData.length > 0) {
                        const rows = state.allData.map(item => {
                            return `<tr><td>${item.label}</td><td class="table-value">${parseFloat(item.value).toFixed(2)} ${unit}</td></tr>`;
                        }).join('');
                        tbody.html(rows);
                    } else {
                        tbody.html('<tr><td colspan="2">No data available</td></tr>');
                    }

                    updatePaginationUI(table);
                }

                function updatePaginationUI(table) {
                    const state = window.tableState[table];
                    const start = (state.currentPage - 1) * state.perPage + 1;
                    const end = Math.min(state.currentPage * state.perPage, state.totalData);
                    const totalPages = Math.ceil(state.totalData / state.perPage);

                    // Update info text
                    $(`#${table}PaginationInfo`).text(`Showing ${start}-${end} of ${state.totalData}`);

                    // Update button states
                    $(`#${table}PrevBtn`).prop('disabled', state.currentPage === 1);
                    $(`#${table}NextBtn`).prop('disabled', state.currentPage >= totalPages);
                }

                // Expose helper functions immediately for use in callbacks
                window.renderTablePage = renderTablePage;
                window.renderTableDirectly = renderTableDirectly;
                window.updatePaginationUI = updatePaginationUI;
                // ========== FILTER & CUSTOM RANGE FUNCTIONALITY ==========

                // Initialize Flatpickr for datetime pickers
                window.startPicker = flatpickr("#startDateTime", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr: true,
                    maxDate: "today",
                    defaultHour: 0,
                    defaultMinute: 0
                });

                window.endPicker = flatpickr("#endDateTime", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr: true,
                    maxDate: "today",
                    defaultHour: 23,
                    defaultMinute: 59
                });

                // Handle filter dropdown changes
                $('.filter-select').on('change', function() {
                    const $select = $(this);
                    const filter = $select.val();
                    const chart = $select.data('chart');

                    // Prevent multiple changes
                    if ($select.prop('disabled')) return;

                    // Keluar dari mode custom range, kembali ke realtime
                    isCustomRangeActive = false;
                    window.isCustomRangeActive = false;

                    // Reset polling to immediate mode
                    consecutiveNoChanges = 0;
                    currentInterval = MIN_INTERVAL;
                    lastServerTimestamp = null; // Force fresh data

                    // Update active state
                    $select.addClass('active');
                    $(`.btn-custom-range[data-chart="${chart}"]`).removeClass('active');

                    // Update current filter
                    if (chart === 'temp') {
                        currentTempFilter = filter;
                    } else if (chart === 'moist') {
                        currentMoistFilter = filter;
                    }

                    // Disable dropdown during fetch
                    $select.prop('disabled', true);

                    // Fetch data with new filter
                    fetchFilteredData(chart, filter, null, null, function() {
                        // Re-enable dropdown after data loaded
                        $select.prop('disabled', false);
                    });
                });

                // Handle custom range button clicks
                $('.btn-custom-range').on('click', function() {
                    window.currentChartForModal = $(this).data('chart');
                    console.log('Opening modal for chart:', window.currentChartForModal);
                    openCustomRangeModal();
                });

                // ========== TABLE MODE HANDLERS ==========

                // Table mode button handler (Last Data / Custom Range)
                $('.btn-table-mode').on('click', function() {
                    const mode = $(this).data('mode');
                    const table = $(this).data('table');

                    // Update button states
                    $(`.btn-table-mode[data-table="${table}"]`).removeClass('active');
                    $(this).addClass('active');

                    // Update table state
                    tableState[table].mode = mode;
                    tableState[table].currentPage = 1; // Reset to first page

                    if (mode === 'latest') {
                        // Switch back to latest mode
                        renderLatestTables();
                    } else if (mode === 'custom') {
                        // Open custom range modal for table
                        window.currentTableForModal = table;
                        openCustomRangeTableModal();
                    }
                });

                // Pagination: Per page selector
                $('#tempPerPage, #moistPerPage').on('change', function() {
                    const table = $(this).attr('id').includes('temp') ? 'temp' : 'moist';
                    window.tableState[table].perPage = parseInt($(this).val());
                    window.tableState[table].currentPage = 1; // Reset to first page

                    // Check if custom range mode
                    if (window.tableState[table].mode === 'custom' && window.tableState[table].customStart) {
                        // Lazy load with new perPage
                        window.fetchTableDataLazy(table, window.tableState[table].customStart, window
                            .tableState[table].customEnd,
                            1, window.tableState[table].perPage);
                    } else {
                        renderTablePage(table);
                    }
                });

                // Pagination: Prev button
                $('#tempPrevBtn, #moistPrevBtn').on('click', function() {
                    const table = $(this).attr('id').includes('temp') ? 'temp' : 'moist';
                    if (window.tableState[table].currentPage > 1) {
                        window.tableState[table].currentPage--;

                        // Check if custom range mode
                        if (window.tableState[table].mode === 'custom' && window.tableState[table]
                            .customStart) {
                            // Lazy load previous page
                            window.fetchTableDataLazy(table, window.tableState[table].customStart, window
                                .tableState[table].customEnd,
                                window.tableState[table].currentPage, window.tableState[table].perPage);
                        } else {
                            window.renderTablePage(table);
                        }
                    }
                });

                // Pagination: Next button
                $('#tempNextBtn, #moistNextBtn').on('click', function() {
                    const table = $(this).attr('id').includes('temp') ? 'temp' : 'moist';
                    const totalPages = Math.ceil(window.tableState[table].totalData / window.tableState[table]
                        .perPage);
                    if (window.tableState[table].currentPage < totalPages) {
                        window.tableState[table].currentPage++;

                        // Check if custom range mode
                        if (window.tableState[table].mode === 'custom' && window.tableState[table]
                            .customStart) {
                            // Lazy load next page
                            window.fetchTableDataLazy(table, window.tableState[table].customStart, window
                                .tableState[table].customEnd,
                                window.tableState[table].currentPage, window.tableState[table].perPage);
                        } else {
                            window.renderTablePage(table);
                        }
                    }
                });

                // ========== CHART FILTER HANDLERS ==========

                // Fetch data with filter
                function fetchFilteredData(chart, filter, startDate = null, endDate = null, callback = null) {
                    const params = {};

                    if (startDate && endDate) {
                        params.start_date = startDate;
                        params.end_date = endDate;
                    } else {
                        params.filter = filter;
                    }

                    $.ajax({
                        url: '{{ route('dashboard.data') }}',
                        method: 'GET',
                        data: params,
                        dataType: 'json',
                        success: function(data) {
                            console.log('Filtered data:', data);

                            // Update only the specific chart
                            if (chart === 'temp') {
                                tempLatest = data.tempLatest;
                                tempChartData = data.tempChartData;

                                if (tempLatest) {
                                    const tempValue = parseFloat(tempLatest.value);
                                    setGaugeValue(tempGauge, tempValue, TEMP_LIMITS.max, '°C',
                                        tempThreshold, 'tempStatus', 'temp');
                                }

                                if (tempChartData && tempChartData.labels && tempChartData.labels.length >
                                    0) {
                                    tempLine.data.labels = tempChartData.labels;
                                    tempLine.data.datasets[0].data = tempChartData.values;
                                    tempLine.update('active');
                                }
                            } else if (chart === 'moist') {
                                moistLatest = data.moistLatest;
                                moistChartData = data.moistChartData;

                                if (moistLatest) {
                                    const moistValue = parseFloat(moistLatest.value);
                                    setGaugeValue(moistGauge, moistValue, MOIST_LIMITS.max, '%',
                                        moistThreshold, 'moistStatus', 'moist');
                                }

                                if (moistChartData && moistChartData.labels && moistChartData.labels
                                    .length > 0) {
                                    moistLine.data.labels = moistChartData.labels;
                                    moistLine.data.datasets[0].data = moistChartData.values;
                                    moistLine.update('active');
                                }
                            }

                            renderLatestTables();

                            // Call callback if provided
                            if (callback) callback();
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching filtered data:', error);

                            // Call callback even on error
                            if (callback) callback();
                        }
                    });
                }

                // Override fetchDashboardData to use current filters
                const originalFetchDashboardData = fetchDashboardData;
                fetchDashboardData = function() {
                    // Fetch temp data with current filter
                    fetchFilteredData('temp', currentTempFilter);
                    // Fetch moist data with current filter
                    fetchFilteredData('moist', currentMoistFilter);
                };

                // ========== MODAL CONTROL FUNCTIONS (Inside jQuery ready) ==========
                function showLoading() {
                    document.getElementById('loadingOverlay').classList.add('active');
                }

                function hideLoading() {
                    document.getElementById('loadingOverlay').classList.remove('active');
                }

                function showToast(type, title, message) {
                    const toast = document.getElementById('toast');
                    const icon = toast.querySelector('.toast-icon i');
                    const titleEl = toast.querySelector('.toast-title');
                    const messageEl = toast.querySelector('.toast-message');

                    // Remove previous classes
                    toast.classList.remove('success', 'error');

                    // Set new content
                    toast.classList.add(type);
                    titleEl.textContent = title;
                    messageEl.textContent = message;

                    // Change icon based on type
                    if (type === 'success') {
                        icon.className = 'fa-solid fa-circle-check';
                    } else if (type === 'error') {
                        icon.className = 'fa-solid fa-circle-exclamation';
                    }

                    // Show toast
                    toast.classList.add('active');

                    // Auto hide after 5 seconds
                    setTimeout(closeToast, 5000);
                }

                function closeToast() {
                    document.getElementById('toast').classList.remove('active');
                }

                function openCustomRangeModal() {
                    const modal = document.getElementById('customRangeModal');
                    modal.classList.add('active');
                    // Add chart-specific class for styling
                    if (window.currentChartForModal === 'moist') {
                        modal.classList.add('moist-chart');
                    } else {
                        modal.classList.remove('moist-chart');
                    }
                    document.body.style.overflow = 'hidden';
                }

                function closeCustomRangeModal() {
                    const modal = document.getElementById('customRangeModal');
                    modal.classList.remove('active');
                    modal.classList.remove('moist-chart');
                    document.body.style.overflow = 'auto';
                    // Clear inputs using Flatpickr API
                    if (window.startPicker) window.startPicker.clear();
                    if (window.endPicker) window.endPicker.clear();
                }

                // Expose modal functions to window for global access
                window.showToast = showToast;
                window.closeToast = closeToast;
                window.openCustomRangeModal = openCustomRangeModal;
                window.closeCustomRangeModal = closeCustomRangeModal;

                function applyCustomRange() {
                    const startDate = document.getElementById('startDateTime').value;
                    const endDate = document.getElementById('endDateTime').value;
                    const chart = window.currentChartForModal;

                    console.log('Applying custom range:', {
                        startDate,
                        endDate,
                        chart
                    });

                    if (!startDate || !endDate) {
                        showToast('error', 'Input Tidak Lengkap', 'Silakan pilih tanggal & waktu start dan end');
                        return;
                    }

                    if (new Date(startDate) >= new Date(endDate)) {
                        showToast('error', 'Rentang Tidak Valid', 'Tanggal start harus lebih awal dari tanggal end');
                        return;
                    }

                    if (!chart) {
                        showToast('error', 'Error', 'Chart tidak terdeteksi. Silakan coba lagi.');
                        return;
                    }

                    // AKTIFKAN MODE CUSTOM RANGE - Stop auto-refresh/polling
                    isCustomRangeActive = true;
                    window.isCustomRangeActive = true;
                    console.log('✋ Custom range mode activated - optimized polling paused');

                    // Show loading overlay
                    showLoading();

                    // Disable apply button
                    const applyBtn = document.querySelector('.btn-apply');
                    const originalContent = applyBtn.innerHTML;
                    applyBtn.disabled = true;
                    applyBtn.innerHTML = '<span class="spinner"></span> Memuat...';

                    // Update active states
                    jQuery(`.filter-select[data-chart="${chart}"]`).removeClass('active');
                    jQuery(`.btn-custom-range[data-chart="${chart}"]`).addClass('active');

                    // Determine sensor code based on chart type
                    let sensorCode = '';
                    if (chart === 'temp') {
                        sensorCode = 'TEMP_01';
                    } else if (chart === 'moist') {
                        sensorCode = 'MOIST_01';
                    }

                    // Fetch data with custom range
                    jQuery.ajax({
                        url: '{{ route('dashboard.data') }}',
                        method: 'GET',
                        data: {
                            sensor: sensorCode,
                            range: 'custom',
                            custom_start: startDate,
                            custom_end: endDate
                        },
                        dataType: 'json',
                        success: function(data) {
                            console.log('Custom range data received:', data);

                            let dataCount = 0;

                            if (chart === 'temp') {
                                // Update latest value and gauge
                                if (data.latest) {
                                    window.tempLatest = data.latest;
                                    const tempValue = parseFloat(data.latest.value);
                                    // Use setGaugeValue with proper parameters
                                    if (window.tempGauge) {
                                        setGaugeValue(window.tempGauge, tempValue, window.TEMP_LIMITS.max,
                                            '°C', window.tempThreshold, 'tempStatus', 'temp');
                                    }
                                }

                                // Update chart data
                                if (window.tempLine && data.labels && data.values) {
                                    dataCount = data.labels.length;
                                    console.log('Temperature data received:', {
                                        labels: dataCount,
                                        values: data.values.length,
                                        firstLabel: data.labels[0],
                                        lastLabel: data.labels[dataCount - 1]
                                    });

                                    // Update chart dengan data baru
                                    window.tempLine.data.labels = data.labels;
                                    window.tempLine.data.datasets[0].data = data.values;
                                    // Update fullDates untuk tooltip
                                    window.tempLine.data.fullDates = data.fullDates || data.labels;

                                    // Reset chart scales untuk range baru
                                    window.tempLine.options.scales.x.min = undefined;
                                    window.tempLine.options.scales.x.max = undefined;

                                    window.tempLine.update('default');
                                    console.log('Temperature chart updated with', dataCount, 'points');
                                }
                            } else if (chart === 'moist') {
                                // Update latest value and gauge
                                if (data.latest) {
                                    window.moistLatest = data.latest;
                                    const moistValue = parseFloat(data.latest.value);
                                    // Use setGaugeValue with proper parameters
                                    if (window.moistGauge) {
                                        setGaugeValue(window.moistGauge, moistValue, window.MOIST_LIMITS
                                            .max, '%', window.moistThreshold, 'moistStatus', 'moist');
                                    }
                                }

                                // Update chart data
                                if (window.moistLine && data.labels && data.values) {
                                    dataCount = data.labels.length;
                                    console.log('Moisture data received:', {
                                        labels: dataCount,
                                        values: data.values.length,
                                        firstLabel: data.labels[0],
                                        lastLabel: data.labels[dataCount - 1]
                                    });

                                    // Update chart dengan data baru
                                    window.moistLine.data.labels = data.labels;
                                    window.moistLine.data.datasets[0].data = data.values;
                                    // Update fullDates untuk tooltip
                                    window.moistLine.data.fullDates = data.fullDates || data.labels;

                                    // Reset chart scales untuk range baru
                                    window.moistLine.options.scales.x.min = undefined;
                                    window.moistLine.options.scales.x.max = undefined;

                                    window.moistLine.update('default');
                                    console.log('Moisture chart updated with', dataCount, 'points');
                                }
                            }

                            // Hide loading
                            hideLoading();

                            // Re-enable button
                            applyBtn.disabled = false;
                            applyBtn.innerHTML = originalContent;

                            // Close modal
                            closeCustomRangeModal();

                            // Show success toast
                            const chartName = chart === 'temp' ? 'Temperature' : 'Moisture';
                            showToast('success', 'Data Berhasil Dimuat!',
                                `${dataCount} data ${chartName} dari ${startDate} s/d ${endDate}`);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching custom range data:', error);
                            console.error('Response:', xhr.responseText);

                            // Hide loading
                            hideLoading();

                            // Re-enable button
                            applyBtn.disabled = false;
                            applyBtn.innerHTML = originalContent;

                            // Show error toast
                            showToast('error', 'Gagal Memuat Data',
                                'Terjadi kesalahan saat memuat data. Silakan coba lagi.');
                        }
                    });
                }

                // Expose applyCustomRange to window for global access
                window.applyCustomRange = applyCustomRange;

                // Close modal when clicking outside
                document.getElementById('customRangeModal').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeCustomRangeModal();
                    }
                });

                // ========== TABLE CUSTOM RANGE MODAL FUNCTIONS ==========

                // Initialize Flatpickr for table datetime pickers
                window.startPickerTable = flatpickr("#startDateTimeTable", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr: true,
                    maxDate: "today",
                    defaultHour: 0,
                    defaultMinute: 0
                });

                window.endPickerTable = flatpickr("#endDateTimeTable", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr: true,
                    maxDate: "today",
                    defaultHour: 23,
                    defaultMinute: 59
                });

                function openCustomRangeTableModal() {
                    document.getElementById('customRangeTableModal').classList.add('active');
                    document.body.style.overflow = 'hidden';
                }

                function closeCustomRangeTableModal() {
                    document.getElementById('customRangeTableModal').classList.remove('active');
                    document.body.style.overflow = 'auto';
                    // Clear inputs
                    if (window.startPickerTable) window.startPickerTable.clear();
                    if (window.endPickerTable) window.endPickerTable.clear();
                }

                function applyCustomRangeTable() {
                    const startDate = document.getElementById('startDateTimeTable').value;
                    const endDate = document.getElementById('endDateTimeTable').value;
                    const table = window.currentTableForModal;

                    console.log('Applying custom range for table:', {
                        startDate,
                        endDate,
                        table
                    });

                    if (!startDate || !endDate) {
                        showToast('error', 'Input Tidak Lengkap', 'Silakan pilih tanggal & waktu start dan end');
                        return;
                    }

                    if (new Date(startDate) >= new Date(endDate)) {
                        showToast('error', 'Rentang Tidak Valid', 'Tanggal start harus lebih awal dari tanggal end');
                        return;
                    }

                    if (!table) {
                        showToast('error', 'Error', 'Tabel tidak terdeteksi. Silakan coba lagi.');
                        return;
                    }

                    // Show loading
                    showLoading();

                    // Close modal
                    closeCustomRangeTableModal();

                    // Store custom range dates (use window.tableState)
                    window.tableState[table].customStart = startDate;
                    window.tableState[table].customEnd = endDate;
                    window.tableState[table].currentPage = 1; // Reset to first page

                    // Fetch first page with lazy loading
                    window.fetchTableDataLazy(table, startDate, endDate, 1, window.tableState[table].perPage);
                }

                // Function to fetch table data with lazy loading (pagination)
                function fetchTableDataLazy(table, startDate, endDate, page, perPage) {
                    const sensorCode = table === 'temp' ? 'TEMP_01' : 'MOIST_01';

                    showLoading();

                    jQuery.ajax({
                        url: '{{ route('dashboard.table-data') }}',
                        method: 'GET',
                        data: {
                            sensor_code: sensorCode,
                            start_date: startDate,
                            end_date: endDate,
                            page: page,
                            per_page: perPage
                        },
                        dataType: 'json',
                        success: function(response) {
                            console.log('Table lazy load data received:', response);

                            if (response.status === 'success') {
                                // Store current page data in allData (for rendering)
                                // This is the ONLY data for current page - no client-side pagination needed
                                window.tableState[table].allData = response.data;
                                window.tableState[table].totalData = response.pagination.total;
                                window.tableState[table].currentPage = response.pagination.current_page;

                                // Render directly (data already filtered by server)
                                renderTableDirectly(table);

                                hideLoading();

                                showToast('success', 'Data Berhasil Dimuat!',
                                    `Menampilkan ${response.data.length} data dari total ${response.pagination.total} data`
                                );
                            } else {
                                hideLoading();
                                showToast('error', 'Gagal Memuat Data', response.message ||
                                    'Terjadi kesalahan');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Table lazy load error:', error);
                            hideLoading();
                            showToast('error', 'Gagal Memuat Data',
                                'Terjadi kesalahan saat memuat data. Silakan coba lagi.');
                        }
                    });
                }

                // Expose modal and lazy loading functions to window for global access
                window.applyCustomRangeTable = applyCustomRangeTable;
                window.closeCustomRangeTableModal = closeCustomRangeTableModal;
                window.fetchTableDataLazy = fetchTableDataLazy;
                // Note: renderTablePage & updatePaginationUI already exposed earlier

                // Close table modal when clicking outside
                document.getElementById('customRangeTableModal').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeCustomRangeTableModal();
                    }
                });

                // ========== DOWNLOAD DATA FUNCTIONALITY ==========

                // Initialize download date pickers
                window.startPickerDownload = flatpickr("#startDateTimeDownload", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i:S",
                    time_24hr: true,
                    maxDate: "today"
                });

                window.endPickerDownload = flatpickr("#endDateTimeDownload", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i:S",
                    time_24hr: true,
                    maxDate: "today",
                    defaultHour: 23,
                    defaultMinute: 59
                });

                // Global variable to store current table for download
                window.currentTableForDownload = null;

                function openDownloadModal(table) {
                    window.currentTableForDownload = table;
                    document.getElementById('downloadModal').classList.add('active');
                    document.body.style.overflow = 'hidden';
                }

                function closeDownloadModal() {
                    document.getElementById('downloadModal').classList.remove('active');
                    document.body.style.overflow = 'auto';
                    // Clear inputs
                    if (window.startPickerDownload) window.startPickerDownload.clear();
                    if (window.endPickerDownload) window.endPickerDownload.clear();
                }

                function downloadData() {
                    const startDate = document.getElementById('startDateTimeDownload').value;
                    const endDate = document.getElementById('endDateTimeDownload').value;
                    const table = window.currentTableForDownload;

                    console.log('Downloading data:', {
                        startDate,
                        endDate,
                        table
                    });

                    if (!startDate || !endDate) {
                        showToast('error', 'Input Tidak Lengkap', 'Silakan pilih tanggal & waktu start dan end');
                        return;
                    }

                    if (new Date(startDate) >= new Date(endDate)) {
                        showToast('error', 'Rentang Tidak Valid', 'Tanggal start harus lebih awal dari tanggal end');
                        return;
                    }

                    if (!table) {
                        showToast('error', 'Error', 'Tabel tidak terdeteksi. Silakan coba lagi.');
                        return;
                    }

                    // Close modal
                    closeDownloadModal();

                    // Show loading with message
                    showLoading();
                    showToast('info', 'Memproses Download', 'Sedang mempersiapkan file Excel, mohon tunggu...');

                    // Build download URL with base URL
                    const downloadUrl = `{{ route('dashboard.download') }}?` +
                        `type=${table}` +
                        `&start=${encodeURIComponent(startDate)}` +
                        `&end=${encodeURIComponent(endDate)}`;

                    console.log('Download URL:', downloadUrl);

                    // Use fetch to download with proper error handling
                    fetch(downloadUrl, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.blob();
                        })
                        .then(blob => {
                            // Create download link
                            const url = window.URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.href = url;

                            // Generate filename
                            const sensorType = table === 'temp' ? 'Suhu' : 'KadarAir';
                            const startFormatted = startDate.replace(/[:\s]/g, '_').replace(/-/g, '');
                            const endFormatted = endDate.replace(/[:\s]/g, '_').replace(/-/g, '');
                            link.download = `${sensorType}_${startFormatted}_to_${endFormatted}.xls`;

                            // Trigger download
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);

                            // Clean up
                            window.URL.revokeObjectURL(url);

                            // Hide loading and show success
                            hideLoading();
                            showToast('success', 'Download Berhasil',
                                `File data ${table === 'temp' ? 'suhu' : 'kadar air'} berhasil diunduh`);
                        })
                        .catch(error => {
                            console.error('Download error:', error);
                            hideLoading();
                            showToast('error', 'Download Gagal',
                                'Terjadi kesalahan saat mengunduh file. Silakan coba lagi.');
                        });
                }

                // Expose download functions to window for global access
                window.openDownloadModal = openDownloadModal;
                window.closeDownloadModal = closeDownloadModal;
                window.downloadData = downloadData;

                // Close download modal when clicking outside
                document.getElementById('downloadModal').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeDownloadModal();
                    }
                });

                // ========== NOTIFICATION SYSTEM ==========

                let notifications = [];
                let unreadCount = 0;

                // Toggle notification panel
                $('#notificationBell').on('click', function(e) {
                    e.stopPropagation();
                    $('#notificationPanel').toggleClass('show');
                });

                // Close notification panel when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('#notificationBell, #notificationPanel').length) {
                        $('#notificationPanel').removeClass('show');
                    }
                });

                // Fetch sensor conditions and generate notifications
                function checkSensorConditions() {
                    const sensors = [{
                            code: 'TEMP_01',
                            name: 'Sensor Suhu 1',
                            type: 'temp'
                        },
                        {
                            code: 'MOIST_01',
                            name: 'Sensor Kadar Air 1',
                            type: 'moist'
                        }
                    ];

                    // Use Promise.all for parallel API calls (faster)
                    const promises = sensors.map(sensor => {
                        return $.ajax({
                            url: `/api/sensors/${sensor.code}/condition`,
                            method: 'GET',
                            dataType: 'json',
                            timeout: 3000 // 3 second timeout
                        }).then(function(response) {
                            // Response now contains: { condition: int, value: float }
                            const conditionInt = parseInt(response.condition);
                            sensor.value = parseFloat(response.value); // Save value to sensor object

                            if (conditionInt === 2 || conditionInt === 3) {
                                addNotification(sensor, conditionInt);
                            } else {
                                // Remove notification if sensor back to normal
                                removeNotificationBySensor(sensor.code);
                            }
                        }).catch(function(xhr, status, error) {
                            console.error(`Error fetching condition for ${sensor.code}:`, error);
                        });
                    });

                    // Wait for all API calls to complete
                    Promise.all(promises);
                }

                // Add notification
                function addNotification(sensor, condition) {
                    // Check if notification already exists
                    const existingIndex = notifications.findIndex(n => n.sensorCode === sensor.code);

                    const notification = {
                        id: Date.now() + '_' + sensor.code,
                        sensorCode: sensor.code,
                        sensorName: sensor.name,
                        sensorType: sensor.type,
                        condition: condition,
                        timestamp: new Date(),
                        read: false
                    };

                    if (existingIndex >= 0) {
                        // Update existing notification if condition changed
                        if (notifications[existingIndex].condition !== condition) {
                            notifications[existingIndex] = notification;
                            // Save updated log to database
                            saveNotificationLog(sensor, condition);
                        }
                    } else {
                        // Add new notification
                        notifications.unshift(notification);
                        // Ring bell animation
                        $('#bellIcon').addClass('has-notifications');
                        setTimeout(() => $('#bellIcon').removeClass('has-notifications'), 500);
                        // Save log to database
                        saveNotificationLog(sensor, condition);
                    }

                    renderNotifications();
                }

                // Save notification log to database
                function saveNotificationLog(sensor, condition) {
                    const conditionText = condition === 2 ? 'Waspada' : 'Bahaya';
                    const message = `Sensor ${sensor.name} dalam kondisi ${conditionText}`;

                    $.ajax({
                        url: '/api/notification-logs',
                        method: 'POST',
                        data: {
                            sensor_code: sensor.code,
                            sensor_name: sensor.name,
                            sensor_type: sensor.type,
                            condition: condition,
                            sensor_value: sensor.value,
                            message: message
                        },
                        dataType: 'json'
                    });
                }

                // Remove notification by sensor code
                function removeNotificationBySensor(sensorCode) {
                    const initialLength = notifications.length;
                    notifications = notifications.filter(n => n.sensorCode !== sensorCode);

                    if (notifications.length !== initialLength) {
                        renderNotifications();
                    }
                }

                // Render notifications
                function renderNotifications() {
                    const $list = $('#notificationList');
                    const $badge = $('#notificationBadge');
                    const $clearBtn = $('#clearAllBtn');

                    // Count unread
                    unreadCount = notifications.filter(n => !n.read).length;

                    // Update badge
                    if (unreadCount > 0) {
                        $badge.text(unreadCount).show();
                    } else {
                        $badge.hide();
                    }

                    // Render list
                    if (notifications.length === 0) {
                        $list.html(`
                        <div class="notification-empty">
                            <i class="fa-solid fa-bell-slash"></i>
                            <p>Tidak ada notifikasi</p>
                        </div>
                    `);
                        $clearBtn.hide();
                    } else {
                        let html = '';
                        notifications.forEach(notif => {
                            const conditionInfo = getConditionInfo(notif.condition);
                            const timeAgo = getTimeAgo(notif.timestamp);
                            const readClass = notif.read ? '' : 'unread';

                            html += `
                            <div class="notification-item ${readClass}" data-notif-id="${notif.id}">
                                <div class="notification-icon ${conditionInfo.class}">
                                    <i class="${conditionInfo.icon}"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title">${conditionInfo.title}</div>
                                    <div class="notification-message">
                                        ${notif.sensorName} mendeteksi kondisi ${conditionInfo.text.toLowerCase()}
                                    </div>
                                    <div class="notification-time">
                                        <i class="fa-regular fa-clock"></i> ${timeAgo}
                                    </div>
                                </div>
                            </div>
                        `;
                        });
                        $list.html(html);
                        $clearBtn.show();
                    }
                }

                // Get condition info
                function getConditionInfo(condition) {
                    if (condition === 3) {
                        return {
                            class: 'danger',
                            icon: 'fa-solid fa-triangle-exclamation',
                            title: 'Peringatan Bahaya!',
                            text: 'Bahaya'
                        };
                    } else if (condition === 2) {
                        return {
                            class: 'warning',
                            icon: 'fa-solid fa-exclamation-circle',
                            title: 'Peringatan Waspada',
                            text: 'Waspada'
                        };
                    }
                    return {
                        class: '',
                        icon: '',
                        title: '',
                        text: ''
                    };
                }

                // Get time ago
                function getTimeAgo(timestamp) {
                    const seconds = Math.floor((new Date() - timestamp) / 1000);

                    if (seconds < 60) return 'Baru saja';
                    if (seconds < 3600) return Math.floor(seconds / 60) + ' menit yang lalu';
                    if (seconds < 86400) return Math.floor(seconds / 3600) + ' jam yang lalu';
                    return Math.floor(seconds / 86400) + ' hari yang lalu';
                }

                // Mark notification as read
                $(document).on('click', '.notification-item', function() {
                    const notifId = $(this).data('notif-id');
                    const notif = notifications.find(n => n.id === notifId);

                    if (notif && !notif.read) {
                        notif.read = true;
                        $(this).removeClass('unread');
                        renderNotifications();
                    }
                });

                // Clear all notifications
                $('#clearAllBtn').on('click', function() {
                    notifications.forEach(n => n.read = true);
                    renderNotifications();

                    showToast('success', 'Notifikasi Dibersihkan',
                        'Semua notifikasi telah ditandai sebagai sudah dibaca');
                });

                // Priority: Check sensor conditions immediately on load and every 5 seconds
                console.log('Starting notification system...');
                checkSensorConditions(); // Immediate check
                setInterval(checkSensorConditions, 5000); // Check every 5 seconds (reduced from 10)

            }); // End of jQuery(document).ready
        </script>
    @endpush
</x-app-layout>
