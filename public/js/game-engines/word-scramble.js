/**
 * Word Scramble Game Engine
 * Simple word unscrambling game
 */

class WordScrambleGame {
    constructor(containerId, settings = {}) {
        this.container = document.getElementById(containerId);
        this.settings = {
            time: settings.time || 60,
            words: settings.words || 5,
            ...settings
        };
        
        this.wordList = [
            'STUDY', 'LEARN', 'BRAIN', 'FOCUS', 'THINK', 'SMART', 'GRADE', 'TEACH',
            'WRITE', 'READ', 'SOLVE', 'PRACTICE', 'MEMORY', 'SKILL', 'KNOWLEDGE',
            'EDUCATION', 'STUDENT', 'TEACHER', 'HOMEWORK', 'LIBRARY', 'SCIENCE',
            'HISTORY', 'MATHEMATICS', 'LANGUAGE', 'LITERATURE', 'PHYSICS', 'CHEMISTRY'
        ];
        
        this.currentWords = [];
        this.currentIndex = 0;
        this.score = 0;
        this.timeLeft = this.settings.time;
        this.timer = null;
        this.gameActive = false;
    }

    init() {
        this.selectRandomWords();
        this.render();
        this.startGame();
    }

    selectRandomWords() {
        const shuffled = [...this.wordList].sort(() => Math.random() - 0.5);
        this.currentWords = shuffled.slice(0, this.settings.words);
    }

    scrambleWord(word) {
        const arr = word.split('');
        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }
        return arr.join('');
    }

    render() {
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        
        this.container.innerHTML = `
            <div class="word-scramble-game">
                <div class="game-header mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">Word Scramble</h4>
                            <p class="text-muted small mb-0">Unscramble the words!</p>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-primary fs-5 px-3 py-2">
                                <i class="bi bi-clock me-1"></i>
                                <span id="timer">${this.timeLeft}</span>s
                            </div>
                        </div>
                    </div>
                </div>

                <div class="progress mb-4" style="height: 8px;">
                    <div class="progress-bar bg-success" id="progressBar" style="width: 0%"></div>
                </div>

                <div class="game-content">
                    <div class="card ${isDark ? 'bg-dark border-secondary' : 'bg-light'}">
                        <div class="card-body text-center py-5">
                            <div class="mb-3">
                                <span class="badge bg-info">Word ${this.currentIndex + 1} of ${this.settings.words}</span>
                            </div>
                            <h1 class="display-3 mb-4 scrambled-word" id="scrambledWord"></h1>
                            <div class="mb-4">
                                <input type="text" 
                                       class="form-control form-control-lg text-center" 
                                       id="answerInput" 
                                       placeholder="Type your answer..."
                                       autocomplete="off"
                                       style="max-width: 400px; margin: 0 auto; font-size: 1.5rem;">
                            </div>
                            <div class="d-flex gap-2 justify-content-center">
                                <button class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="bi bi-check-circle me-2"></i>Submit
                                </button>
                                <button class="btn btn-outline-secondary btn-lg" id="skipBtn">
                                    <i class="bi bi-skip-forward me-2"></i>Skip
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 text-center">
                        <div class="badge bg-success fs-6 px-3 py-2">
                            <i class="bi bi-star-fill me-1"></i>
                            Score: <span id="scoreDisplay">0</span>/${this.settings.words}
                        </div>
                    </div>
                </div>

                <div id="resultMessage" class="mt-3"></div>
            </div>
        `;

        this.attachEventListeners();
        this.showNextWord();
    }

    attachEventListeners() {
        const input = document.getElementById('answerInput');
        const submitBtn = document.getElementById('submitBtn');
        const skipBtn = document.getElementById('skipBtn');

        submitBtn.addEventListener('click', () => this.checkAnswer());
        skipBtn.addEventListener('click', () => this.skipWord());
        
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.checkAnswer();
            }
        });
    }

    showNextWord() {
        if (this.currentIndex >= this.currentWords.length) {
            this.endGame(true);
            return;
        }

        const word = this.currentWords[this.currentIndex];
        const scrambled = this.scrambleWord(word);
        
        document.getElementById('scrambledWord').textContent = scrambled;
        document.getElementById('answerInput').value = '';
        document.getElementById('answerInput').focus();
        
        this.updateProgress();
    }

    checkAnswer() {
        if (!this.gameActive) return;

        const input = document.getElementById('answerInput');
        const answer = input.value.trim().toUpperCase();
        const correct = this.currentWords[this.currentIndex];

        if (answer === correct) {
            this.score++;
            this.showFeedback('Correct! 🎉', 'success');
            document.getElementById('scoreDisplay').textContent = this.score;
            
            setTimeout(() => {
                this.currentIndex++;
                this.showNextWord();
            }, 1000);
        } else {
            this.showFeedback('Try again!', 'danger');
            input.value = '';
        }
    }

    skipWord() {
        if (!this.gameActive) return;
        
        this.showFeedback(`Skipped! The word was: ${this.currentWords[this.currentIndex]}`, 'warning');
        
        setTimeout(() => {
            this.currentIndex++;
            this.showNextWord();
        }, 1500);
    }

    showFeedback(message, type) {
        const resultDiv = document.getElementById('resultMessage');
        resultDiv.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show">
                ${message}
            </div>
        `;
        
        setTimeout(() => {
            resultDiv.innerHTML = '';
        }, 2000);
    }

    updateProgress() {
        const progress = (this.currentIndex / this.settings.words) * 100;
        document.getElementById('progressBar').style.width = progress + '%';
    }

    startGame() {
        this.gameActive = true;
        this.timer = setInterval(() => {
            this.timeLeft--;
            document.getElementById('timer').textContent = this.timeLeft;
            
            if (this.timeLeft <= 0) {
                this.endGame(false);
            }
        }, 1000);
    }

    endGame(completed) {
        this.gameActive = false;
        clearInterval(this.timer);
        
        const percentage = Math.round((this.score / this.settings.words) * 100);
        const passed = percentage >= 60;
        
        this.container.innerHTML = `
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-${passed ? 'trophy-fill text-warning' : 'x-circle text-danger'}" style="font-size: 5rem;"></i>
                </div>
                <h2 class="mb-3">${passed ? 'Congratulations!' : 'Game Over'}</h2>
                <p class="lead mb-4">You scored ${this.score} out of ${this.settings.words} (${percentage}%)</p>
                
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-primary btn-lg" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Play Again
                    </button>
                    <a href="/games" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-arrow-left me-2"></i>Back to Games
                    </a>
                </div>
            </div>
        `;

        // Trigger game completion
        if (typeof window.completeGame === 'function') {
            window.completeGame(passed, this.score, this.settings.words);
        }
    }
}

// Auto-initialize if container exists
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('gameContainer');
    if (container && container.dataset.engine === 'word_scramble') {
        const settings = JSON.parse(container.dataset.settings || '{}');
        const game = new WordScrambleGame('gameContainer', settings);
        game.init();
    }
});
