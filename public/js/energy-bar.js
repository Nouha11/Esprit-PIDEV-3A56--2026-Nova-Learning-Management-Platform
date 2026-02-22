/**
 * EnergyBar Component
 * Monitors and displays student energy levels during course sessions
 */
class EnergyBar {
    constructor(initialEnergy, checkUrl) {
        this.energy = initialEnergy;
        this.checkUrl = checkUrl;
        this.element = document.getElementById('energy-bar');
        this.modal = document.getElementById('energy-depletion-modal');
        this.modalShown = false;
        this.contentOverlay = document.getElementById('course-content-overlay');
        
        if (!this.element) {
            console.error('Energy bar element not found');
            return;
        }
        
        // Initialize display
        this.updateDisplay();
    }

    /**
     * Update the visual display of the energy bar
     */
    updateDisplay() {
        if (!this.element) return;
        
        const progressBar = this.element.querySelector('.progress-bar');
        const percentageText = this.element.querySelector('.energy-percentage');
        
        if (progressBar) {
            // Update width
            progressBar.style.width = this.energy + '%';
            progressBar.setAttribute('aria-valuenow', this.energy);
            
            // Update color based on energy level
            progressBar.className = 'progress-bar';
            if (this.energy > 50) {
                progressBar.classList.add('bg-success');
            } else if (this.energy > 20) {
                progressBar.classList.add('bg-warning');
            } else {
                progressBar.classList.add('bg-danger');
            }
        }
        
        if (percentageText) {
            percentageText.textContent = this.energy + '%';
        }
        
        // Update interaction blocking based on energy
        this.updateInteractionBlocking();
    }

    /**
     * Start monitoring energy levels with periodic polling
     */
    startMonitoring() {
        // Poll every 5 seconds
        setInterval(() => {
            this.checkEnergy();
        }, 5000);
    }

    /**
     * Check energy level via AJAX
     */
    async checkEnergy() {
        try {
            const response = await fetch(this.checkUrl);
            
            if (!response.ok) {
                console.error('Failed to check energy:', response.statusText);
                return;
            }
            
            const data = await response.json();
            
            // Update energy level
            this.energy = data.energy;
            this.updateDisplay();
            
            // Show modal if energy is depleted and modal hasn't been shown yet
            if (data.depleted && !this.modalShown) {
                this.showDepletionModal();
            }
            
            // Hide modal if energy is restored
            if (!data.depleted && this.modalShown) {
                this.hideDepletionModal();
            }
        } catch (error) {
            console.error('Error checking energy:', error);
        }
    }

    /**
     * Show the energy depletion modal
     */
    showDepletionModal() {
        if (!this.modal) {
            console.error('Energy depletion modal not found');
            return;
        }
        
        // Show modal using Bootstrap modal API if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = new bootstrap.Modal(this.modal, {
                backdrop: 'static',
                keyboard: false
            });
            modalInstance.show();
        } else {
            // Fallback to manual display
            this.modal.style.display = 'block';
            this.modal.classList.add('show');
            
            // Add backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'energy-modal-backdrop';
            document.body.appendChild(backdrop);
            document.body.classList.add('modal-open');
        }
        
        this.modalShown = true;
        this.updateInteractionBlocking();
    }

    /**
     * Hide the energy depletion modal (when energy is restored)
     */
    hideDepletionModal() {
        if (!this.modal) return;
        
        // Hide modal using Bootstrap modal API if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getInstance(this.modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        } else {
            // Fallback to manual hide
            this.modal.style.display = 'none';
            this.modal.classList.remove('show');
            
            // Remove backdrop
            const backdrop = document.getElementById('energy-modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            document.body.classList.remove('modal-open');
        }
        
        this.modalShown = false;
        this.updateInteractionBlocking();
    }

    /**
     * Update course interaction blocking based on energy level
     */
    updateInteractionBlocking() {
        if (!this.contentOverlay) return;
        
        if (this.energy <= 0) {
            // Block interactions
            this.contentOverlay.style.display = 'block';
        } else {
            // Enable interactions
            this.contentOverlay.style.display = 'none';
        }
    }
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EnergyBar;
}
