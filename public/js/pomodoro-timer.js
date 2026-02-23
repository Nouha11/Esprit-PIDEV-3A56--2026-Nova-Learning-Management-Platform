/**
 * Pomodoro Timer Module
 * 
 * This module handles the Pomodoro timer functionality for study sessions.
 * Features:
 * - 25-minute countdown timer
 * - Start, pause, resume, and reset controls
 * - Automatic break suggestions (5 min for <4 pomodoros, 15 min for 4+)
 * - Visual progress indicators
 * - Notification sound on completion
 * - AJAX update to server on pomodoro completion
 */

class PomodoroTimer {
    constructor(options = {}) {
        // Timer configuration
        this.POMODORO_DURATION = 25 * 60; // 25 minutes in seconds
        this.SHORT_BREAK = 5; // 5 minutes
        this.LONG_BREAK = 15; // 15 minutes
        this.LONG_BREAK_THRESHOLD = 4; // Long break after 4 pomodoros
        
        // Course ID for saving state
        this.courseId = options.courseId;
        
        // Load saved state or use defaults
        const savedState = this.loadState();
        
        // Timer state
        this.timeRemaining = savedState.timeRemaining || this.POMODORO_DURATION;
        this.isRunning = false;
        this.isPaused = savedState.isPaused || false;
        this.intervalId = null;
        this.pomodoroCount = savedState.pomodoroCount || options.currentCount || 0;
        
        // Configuration
        this.sessionId = options.sessionId;
        this.updateUrl = options.updateUrl;
        this.depleteEnergyUrl = options.depleteEnergyUrl;
        this.updateProgressUrl = options.updateProgressUrl;
        
        // DOM elements
        this.initializeElements();
        
        // Event listeners
        this.attachEventListeners();
        
        // Initialize display
        this.updateDisplay();
        this.updateProgress();
        
        // Auto-save state periodically
        setInterval(() => this.saveState(), 5000); // Save every 5 seconds
        
        // Save state before page unload
        window.addEventListener('beforeunload', () => this.saveState());
        
        console.log('✓ Loaded saved timer state:', savedState);
    }
    
    /**
     * Initialize DOM element references
     */
    initializeElements() {
        this.timerMinutes = document.getElementById('timer-minutes');
        this.timerSeconds = document.getElementById('timer-seconds');
        this.timerStatus = document.getElementById('timer-status');
        this.timerCircle = document.getElementById('timer-circle');
        
        this.startBtn = document.getElementById('start-btn');
        this.pauseBtn = document.getElementById('pause-btn');
        this.resumeBtn = document.getElementById('resume-btn');
        this.resetBtn = document.getElementById('reset-btn');
        
        this.breakSuggestion = document.getElementById('break-suggestion');
        this.breakMessage = document.getElementById('break-message');
        
        this.pomodoroCountDisplay = document.getElementById('pomodoro-count-display');
        this.progressBar = document.getElementById('pomodoro-progress-bar');
        this.progressText = document.getElementById('progress-text');
        
        this.notificationSound = document.getElementById('notification-sound');
        this.pomodoroIndicators = document.querySelectorAll('.pomodoro-indicator');
    }
    
    /**
     * Attach event listeners to control buttons
     */
    attachEventListeners() {
        this.startBtn.addEventListener('click', () => this.start());
        this.pauseBtn.addEventListener('click', () => this.pause());
        this.resumeBtn.addEventListener('click', () => this.resume());
        this.resetBtn.addEventListener('click', () => this.reset());
    }
    
    /**
     * Start the timer
     */
    start() {
        if (this.isRunning) return;
        
        this.isRunning = true;
        this.isPaused = false;
        this.timerCircle.classList.add('running');
        this.timerCircle.classList.remove('paused');
        this.timerStatus.textContent = 'Focus time!';
        
        this.startBtn.style.display = 'none';
        this.pauseBtn.style.display = 'inline-block';
        this.resumeBtn.style.display = 'none';
        
        this.hideBreakSuggestion();
        
        // Stop energy regeneration when timer starts
        this.stopEnergyRegeneration();
        
        this.intervalId = setInterval(() => this.tick(), 1000);
    }
    
