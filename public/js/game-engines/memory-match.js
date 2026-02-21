/**
 * Memory Match Game Engine
 * Classic card matching memory game
 */

class MemoryMatchGame {
    constructor(containerId, settings = {}) {
        this.container = document.getElementById(containerId);
        this.settings = {
            pairs: settings.pairs || 6,
            time: settings.time || 90,
            ...settings
        };
        
        this.icons = ['📚', '✏️', '📖', '🎓', '🧠', '💡', '⭐', '🏆', '🎯', '🔬', '🎨', '🎵', '⚡', '🌟', '💻'];
        this.cards = [];
        this.flippedCards = [];
        this.matchedPairs = 0;
        this.moves = 0;
        this.timeLeft = this.settings.time;
        this.timer = null;
        this.gameActive = false;
    }

    init() {
        this.generateCards();
        this.render();
        this.startGame();
    }

    generateCards() {
        const selectedIcons = this.icons.slice(0, this.settings.pairs);
        const cardPairs = [...selectedIcons, ...selectedIcons];
        
        // Shuffle cards
        this.cards = cardPairs
            .map((icon, index) => ({ id: index, icon, matched: false }))
            .sort(() => Math.random() - 0.5);
    }

    render() {
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const gridCols = this.settings.pairs <= 6 ? 4 : (this.settings.pairs <= 12 ? 5 : 6);
        
        this.container.innerHTML = `
            <div class="memory-match-game">
                <div class="game-header mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card ${isDark ? 'bg-dark border-secondary' : 'bg-light'}">
                                <div class="card-body text-center py-2">
                                    <small class="text-muted d-block">Time Left</small>
                                    <h5 class="mb-0">
                                        <i class="bi bi-clock me-1"></i>
                                        <span id="timer">${this.timeLeft}</span>s
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card ${isDark ? 'bg-dark border-secondary' : 'bg-light'}">
                                <div class="card-body text-center py-2">
                                    <small class="text-muted d-block">Moves</small>
                                    <h5 class="mb-0">
                                        <i class="bi bi-cursor me-1"></i>
                                        <span id="moves">0</span>
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card ${isDark ? 'bg-dark border-secondary' : 'bg-light'}">
                                <div class="card-body text-center py-2">
                                    <small class="text-muted d-block">Matched</small>
                                    <h5 class="mb-0">
                                        <i class="bi bi-check-circle me-1"></i>
                                        <span id="matched">0</span>/${this.settings.pairs}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="memory-grid" style="
                    display: grid;
                    grid-template-columns: repeat(${gridCols}, 1fr);
                    gap: 10px;
                    max-width: 600px;
                    margin: 0 auto;
                ">
                    ${this.cards.map(card => `
                        <div class="memory-card ${isDark ? 'dark-theme' : ''}" 
                             data-id="${card.id}" 
                             data-icon="${card.icon}">
                            <div class="card-inner">
                                <div class="card-front">?</div>
                                <div class="card-back">${card.icon}</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;

        this.attachEventListeners();
    }

    attachEventListeners() {
        document.querySelectorAll('.memory-card').forEach(card => {
            card.addEventListener('click', () => this.flipCard(card));
        });
    }

    flipCard(cardElement) {
        if (!this.gameActive) return;
        if (cardElement.classList.contains('flipped')) return;
        if (cardElement.classList.contains('matched')) return;
        if (this.flippedCards.length >= 2) return;

        cardElement.classList.add('flipped');
        this.flippedCards.push(cardElement);

        if (this.flippedCards.length === 2) {
            this.moves++;
            document.getElementById('moves').textContent = this.moves;
            this.checkMatch();
        }
    }

    checkMatch() {
        const [card1, card2] = this.flippedCards;
        const icon1 = card1.dataset.icon;
        const icon2 = card2.dataset.icon;

        if (icon1 === icon2) {
            // Match found
            card1.classList.add('matched');
            card2.classList.add('matched');
            this.matchedPairs++;
            document.getElementById('matched').textContent = this.matchedPairs;
            this.flippedCards = [];

            if (this.matchedPairs === this.settings.pairs) {
                setTimeout(() => this.endGame(true), 500);
            }
        } else {
            // No match
            setTimeout(() => {
                card1.classList.remove('flipped');
                card2.classList.remove('flipped');
                this.flippedCards = [];
            }, 1000);
        }
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
        
        const passed = completed;
        
        this.container.innerHTML = `
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-${passed ? 'trophy-fill text-warning' : 'x-circle text-danger'}" style="font-size: 5rem;"></i>
                </div>
                <h2 class="mb-3">${passed ? 'Congratulations!' : 'Time\'s Up!'}</h2>
                <p class="lead mb-2">Matched: ${this.matchedPairs}/${this.settings.pairs} pairs</p>
                <p class="text-muted mb-4">Moves: ${this.moves}</p>
                
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

        if (typeof window.completeGame === 'function') {
            window.completeGame(passed, this.matchedPairs, this.settings.pairs);
        }
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('gameContainer');
    if (container && container.dataset.engine === 'memory_match') {
        const settings = JSON.parse(container.dataset.settings || '{}');
        const game = new MemoryMatchGame('gameContainer', settings);
        game.init();
    }
});
