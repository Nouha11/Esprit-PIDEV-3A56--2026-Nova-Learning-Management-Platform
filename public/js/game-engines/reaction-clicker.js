/**
 * Reaction Clicker Game Engine
 * Click on targets as quickly as possible
 */

(function() {
    'use strict';

    const container = document.getElementById('gameContainer');
    if (!container) return;

    const gameId = container.dataset.gameId;
    const settings = JSON.parse(container.dataset.settings || '{}');

    // Game state
    let score = 0;
    let missed = 0;
    let targetsClicked = 0;
    let totalTargets = settings.targets || 10;
    let targetSpeed = settings.speed || 2000; // Time each target stays visible (ms)
    let currentTarget = null;
    let targetTimeout = null;
    let gameActive = false;

    // Initialize game
    function init() {
        container.innerHTML = '';
        renderGame();
        startGame();
    }

    // Render game UI
    function renderGame() {
        const gameHTML = `
            <div class="reaction-clicker-game">
                <!-- Header -->
                <div class="game-header mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="stat-card text-center p-3 bg-primary bg-opacity-10 rounded">
                                <div class="stat-label text-muted small">Targets Hit</div>
                                <div class="stat-value fs-3 fw-bold text-primary" id="targetsHit">
                                    ${targetsClicked} / ${totalTargets}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card text-center p-3 bg-success bg-opacity-10 rounded">
                                <div class="stat-label text-muted small">Score</div>
                                <div class="stat-value fs-3 fw-bold text-success" id="scoreDisplay">
                                    ${score}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card text-center p-3 bg-danger bg-opacity-10 rounded">
                                <div class="stat-label text-muted small">Missed</div>
                                <div class="stat-value fs-3 fw-bold text-danger" id="missedDisplay">
                                    ${missed}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="progress mt-3" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: ${(targetsClicked / totalTargets) * 100}%"
                             id="progressBar"></div>
                    </div>
                </div>

                <!-- Game Area -->
                <div class="game-area position-relative bg-light rounded" 
                     style="height: 400px; overflow: hidden; cursor: crosshair;"
                     id="gameArea">
                    <div class="text-center position-absolute top-50 start-50 translate-middle text-muted">
                        <i class="bi bi-hand-index fs-1"></i>
                        <p class="mt-2">Click the targets as they appear!</p>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>How to play:</strong> Click on the colored circles as quickly as possible before they disappear. 
                    Each successful click earns points. Missing targets will count against you!
                </div>
            </div>
        `;

        container.innerHTML = gameHTML;
    }

    // Start game
    function startGame() {
        gameActive = true;
        spawnTarget();
    }

    // Spawn a new target
    function spawnTarget() {
        if (!gameActive || targetsClicked >= totalTargets) {
            endGame();
            return;
        }

        const gameArea = document.getElementById('gameArea');
        if (!gameArea) return;

        // Clear any existing target
        if (currentTarget) {
            currentTarget.remove();
        }

        // Create new target
        const target = document.createElement('div');
        target.className = 'reaction-target';
        
        // Random position within game area (with padding)
        const areaRect = gameArea.getBoundingClientRect();
        const targetSize = 60;
        const padding = 20;
        
        const maxX = areaRect.width - targetSize - padding;
        const maxY = areaRect.height - targetSize - padding;
        
        const x = Math.random() * maxX + padding;
        const y = Math.random() * maxY + padding;
        
        target.style.left = x + 'px';
        target.style.top = y + 'px';
        
        // Random color
        const colors = [
            'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
            'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
            'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
            'linear-gradient(135deg, #30cfd0 0%, #330867 100%)',
        ];
        target.style.background = colors[Math.floor(Math.random() * colors.length)];
        
        // Add click handler
        target.addEventListener('click', handleTargetClick);
        
        // Add to game area
        gameArea.appendChild(target);
        currentTarget = target;
        
        // Animate target appearance
        target.style.transform = 'scale(0)';
        setTimeout(() => {
            target.style.transform = 'scale(1)';
            target.style.transition = 'transform 0.2s ease-out';
        }, 10);
        
        // Set timeout to remove target if not clicked
        targetTimeout = setTimeout(() => {
            if (currentTarget === target) {
                missTarget();
            }
        }, targetSpeed);
    }

    // Handle target click
    function handleTargetClick(e) {
        if (!gameActive) return;
        
        e.stopPropagation();
        
        // Clear timeout
        if (targetTimeout) {
            clearTimeout(targetTimeout);
        }
        
        // Calculate score based on speed (faster = more points)
        const basePoints = 100;
        score += basePoints;
        targetsClicked++;
        
        // Visual feedback
        const target = e.currentTarget;
        target.style.transform = 'scale(1.5)';
        target.style.opacity = '0';
        
        // Show points popup
        showPointsPopup(target, `+${basePoints}`);
        
        // Update UI
        updateUI();
        
        // Spawn next target after short delay
        setTimeout(() => {
            target.remove();
            spawnTarget();
        }, 200);
    }

    // Handle missed target
    function missTarget() {
        if (!gameActive) return;
        
        missed++;
        
        // Visual feedback
        if (currentTarget) {
            currentTarget.style.transform = 'scale(0)';
            currentTarget.style.opacity = '0';
            
            // Show miss popup
            showPointsPopup(currentTarget, 'MISS', true);
            
            setTimeout(() => {
                if (currentTarget) {
                    currentTarget.remove();
                }
            }, 300);
        }
        
        // Update UI
        updateUI();
        
        // Spawn next target
        setTimeout(() => {
            spawnTarget();
        }, 500);
    }

    // Show points popup
    function showPointsPopup(target, text, isMiss = false) {
        const popup = document.createElement('div');
        popup.className = 'points-popup';
        popup.textContent = text;
        popup.style.cssText = `
            position: absolute;
            left: ${target.style.left};
            top: ${target.style.top};
            color: ${isMiss ? '#dc3545' : '#28a745'};
            font-weight: bold;
            font-size: 1.5rem;
            pointer-events: none;
            animation: floatUp 1s ease-out forwards;
            z-index: 1000;
        `;
        
        const gameArea = document.getElementById('gameArea');
        if (gameArea) {
            gameArea.appendChild(popup);
            setTimeout(() => popup.remove(), 1000);
        }
    }

    // Update UI
    function updateUI() {
        document.getElementById('targetsHit').textContent = `${targetsClicked} / ${totalTargets}`;
        document.getElementById('scoreDisplay').textContent = score;
        document.getElementById('missedDisplay').textContent = missed;
        
        const progressBar = document.getElementById('progressBar');
        if (progressBar) {
            progressBar.style.width = `${(targetsClicked / totalTargets) * 100}%`;
        }
    }

    // End game
    function endGame() {
        gameActive = false;
        
        if (targetTimeout) {
            clearTimeout(targetTimeout);
        }
        
        if (currentTarget) {
            currentTarget.remove();
        }

        const accuracy = totalTargets > 0 ? ((targetsClicked / totalTargets) * 100).toFixed(1) : 0;
        const passed = accuracy >= 60; // Need 60% accuracy to pass

        container.innerHTML = `
            <div class="reaction-results text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-${passed ? 'trophy' : 'x-circle'} display-1 text-${passed ? 'success' : 'danger'}"></i>
                </div>
                
                <h2 class="mb-3">${passed ? 'Excellent Reflexes!' : 'Game Over'}</h2>
                
                <div class="card shadow-sm mx-auto" style="max-width: 500px;">
                    <div class="card-body p-4">
                        <h3 class="mb-4">Your Results</h3>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="stat-box p-3 bg-primary bg-opacity-10 rounded">
                                    <div class="small text-muted">Targets Hit</div>
                                    <div class="fs-4 fw-bold text-primary">${targetsClicked}/${totalTargets}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box p-3 bg-success bg-opacity-10 rounded">
                                    <div class="small text-muted">Final Score</div>
                                    <div class="fs-4 fw-bold text-success">${score}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box p-3 bg-danger bg-opacity-10 rounded">
                                    <div class="small text-muted">Missed</div>
                                    <div class="fs-4 fw-bold text-danger">${missed}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box p-3 bg-info bg-opacity-10 rounded">
                                    <div class="small text-muted">Accuracy</div>
                                    <div class="fs-4 fw-bold text-info">${accuracy}%</div>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-muted mb-4">
                            ${passed 
                                ? 'Great job! Your reflexes are sharp!' 
                                : 'Keep practicing to improve your reaction time!'}
                        </p>
                        
                        <div class="d-grid gap-2">
                            ${passed ? `
                                <button class="btn btn-success btn-lg" onclick="window.completeGame(true, ${score}, ${totalTargets * 100})">
                                    <i class="bi bi-check-circle me-2"></i>Claim Rewards
                                </button>
                            ` : `
                                <button class="btn btn-primary btn-lg" onclick="location.reload()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Try Again
                                </button>
                            `}
                            <a href="/games" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Games
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Add CSS animation for points popup
    const style = document.createElement('style');
    style.textContent = `
        @keyframes floatUp {
            0% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            100% {
                transform: translateY(-50px) scale(1.2);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Start the game
    init();
})();
