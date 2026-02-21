/**
 * Breathing Exercise Mini Game
 * Calm breathing exercise for energy regeneration
 */

class BreathingExercise {
    constructor(containerId, settings = {}) {
        this.container = document.getElementById(containerId);
        this.settings = {
            cycles: settings.cycles || 3,
            ...settings
        };
        
        this.currentCycle = 0;
        this.phase = 'inhale'; // inhale, hold, exhale
        this.gameActive = false;
    }

    init() {
        this.render();
        this.startExercise();
    }

    render() {
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        
        this.container.innerHTML = `
            <div class="breathing-exercise text-center py-5">
                <h3 class="mb-4">Breathing Exercise</h3>
                <p class="text-muted mb-5">Follow the circle and breathe</p>
                
                <div class="breathing-circle mb-5" id="breathingCircle"></div>
                
                <div class="mb-4">
                    <h2 class="breathing-instruction" id="instruction">Get Ready...</h2>
                    <p class="text-muted" id="cycleCount">Cycle 0 of ${this.settings.cycles}</p>
                </div>
                
                <div class="progress" style="max-width: 400px; margin: 0 auto; height: 8px;">
                    <div class="progress-bar bg-success" id="progressBar" style="width: 0%"></div>
                </div>
            </div>
        `;
    }

    startExercise() {
        this.gameActive = true;
        
        setTimeout(() => {
            this.runCycle();
        }, 2000);
    }

    async runCycle() {
        while (this.currentCycle < this.settings.cycles && this.gameActive) {
            this.currentCycle++;
            this.updateProgress();
            
            // Inhale (4 seconds)
            await this.breathePhase('Breathe In...', 4000);
            
            // Hold (4 seconds)
            await this.breathePhase('Hold...', 4000);
            
            // Exhale (4 seconds)
            await this.breathePhase('Breathe Out...', 4000);
        }
        
        if (this.gameActive) {
            this.endExercise();
        }
    }

    breathePhase(text, duration) {
        return new Promise(resolve => {
            document.getElementById('instruction').textContent = text;
            document.getElementById('cycleCount').textContent = `Cycle ${this.currentCycle} of ${this.settings.cycles}`;
            setTimeout(resolve, duration);
        });
    }

    updateProgress() {
        const progress = (this.currentCycle / this.settings.cycles) * 100;
        document.getElementById('progressBar').style.width = progress + '%';
    }

    endExercise() {
        this.gameActive = false;
        
        this.container.innerHTML = `
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                </div>
                <h2 class="mb-3">Well Done!</h2>
                <p class="lead mb-4">You completed ${this.settings.cycles} breathing cycles</p>
                <p class="text-muted mb-4">Energy restored! 🌟</p>
                
                <div class="d-flex gap-2 justify-content-center">
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
            window.completeGame(true, this.settings.cycles, this.settings.cycles);
        }
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('gameContainer');
    if (container && container.dataset.engine === 'breathing') {
        const settings = JSON.parse(container.dataset.settings || '{}');
        const game = new BreathingExercise('gameContainer', settings);
        game.init();
    }
});
