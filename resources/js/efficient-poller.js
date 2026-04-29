/**
 * Efficient Dashboard Data Polling System
 * 
 * Features:
 * - Only fetches when new data exists
 * - Exponential backoff when no updates
 * - Pauses when tab is hidden
 * - Minimal server load
 */

class EfficientDashboardPoller {
    constructor(options = {}) {
        this.lastTimestamp = null;
        this.pollInterval = options.pollInterval || 5000; // 5 seconds default
        this.maxPollInterval = options.maxPollInterval || 60000; // 1 minute max
        this.backoffMultiplier = options.backoffMultiplier || 1.5;
        this.currentInterval = this.pollInterval;
        this.pollTimer = null;
        this.isPolling = false;
        this.consecutiveNoData = 0;
        
        // API endpoints
        this.checkEndpoint = '/api/v1/dashboard/check-new-data';
        this.dataEndpoint = '/api/v1/dashboard/latest-data';
        
        // Callbacks
        this.onDataUpdate = options.onDataUpdate || (() => {});
        this.onError = options.onError || ((err) => console.error('Polling error:', err));
        
        // Pause when tab is hidden
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pause();
            } else {
                this.resume();
            }
        });
        
        console.log('✅ Efficient Dashboard Poller initialized');
    }
    
    /**
     * Check if new data exists (lightweight)
     */
    async checkForNewData() {
        const url = new URL(this.checkEndpoint, window.location.origin);
        if (this.lastTimestamp) {
            url.searchParams.set('last_timestamp', this.lastTimestamp);
        }
        
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`Check failed: ${response.status}`);
        }
        
        return await response.json();
    }
    
    /**
     * Fetch full dataset when new data exists
     */
    async fetchLatestData() {
        const url = new URL(this.dataEndpoint, window.location.origin);
        if (this.lastTimestamp) {
            url.searchParams.set('last_timestamp', this.lastTimestamp);
        }
        
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`Fetch failed: ${response.status}`);
        }
        
        return await response.json();
    }
    
    /**
     * Main polling logic
     */
    async poll() {
        if (!this.isPolling) return;
        
        try {
            // Step 1: Lightweight check
            const checkResult = await this.checkForNewData();
            
            if (checkResult.has_new_data) {
                console.log('🔄 New data detected, fetching...');
                
                // Step 2: Fetch full data only if new data exists
                const data = await this.fetchLatestData();
                
                if (data.has_new_data) {
                    this.lastTimestamp = data.last_timestamp;
                    this.onDataUpdate(data);
                    
                    // Reset backoff when new data arrives
                    this.currentInterval = this.pollInterval;
                    this.consecutiveNoData = 0;
                    
                    console.log('✅ Data updated at', new Date(data.last_timestamp).toLocaleTimeString());
                }
            } else {
                // No new data - apply exponential backoff
                this.consecutiveNoData++;
                this.currentInterval = Math.min(
                    this.currentInterval * this.backoffMultiplier,
                    this.maxPollInterval
                );
                
                console.log(`⏸️ No new data (${this.consecutiveNoData}x), next check in ${(this.currentInterval / 1000).toFixed(0)}s`);
            }
            
            this.lastTimestamp = checkResult.last_timestamp || this.lastTimestamp;
            
        } catch (error) {
            console.error('❌ Polling error:', error);
            this.onError(error);
            
            // Backoff on error
            this.currentInterval = Math.min(
                this.currentInterval * this.backoffMultiplier,
                this.maxPollInterval
            );
        }
        
        // Schedule next poll
        if (this.isPolling) {
            this.pollTimer = setTimeout(() => this.poll(), this.currentInterval);
        }
    }
    
    /**
     * Start polling
     */
    start() {
        if (this.isPolling) {
            console.warn('⚠️ Poller already running');
            return;
        }
        
        console.log('▶️ Starting dashboard poller');
        this.isPolling = true;
        this.currentInterval = this.pollInterval;
        this.consecutiveNoData = 0;
        this.poll();
    }
    
    /**
     * Stop polling
     */
    stop() {
        console.log('⏹️ Stopping dashboard poller');
        this.isPolling = false;
        if (this.pollTimer) {
            clearTimeout(this.pollTimer);
            this.pollTimer = null;
        }
    }
    
    /**
     * Pause polling (e.g., when tab hidden)
     */
    pause() {
        if (!this.isPolling) return;
        console.log('⏸️ Pausing dashboard poller');
        if (this.pollTimer) {
            clearTimeout(this.pollTimer);
            this.pollTimer = null;
        }
    }
    
    /**
     * Resume polling
     */
    resume() {
        if (!this.isPolling) return;
        console.log('▶️ Resuming dashboard poller');
        this.currentInterval = this.pollInterval; // Reset interval
        this.poll();
    }
    
    /**
     * Force immediate poll
     */
    forceUpdate() {
        console.log('🔄 Forcing immediate update');
        if (this.pollTimer) {
            clearTimeout(this.pollTimer);
        }
        this.currentInterval = this.pollInterval;
        this.poll();
    }
}

// Export for use in dashboard
window.EfficientDashboardPoller = EfficientDashboardPoller;
