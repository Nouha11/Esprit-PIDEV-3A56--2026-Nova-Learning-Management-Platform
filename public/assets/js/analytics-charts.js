/**
 * Analytics Charts - Study Session Enhancement
 * 
 * This module handles Chart.js visualizations for the analytics dashboard.
 * It creates two main charts:
 * 1. Study Time by Course (Bar Chart)
 * 2. XP Earned Over Time (Line Chart)
 * 
 * Requirements: 2.4, 2.5, 24.1
 */

(function() {
    'use strict';

    /**
     * Initialize Study Time by Course Bar Chart
     * @param {string} canvasId - The canvas element ID
     * @param {Array} labels - Course names
     * @param {Array} data - Study time durations in minutes
     */
    function initStudyTimeByCourseChart(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) {
            console.warn(`Canvas element with ID "${canvasId}" not found`);
            return null;
        }

        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Study Time (minutes)',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' min';
                            }
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
                                return context.parsed.y + ' minutes';
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Initialize XP Over Time Line Chart
     * @param {string} canvasId - The canvas element ID
     * @param {Array} labels - Date labels
     * @param {Array} data - XP values
     */
    function initXPOverTimeChart(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) {
            console.warn(`Canvas element with ID "${canvasId}" not found`);
            return null;
        }

        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'XP Earned',
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' XP';
                            }
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
                                return context.parsed.y + ' XP';
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Initialize all analytics charts from data attributes
     * This function reads data from the DOM and initializes charts
     */
    function initAnalyticsCharts() {
        // Check if analytics data is available
        if (!window.analyticsData) {
            console.warn('Analytics data not found');
            return;
        }

        // Initialize Study Time by Course Chart
        if (window.analyticsData.hasCourseData && window.analyticsData.course) {
            const courseData = window.analyticsData.course;
            if (courseData.labels && courseData.data) {
                initStudyTimeByCourseChart(
                    'studyTimeByCourseChart',
                    courseData.labels,
                    courseData.data
                );
            }
        }

        // Initialize XP Over Time Chart
        if (window.analyticsData.hasXpData && window.analyticsData.xp) {
            const xpData = window.analyticsData.xp;
            if (xpData.labels && xpData.data) {
                initXPOverTimeChart(
                    'xpOverTimeChart',
                    xpData.labels,
                    xpData.data
                );
            }
        }
    }

    /**
     * Fetch analytics data via AJAX and update charts
     * @param {string} timeRange - The time range filter (week, month, year)
     */
    function fetchAnalyticsData(timeRange) {
        // This function can be used for AJAX-based data fetching
        // Currently, data is passed via Twig template
        console.log('Fetching analytics data for time range:', timeRange);
        
        // Example AJAX implementation (commented out):
        /*
        fetch(`/analytics/data?range=${timeRange}`)
            .then(response => response.json())
            .then(data => {
                // Update charts with new data
                updateCharts(data);
            })
            .catch(error => {
                console.error('Error fetching analytics data:', error);
            });
        */
    }

    /**
     * Make charts responsive for mobile devices
     * Adjusts chart options based on screen size
     */
    function makeChartsResponsive() {
        const isMobile = window.innerWidth < 768;
        
        if (isMobile) {
            // Adjust chart options for mobile
            Chart.defaults.font.size = 10;
            Chart.defaults.plugins.legend.display = false;
        }
    }

    // Initialize charts when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            makeChartsResponsive();
            initAnalyticsCharts();
        });
    } else {
        makeChartsResponsive();
        initAnalyticsCharts();
    }

    // Handle window resize for responsive charts
    window.addEventListener('resize', makeChartsResponsive);

    // Export functions for external use
    window.AnalyticsCharts = {
        initStudyTimeByCourseChart: initStudyTimeByCourseChart,
        initXPOverTimeChart: initXPOverTimeChart,
        initAnalyticsCharts: initAnalyticsCharts,
        fetchAnalyticsData: fetchAnalyticsData
    };

})();
