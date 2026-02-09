<div class="modal fade" id="sessionDetailModal" tabindex="-1" aria-labelledby="sessionDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionDetailTitle">Session Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="sessionDetailId">

                {{-- Event Info --}}
                <div class="row mb-3">
                    <div class="col-md-8">
                        <h6 class="text-muted mb-1">Event</h6>
                        <h5 id="sessionEventTitle" class="mb-0"></h5>
                    </div>
                    <div class="col-md-4 text-end">
                        <span id="sessionTypeBadge" class="badge"></span>
                        <span id="sessionStatusBadge" class="badge ms-1"></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Date & Time</small>
                        <strong id="sessionDateTime"></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Location</small>
                        <strong id="sessionLocation"></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Instructor</small>
                        <strong id="sessionInstructor"></strong>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Category</small>
                        <strong id="sessionCategory"></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Target Audience</small>
                        <strong id="sessionAudience"></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Spots Remaining</small>
                        <strong id="sessionSpots"></strong>
                    </div>
                </div>

                <div id="sessionMeetingUrlRow" class="mb-3" style="display:none;">
                    <small class="text-muted d-block">Meeting Link</small>
                    <a id="sessionMeetingUrl" href="#" target="_blank"></a>
                </div>

                <div id="sessionDescriptionRow" class="mb-3" style="display:none;">
                    <small class="text-muted d-block">Description</small>
                    <p id="sessionDescription" class="mb-0"></p>
                </div>

                <div id="sessionObjectivesRow" class="mb-3" style="display:none;">
                    <small class="text-muted d-block">Learning Objectives</small>
                    <p id="sessionObjectives" class="mb-0"></p>
                </div>

                <div id="sessionPrerequisitesRow" class="mb-3" style="display:none;">
                    <small class="text-muted d-block">Prerequisites</small>
                    <p id="sessionPrerequisites" class="mb-0"></p>
                </div>

                {{-- My Registrations --}}
                <hr>
                <h6 class="text-muted mb-2"><i class="fal fa-users me-1"></i> My Partner Registrations</h6>
                <div id="sessionRegistrationsList">
                    <p class="text-muted">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="openNominateBtn" style="display:none;">
                    <i class="fal fa-user-plus me-1"></i> Nominate Contacts
                </button>
            </div>
        </div>
    </div>
</div>