    /**
     * Pause the timer
     */
    pause() {
        if (!this.isRunning || this.isPaused) return;
        
        this.isPaused = true;
        this.timerCircle.classList.remove('running');
        this.timerCircle.classList.add('paused');
        this.timerStatus.textContent = 'Paused';
        
        this.pauseBtn.style.display = 'none';
        this.resumeBtn.style.display = 'inline-block';
        
        clearInterval(this.intervalId);
        
        // Resume energy regeneration when paused
        this.resumeEnergyRegeneration();
        
        // Save state when paused
        this.saveState();
    }
    
    /**
     * Resume the timer from paused state
     */
    resume() {
        if (!this.isRunning || !this.isPaused) return;
        
        this.isPaused = false;
        this.timerCircle.classList.add('running');
        this.timerCircle.classList.remove('paused');
        this.timerStatus.textContent = 'Focus time!';
        
        this.pauseBtn.style.display = 'inline-block';
        this.resumeBtn.style.display = 'none';
        
        // Stop energy regeneration when resuming
        this.stopEnergyRegeneration();
        
        this.intervalId = setInterval(() => this.tick(), 1000);
    }
    
    /**
     * Reset the timer to initial state
     */
    reset() {
        this.isRunning = false;
        this.isPaused = false;
        this.timeRemaining = this.POMODORO_DURATION;
        
        this.timerCircle.classList.remove('running', 'paused');
        this.timerStatus.textContent = 'Ready to start';
        
        this.startBtn.style.display = 'inline-block';
        this.pauseBtn.style.display = 'none';
        this.resumeBtn.style.display = 'none';
        
        clearInterval(this.intervalId);
        this.updateDisplay();
        this.hideBreakSuggestion();
        
        // Resume energy regeneration when reset
        this.resumeEnergyRegeneration();
        
        // Clear saved state when reset
        this.clearState();
    }
    
    /**
     * Timer tick - called every second
     */
    tick() {
        if (this.timeRemaining > 0) {
            this.timeRemaining--;
            this.updateDisplay();
            
            // Check if a full minute has passed (when seconds = 0)
            const seconds = this.timeRemaining % 60;
            if (seconds === 0 && this.timeRemaining > 0) {
                const minutesRemaining = Math.floor(this.timeRemaining / 60);
                console.log('✓ Minute completed! Minutes remaining:', minutesRemaining);
                console.log('  - Depleting 1 energy point...');
                console.log('  - Updating course progress...');
                
                // Deplete 1 energy point per minute
                this.depleteEnergy(1);
                
                // Update course progress (increase gradually)
                this.updateCourseProgressGradual();
            }
        } else {
            this.complete();
        }
    }
    
    /**
     * Complete a Pomodoro interval
     */
    async complete() {
        clearInterval(this.intervalId);
        this.isRunning = false;
        this.timerCircle.classList.remove('running');
        this.timerStatus.textContent = 'Completed!';
        
        // Play notification sound
        this.playNotificationSound();
        
        // Increment pomodoro count
        this.pomodoroCount++;
        
        // Update server with final pomodoro count
        await this.updateServer();
        
        // Update UI
        this.updateProgress();
        this.updatePomodoroCountDisplay();
        
        // Show break suggestion
        this.showBreakSuggestion();
        
        // Reset timer for next pomodoro
        this.timeRemaining = this.POMODORO_DURATION;
        this.updateDisplay();
        
        this.startBtn.style.display = 'inline-block';
        this.pauseBtn.style.display = 'none';
        this.resumeBtn.style.display = 'none';
        
        // Save updated state (with new pomodoro count and reset time)
        this.saveState();
    }
    
    /**
     * Update the timer display
     */
    updateDisplay() {
        const minutes = Math.floor(this.timeRemaining / 60);
        const seconds = this.timeRemaining % 60;
        
        this.timerMinutes.textContent = String(minutes).padStart(2, '0');
        this.timerSeconds.textContent = String(seconds).padStart(2, '0');
    }
    
    /**
     * Update progress bar and indicators
     */
    updateProgress() {
        const progressPercentage = (this.pomodoroCount % 4) * 25;
        
        if (this.progressBar) {
            this.progressBar.style.width = progressPercentage + '%';
            this.progressBar.setAttribute('aria-valuenow', progressPercentage);
            this.progressBar.textContent = progressPercentage + '%';
        }
        
        if (this.progressText) {
            this.progressText.textContent = `${this.pomodoroCount % 4} / 4 Pomodoros`;
        }
        
        // Update visual indicators
        if (this.pomodoroIndicators && this.pomodoroIndicators.length > 0) {
            this.pomodoroIndicators.forEach((indicator, index) => {
                if (index < (this.pomodoroCount % 4)) {
                    indicator.classList.add('completed');
                } else {
                    indicator.classList.remove('completed');
                }
            });
        }
    }
    
