/**
 * Hydration Break Mini Game
 * Reminder to drink water and stay hydrated
 */

class HydrationBreak {
    constructor(containerId, settings = {}) {
        this.container = document.getElementById(containerId);
        this.settings = {
            duration: settings.duration || 30,
            ...settings
        };
        
        this.timeRemaining = this.settings.duration;
        this.gameActive = false;
        this.interval = null;
        this.glassLevel = 0;
    }

    init() {
        this.render();
        this.startExercise();
    }

    render() {
        this.container.innerHTML = `
            <div class="hydration-exercise text-center py-5">
                <h3 class="mb-4">Hydration Break</h3>
                <p class="text-muted mb-5">Time to drink some water!</p>
                
                <div class="water-icon mb-4" style="font-size: 5rem;">
                    💧
                </div>
                
                <div class="mb-4">
                    <h2 class="hydration-instruction" id="instruction">Get Your Water</h2>
                    <p class="text-muted" id="tip">Staying hydrated improves focus and energy</p>
                </div>
                
                <div class="water-glass-container mb-4" style="position: relative; width: 100px; height: 150px; margin: 0 auto;">
                    <div class="water-glass" style="
                        width: 100px;
                        height: 150px;
                        border: 3px solid #0dcaf0;
                        border-radius: 0 0 10px 10px;
                        position: relative;
                        overflow: hidden;
                        background: rgba(13, 202, 240, 0.1);
                    ">
                        <div id="waterLevel" style="
                            position: absolute;
                            bottom: 0;
                            width: 100%;
                            height: 0%;
                            background: linear-gradient(to top, #0dcaf0, #6edff6);
                            transition: height 0.3s ease;
                        "></div>
                    </div>
                </div>
                
                <div class="timer-display mb-4" id="timerDisplay" style="font-size: 3rem; font-weight: bold; color: #0dcaf0;">
                    ${this.settings.duration}s
                </div>
                
                <div class="progress" style="max-width: 400px; margin: 0 auto; height: 8px;">
                    <div class="progress-bar bg-info" id="progressBar" style="width: 0%"></div>
                </div>
                
                <button class="btn btn-info btn-lg mt-4" id="drinkBtn" onclick="window.currentHydrationGame.drink()">
                    <i class="bi bi-droplet-fill me-2"></i>I'm Drinking!
                </button>
            </div>
        `;
        
        // Store reference for button onclick
        window.currentHydrationGame = this;
    }

    startExercise() {
        this.gameActive = true;
        
        setTimeout(() => {
            this.runTimer();
        }, 2000);
    }

    drink() {
        // Animate water level
        this.glassLevel = Math.min(this.glassLevel + 20, 100);
        document.getElementById('waterLevel').style.height = this.glassLevel + '%';
        
        // Update instruction
        if (this.glassLevel >= 100) {
            document.getElementById('instruction').textContent = 'Great! Keep Drinking';
            document.getElementById('tip').textContent = 'You\'re doing awesome! 💪';
        } else {
            document.getElementById('instruction').textContent = 'Keep Going!';
            document.getElementById('tip').textContent = 'A few more sips...';
        }
    }

    runTimer() {
        document.getElementById('instruction').textContent = 'Drink Water Now';
        document.getElementById('tip').textContent = 'Click the button as you drink';
        
        this.interval = setInterval(() => {
            if (!this.gameActive) {
                clearInterval(this.interval);
                return;
            }
            
            this.timeRemaining--;
            document.getElementById('timerDisplay').textContent = this.timeRemaining + 's';
            
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
        
        const hydrationLevel = this.glassLevel >= 80 ? 'Excellent' : this.glassLevel >= 50 ? 'Good' : 'Keep it up';
        const emoji = this.glassLevel >= 80 ? '🌟' : this.glassLevel >= 50 ? '👍' : '💧';
        
        this.container.innerHTML = `
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                </div>
                <h2 class="mb-3">Hydration Complete!</h2>
                <p class="lead mb-4">${hydrationLevel} hydration level ${emoji}</p>
                <p class="text-muted mb-4">Energy restored! 💧</p>
                
                <div class="alert alert-info mx-auto" style="max-width: 500px;">
                    <strong>Tip:</strong> Aim to drink 8 glasses of water per day for optimal health and focus!
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
        
        // Clean up
        delete window.currentHydrationGame;
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('gameContainer');
    if (container && container.dataset.engine === 'hydration') {
        const settings = JSON.parse(container.dataset.settings || '{}');
        const game = new HydrationBreak('gameContainer', settings);
        game.init();
    }
});
