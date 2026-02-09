<div class="modal fade" id="nominationModal" tabindex="-1" aria-labelledby="nominationTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nominationTitle">Nominate Contacts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="nominationForm">
                <div class="modal-body">
                    <input type="hidden" id="nominationSessionId">

                    <div class="mb-3">
                        <label for="nominationPartner" class="form-label">Partner <span class="text-danger">*</span></label>
                        <select class="form-select" id="nominationPartner" required>
                            <option value="">Select Partner...</option>
                        </select>
                    </div>

                    <div class="mb-3" id="contactsContainer" style="display:none;">
                        <label class="form-label">Select Contacts <span class="text-danger">*</span></label>
                        <div id="contactCheckboxes" class="border rounded p-2" style="max-height: 250px; overflow-y: auto;">
                            <p class="text-muted mb-0">Select a partner first...</p>
                        </div>
                        <div class="form-text">Select one or more contacts to nominate for this session.</div>
                    </div>

                    <div id="nominationLoading" style="display:none;" class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span class="ms-2 text-muted">Loading contacts...</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitNomination" disabled>
                        <i class="fal fa-user-plus me-1"></i> Nominate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
