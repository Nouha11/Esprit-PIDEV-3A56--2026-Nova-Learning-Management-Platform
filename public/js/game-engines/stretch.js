/**
 * Quick Stretch Mini Game
 * Simple stretching exercises for energy regeneration
 */

class QuickStretch {
    constructor(containerId, settings = {}) {
        this.container = document.getElementById(containerId);
        this.settings = {
            cycles: settings.cycles || 3,
            ...settings
        };
        
        this.currentExercise = 0;
        this.gameActive = false;
        
        this.exercises = [
            { name: 'Neck Rolls', duration: 10, instruction: 'Slowly roll your neck in circles' },
            { name: 'Shoulder Shrugs', duration: 10, instruction: 'Lift shoulders up and down' },
            { name: 'Arm Circles', duration: 10, instruction: 'Make circles with your arms' },
            { name: 'Wrist Rotations', duration: 10, instruction: 'Rotate your wrists gently' },
            { name: 'Back Stretch', duration: 10, instruction: 'Reach up and stretch your back' }
        ];
    }

    init() {
        this.render();
        this.startExercise();
    }

    render() {
        this.container.innerHTML = `
            <div class="stretch-exercise text-center py-5">
                <h3 class="mb-4">Quick Stretch</h3>
                <p class="text-muted mb-5">Follow along with these simple stretches</p>
                
                <div class="stretch-icon mb-4" style="font-size: 5rem;">
                    🧘
                </div>
                
                <div class="mb-4">
                    <h2 class="stretch-instruction" id="instruction">Get Ready...</h2>
                    <p class="text-muted" id="exerciseInfo">Preparing exercises...</p>
                </div>
                
                <div class="progress" style="max-width: 400px; margin: 0 auto; height: 8px;">
                    <div class="progress-bar bg-info" id="progressBar" style="width: 0%"></div>
                </div>
                
                <div class="mt-4">
                    <div class="timer-circle" id="timer" style="font-size: 3rem; font-weight: bold; color: #0dcaf0;">
                        10
                    </div>
                </div>
            </div>
        `;
    }

    startExercise() {
        this.gameActive = true;
        
        setTimeout(() => {
            this.runExercises();
        }, 2000);
    }

    async runExercises() {
        for (let i = 0; i < this.exercises.length && this.gameActive; i++) {
            this.currentExercise = i;
            const exercise = this.exercises[i];
            
            document.getElementById('instruction').textContent = exercise.name;
            document.getElementById('exerciseInfo').textContent = exercise.instruction;
            
            // Countdown timer
            for (let t = exercise.duration; t > 0 && this.gameActive; t--) {
                document.getElementById('timer').textContent = t;
                await this.wait(1000);
            }
            
            this.updateProgress();
        }
        
        if (this.gameActive) {
            this.endExercise();
        }
    }

    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    updateProgress() {
        const progress = ((this.currentExercise + 1) / this.exercises.length) * 100;
        document.getElementById('progressBar').style.width = progress + '%';
    }

    endExercise() {
        this.gameActive = false;
        
        this.container.innerHTML = `
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                </div>
                <h2 class="mb-3">Great Job!</h2>
                <p class="lead mb-4">You completed ${this.exercises.length} stretching exercises</p>
                <p class="text-muted mb-4">Energy restored! 💪</p>
                
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
            window.completeGame(true, this.exercises.length, this.exercises.length);
        }
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('gameContainer');
    if (container && container.dataset.engine === 'stretch') {
        const settings = JSON.parse(container.dataset.settings || '{}');
        const game = new QuickStretch('gameContainer', settings);
        game.init();
    }
});