    /**
     * Update pomodoro count display
     */
    updatePomodoroCountDisplay() {
        if (this.pomodoroCountDisplay) {
            this.pomodoroCountDisplay.innerHTML = `<strong>${this.pomodoroCount}</strong>`;
        }
    }
    
    /**
     * Show break suggestion based on completed pomodoros
     */
    showBreakSuggestion() {
        const breakDuration = this.getBreakDuration();
        const breakType = breakDuration === this.LONG_BREAK ? 'long' : 'short';
        
        let message = '';
        if (breakType === 'long') {
            message = `Great work! You've completed ${this.pomodoroCount} pomodoros. Take a ${breakDuration}-minute long break to recharge.`;
        } else {
            message = `Pomodoro complete! Take a ${breakDuration}-minute break before starting the next one.`;
        }
        
        this.breakMessage.textContent = message;
        this.breakSuggestion.style.display = 'block';
    }
    
    /**
     * Hide break suggestion
     */
    hideBreakSuggestion() {
        this.breakSuggestion.style.display = 'none';
    }
    
    /**
     * Get recommended break duration
     */
    getBreakDuration() {
        if (this.pomodoroCount % this.LONG_BREAK_THRESHOLD === 0 && this.pomodoroCount > 0) {
            return this.LONG_BREAK;
        }
        return this.SHORT_BREAK;
    }
    
    /**
     * Play notification sound
     */
    playNotificationSound() {
        try {
            this.notificationSound.play().catch(error => {
                console.warn('Could not play notification sound:', error);
            });
        } catch (error) {
            console.warn('Notification sound error:', error);
        }
    }
    
