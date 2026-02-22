/**
 * Quick Quiz Game Engine
 * Multiple choice trivia game
 */

(function() {
    'use strict';

    const container = document.getElementById('gameContainer');
    if (!container) return;

    const gameId = container.dataset.gameId;
    const settings = JSON.parse(container.dataset.settings || '{}');
    
    // DEBUG: Log what we received
    console.log('=== QUICK QUIZ DEBUG START ===');
    console.log('Quick Quiz Engine Started');
    console.log('Game ID:', gameId);
    console.log('Raw settings object:', settings);
    console.log('settings.difficulty:', settings.difficulty);
    console.log('settings.questions:', settings.questions);
    console.log('settings.timeLimit:', settings.timeLimit);
    console.log('Has questionsData:', !!settings.questionsData);
    if (settings.questionsData) {
        console.log('Number of questions:', settings.questionsData.length);
    }
    console.log('=== QUICK QUIZ DEBUG END ===');

    // Game state
    let currentQuestionIndex = 0;
    let score = 0;
    let timeLeft = settings.timeLimit || 60;
    let timerInterval = null;
    let totalQuestions = settings.questions || 5;
    
    // Check if we have custom questions or need to generate defaults
    let questions = [];
    if (settings.questionsData && Array.isArray(settings.questionsData) && settings.questionsData.length > 0) {
        // Use custom questions from game content
        // Normalize the format (handle both "choices"/"correct" and "options"/"correctAnswer")
        questions = settings.questionsData.map(q => ({
            question: q.question,
            options: q.options || q.choices || [],
            correctAnswer: q.correctAnswer !== undefined ? q.correctAnswer : q.correct
        }));
        console.log('Loaded custom questions:', questions);
    } else {
        // Generate default questions
        console.log('No custom questions found, using defaults');
        console.log('Difficulty from settings:', settings.difficulty);
        console.log('Total questions needed:', totalQuestions);
        questions = generateDefaultQuestions(settings.difficulty || 'MEDIUM', totalQuestions);
        console.log('Generated questions:', questions.length, 'from difficulty:', settings.difficulty || 'MEDIUM');
    }

    // Initialize game
    function init() {
        container.innerHTML = '';
        renderGame();
        startTimer();
    }

    // Generate default questions based on difficulty
    function generateDefaultQuestions(difficulty, count) {
        console.log('generateDefaultQuestions called with:', difficulty, count);
        const questionSets = {
            EASY: [
                {
                    question: "What is 2 + 2?",
                    options: ["3", "4", "5", "6"],
                    correctAnswer: 1
                },
                {
                    question: "What color is the sky on a clear day?",
                    options: ["Green", "Blue", "Red", "Yellow"],
                    correctAnswer: 1
                },
                {
                    question: "How many days are in a week?",
                    options: ["5", "6", "7", "8"],
                    correctAnswer: 2
                },
                {
                    question: "What is the capital of France?",
                    options: ["London", "Berlin", "Paris", "Madrid"],
                    correctAnswer: 2
                },
                {
                    question: "How many legs does a spider have?",
                    options: ["6", "8", "10", "12"],
                    correctAnswer: 1
                }
            ],
            MEDIUM: [
                {
                    question: "What is the largest planet in our solar system?",
                    options: ["Earth", "Mars", "Jupiter", "Saturn"],
                    correctAnswer: 2
                },
                {
                    question: "Who wrote 'Romeo and Juliet'?",
                    options: ["Charles Dickens", "William Shakespeare", "Jane Austen", "Mark Twain"],
                    correctAnswer: 1
                },
                {
                    question: "What is the chemical symbol for gold?",
                    options: ["Go", "Gd", "Au", "Ag"],
                    correctAnswer: 2
                },
                {
                    question: "In which year did World War II end?",
                    options: ["1943", "1944", "1945", "1946"],
                    correctAnswer: 2
                },
                {
                    question: "What is the speed of light?",
                    options: ["300,000 km/s", "150,000 km/s", "450,000 km/s", "600,000 km/s"],
                    correctAnswer: 0
                },
                {
                    question: "How many continents are there?",
                    options: ["5", "6", "7", "8"],
                    correctAnswer: 2
                },
                {
                    question: "What is the smallest country in the world?",
                    options: ["Monaco", "Vatican City", "San Marino", "Liechtenstein"],
                    correctAnswer: 1
                }
            ],
            HARD: [
                {
                    question: "What is the smallest prime number?",
                    options: ["0", "1", "2", "3"],
                    correctAnswer: 2
                },
                {
                    question: "Who developed the theory of relativity?",
                    options: ["Isaac Newton", "Albert Einstein", "Stephen Hawking", "Niels Bohr"],
                    correctAnswer: 1
                },
                {
                    question: "What is the capital of Australia?",
                    options: ["Sydney", "Melbourne", "Canberra", "Brisbane"],
                    correctAnswer: 2
                },
                {
                    question: "How many elements are in the periodic table?",
                    options: ["108", "118", "128", "138"],
                    correctAnswer: 1
                },
                {
                    question: "What is the longest river in the world?",
                    options: ["Amazon", "Nile", "Yangtze", "Mississippi"],
                    correctAnswer: 1
                },
                {
                    question: "What is the square root of 144?",
                    options: ["10", "11", "12", "13"],
                    correctAnswer: 2
                },
                {
                    question: "Who painted the Mona Lisa?",
                    options: ["Michelangelo", "Leonardo da Vinci", "Raphael", "Donatello"],
                    correctAnswer: 1
                },
                {
                    question: "What is the boiling point of water at sea level?",
                    options: ["90°C", "95°C", "100°C", "105°C"],
                    correctAnswer: 2
                }
            ]
        };

        const allQuestions = questionSets[difficulty] || questionSets.MEDIUM;
        console.log('Selected question set for difficulty:', difficulty, '- Available questions:', allQuestions.length);
        
        // Return the requested number of questions (or all if count is larger)
        const selectedQuestions = allQuestions.slice(0, Math.min(count, allQuestions.length));
        console.log('Returning', selectedQuestions.length, 'questions');
        return selectedQuestions;
    }

    // Render game UI
    function renderGame() {
        const gameHTML = `
            <div class="quick-quiz-game">
                <!-- Header -->
                <div class="quiz-header mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="quiz-progress">
                            <span class="badge bg-primary fs-6">
                                Question ${currentQuestionIndex + 1} of ${questions.length}
                            </span>
                        </div>
                        <div class="quiz-score">
                            <span class="badge bg-success fs-6">
                                <i class="bi bi-trophy"></i> Score: ${score}/${questions.length}
                            </span>
                        </div>
                        <div class="quiz-timer">
                            <span class="badge bg-warning fs-6" id="timer">
                                <i class="bi bi-clock"></i> ${timeLeft}s
                            </span>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: ${(currentQuestionIndex / questions.length) * 100}%"
                             id="progressBar"></div>
                    </div>
                </div>

                <!-- Question Card -->
                <div class="quiz-question-card">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h4 class="question-text mb-4" id="questionText">
                                ${questions[currentQuestionIndex].question}
                            </h4>
                            
                            <div class="quiz-options" id="optionsContainer">
                                ${renderOptions()}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feedback Area -->
                <div id="feedbackArea" class="mt-3"></div>
            </div>
        `;

        container.innerHTML = gameHTML;
        attachEventListeners();
    }

    // Render answer options
    function renderOptions() {
        const currentQuestion = questions[currentQuestionIndex];
        return currentQuestion.options.map((option, index) => `
            <button class="quiz-option btn btn-outline-primary btn-lg w-100 mb-3 text-start" 
                    data-index="${index}">
                <span class="option-letter">${String.fromCharCode(65 + index)}.</span>
                <span class="option-text">${option}</span>
            </button>
        `).join('');
    }

    // Attach event listeners
    function attachEventListeners() {
        const options = container.querySelectorAll('.quiz-option');
        options.forEach(option => {
            option.addEventListener('click', handleAnswer);
        });
    }

    // Handle answer selection
    function handleAnswer(e) {
        const selectedIndex = parseInt(e.currentTarget.dataset.index);
        const currentQuestion = questions[currentQuestionIndex];
        const isCorrect = selectedIndex === currentQuestion.correctAnswer;

        // Disable all options
        const allOptions = container.querySelectorAll('.quiz-option');
        allOptions.forEach(opt => {
            opt.disabled = true;
            opt.classList.remove('btn-outline-primary');
            
            const optIndex = parseInt(opt.dataset.index);
            if (optIndex === currentQuestion.correctAnswer) {
                opt.classList.add('btn-success');
            } else if (optIndex === selectedIndex && !isCorrect) {
                opt.classList.add('btn-danger');
            } else {
                opt.classList.add('btn-secondary');
            }
        });

        // Update score
        if (isCorrect) {
            score++;
            showFeedback(true, "Correct! Well done!");
        } else {
            showFeedback(false, `Incorrect. The correct answer was: ${currentQuestion.options[currentQuestion.correctAnswer]}`);
        }

        // Move to next question after delay
        setTimeout(() => {
            currentQuestionIndex++;
            
            if (currentQuestionIndex < questions.length) {
                renderGame();
            } else {
                endGame();
            }
        }, 2000);
    }

    // Show feedback
    function showFeedback(isCorrect, message) {
        const feedbackArea = document.getElementById('feedbackArea');
        const alertClass = isCorrect ? 'alert-success' : 'alert-danger';
        const icon = isCorrect ? 'check-circle' : 'x-circle';
        
        feedbackArea.innerHTML = `
            <div class="alert ${alertClass} d-flex align-items-center">
                <i class="bi bi-${icon} fs-4 me-3"></i>
                <div>${message}</div>
            </div>
        `;
    }

    // Start timer
    function startTimer() {
        timerInterval = setInterval(() => {
            timeLeft--;
            
            const timerElement = document.getElementById('timer');
            if (timerElement) {
                timerElement.innerHTML = `<i class="bi bi-clock"></i> ${timeLeft}s`;
                
                // Change color when time is running out
                if (timeLeft <= 10) {
                    timerElement.classList.remove('bg-warning');
                    timerElement.classList.add('bg-danger');
                }
            }

            if (timeLeft <= 0) {
                endGame();
            }
        }, 1000);
    }

    // End game
    function endGame() {
        clearInterval(timerInterval);

        const percentage = (score / questions.length) * 100;
        const passed = percentage >= 60; // 60% to pass

        container.innerHTML = `
            <div class="quiz-results text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-${passed ? 'trophy' : 'x-circle'} display-1 text-${passed ? 'success' : 'danger'}"></i>
                </div>
                
                <h2 class="mb-3">${passed ? 'Congratulations!' : 'Game Over'}</h2>
                
                <div class="card shadow-sm mx-auto" style="max-width: 500px;">
                    <div class="card-body p-4">
                        <h3 class="mb-4">Your Score</h3>
                        
                        <div class="display-4 mb-3 text-${passed ? 'success' : 'danger'}">
                            ${score} / ${questions.length}
                        </div>
                        
                        <div class="progress mb-3" style="height: 30px;">
                            <div class="progress-bar bg-${passed ? 'success' : 'danger'}" 
                                 role="progressbar" 
                                 style="width: ${percentage}%">
                                ${percentage.toFixed(0)}%
                            </div>
                        </div>
                        
                        <p class="text-muted mb-4">
                            ${passed 
                                ? 'Great job! You passed the quiz!' 
                                : 'Keep practicing! You need 60% to pass.'}
                        </p>
                        
                        <div class="d-grid gap-2">
                            ${passed ? `
                                <button class="btn btn-success btn-lg" onclick="window.completeGame(true, ${score}, ${questions.length})">
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

        // Call completion handler if passed
        if (passed && typeof window.completeGame === 'function') {
            // Don't auto-call, let user click the button
        }
    }

    // Start the game
    init();
})();
