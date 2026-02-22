/**
 * Eye Rest - 20-20-20 Rule Mini Game
 * Look away for 20 seconds every 20 minutes at something 20 feet away
 */

class EyeRest {
    constructor(containerId, settings = {}) {
        this.container = document.getElementById(containerId);
        this.settings = {
            duration: settings.duration || 20,
            ...settings
        };
        
        this.timeRemaining = this.settings.duration;
        this.gameActive = false;
        this.interval = null;
    }

    init() {
        this.render();
        this.startExercise();
    }

    render() {
        this.container.innerHTML = `
            <div class="eye-rest-exercise text-center py-5">
                <h3 class="mb-4">Eye Rest - 20-20-20 Rule</h3>
                <p class="text-muted mb-5">Look at something 20 feet away for 20 seconds</p>
                
                <div class="eye-icon mb-4" style="font-size: 5rem;">
                    👁️
                </div>
                
                <div class="mb-4">
                    <h2 class="eye-instruction" id="instruction">Get Ready...</h2>
                    <p class="text-muted" id="tip">This helps reduce eye strain from screen time</p>
                </div>
                
                <div class="timer-display mb-4" id="timerDisplay" style="font-size: 4rem; font-weight: bold; color: #0d6efd;">
                    ${this.settings.duration}
                </div>
                
                <div class="progress" style="max-width: 400px; margin: 0 auto; height: 8px;">
                    <div class="progress-bar bg-primary" id="progressBar" style="width: 0%"></div>
                </div>
            </div>
        `;
    }

    startExercise() {
        this.gameActive = true;
        
        setTimeout(() => {
            this.runTimer();
        }, 2000);
    }

    runTimer() {
        document.getElementById('instruction').textContent = 'Look Away From Screen';
        document.getElementById('tip').textContent = 'Focus on something far away...';
        
        this.interval = setInterval(() => {
            if (!this.gameActive) {
                clearInterval(this.interval);
                return;
            }
            
            this.timeRemaining--;
            document.getElementById('timerDisplay').textContent = this.timeRemaining;
            
            const progress = ((this.settings.duration - this.timeRemaining) / this.settings.duration) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
            
            if (this.timeRemaining <= 0) {
                clearInterval(this.interval);
                this.endExercise();
            }
        }, 1000);
    }

    endExercise() {
        this.gameActive = false;
        
        this.container.innerHTML = `
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                </div>
                <h2 class="mb-3">Eyes Refreshed!</h2>
                <p class="lead mb-4">You gave your eyes a ${this.settings.duration}-second break</p>
                <p class="text-muted mb-4">Energy restored! 👀</p>
                
                <div class="alert alert-info mx-auto" style="max-width: 500px;">
                    <strong>Tip:</strong> Remember the 20-20-20 rule: Every 20 minutes, look at something 20 feet away for 20 seconds!
                </div>
                
                <div class="d-flex gap-2 justify-content-center mt-4">
                    <button class="btn btn-primary btn-lg" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Do Again
                    </button>
                    <a href="/games" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-arrow-left me-2"></i>Back to Games
                    </a>
                </div>
            </div>
        `;

        if (typeof window.completeGame === 'function') {
            window.completeGame(true, 1, 1);
        }
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('gameContainer');
    if (container && container.dataset.engine === 'eye_rest') {
        const settings = JSON.parse(container.dataset.settings || '{}');
        const game = new EyeRest('gameContainer', settings);
        game.init();
    }
});
