/*!
 * Partner Calendar - API-driven FullCalendar for Partner Events
 * Partner Manager 3
 */

import bootstrap from 'bootstrap/dist/js/bootstrap.min';
import { Calendar } from '@fullcalendar/core';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import dayGridPlugin from '@fullcalendar/daygrid';

document.addEventListener('DOMContentLoaded', function () {
    const config = window.calendarConfig;
    if (!config) return;

    // ─── DOM References ────────────────────────────────────────
    const calendarEl = document.getElementById('calendar');
    const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const eventForm = document.getElementById('eventForm');
    const addEventBtn = document.getElementById('addEventBtn');

    // Form fields
    const f = {
        id: document.getElementById('eventId'),
        parentId: document.getElementById('eventParentId'),
        title: document.getElementById('eventTitle'),
        partner: document.getElementById('eventPartner'),
        type: document.getElementById('eventType'),
        start: document.getElementById('eventStart'),
        end: document.getElementById('eventEnd'),
        allDay: document.getElementById('eventAllDay'),
        status: document.getElementById('eventStatus'),
        location: document.getElementById('eventLocation'),
        opportunity: document.getElementById('eventOpportunity'),
        project: document.getElementById('eventProject'),
        participants: document.getElementById('eventParticipants'),
        description: document.getElementById('eventDescription'),
        recurrence: document.getElementById('eventRecurrence'),
        recurrenceEnd: document.getElementById('eventRecurrenceEnd'),
        outcome: document.getElementById('eventOutcome'),
        nextSteps: document.getElementById('eventNextSteps'),
    };

    // ─── Populate Static Dropdowns ─────────────────────────────
    // Partners
    config.partners.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = p.name;
        f.partner.appendChild(opt);
    });

    // If single-partner mode, pre-select and disable
    if (config.mode === 'partner' && config.partnerId) {
        f.partner.value = config.partnerId;
        f.partner.disabled = true;
        loadPartnerData(config.partnerId);
    }

    // Event types
    Object.entries(config.eventTypes).forEach(([key, label]) => {
        const opt = document.createElement('option');
        opt.value = key;
        opt.textContent = label;
        f.type.appendChild(opt);
    });

    // Type filter checkboxes
    const typeFiltersEl = document.getElementById('type-filters');
    if (typeFiltersEl) {
        Object.entries(config.eventTypes).forEach(([key, label]) => {
            const color = config.typeColors[key] || '#868e96';
            const div = document.createElement('div');
            div.className = 'form-check';
            div.innerHTML = `
                <input class="form-check-input type-filter" type="checkbox" value="${key}" id="type-filter-${key}" checked>
                <label class="form-check-label" for="type-filter-${key}">
                    <span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:${color}"></span>
                    ${label}
                </label>
            `;
            typeFiltersEl.appendChild(div);
        });
    }

    // ─── Initialize Calendar ───────────────────────────────────
    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin, listPlugin, timeGridPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            right: 'today prev,next',
            left: 'title'
        },
        datesSet: function () {
            setTimeout(() => applyBootstrapStyling(), 0);
        },
        footerToolbar: {
            left: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Today',
            month: 'Month',
            week: 'Week',
            day: 'Day',
            list: 'List'
        },
        dayMaxEvents: 2,
        height: undefined,
        minHeight: '750px',
        editable: true,
        selectable: true,
        businessHours: {
            daysOfWeek: [1, 2, 3, 4, 5],
            startTime: '9:00',
            endTime: '17:00'
        },
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        },

        // API-driven event source — merges partner events + enablement sessions
        events: function (info, successCallback, failureCallback) {
            const params = new URLSearchParams({
                start: info.startStr,
                end: info.endStr,
            });

            if (config.mode === 'partner' && config.partnerId) {
                params.append('partner_id', config.partnerId);
            }

            // Fetch partner events
            const partnerEventsP = apiFetch(`/events?${params.toString()}`);

            // Fetch enablement sessions this partner is registered for
            const enablementParams = new URLSearchParams({
                start: info.startStr,
                end: info.endStr,
            });
            const enablementP = config.partnerId
                ? apiFetch(`/enablement/partners/${config.partnerId}/sessions?${enablementParams.toString()}`)
                    .catch(() => [])
                : Promise.resolve([]);

            // Fetch all available published enablement sessions (for sidebar)
            const allEnablementP = apiFetch(`/enablement/sessions?${enablementParams.toString()}`)
                .catch(() => []);

            Promise.all([partnerEventsP, enablementP, allEnablementP])
                .then(([partnerEvents, registeredSessions, allSessions]) => {
                    // Tag enablement sessions so they're distinguishable
                    const enablementEvents = registeredSessions.map(s => ({
                        ...s,
                        editable: false,
                        extendedProps: {
                            ...s.extendedProps,
                            is_enablement: true,
                            source: 'enablement',
                        },
                    }));

                    const merged = partnerEvents.concat(enablementEvents);
                    successCallback(merged);
                    setTimeout(() => applyTypeFilter(), 50);
                    if (config.mode === 'partner') {
                        setTimeout(() => updateUpcomingList(partnerEvents), 100);
                        setTimeout(() => updateEnablementSidebar(allSessions, registeredSessions), 100);
                    }
                })
                .catch(err => {
                    console.error('Error fetching events:', err);
                    failureCallback(err);
                });
        },

        // Click empty date → create
        select: function (info) {
            openCreateModal(info.start, info.end, info.allDay);
        },

        // Click event → edit (partner events) or navigate (enablement)
        eventClick: function (info) {
            const props = info.event.extendedProps;

            // Enablement sessions → go to enablement calendar
            if (props.is_enablement) {
                info.jsEvent.preventDefault();
                if (config.enablementCalendarUrl) {
                    window.location.href = config.enablementCalendarUrl;
                }
                return;
            }

            const eventId = props.event_id || info.event.id;
            // For recurring occurrences, edit the parent
            const realId = props.is_occurrence
                ? props.parent_event_id
                : eventId;
            openEditModal(realId);
        },

        // Drag-drop → quick update
        eventDrop: function (info) {
            quickUpdateEvent(info.event);
        },

        // Resize → quick update
        eventResize: function (info) {
            quickUpdateEvent(info.event);
        },

        // Tooltip on hover
        eventDidMount: function (info) {
            const props = info.event.extendedProps;
            let tipParts = [info.event.title];
            if (props.is_enablement) tipParts.push('Enablement');
            if (props.partner_name) tipParts.push(props.partner_name);
            if (props.type_label) tipParts.push(props.type_label);
            if (props.location) tipParts.push(props.location);

            // Visual distinction for enablement sessions
            if (props.is_enablement) {
                info.el.style.borderLeft = '3px solid #fff';
                info.el.style.borderStyle = 'dashed';
                info.el.style.borderWidth = '1px';
            }

            if (props.status === 'completed') {
                info.el.style.opacity = '0.7';
                info.el.style.textDecoration = 'line-through';
            }
            if (props.status === 'cancelled') {
                info.el.style.opacity = '0.5';
                info.el.style.textDecoration = 'line-through';
            }
            info.el.setAttribute('title', tipParts.join(' | '));
        },
    });

    calendar.render();

    // ─── "New Event" Button ────────────────────────────────────
    if (addEventBtn) {
        addEventBtn.addEventListener('click', () => {
            openCreateModal(new Date(), null, false);
        });
    }

    // ─── Form Submission ───────────────────────────────────────
    eventForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = f.id.value;
        const payload = gatherFormData();

        if (id) {
            apiFetch(`/events/${id}`, 'PUT', payload)
                .then(() => {
                    calendar.refetchEvents();
                    eventModal.hide();
                    showToast('Event updated successfully');
                })
                .catch(err => showToast('Error: ' + err.message, 'danger'));
        } else {
            apiFetch('/events', 'POST', payload)
                .then(() => {
                    calendar.refetchEvents();
                    eventModal.hide();
                    showToast('Event created successfully');
                })
                .catch(err => showToast('Error: ' + err.message, 'danger'));
        }
    });

    // ─── Delete Button in Modal ────────────────────────────────
    document.getElementById('deleteEvent').addEventListener('click', function () {
        const id = f.id.value;
        if (!id) return;
        document.getElementById('deleteEventId').value = id;
        document.getElementById('deleteEventTitle').textContent = f.title.value;
        eventModal.hide();
        deleteModal.show();
    });

    document.getElementById('confirmDelete').addEventListener('click', function () {
        const id = document.getElementById('deleteEventId').value;
        if (!id) return;
        apiFetch(`/events/${id}`, 'DELETE')
            .then(() => {
                calendar.refetchEvents();
                deleteModal.hide();
                showToast('Event deleted');
            })
            .catch(err => showToast('Error: ' + err.message, 'danger'));
    });

    // ─── Partner Change → Load Contacts/Opps/Projects ─────────
    f.partner.addEventListener('change', function () {
        const partnerId = this.value;
        if (partnerId) {
            loadPartnerData(partnerId);
        } else {
            document.getElementById('linkSection').style.display = 'none';
            document.getElementById('participantSection').style.display = 'none';
        }
    });

    // ─── Status Change → Show/Hide Outcome ─────────────────────
    f.status.addEventListener('change', function () {
        document.getElementById('outcomeSection').style.display =
            (this.value === 'completed') ? '' : 'none';
    });

    // ─── Recurrence Change → Show/Hide End Date ─────────────────
    f.recurrence.addEventListener('change', function () {
        document.getElementById('recurrenceEndSection').style.display =
            (this.value !== 'none') ? '' : 'none';
    });

    // ─── Type Filter ───────────────────────────────────────────
    document.querySelectorAll('.type-filter').forEach(cb => {
        cb.addEventListener('change', () => applyTypeFilter());
    });

    const toggleAllTypesBtn = document.getElementById('toggleAllTypes');
    if (toggleAllTypesBtn) {
        toggleAllTypesBtn.addEventListener('click', function () {
            const checkboxes = document.querySelectorAll('.type-filter');
            const allChecked = [...checkboxes].every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
            applyTypeFilter();
        });
    }

    // ─── Partner Filter (portfolio mode) ───────────────────────
    document.querySelectorAll('.partner-filter').forEach(cb => {
        cb.addEventListener('change', () => calendar.refetchEvents());
    });

    const toggleAllPartnersBtn = document.getElementById('toggleAllPartners');
    if (toggleAllPartnersBtn) {
        toggleAllPartnersBtn.addEventListener('click', function () {
            const checkboxes = document.querySelectorAll('.partner-filter');
            const allChecked = [...checkboxes].every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
            calendar.refetchEvents();
        });
    }

    // ─── Helper Functions ──────────────────────────────────────

    function apiFetch(endpoint, method = 'GET', body = null) {
        const opts = {
            method,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken,
            },
        };
        if (body && method !== 'GET') {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        return fetch(config.apiBaseUrl + endpoint, opts)
            .then(r => {
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                if (r.status === 204) return null;
                return r.json();
            });
    }

    function loadPartnerData(partnerId) {
        // Load contacts for participant picker
        apiFetch(`/partners/${partnerId}/contacts`)
            .then(contacts => {
                f.participants.innerHTML = '';
                contacts.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = `${c.first_name} ${c.last_name}`;
                    f.participants.appendChild(opt);
                });
                document.getElementById('participantSection').style.display = '';
            });

        // In portfolio mode, also load opps/projects for the selected partner
        if (config.mode === 'portfolio') {
            // Fetch partner opps and projects via separate lightweight queries
            // For now, just show the link section — user can type IDs or we keep it simple
            document.getElementById('linkSection').style.display = '';
            f.opportunity.innerHTML = '<option value="">None</option>';
            f.project.innerHTML = '<option value="">None</option>';
        } else {
            // Partner mode: populate from config
            document.getElementById('linkSection').style.display = '';
            f.opportunity.innerHTML = '<option value="">None</option>';
            config.opportunities.forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.id;
                opt.textContent = o.name;
                f.opportunity.appendChild(opt);
            });
            f.project.innerHTML = '<option value="">None</option>';
            config.projects.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name;
                f.project.appendChild(opt);
            });
        }
    }

    function openCreateModal(start, end, allDay) {
        eventForm.reset();
        f.id.value = '';
        f.parentId.value = '';
        f.start.value = formatDateForInput(start);
        if (end) f.end.value = formatDateForInput(end);
        f.allDay.checked = allDay;
        f.status.value = 'scheduled';
        f.recurrence.value = 'none';

        if (config.mode === 'partner') {
            f.partner.value = config.partnerId;
        } else {
            f.partner.value = '';
        }

        document.getElementById('eventModalTitle').textContent = 'New Event';
        document.getElementById('deleteEvent').style.display = 'none';
        document.getElementById('outcomeSection').style.display = 'none';
        document.getElementById('recurrenceEndSection').style.display = 'none';

        if (config.mode === 'partner') {
            document.getElementById('linkSection').style.display = '';
            document.getElementById('participantSection').style.display = '';
        } else {
            document.getElementById('linkSection').style.display = 'none';
            document.getElementById('participantSection').style.display = 'none';
        }

        eventModal.show();
    }

    function openEditModal(eventId) {
        apiFetch(`/events/${eventId}`)
            .then(data => {
                f.id.value = data.id;
                f.parentId.value = data.parent_event_id || '';
                f.title.value = data.title;
                f.partner.value = data.partner_id;
                f.type.value = data.type;
                f.start.value = formatDateForInput(new Date(data.start_at));
                f.end.value = data.end_at ? formatDateForInput(new Date(data.end_at)) : '';
                f.allDay.checked = data.all_day;
                f.status.value = data.status;
                f.location.value = data.location || '';
                f.description.value = data.description || '';
                f.outcome.value = data.outcome || '';
                f.nextSteps.value = data.next_steps || '';
                f.recurrence.value = data.recurrence_type || 'none';
                f.recurrenceEnd.value = data.recurrence_end || '';

                // Show/hide sections
                document.getElementById('outcomeSection').style.display =
                    (data.status === 'completed') ? '' : 'none';
                document.getElementById('recurrenceEndSection').style.display =
                    (data.recurrence_type !== 'none') ? '' : 'none';

                // Load partner data then set selections
                loadPartnerData(data.partner_id);
                setTimeout(() => {
                    f.opportunity.value = data.opportunity_id || '';
                    f.project.value = data.project_id || '';
                    // Select participants
                    if (data.participants) {
                        const ids = data.participants.map(p => String(p.id));
                        [...f.participants.options].forEach(opt => {
                            opt.selected = ids.includes(opt.value);
                        });
                    }
                }, 300);

                document.getElementById('eventModalTitle').textContent = 'Edit Event';
                document.getElementById('deleteEvent').style.display = '';
                eventModal.show();
            })
            .catch(err => showToast('Error loading event: ' + err.message, 'danger'));
    }

    function quickUpdateEvent(fcEvent) {
        const realId = fcEvent.extendedProps.event_id || fcEvent.id;
        apiFetch(`/events/${realId}`, 'PUT', {
            start_at: fcEvent.start.toISOString(),
            end_at: fcEvent.end ? fcEvent.end.toISOString() : null,
            all_day: fcEvent.allDay,
        })
            .then(() => showToast('Event moved'))
            .catch(() => calendar.refetchEvents());
    }

    function gatherFormData() {
        const data = {
            title: f.title.value,
            partner_id: config.mode === 'partner' ? config.partnerId : parseInt(f.partner.value),
            type: f.type.value,
            start_at: new Date(f.start.value).toISOString(),
            end_at: f.end.value ? new Date(f.end.value).toISOString() : null,
            all_day: f.allDay.checked,
            status: f.status.value,
            location: f.location.value || null,
            description: f.description.value || null,
            recurrence_type: f.recurrence.value,
            recurrence_end: f.recurrenceEnd.value || null,
        };

        if (f.opportunity.value) data.opportunity_id = parseInt(f.opportunity.value);
        if (f.project.value) data.project_id = parseInt(f.project.value);

        // Participants
        const selectedParticipants = [...f.participants.selectedOptions].map(o => parseInt(o.value));
        if (selectedParticipants.length > 0) {
            data.participant_ids = selectedParticipants;
        }

        // Outcome fields
        if (f.status.value === 'completed') {
            data.outcome = f.outcome.value || null;
            data.next_steps = f.nextSteps.value || null;
        }

        return data;
    }

    function applyTypeFilter() {
        const checked = [...document.querySelectorAll('.type-filter:checked')].map(cb => cb.value);
        const total = document.querySelectorAll('.type-filter').length;
        if (checked.length === total || checked.length === 0) return; // show all
        calendar.getEvents().forEach(event => {
            const type = event.extendedProps.type;
            event.setProp('display', checked.includes(type) ? 'auto' : 'none');
        });
    }

    function updateUpcomingList(events) {
        const listEl = document.getElementById('upcoming-events-list');
        if (!listEl) return;

        const now = new Date();
        const upcoming = events
            .filter(e => new Date(e.start) >= now && (e.extendedProps?.status !== 'cancelled'))
            .sort((a, b) => new Date(a.start) - new Date(b.start))
            .slice(0, 5);

        if (upcoming.length === 0) {
            listEl.innerHTML = '<p class="text-muted small mb-0">No upcoming events</p>';
            return;
        }

        listEl.innerHTML = upcoming.map(e => {
            const date = new Date(e.start);
            const color = e.backgroundColor || '#868e96';
            const typeLabel = e.extendedProps?.type_label || '';
            return `
                <div class="d-flex align-items-start mb-2 pb-2 border-bottom">
                    <span class="d-inline-block rounded-circle me-2 mt-1 flex-shrink-0" style="width:8px;height:8px;background:${color}"></span>
                    <div class="flex-grow-1">
                        <div class="fw-500 fs-sm">${e.title}</div>
                        <div class="fs-xs text-muted">
                            ${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                            ${!e.allDay ? ' at ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }) : ''}
                            ${typeLabel ? ' &bull; ' + typeLabel : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function updateEnablementSidebar(allSessions, registeredSessions) {
        const listEl = document.getElementById('enablement-sessions-list');
        if (!listEl) return;

        const now = new Date();
        const registeredIds = registeredSessions.map(s =>
            parseInt(s.extendedProps?.session_id || s.id)
        );

        // Upcoming sessions from all published events
        const upcoming = allSessions
            .filter(s => new Date(s.start) >= now && s.extendedProps?.status !== 'cancelled')
            .sort((a, b) => new Date(a.start) - new Date(b.start))
            .slice(0, 10);

        if (upcoming.length === 0) {
            listEl.innerHTML = '<p class="text-muted small mb-0">No upcoming enablement sessions</p>';
            return;
        }

        const eColors = config.enablementTypeColors || {};

        listEl.innerHTML = upcoming.map(s => {
            const sid = parseInt(s.extendedProps?.session_id || s.id);
            const isRegistered = registeredIds.indexOf(sid) !== -1;
            const date = new Date(s.start);
            const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            const color = eColors[s.extendedProps?.type] || '#868e96';
            const typeLabel = s.extendedProps?.type_label || '';
            const regBadge = isRegistered
                ? '<span class="badge bg-success ms-1" style="font-size:0.65rem">Registered</span>'
                : '';
            const title = s.title || s.extendedProps?.event_title || '';

            return `
                <div class="d-flex align-items-start mb-2 pb-2 border-bottom">
                    <span class="d-inline-block rounded-circle me-2 mt-1 flex-shrink-0" style="width:8px;height:8px;background:${color}"></span>
                    <div class="flex-grow-1">
                        <div class="fw-500 fs-sm">${escapeHtml(title)}${regBadge}</div>
                        <div class="fs-xs text-muted">
                            ${dateStr} &bull; ${escapeHtml(typeLabel)}
                            ${s.extendedProps?.location ? ' &bull; ' + escapeHtml(s.extendedProps.location) : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function formatDateForInput(date) {
        if (!date) return '';
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    function applyBootstrapStyling() {
        document.querySelectorAll('.fc-button-group').forEach(el => {
            el.classList.remove('fc-button-group');
            el.classList.add('btn-group');
        });
        document.querySelectorAll('.fc-button').forEach(btn => {
            btn.classList.remove('fc-button');
            btn.classList.add('btn', 'btn-outline-default', 'btn-sm');
        });
        document.querySelectorAll('.fc-button-primary').forEach(btn => {
            btn.classList.remove('fc-button-primary');
        });
        const activeBtn = document.querySelector('.fc-button-active');
        if (activeBtn) {
            activeBtn.classList.remove('fc-button-active');
            activeBtn.classList.add('active');
        }
        ['fc-prev-button', 'fc-next-button', 'fc-today-button'].forEach(cls => {
            const btn = document.querySelector('.' + cls);
            if (btn) btn.className = 'btn btn-outline-default btn-sm';
        });
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const id = 'toast-' + Date.now();
        const bgClass = type === 'danger' ? 'bg-danger' : 'bg-success';
        container.insertAdjacentHTML('beforeend', `
            <div id="${id}" class="toast align-items-center text-white ${bgClass} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        const toastEl = document.getElementById(id);
        const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }
});
