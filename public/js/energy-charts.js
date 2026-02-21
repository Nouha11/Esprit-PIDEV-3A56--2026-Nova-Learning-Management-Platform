/**
 * Energy Charts - Study Session Enhancement
 * 
 * This module handles Chart.js visualizations for energy analytics.
 * It creates a bar chart showing energy levels by time of day,
 * highlighting high-energy and low-energy periods.
 * 
 * Requirements: 13.4
 */

(function() {
    'use strict';

    /**
     * Map energy level strings to numeric values for chart display
     * @param {string} energyLevel - Energy level ('low', 'medium', 'high')
     * @returns {number} Numeric value (1, 2, or 3)
     */
    function mapEnergyToValue(energyLevel) {
        const mapping = {
            'low': 1,
            'medium': 2,
            'high': 3
        };
        return mapping[energyLevel] || 0;
    }

    /**
     * Get color for energy level
     * @param {string} energyLevel - Energy level ('low', 'medium', 'high')
     * @returns {string} Color code
     */
    function getEnergyColor(energyLevel) {
        const colors = {
            'low': 'rgba(255, 99, 132, 0.6)',      // Red for low energy
            'medium': 'rgba(255, 206, 86, 0.6)',   // Yellow for medium energy
            'high': 'rgba(75, 192, 192, 0.6)'      // Green for high energy
        };
        return colors[energyLevel] || 'rgba(201, 203, 207, 0.6)';
    }

    /**
     * Get border color for energy level
     * @param {string} energyLevel - Energy level ('low', 'medium', 'high')
     * @returns {string} Border color code
     */
    function getEnergyBorderColor(energyLevel) {
        const colors = {
            'low': 'rgba(255, 99, 132, 1)',      // Red for low energy
            'medium': 'rgba(255, 206, 86, 1)',   // Yellow for medium energy
            'high': 'rgba(75, 192, 192, 1)'      // Green for high energy
        };
        return colors[energyLevel] || 'rgba(201, 203, 207, 1)';
    }

    /**
     * Format hour to readable time string
     * @param {number} hour - Hour in 24-hour format (0-23)
     * @returns {string} Formatted time string
     */
    function formatHour(hour) {
        if (hour === 0) {
            return '12 AM';
        } else if (hour < 12) {
            return hour + ' AM';
        } else if (hour === 12) {
            return '12 PM';
        } else {
            return (hour - 12) + ' PM';
        }
    }

    /**
     * Initialize Energy Patterns Bar Chart
     * @param {string} canvasId - The canvas element ID
     * @param {Array} patterns - Energy pattern data
     */
    function initEnergyPatternsChart(canvasId, patterns) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) {
            console.warn(`Canvas element with ID "${canvasId}" not found`);
            return null;
        }

        // Sort patterns by hour
        patterns.sort((a, b) => a.hour - b.hour);

        // Prepare data for chart
        const labels = patterns.map(p => formatHour(p.hour));
        const data = patterns.map(p => mapEnergyToValue(p.avg_energy));
        const backgroundColors = patterns.map(p => getEnergyColor(p.avg_energy));
        const borderColors = patterns.map(p => getEnergyBorderColor(p.avg_energy));

        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Energy Level',
                    data: data,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 3,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                const labels = {
                                    0: '',
                                    1: 'Low',
                                    2: 'Medium',
                                    3: 'High'
                                };
                                return labels[value] || '';
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Time of Day'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const pattern = patterns[context.dataIndex];
                                const energyLabel = pattern.avg_energy.charAt(0).toUpperCase() + 
                                                  pattern.avg_energy.slice(1);
                                return [
                                    `Energy Level: ${energyLabel}`,
                                    `Sessions: ${pattern.session_count}`
                                ];
                            }
                        }
                    },
                    title: {
                        display: false
                    }
                }
            }
        });
    }

    /**
     * Highlight high-energy and low-energy periods
     * Adds visual indicators to the chart
     * @param {Array} patterns - Energy pattern data
     */
    function highlightEnergyPeriods(patterns) {
        const highEnergyHours = patterns
            .filter(p => p.avg_energy === 'high')
            .map(p => p.hour);
        
        const lowEnergyHours = patterns
            .filter(p => p.avg_energy === 'low')
            .map(p => p.hour);

        // Log high and low energy periods for debugging
        if (highEnergyHours.length > 0) {
            console.log('High energy periods:', highEnergyHours.map(h => formatHour(h)).join(', '));
        }
        
        if (lowEnergyHours.length > 0) {
            console.log('Low energy periods:', lowEnergyHours.map(h => formatHour(h)).join(', '));
        }

        return {
            highEnergyHours: highEnergyHours,
            lowEnergyHours: lowEnergyHours
        };
    }

    /**
     * Initialize all energy charts from data
     */
    function initEnergyCharts() {
        // Check if energy data is available
        if (!window.energyData || !window.energyData.patterns) {
            console.warn('Energy data not found');
            return;
        }

        const patterns = window.energyData.patterns;

        if (!patterns || patterns.length === 0) {
            console.warn('No energy patterns data available');
            return;
        }

        // Initialize Energy Patterns Chart
        initEnergyPatternsChart('energyPatternsChart', patterns);

        // Highlight high and low energy periods
        const highlights = highlightEnergyPeriods(patterns);
        
        // Store highlights for external access
        window.energyData.highlights = highlights;
    }

    // Initialize charts when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEnergyCharts);
    } else {
        initEnergyCharts();
    }

    // Export functions for external use
    window.EnergyCharts = {
        initEnergyPatternsChart: initEnergyPatternsChart,
        highlightEnergyPeriods: highlightEnergyPeriods,
        initEnergyCharts: initEnergyCharts
    };

})();