    /**
     * Deplete energy after Pomodoro completion
     */
    async depleteEnergy(amount) {
        if (!this.depleteEnergyUrl) {
            console.warn('⚠️ Energy depletion URL not configured');
            console.log('   URLs available:', {
                depleteEnergyUrl: this.depleteEnergyUrl,
                updateProgressUrl: this.updateProgressUrl
            });
            return;
        }
        
        console.log('🔋 Depleting energy:', amount, 'points');
        console.log('   URL:', this.depleteEnergyUrl);
        
        try {
            const response = await fetch(this.depleteEnergyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ amount })
            });
            
            console.log('   Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('   ✓ Energy depleted successfully:', data);
            
            if (data.success) {
                // Update energy bar display with animation
                if (typeof window.updateEnergyBar === 'function') {
                    console.log('   Updating energy bar to:', data.energy);
                    window.updateEnergyBar(data.energy);
                    
                    // Add pulse animation
                    const energyBar = document.getElementById('energy-bar');
                    if (energyBar) {
                        energyBar.classList.add('energy-depleting');
                        setTimeout(() => energyBar.classList.remove('energy-depleting'), 500);
                    }
                } else {
                    console.warn('   ⚠️ window.updateEnergyBar function not found');
                }
                
                // Check if energy is depleted
                if (data.depleted) {
                    console.log('   ⚠️ Energy depleted! Blocking course...');
                    this.handleEnergyDepletion();
                } else if (data.energy <= 20) {
                    this.showAlert('warning', 'Your energy is running low! Consider playing a mini game.');
                }
            }
        } catch (error) {
            console.error('❌ Error depleting energy:', error);
        }
    }
    
    /**
     * Update course progress gradually (called every minute)
     */
    async updateCourseProgressGradual() {
        if (!this.updateProgressUrl) {
            console.warn('⚠️ Progress update URL not configured');
            return;
        }
        
        // Calculate minutes studied (25 - remaining minutes)
        const minutesStudied = 25 - Math.floor(this.timeRemaining / 60);
        
        console.log('📊 Updating course progress');
        console.log('   Minutes studied:', minutesStudied);
        console.log('   URL:', this.updateProgressUrl);
        
        try {
            const response = await fetch(this.updateProgressUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    pomodoroCount: this.pomodoroCount,
                    minutesStudied: minutesStudied
                })
            });
            
            console.log('   Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('   ✓ Progress updated successfully:', data);
            
            if (data.success && data.progress !== undefined) {
                // Find the course progress bar by ID
                const courseProgressBar = document.getElementById('course-progress-bar');
                
                console.log('   Looking for course progress bar...');
                console.log('   Found element:', courseProgressBar);
                
                if (courseProgressBar) {
                    console.log('   ✓ Updating course progress bar to:', data.progress + '%');
                    courseProgressBar.style.width = data.progress + '%';
                    courseProgressBar.setAttribute('aria-valuenow', data.progress);
                    courseProgressBar.innerHTML = '<strong>' + data.progress + '%</strong>';
                    
                    // Add grow animation
                    courseProgressBar.classList.add('progress-growing');
                    setTimeout(() => courseProgressBar.classList.remove('progress-growing'), 500);
                } else {
                    console.warn('   ⚠️ Course progress bar element not found');
                }
            }
        } catch (error) {
            console.error('❌ Error updating course progress:', error);
        }
    }
    
    /**
     * Update course progress after Pomodoro completion
     */
    async updateCourseProgress() {
        if (!this.updateProgressUrl) {
            console.warn('Progress update URL not configured');
            return;
        }
        
        try {
            const response = await fetch(this.updateProgressUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    pomodoroCount: this.pomodoroCount
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.progress !== undefined) {
                // Update progress bar display
                const progressBar = document.querySelector('.progress-bar');
                if (progressBar) {
                    progressBar.style.width = data.progress + '%';
                    progressBar.setAttribute('aria-valuenow', data.progress);
                    progressBar.innerHTML = '<strong>' + data.progress + '%</strong>';
                }
            }
        } catch (error) {
            console.error('Error updating course progress:', error);
        }
    }
    
    /**
     * Handle energy depletion
     */
    handleEnergyDepletion() {
        // Pause the timer
        if (this.isRunning && !this.isPaused) {
            this.pause();
        }
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('energy-depletion-modal'));
        modal.show();
        
        // Block course content
        const overlay = document.getElementById('course-content-overlay');
        if (overlay) {
            overlay.style.display = 'block';
        }
        
        // Disable start button
        this.startBtn.disabled = true;
        this.startBtn.title = 'Restore energy to continue';
    }
    
    /**
     * Send AJAX request to update pomodoro count on server
     */
    async updateServer() {
        if (!this.updateUrl) {
            console.warn('Update URL not configured');
            return;
        }
        
        try {
            const response = await fetch(this.updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    pomodoroCount: this.pomodoroCount
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                console.log('Pomodoro count updated successfully');
            } else {
                console.error('Failed to update pomodoro count:', data.error);
                this.showAlert('danger', data.error || 'Failed to update pomodoro count');
            }
        } catch (error) {
            console.error('Error updating server:', error);
            this.showAlert('danger', 'Network error: Could not update pomodoro count');
        }
    }
    
    /**
     * Show alert message
     */
    showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${this.escapeHtml(message)}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        const container = document.querySelector('.container') || document.querySelector('.row');
        if (!container) {
            console.error('Container element not found for alert');
            return;
        }
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = alertHtml;
        container.insertBefore(tempDiv.firstElementChild, container.firstChild);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
    
    /**
     * Save timer state to localStorage
     */
    saveState() {
        if (!this.courseId) return;
        
        const state = {
            timeRemaining: this.timeRemaining,
            pomodoroCount: this.pomodoroCount,
            isPaused: this.isPaused,
            timestamp: Date.now()
        };
        
        const key = `pomodoro_state_course_${this.courseId}`;
        localStorage.setItem(key, JSON.stringify(state));
    }
    
    /**
     * Load timer state from localStorage
     */
    loadState() {
        if (!this.courseId) return {};
        
        const key = `pomodoro_state_course_${this.courseId}`;
        const saved = localStorage.getItem(key);
        
        if (!saved) return {};
        
        try {
            const state = JSON.parse(saved);
            
            // Check if state is recent (within last 24 hours)
            const hoursSinceLastSave = (Date.now() - state.timestamp) / (1000 * 60 * 60);
            if (hoursSinceLastSave > 24) {
                // State is too old, clear it
                this.clearState();
                return {};
            }
            
            return state;
        } catch (error) {
            console.error('Error loading timer state:', error);
            return {};
        }
    }
    
    /**
     * Clear saved timer state
     */
    clearState() {
        if (!this.courseId) return;
        
        const key = `pomodoro_state_course_${this.courseId}`;
        localStorage.removeItem(key);
    }
    
    /**
     * Stop energy regeneration display when timer is running
     */
    stopEnergyRegeneration() {
        const regenInfo = document.getElementById('energy-regen-info');
        const regenStatus = document.getElementById('regen-status');
        
        if (!regenInfo || !regenStatus) {
            console.warn('⚠️ Energy regeneration elements not found');
            return;
        }
        
        console.log('⏸️ Stopping energy regeneration display');
        
        // Update status to show regeneration is paused
        regenStatus.innerHTML = `
            <i class="bi bi-pause-circle-fill text-warning"></i>
            <small class="text-warning"><strong>Regeneration Paused</strong> - Timer is running</small>
        `;
        
        // Hide the refill countdown timer
        const refillTimer = document.getElementById('refill-timer');
        if (refillTimer) {
            refillTimer.style.display = 'none';
        }
        
        // Add visual indicator that regeneration is paused
        regenInfo.style.background = 'rgba(var(--bs-warning-rgb), 0.1)';
        regenInfo.style.borderColor = 'rgba(var(--bs-warning-rgb), 0.2)';
    }
    
    /**
     * Resume energy regeneration display when timer is paused/stopped
     */
    resumeEnergyRegeneration() {
        const regenInfo = document.getElementById('energy-regen-info');
        const regenStatus = document.getElementById('regen-status');
        
        if (!regenInfo || !regenStatus) {
            console.warn('⚠️ Energy regeneration elements not found');
            return;
        }
        
        console.log('▶️ Resuming energy regeneration display');
        
        // Get current energy level
        const energyBar = document.getElementById('energy-bar');
        const currentEnergy = parseInt(energyBar?.getAttribute('data-energy') || 100);
        
        // Update status based on energy level
        if (currentEnergy >= 100) {
            regenStatus.innerHTML = `
                <i class="bi bi-check-circle-fill text-success"></i>
                <small class="text-success"><strong>Energy Full!</strong> Ready to study</small>
            `;
        } else {
            regenStatus.innerHTML = `
                <i class="bi bi-arrow-clockwise text-success"></i>
                <small class="text-muted">Regenerating: <strong class="text-success">+1 energy</strong> every 5 minutes</small>
            `;
        }
        
        // Show the refill countdown timer again
        const refillTimer = document.getElementById('refill-timer');
        if (refillTimer && currentEnergy < 100) {
            refillTimer.style.display = 'flex';
        }
        
        // Restore original styling
        regenInfo.style.background = 'rgba(var(--bs-success-rgb), 0.1)';
        regenInfo.style.borderColor = 'rgba(var(--bs-success-rgb), 0.2)';
    }
}


