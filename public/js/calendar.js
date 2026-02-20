/**
 * Study Session Calendar Integration
 * 
 * This module handles FullCalendar initialization and event handling for study sessions.
 * Features:
 * - Display study sessions in calendar view (month/week/day)
 * - Drag-and-drop to reschedule sessions
 * - Click on date to create new session
 * - Click on event to view session details
 * - Different colors for planned vs completed sessions
 */

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';

/**
 * Initialize the calendar when DOM is ready
 */
export function initializeCalendar(options = {}) {
    const calendarEl = document.getElementById('calendar');
    
    if (!calendarEl) {
        console.error('Calendar element not found');
        return null;
    }

    // Get URLs from options or data attributes
    const eventsUrl = options.eventsUrl || calendarEl.dataset.eventsUrl;
    const updateUrl = options.updateUrl || calendarEl.dataset.updateUrl;
    const createUrl = options.createUrl || calendarEl.dataset.createUrl;
    const showUrl = options.showUrl || calendarEl.dataset.showUrl;
    const editUrl = options.editUrl || calendarEl.dataset.editUrl;

    // Initialize Bootstrap modals
    const eventDetailModal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
    const createSessionModal = new bootstrap.Modal(document.getElementById('createSessionModal'));

    // Create calendar instance
    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        editable: true,
        droppable: true,
        selectable: true,
        eventDrop: (info) => handleEventDrop(info, updateUrl),
        eventResize: (info) => handleEventResize(info, updateUrl),
        dateClick: (info) => handleDateClick(info, createUrl, createSessionModal),
        eventClick: (info) => handleEventClick(info, showUrl, editUrl, eventDetailModal),
        events: {
            url: eventsUrl,
            failure: function() {
                showAlert('danger', 'There was an error loading calendar events.');
            }
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        height: 'auto',
        aspectRatio: 1.8,
        ...options.calendarOptions
    });

    calendar.render();
    
    return calendar;
}

/**
 * Handle event drop (drag and drop to reschedule)
 */
function handleEventDrop(info, updateUrl) {
    const eventId = info.event.id;
    const newStart = info.event.start.toISOString();
    const newEnd = info.event.end ? info.event.end.toISOString() : null;

    fetch(updateUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: eventId,
            start: newStart,
            end: newEnd
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || 'Event updated successfully');
        } else {
            // Revert the event to original position
            info.revert();
            showAlert('danger', data.error || 'Failed to update event');
        }
    })
    .catch(error => {
        // Revert on network error
        info.revert();
        showAlert('danger', 'Network error: ' + error.message);
    });
}

/**
 * Handle event resize (change duration)
 */
function handleEventResize(info, updateUrl) {
    // Reuse the drop handler since the logic is the same
    handleEventDrop(info, updateUrl);
}

/**
 * Handle date click (create new session)
 */
function handleDateClick(info, createUrl, modal) {
    const clickedDate = info.dateStr;
    const dateObj = new Date(clickedDate);
    
    // Format the date for display
    const formattedDate = dateObj.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    document.getElementById('selectedDate').textContent = formattedDate;

    // Build create URL with date parameter
    const url = new URL(createUrl, window.location.origin);
    url.searchParams.set('date', clickedDate);
    
    // If the click includes a time (from timeGrid views), add it
    if (info.date.getHours() !== 0 || info.date.getMinutes() !== 0) {
        const timeStr = info.date.toTimeString().substring(0, 5); // HH:MM format
        url.searchParams.set('time', timeStr);
    }
    
    document.getElementById('createSessionBtn').href = url.toString();

    modal.show();
}

/**
 * Handle event click (show session details)
 */
function handleEventClick(info, showUrl, editUrl, modal) {
    const event = info.event;
    const extendedProps = event.extendedProps;
    
    // Build details HTML
    let detailsHtml = buildEventDetailsHtml(event, extendedProps);
    
    document.getElementById('eventDetailContent').innerHTML = detailsHtml;

    // Configure action buttons
    const viewBtn = document.getElementById('viewSessionBtn');
    const editBtn = document.getElementById('editSessionBtn');
    
    if (extendedProps.sessionId) {
        // Session exists - show view and edit buttons
        viewBtn.href = showUrl.replace('__ID__', extendedProps.sessionId);
        viewBtn.style.display = 'inline-block';
        editBtn.href = editUrl.replace('__ID__', extendedProps.sessionId);
        editBtn.style.display = 'inline-block';
    } else {
        // Only planning exists - hide buttons
        viewBtn.style.display = 'none';
        editBtn.style.display = 'none';
    }

    modal.show();
}

/**
 * Build HTML for event details display
 */
function buildEventDetailsHtml(event, extendedProps) {
    let html = '';
    
    // Title
    html += `<div class="detail-row"><span class="detail-label">Title:</span> ${escapeHtml(event.title)}</div>`;
    
    // Start time
    html += `<div class="detail-row"><span class="detail-label">Start:</span> ${event.start.toLocaleString()}</div>`;
    
    // End time
    if (event.end) {
        html += `<div class="detail-row"><span class="detail-label">End:</span> ${event.end.toLocaleString()}</div>`;
    }
    
    // Status
    const statusBadgeClass = getStatusBadgeClass(extendedProps.status);
    html += `<div class="detail-row"><span class="detail-label">Status:</span> <span class="badge bg-${statusBadgeClass}">${escapeHtml(extendedProps.status)}</span></div>`;
    
    // XP Earned
    if (extendedProps.xp !== undefined && extendedProps.xp !== null) {
        html += `<div class="detail-row"><span class="detail-label">XP Earned:</span> ${extendedProps.xp}</div>`;
    }
    
    // Mood
    if (extendedProps.mood) {
        html += `<div class="detail-row"><span class="detail-label">Mood:</span> ${escapeHtml(extendedProps.mood)}</div>`;
    }
    
    // Energy Level
    if (extendedProps.energyLevel) {
        html += `<div class="detail-row"><span class="detail-label">Energy Level:</span> ${escapeHtml(extendedProps.energyLevel)}</div>`;
    }
    
    return html;
}

/**
 * Get Bootstrap badge class for status
 */
function getStatusBadgeClass(status) {
    const statusMap = {
        'completed': 'success',
        'planned': 'primary',
        'scheduled': 'secondary'
    };
    
    return statusMap[status] || 'secondary';
}

/**
 * Show alert message
 */
function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    const container = document.querySelector('.container');
    if (!container) {
        console.error('Container element not found for alert');
        return;
    }
    
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = alertHtml;
    container.insertBefore(tempDiv.firstElementChild, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (text === null || text === undefined) {
        return '';
    }
    
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

/**
 * Export for use in templates
 */
export default {
    initializeCalendar,
    showAlert
};
