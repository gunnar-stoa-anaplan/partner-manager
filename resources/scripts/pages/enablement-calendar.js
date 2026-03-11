/*!
 * Enablement Calendar - FullCalendar for Enablement Sessions
 * Handles three modes: admin, psm, partner
 * Partner Manager
 */

import { Calendar } from '@fullcalendar/core';
import interactionPlugin, { Draggable } from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import dayGridPlugin from '@fullcalendar/daygrid';

function initEnablementCalendar() {
    var config = window.enablementCalendarConfig;
    if (!config) return;

    // Support embedded mode with custom element IDs (e.g. SoB page)
    var ids = {
        calendar: config.calendarEl || 'enablement-calendar',
        draggable: config.draggableEl || 'draggable-sessions',
        typeFilterClass: config.typeFilterClass || 'type-filter',
        toggleAll: config.toggleAllId || 'toggleAllTypes',
        modal: config.modalId || 'contactAssignModal',
        assignSessionId: config.assignSessionIdEl || 'assignSessionId',
        assignSessionTitle: config.assignSessionTitleEl || 'assignSessionTitle',
        assignTypeBadge: config.assignTypeBadgeEl || 'assignTypeBadge',
        assignSessionDate: config.assignSessionDateEl || 'assignSessionDate',
        assignSessionLocation: config.assignSessionLocationEl || 'assignSessionLocation',
        assignSessionInstructor: config.assignSessionInstructorEl || 'assignSessionInstructor',
        assignedContactsList: config.assignedContactsListEl || 'assignedContactsList',
        assignContactCbClass: config.assignContactCbClass || 'assign-contact-cb',
        assignContactsBtn: config.assignContactsBtnEl || 'assignContactsBtn',
        removePartnerBtn: config.removePartnerBtnEl || 'removePartnerBtn',
        contactRowPrefix: config.contactRowPrefix || 'contact-row-',
    };

    var calendarEl = document.getElementById(ids.calendar);
    if (!calendarEl) return;

    // Prevent double-init
    if (calendarEl.dataset.calendarInit) return;
    calendarEl.dataset.calendarInit = '1';

    var mode = config.mode; // 'admin', 'psm', 'partner'

    // ─── Toast Helper ───────────────────────────────────────────
    function showToast(message, type) {
        type = type || 'success';
        var container = document.getElementById('toast-container');
        if (!container) return;
        var bgClass = type === 'error' ? 'bg-danger' : type === 'warning' ? 'bg-warning text-dark' : 'bg-success';
        var html = '<div class="toast align-items-center text-white ' + bgClass + ' border-0 show" role="alert">' +
            '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div></div>';
        container.insertAdjacentHTML('beforeend', html);
        var toastEl = container.lastElementChild;
        setTimeout(function () { toastEl.remove(); }, 4000);
    }

    // ─── Type Filter ────────────────────────────────────────────
    var activeTypes = Object.keys(config.eventTypes || {});

    var toggleAllBtn = document.getElementById(ids.toggleAll);
    if (toggleAllBtn) {
        toggleAllBtn.addEventListener('click', function () {
            var checkboxes = document.querySelectorAll('.' + ids.typeFilterClass);
            var allChecked = Array.from(checkboxes).every(function (cb) { return cb.checked; });
            checkboxes.forEach(function (cb) { cb.checked = !allChecked; });
            updateActiveTypes();
            calendar.refetchEvents();
        });
    }

    document.querySelectorAll('.' + ids.typeFilterClass).forEach(function (cb) {
        cb.addEventListener('change', function () {
            updateActiveTypes();
            calendar.refetchEvents();
        });
    });

    function updateActiveTypes() {
        activeTypes = [];
        document.querySelectorAll('.' + ids.typeFilterClass + ':checked').forEach(function (cb) {
            activeTypes.push(cb.value);
        });
        filterSidebarEvents();
    }

    function filterSidebarEvents() {
        // Filter both admin sidebar (#draggable-events) and partner sidebar
        ['draggable-events', ids.draggable].forEach(function (id) {
            var container = document.getElementById(id);
            if (!container) return;
            container.querySelectorAll('.fc-event').forEach(function (el) {
                var type = el.getAttribute('data-event-type');
                el.style.display = activeTypes.indexOf(type) !== -1 ? '' : 'none';
            });
        });
    }

    // ─── Geo Filter (Admin) ──────────────────────────────────────
    var activeGeos = Object.keys(config.geographies || {}).concat(['_none']);

    var toggleAllGeosBtn = document.getElementById('toggleAllGeos');
    if (toggleAllGeosBtn) {
        toggleAllGeosBtn.addEventListener('click', function () {
            var checkboxes = document.querySelectorAll('.geo-filter');
            var allChecked = Array.from(checkboxes).every(function (cb) { return cb.checked; });
            checkboxes.forEach(function (cb) { cb.checked = !allChecked; });
            updateActiveGeos();
            calendar.refetchEvents();
        });
    }

    document.querySelectorAll('.geo-filter').forEach(function (cb) {
        cb.addEventListener('change', function () {
            updateActiveGeos();
            calendar.refetchEvents();
        });
    });

    function updateActiveGeos() {
        activeGeos = [];
        document.querySelectorAll('.geo-filter:checked').forEach(function (cb) {
            activeGeos.push(cb.value);
        });
    }

    // ─── Admin: Initialize Draggable Published Events ───────────
    if (mode === 'admin') {
        var draggableContainer = document.getElementById('draggable-events');
        if (draggableContainer) {
            new Draggable(draggableContainer, {
                itemSelector: '.fc-event',
                eventData: function (eventEl) {
                    var durationMin = parseInt(eventEl.getAttribute('data-duration')) || 60;
                    return {
                        title: eventEl.querySelector('.fw-500').textContent.trim(),
                        duration: { minutes: durationMin },
                        backgroundColor: eventEl.style.backgroundColor,
                        borderColor: eventEl.style.backgroundColor,
                        create: true,
                        extendedProps: {
                            enablement_event_id: parseInt(eventEl.getAttribute('data-event-id')),
                            type: eventEl.getAttribute('data-event-type'),
                        }
                    };
                }
            });
        }
    }

    // ─── Partner: Track Registered Session IDs ──────────────────
    var partnerRegisteredSessionIds = [];
    var partnerDraggable = null;

    // ─── Calendar Options ───────────────────────────────────────
    var calendarOptions = {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        height: 'auto',
        navLinks: true,
        editable: mode === 'admin',
        droppable: mode === 'admin' || mode === 'partner',
        selectable: false,

        events: function (info, successCallback, failureCallback) {
            if (mode === 'partner') {
                // Partner mode: fetch only this partner's registered sessions
                var params = new URLSearchParams({
                    start: info.startStr,
                    end: info.endStr,
                });

                fetch(config.apiBaseUrl + '/enablement/partners/' + config.partnerId + '/sessions?' + params.toString(), {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrfToken }
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    partnerRegisteredSessionIds = data.map(function (ev) {
                        return parseInt(ev.extendedProps.session_id || ev.id);
                    });

                    var filtered = data.filter(function (ev) {
                        return activeTypes.indexOf(ev.extendedProps.type) !== -1;
                    });
                    successCallback(filtered);
                    loadAvailableSessions();
                })
                .catch(function (err) { failureCallback(err); });
            } else {
                // Admin / PSM: fetch all sessions
                var params2 = new URLSearchParams({
                    start: info.startStr,
                    end: info.endStr,
                });

                fetch(config.apiBaseUrl + '/enablement/sessions?' + params2.toString(), {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrfToken }
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var filtered = data.filter(function (ev) {
                        if (activeTypes.indexOf(ev.extendedProps.type) === -1) return false;
                        if (mode === 'admin' && activeGeos.length > 0) {
                            var geo = ev.extendedProps.geography || '_none';
                            if (activeGeos.indexOf(geo) === -1) return false;
                        }
                        return true;
                    });
                    successCallback(filtered);
                    if (mode === 'psm') {
                        updateUpcomingSessions(data);
                    }
                })
                .catch(function (err) { failureCallback(err); });
            }
        },

        eventClick: function (info) {
            info.jsEvent.preventDefault();
            var sessionId = info.event.extendedProps.session_id || info.event.id;

            if (mode === 'admin') {
                window.location.href = config.sessionDetailBaseUrl + '/' + sessionId + '/registrations';
            } else if (mode === 'partner') {
                openContactAssignModal(info.event);
            } else {
                openSessionDetail(sessionId);
            }
        },

        eventDidMount: function (info) {
            var props = info.event.extendedProps;
            var tooltip = props.type_label || '';
            if (props.location) tooltip += ' | ' + props.location;
            if (props.instructor) tooltip += ' | ' + props.instructor;
            info.el.title = tooltip;
        },
    };

    // ─── Admin: Geo Selection Modal Helper ──────────────────────
    var geoSelectModalEl = document.getElementById('geoSelectModal');
    var geoSelectModal = geoSelectModalEl ? new window.bootstrap.Modal(geoSelectModalEl) : null;
    var pendingEventInfo = null;

    function showGeoSelectAndCreate(info) {
        pendingEventInfo = info;
        var geoSelect = document.getElementById('geoSelectValue');
        if (geoSelect) geoSelect.value = '';
        if (geoSelectModal) geoSelectModal.show();
    }

    var geoConfirmBtn = document.getElementById('geoSelectConfirm');
    if (geoConfirmBtn) {
        geoConfirmBtn.addEventListener('click', function () {
            if (!pendingEventInfo) return;
            var info = pendingEventInfo;
            pendingEventInfo = null;

            var selectedGeo = document.getElementById('geoSelectValue').value || null;
            if (geoSelectModal) geoSelectModal.hide();

            var props = info.event.extendedProps;
            var startStr = info.event.start.toISOString();
            var endStr = info.event.end ? info.event.end.toISOString() : null;

            fetch(config.apiBaseUrl + '/enablement/sessions', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                body: JSON.stringify({
                    enablement_event_id: props.enablement_event_id,
                    start_at: startStr,
                    end_at: endStr,
                    all_day: info.event.allDay,
                    geography: selectedGeo,
                }),
            })
            .then(function (r) {
                if (!r.ok) throw new Error('Failed');
                return r.json();
            })
            .then(function () {
                info.event.remove();
                calendar.refetchEvents();
                showToast('Session scheduled successfully.');
            })
            .catch(function () {
                info.event.remove();
                showToast('Failed to create session.', 'error');
            });
        });
    }

    // Cancel geo modal -> remove the placeholder event
    if (geoSelectModalEl) {
        geoSelectModalEl.addEventListener('hidden.bs.modal', function () {
            if (pendingEventInfo) {
                pendingEventInfo.event.remove();
                pendingEventInfo = null;
            }
        });
    }

    // ─── Admin: Drag-Drop Handlers ──────────────────────────────
    if (mode === 'admin') {
        calendarOptions.eventReceive = function (info) {
            showGeoSelectAndCreate(info);
        };

        calendarOptions.eventDrop = function (info) {
            var sessionId = info.event.extendedProps.session_id || info.event.id;
            fetch(config.apiBaseUrl + '/enablement/sessions/' + sessionId, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                body: JSON.stringify({
                    start_at: info.event.start.toISOString(),
                    end_at: info.event.end ? info.event.end.toISOString() : null,
                    all_day: info.event.allDay,
                }),
            })
            .then(function (r) {
                if (!r.ok) throw new Error('Failed');
                return r.json();
            })
            .then(function () {
                showToast('Session moved.');
            })
            .catch(function () {
                info.revert();
                showToast('Failed to update session.', 'error');
            });
        };

        calendarOptions.eventResize = function (info) {
            var sessionId = info.event.extendedProps.session_id || info.event.id;
            fetch(config.apiBaseUrl + '/enablement/sessions/' + sessionId, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                body: JSON.stringify({
                    start_at: info.event.start.toISOString(),
                    end_at: info.event.end ? info.event.end.toISOString() : null,
                    all_day: info.event.allDay,
                }),
            })
            .then(function (r) {
                if (!r.ok) throw new Error('Failed');
                return r.json();
            })
            .then(function () {
                showToast('Session resized.');
            })
            .catch(function () {
                info.revert();
                showToast('Failed to update session.', 'error');
            });
        };
    }

    // ─── Partner: Drag-Drop Handler ─────────────────────────────
    if (mode === 'partner') {
        calendarOptions.eventReceive = function (info) {
            var sessionId = info.event.extendedProps.session_id;

            fetch(config.apiBaseUrl + '/enablement/sessions/' + sessionId + '/register-partner', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                body: JSON.stringify({
                    partner_id: config.partnerId,
                }),
            })
            .then(function (r) {
                return r.json().then(function (d) { return { ok: r.ok, data: d }; });
            })
            .then(function (result) {
                info.event.remove();
                if (result.ok) {
                    calendar.refetchEvents();
                    showToast('Partner registered for session.');
                } else {
                    var isWarning = result.data.message && result.data.message.indexOf('already') !== -1;
                    showToast(result.data.message || 'Failed to register.', isWarning ? 'warning' : 'error');
                }
            })
            .catch(function () {
                info.event.remove();
                showToast('Failed to register partner.', 'error');
            });
        };
    }

    // ─── Create Calendar ────────────────────────────────────────
    var calendar = new Calendar(calendarEl, calendarOptions);
    calendar.render();

    // ─── Partner: Load Available Sessions for Sidebar ────────────
    function loadAvailableSessions() {
        if (mode !== 'partner') return;

        var container = document.getElementById(ids.draggable);
        if (!container) return;

        var now = new Date();
        var future = new Date();
        future.setFullYear(future.getFullYear() + 1);

        var params = new URLSearchParams({
            start: now.toISOString(),
            end: future.toISOString(),
        });

        fetch(config.apiBaseUrl + '/enablement/sessions?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrfToken }
        })
        .then(function (r) { return r.json(); })
        .then(function (allSessions) {
            var available = allSessions.filter(function (s) {
                var sid = parseInt(s.extendedProps.session_id || s.id);
                return partnerRegisteredSessionIds.indexOf(sid) === -1;
            });

            container.innerHTML = '';

            if (available.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-2"><small>No available sessions</small></div>';
                return;
            }

            available.forEach(function (s) {
                var typeColor = config.typeColors[s.extendedProps.type] || '#868e96';
                var dateStr = new Date(s.start).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                var div = document.createElement('div');
                div.className = 'fc-event';
                div.setAttribute('data-session-id', s.extendedProps.session_id || s.id);
                div.setAttribute('data-event-type', s.extendedProps.type || '');
                div.style.backgroundColor = typeColor;
                div.innerHTML = '<div class="fw-500">' + escapeHtml(s.title) + '</div>' +
                    '<div class="event-meta">' + dateStr + ' &middot; ' + escapeHtml(s.extendedProps.type_label || '') + '</div>';
                container.appendChild(div);
            });

            initPartnerDraggable();
            filterSidebarEvents();
        })
        .catch(function () {
            container.innerHTML = '<div class="text-center text-danger py-2"><small>Failed to load sessions</small></div>';
        });
    }

    function initPartnerDraggable() {
        var container = document.getElementById(ids.draggable);
        if (!container) return;

        if (partnerDraggable) {
            partnerDraggable.destroy();
        }

        partnerDraggable = new Draggable(container, {
            itemSelector: '.fc-event',
            eventData: function (eventEl) {
                return {
                    title: eventEl.querySelector('.fw-500').textContent.trim(),
                    backgroundColor: eventEl.style.backgroundColor,
                    borderColor: eventEl.style.backgroundColor,
                    create: true,
                    extendedProps: {
                        session_id: parseInt(eventEl.getAttribute('data-session-id')),
                        type: eventEl.getAttribute('data-event-type'),
                    }
                };
            }
        });
    }

    // ─── Upcoming Sessions Sidebar (PSM global only) ────────────
    function updateUpcomingSessions(allSessions) {
        var list = document.getElementById('upcoming-sessions-list');
        if (!list) return;

        var now = new Date();
        var upcoming = allSessions
            .filter(function (s) { return new Date(s.start) >= now && s.extendedProps.status !== 'cancelled'; })
            .sort(function (a, b) { return new Date(a.start) - new Date(b.start); })
            .slice(0, 8);

        if (upcoming.length === 0) {
            list.innerHTML = '<div class="list-group-item text-center text-muted py-3"><small>No upcoming sessions</small></div>';
            return;
        }

        list.innerHTML = '';
        upcoming.forEach(function (s) {
            var date = new Date(s.start);
            var dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            var typeColor = config.typeColors[s.extendedProps.type] || '#868e96';
            var spots = s.extendedProps.spots_remaining;
            var spotsHtml = spots !== null ? '<span class="badge bg-secondary">' + spots + ' spots</span>' : '';

            var item = '<a href="#" class="list-group-item list-group-item-action upcoming-session" data-session-id="' + s.extendedProps.session_id + '">' +
                '<div class="d-flex justify-content-between align-items-start">' +
                '<div>' +
                '<small class="text-muted">' + dateStr + '</small>' +
                '<div class="fw-500" style="font-size: 0.85rem;">' + escapeHtml(s.title) + '</div>' +
                '<small style="color:' + typeColor + '">' + escapeHtml(s.extendedProps.type_label || '') + '</small>' +
                '</div>' +
                spotsHtml +
                '</div></a>';
            list.insertAdjacentHTML('beforeend', item);
        });

        list.querySelectorAll('.upcoming-session').forEach(function (item) {
            item.addEventListener('click', function (e) {
                e.preventDefault();
                var sessionId = this.getAttribute('data-session-id');
                openSessionDetail(sessionId);
            });
        });
    }

    // ─── Session Detail Modal (PSM global only) ─────────────────
    var sessionDetailModal = null;
    var nominationModal = null;

    if (mode === 'psm') {
        var detailModalEl = document.getElementById('sessionDetailModal');
        var nominationModalEl = document.getElementById('nominationModal');
        if (detailModalEl) sessionDetailModal = new window.bootstrap.Modal(detailModalEl);
        if (nominationModalEl) nominationModal = new window.bootstrap.Modal(nominationModalEl);
    }

    function openSessionDetail(sessionId) {
        if (!sessionDetailModal) return;

        fetch(config.apiBaseUrl + '/enablement/sessions/' + sessionId, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrfToken }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            populateDetailModal(data);
            sessionDetailModal.show();
        })
        .catch(function () {
            showToast('Failed to load session details.', 'error');
        });
    }

    function populateDetailModal(data) {
        var el = function (id) { return document.getElementById(id); };

        el('sessionDetailId').value = data.id;
        el('sessionEventTitle').textContent = data.event_title || '';

        var typeBadge = el('sessionTypeBadge');
        typeBadge.textContent = data.event_type_label || '';
        typeBadge.style.backgroundColor = config.typeColors[data.event_type] || '#868e96';

        var statusBadge = el('sessionStatusBadge');
        statusBadge.textContent = data.status_label || '';
        statusBadge.className = 'badge ms-1 bg-' + getStatusColor(data.status);

        var startDate = new Date(data.start_at);
        var dateStr = startDate.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
        if (!data.all_day) {
            dateStr += ' ' + startDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        }
        el('sessionDateTime').textContent = dateStr;

        el('sessionLocation').textContent = data.location || 'TBD';
        el('sessionInstructor').textContent = data.instructor || '\u2014';
        el('sessionCategory').textContent = data.category || '\u2014';
        el('sessionAudience').textContent = data.target_audience || '\u2014';
        el('sessionSpots').textContent = data.spots_remaining !== null ? data.spots_remaining : 'Unlimited';

        // Meeting URL
        var meetingRow = el('sessionMeetingUrlRow');
        if (data.meeting_url) {
            meetingRow.style.display = 'block';
            var link = el('sessionMeetingUrl');
            link.href = data.meeting_url;
            link.textContent = 'Join Meeting';
        } else {
            meetingRow.style.display = 'none';
        }

        // Optional text sections
        toggleSection('sessionDescriptionRow', 'sessionDescription', data.description);
        toggleSection('sessionObjectivesRow', 'sessionObjectives', data.objectives);
        toggleSection('sessionPrerequisitesRow', 'sessionPrerequisites', data.prerequisites);

        // Registrations
        var regList = el('sessionRegistrationsList');
        if (data.registrations && data.registrations.length > 0) {
            var html = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead><tr>' +
                '<th>Contact</th><th>Partner</th><th>Status</th><th></th>' +
                '</tr></thead><tbody>';
            data.registrations.forEach(function (r) {
                var removeBtn = '';
                if (r.status === 'nominated' || r.status === 'registered') {
                    removeBtn = '<button class="btn btn-xs btn-outline-danger remove-reg" data-id="' + r.id + '" title="Remove"><svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#x"></use></svg></button>';
                }
                html += '<tr>' +
                    '<td>' + escapeHtml(r.contact_name) + '</td>' +
                    '<td>' + escapeHtml(r.partner_name) + '</td>' +
                    '<td><span class="badge bg-' + r.status_color + '">' + escapeHtml(r.status_label) + '</span></td>' +
                    '<td>' + removeBtn + '</td>' +
                    '</tr>';
            });
            html += '</tbody></table></div>';
            regList.innerHTML = html;

            regList.querySelectorAll('.remove-reg').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!confirm('Remove this nomination?')) return;
                    var regId = this.getAttribute('data-id');
                    fetch(config.apiBaseUrl + '/enablement/registrations/' + regId, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrfToken }
                    })
                    .then(function (r) { return r.json(); })
                    .then(function () {
                        showToast('Nomination removed.');
                        openSessionDetail(data.id);
                        calendar.refetchEvents();
                    })
                    .catch(function () { showToast('Failed to remove nomination.', 'error'); });
                });
            });
        } else {
            regList.innerHTML = '<p class="text-muted mb-0">No registrations from your partners yet.</p>';
        }

        // Nominate button
        var nominateBtn = el('openNominateBtn');
        if (data.registration_open) {
            nominateBtn.style.display = 'inline-block';
            nominateBtn.onclick = function () {
                sessionDetailModal.hide();
                openNominationModal(data.id);
            };
        } else {
            nominateBtn.style.display = 'none';
        }
    }

    function toggleSection(rowId, contentId, value) {
        var row = document.getElementById(rowId);
        var content = document.getElementById(contentId);
        if (row && content) {
            if (value) {
                row.style.display = 'block';
                content.textContent = value;
            } else {
                row.style.display = 'none';
            }
        }
    }

    function getStatusColor(status) {
        var colors = { scheduled: 'primary', in_progress: 'warning', completed: 'success', cancelled: 'danger' };
        return colors[status] || 'secondary';
    }

    // ─── Nomination Modal (PSM global only) ─────────────────────
    function openNominationModal(sessionId) {
        if (!nominationModal) return;

        document.getElementById('nominationSessionId').value = sessionId;

        var partnerSelect = document.getElementById('nominationPartner');
        partnerSelect.innerHTML = '<option value="">Select Partner...</option>';
        if (config.partners) {
            config.partners.forEach(function (p) {
                var opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name;
                partnerSelect.appendChild(opt);
            });
        }

        document.getElementById('contactsContainer').style.display = 'none';
        document.getElementById('contactCheckboxes').innerHTML = '<p class="text-muted mb-0">Select a partner first...</p>';
        document.getElementById('submitNomination').disabled = true;

        nominationModal.show();
    }

    // Partner select -> load contacts (PSM global)
    var partnerSelect = document.getElementById('nominationPartner');
    if (partnerSelect) {
        partnerSelect.addEventListener('change', function () {
            var partnerId = this.value;
            var container = document.getElementById('contactsContainer');
            var checkboxes = document.getElementById('contactCheckboxes');
            var loading = document.getElementById('nominationLoading');

            if (!partnerId) {
                container.style.display = 'none';
                return;
            }

            loading.style.display = 'block';
            container.style.display = 'none';

            fetch(config.apiBaseUrl + '/partners/' + partnerId + '/contacts', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrfToken }
            })
            .then(function (r) { return r.json(); })
            .then(function (contacts) {
                loading.style.display = 'none';
                container.style.display = 'block';

                if (!contacts || contacts.length === 0) {
                    checkboxes.innerHTML = '<p class="text-muted mb-0">No contacts found for this partner.</p>';
                    document.getElementById('submitNomination').disabled = true;
                    return;
                }

                checkboxes.innerHTML = '';
                contacts.forEach(function (c) {
                    var name = (c.first_name || '') + ' ' + (c.last_name || '');
                    var emailStr = c.email ? ' <small class="text-muted">(' + escapeHtml(c.email) + ')</small>' : '';
                    var div = document.createElement('div');
                    div.className = 'form-check';
                    div.innerHTML = '<input class="form-check-input contact-checkbox" type="checkbox" value="' + c.id + '" id="contact-' + c.id + '">' +
                        '<label class="form-check-label" for="contact-' + c.id + '">' + escapeHtml(name) + emailStr + '</label>';
                    checkboxes.appendChild(div);
                });

                checkboxes.querySelectorAll('.contact-checkbox').forEach(function (cb) {
                    cb.addEventListener('change', function () {
                        var anyChecked = checkboxes.querySelector('.contact-checkbox:checked');
                        document.getElementById('submitNomination').disabled = !anyChecked;
                    });
                });
            })
            .catch(function () {
                loading.style.display = 'none';
                checkboxes.innerHTML = '<p class="text-danger mb-0">Failed to load contacts.</p>';
            });
        });
    }

    // Submit nomination (PSM global)
    var nominationForm = document.getElementById('nominationForm');
    if (nominationForm) {
        nominationForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var sessionId = document.getElementById('nominationSessionId').value;
            var partnerId = document.getElementById('nominationPartner').value;
            var contactIds = [];
            document.querySelectorAll('.contact-checkbox:checked').forEach(function (cb) {
                contactIds.push(parseInt(cb.value));
            });

            if (!partnerId || contactIds.length === 0) return;

            var submitBtn = document.getElementById('submitNomination');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Nominating...';

            fetch(config.apiBaseUrl + '/enablement/sessions/' + sessionId + '/nominate', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                body: JSON.stringify({
                    partner_id: parseInt(partnerId),
                    contact_ids: contactIds,
                }),
            })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (result) {
                if (result.ok) {
                    nominationModal.hide();
                    showToast(result.data.message || 'Contacts nominated successfully.');
                    calendar.refetchEvents();
                } else {
                    showToast(result.data.message || 'Failed to nominate.', 'error');
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fal fa-user-plus me-1"></i> Nominate';
            })
            .catch(function () {
                showToast('Network error. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fal fa-user-plus me-1"></i> Nominate';
            });
        });
    }

    // ─── Contact Assignment Modal (Partner mode only) ────────────
    var contactAssignModal = null;

    if (mode === 'partner') {
        var assignModalEl = document.getElementById(ids.modal);
        if (assignModalEl) contactAssignModal = new window.bootstrap.Modal(assignModalEl);
    }

    function openContactAssignModal(fcEvent) {
        if (!contactAssignModal) return;

        var props = fcEvent.extendedProps;
        var sessionId = props.session_id || fcEvent.id;

        document.getElementById(ids.assignSessionId).value = sessionId;
        document.getElementById(ids.assignSessionTitle).textContent = fcEvent.title;

        var typeBadge = document.getElementById(ids.assignTypeBadge);
        typeBadge.textContent = props.type_label || '';
        typeBadge.style.backgroundColor = config.typeColors[props.type] || '#868e96';

        var startDate = fcEvent.start;
        var dateStr = startDate ? startDate.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) : '';
        document.getElementById(ids.assignSessionDate).textContent = dateStr;
        document.getElementById(ids.assignSessionLocation).textContent = props.location || 'TBD';
        document.getElementById(ids.assignSessionInstructor).textContent = props.instructor || '\u2014';

        // Load assigned contacts from extendedProps
        var assignedList = document.getElementById(ids.assignedContactsList);
        var partnerRegs = props.partner_registrations || [];
        var assignedContactIds = [];

        if (partnerRegs.length > 0) {
            var html = '';
            partnerRegs.forEach(function (r) {
                assignedContactIds.push(r.contact_id);
                html += '<div class="d-flex justify-content-between align-items-center mb-1" id="assigned-' + r.id + '">' +
                    '<span>' + escapeHtml(r.contact_name) + ' <span class="badge bg-' + (r.status_color || 'secondary') + '">' + escapeHtml(r.status_label || r.status) + '</span></span>' +
                    '<button class="btn btn-xs btn-outline-danger remove-assigned" data-id="' + r.id + '" title="Remove">' +
                    '<svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#x"></use></svg>' +
                    '</button>' +
                    '</div>';
            });
            assignedList.innerHTML = html;

            // Remove contact handlers
            assignedList.querySelectorAll('.remove-assigned').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!confirm('Remove this contact from the session?')) return;
                    var regId = this.getAttribute('data-id');
                    fetch(config.apiBaseUrl + '/enablement/registrations/' + regId, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrfToken }
                    })
                    .then(function (r) { return r.json(); })
                    .then(function () {
                        showToast('Contact removed.');
                        contactAssignModal.hide();
                        calendar.refetchEvents();
                    })
                    .catch(function () { showToast('Failed to remove contact.', 'error'); });
                });
            });
        } else {
            assignedList.innerHTML = '<p class="text-muted mb-0">No contacts assigned yet.</p>';
        }

        // Show/hide contact checkboxes based on what's already assigned
        document.querySelectorAll('.' + ids.assignContactCbClass).forEach(function (cb) {
            var contactId = parseInt(cb.value);
            var row = document.getElementById(ids.contactRowPrefix + cb.value);
            if (assignedContactIds.indexOf(contactId) !== -1) {
                if (row) row.style.display = 'none';
                cb.checked = false;
            } else {
                if (row) row.style.display = '';
                cb.checked = false;
            }
        });

        updateAssignBtn();
        contactAssignModal.show();
    }

    // Enable/disable assign button based on checkbox selection
    if (mode === 'partner') {
        document.querySelectorAll('.' + ids.assignContactCbClass).forEach(function (cb) {
            cb.addEventListener('change', updateAssignBtn);
        });
    }

    function updateAssignBtn() {
        var btn = document.getElementById(ids.assignContactsBtn);
        if (!btn) return;
        var anyChecked = document.querySelector('.' + ids.assignContactCbClass + ':checked');
        btn.disabled = !anyChecked;
    }

    // Assign contacts button handler
    var assignBtn = document.getElementById(ids.assignContactsBtn);
    if (assignBtn && mode === 'partner') {
        assignBtn.addEventListener('click', function () {
            var sessionId = document.getElementById(ids.assignSessionId).value;
            var contactIds = [];
            document.querySelectorAll('.' + ids.assignContactCbClass + ':checked').forEach(function (cb) {
                contactIds.push(parseInt(cb.value));
            });

            if (contactIds.length === 0) return;

            assignBtn.disabled = true;
            assignBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Assigning...';

            fetch(config.apiBaseUrl + '/enablement/sessions/' + sessionId + '/nominate', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                body: JSON.stringify({
                    partner_id: config.partnerId,
                    contact_ids: contactIds,
                }),
            })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (result) {
                if (result.ok) {
                    contactAssignModal.hide();
                    showToast(result.data.message || 'Contacts assigned.');
                    calendar.refetchEvents();
                } else {
                    showToast(result.data.message || 'Failed to assign.', 'error');
                }
                assignBtn.disabled = false;
                assignBtn.innerHTML = '<i class="fal fa-user-plus me-1"></i> Assign Selected';
            })
            .catch(function () {
                showToast('Network error.', 'error');
                assignBtn.disabled = false;
                assignBtn.innerHTML = '<i class="fal fa-user-plus me-1"></i> Assign Selected';
            });
        });
    }

    // Remove partner from session button handler
    var removeBtn = document.getElementById(ids.removePartnerBtn);
    if (removeBtn && mode === 'partner') {
        removeBtn.addEventListener('click', function () {
            if (!confirm('Remove ' + config.partnerName + ' from this session? This will also remove all contact assignments.')) return;

            var sessionId = document.getElementById(ids.assignSessionId).value;

            fetch(config.apiBaseUrl + '/enablement/sessions/' + sessionId + '/unregister-partner', {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                },
                body: JSON.stringify({
                    partner_id: config.partnerId,
                }),
            })
            .then(function (r) { return r.json(); })
            .then(function () {
                contactAssignModal.hide();
                showToast('Partner removed from session.');
                calendar.refetchEvents();
            })
            .catch(function () {
                showToast('Failed to remove partner.', 'error');
            });
        });
    }

    // ─── Utility ────────────────────────────────────────────────
    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
}

// Support both page load and AJAX tab loading (DOMContentLoaded already fired)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEnablementCalendar);
} else {
    initEnablementCalendar();
}