/**
 * Initialize timer when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    // Check if we're on the pomodoro page
    if (!document.getElementById('timer-circle')) {
        console.log('⚠️ Timer circle not found - not on Pomodoro page');
        return;
    }
    
    // Get configuration from window object (set in template)
    const config = window.pomodoroData || {};
    
    console.log('🚀 Initializing Pomodoro Timer');
    console.log('   Configuration:', config);
    
    // Initialize timer
    const timer = new PomodoroTimer({
        courseId: config.courseId,
        sessionId: config.sessionId,
        currentCount: config.pomodoroCount || 0,
        updateUrl: config.updateUrl,
        depleteEnergyUrl: config.depleteEnergyUrl,
        updateProgressUrl: config.updateProgressUrl
    });
    
    // Make timer accessible globally for debugging
    window.pomodoroTimer = timer;
    
    console.log('✓ Timer initialized successfully');
    console.log('   Deplete Energy URL:', timer.depleteEnergyUrl);
    console.log('   Update Progress URL:', timer.updateProgressUrl);
    console.log('   Initial Pomodoro Count:', timer.pomodoroCount);
    console.log('   Time Remaining:', timer.timeRemaining, 'seconds');
    console.log('   Is Paused:', timer.isPaused);
    
    // If timer was paused, show resume button
    if (timer.isPaused && timer.timeRemaining < timer.POMODORO_DURATION) {
        timer.timerCircle.classList.add('paused');
        timer.timerStatus.textContent = 'Paused - Resume to continue';
        timer.startBtn.style.display = 'none';
        timer.resumeBtn.style.display = 'inline-block';
        timer.isRunning = true; // Mark as running so resume works
    }
    
    // Test if updateEnergyBar function exists
    if (typeof window.updateEnergyBar === 'function') {
        console.log('✓ window.updateEnergyBar function is available');
    } else {
        console.warn('⚠️ window.updateEnergyBar function NOT found');
    }
});
