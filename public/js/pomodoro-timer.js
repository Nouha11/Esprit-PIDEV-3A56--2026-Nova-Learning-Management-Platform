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
        
        // Timer state
        this.timeRemaining = this.POMODORO_DURATION;
        this.isRunning = false;
        this.isPaused = false;
        this.intervalId = null;
        this.pomodoroCount = options.currentCount || 0;
        
        // Configuration
        this.sessionId = options.sessionId;
        this.updateUrl = options.updateUrl;
        
        // DOM elements
        this.initializeElements();
        
        // Event listeners
        this.attachEventListeners();
        
        // Initialize display
        this.updateDisplay();
        this.updateProgress();
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
    }
    
    /**
     * Timer tick - called every second
     */
    tick() {
        if (this.timeRemaining > 0) {
            this.timeRemaining--;
            this.updateDisplay();
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
        
        // Update server
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
        this.progressBar.style.width = progressPercentage + '%';
        this.progressBar.setAttribute('aria-valuenow', progressPercentage);
        this.progressText.textContent = `${this.pomodoroCount % 4} / 4 Pomodoros`;
        
        // Update visual indicators
        this.pomodoroIndicators.forEach((indicator, index) => {
            if (index < (this.pomodoroCount % 4)) {
                indicator.classList.add('completed');
            } else {
                indicator.classList.remove('completed');
            }
        });
    }
    
    /**
     * Update pomodoro count display
     */
    updatePomodoroCountDisplay() {
        this.pomodoroCountDisplay.innerHTML = `<strong>${this.pomodoroCount}</strong>`;
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
}


/**
 * Initialize timer when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    // Check if we're on the pomodoro page
    if (!document.getElementById('timer-circle')) {
        return;
    }
    
    // Get configuration from window object (set in template)
    const config = window.pomodoroData || {};
    
    // Initialize timer
    const timer = new PomodoroTimer({
        sessionId: config.sessionId,
        currentCount: config.currentCount || 0,
        updateUrl: config.updateUrl
    });
    
    // Make timer accessible globally for debugging
    window.pomodoroTimer = timer;
});

/**
 * Export for module usage
 */
export default PomodoroTimer;
