<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark bg-opacity-50 shadow-5 translucent-dark">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title text-white" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn btn-system btn-system-light ms-auto" data-bs-dismiss="modal" aria-label="Close">
                    <svg class="sa-icon sa-icon-2x">
                        <use href="/img/sprite.svg#x"></use>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="deleteEventId">
                <div class="alert alert-danger bg-danger border-danger text-light border-opacity-50 bg-opacity-10 mb-0">
                    Are you sure you want to delete <span class="fw-600 text-danger" id="deleteEventTitle">this event</span>?
                    <br>This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Yes, delete</button>
            </div>
        </div>
    </div>
</div>
