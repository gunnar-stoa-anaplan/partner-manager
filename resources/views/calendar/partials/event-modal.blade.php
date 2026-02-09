<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="eventForm">
                <div class="modal-body">
                    <input type="hidden" id="eventId">
                    <input type="hidden" id="eventParentId">

                    <div class="mb-3">
                        <label for="eventTitle" class="form-label">Event Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="eventTitle" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="eventPartner" class="form-label">Partner <span class="text-danger">*</span></label>
                            <select class="form-select" id="eventPartner" required>
                                <option value="">Select Partner...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="eventType" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="eventType" required>
                                <option value="">Select Type...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="eventStart" class="form-label">Start <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="eventStart" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for="eventEnd" class="form-label">End</label>
                            <input type="datetime-local" class="form-control" id="eventEnd">
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="eventAllDay">
                                <label class="form-check-label" for="eventAllDay">All Day</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="eventStatus" class="form-label">Status</label>
                            <select class="form-select" id="eventStatus">
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="eventLocation" class="form-label">Location</label>
                            <input type="text" class="form-control" id="eventLocation">
                        </div>
                    </div>

                    <div class="row" id="linkSection" style="display:none;">
                        <div class="col-md-6 mb-3">
                            <label for="eventOpportunity" class="form-label">Link to Opportunity</label>
                            <select class="form-select" id="eventOpportunity">
                                <option value="">None</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="eventProject" class="form-label">Link to Project</label>
                            <select class="form-select" id="eventProject">
                                <option value="">None</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3" id="participantSection" style="display:none;">
                        <label for="eventParticipants" class="form-label">Participants (Contacts)</label>
                        <select class="form-select" id="eventParticipants" multiple size="4">
                        </select>
                        <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple contacts</small>
                    </div>

                    <div class="mb-3">
                        <label for="eventDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="eventDescription" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="eventRecurrence" class="form-label">Recurrence</label>
                            <select class="form-select" id="eventRecurrence">
                                <option value="none">None (one-time)</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="biweekly">Biweekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="recurrenceEndSection" style="display:none;">
                            <label for="eventRecurrenceEnd" class="form-label">Repeat Until</label>
                            <input type="date" class="form-control" id="eventRecurrenceEnd">
                        </div>
                    </div>

                    <div id="outcomeSection" style="display:none;">
                        <hr>
                        <h6 class="text-muted mb-3"><i class="fal fa-clipboard-check me-1"></i> Outcome Tracking</h6>
                        <div class="mb-3">
                            <label for="eventOutcome" class="form-label">Outcome / Notes</label>
                            <textarea class="form-control" id="eventOutcome" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="eventNextSteps" class="form-label">Next Steps</label>
                            <textarea class="form-control" id="eventNextSteps" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger me-auto" id="deleteEvent" style="display:none;">
                        <i class="fal fa-trash-alt me-1"></i> Delete
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fal fa-save me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
