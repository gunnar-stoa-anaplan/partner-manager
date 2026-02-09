@extends('layouts.vertical', ['pageTitle' => 'Full Calendar'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Full Calendar', 'pageSubTitle1' => 'Data Visualization', 'pageSubText' => 'Interactive event calendar with drag and drop'])
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-lg-8 col-xl-9">
            <div class="card">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-xl-3">

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-outline-primary btn-sm" id="manageCategoriesBtn">
                            Manage Categories
                        </button>
                        <button class="btn btn-outline-primary btn-sm" id="exportEvents">
                            Export Events
                        </button>
                        <label class="btn btn-outline-primary btn-sm mb-0" for="importEvents">
                            Import Events
                        </label>
                        <input type="file" id="importEvents" accept=".json" class="d-none">
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Draggable Events</h5>
                </div>
                <div class="card-body">
                    <div id="external-events">
                        <p class="text-muted small">Drag these events to the calendar</p>
                        <div class="external-event p-2 mb-2 rounded bg-primary text-white"
                             data-description="Regular check-in with team members" data-location="Meeting Room 1"
                             data-category="meeting">Team Standup
                        </div>
                        <div class="external-event p-2 mb-2 rounded bg-success text-white"
                             data-description="Time to breathe and recharge" data-location="Break Room">Coffee Break
                        </div>
                        <div class="external-event p-2 mb-2 rounded bg-warning text-dark"
                             data-description="One-on-one with supervisor" data-location="Manager's Office"
                             data-category="meeting">Performance Review
                        </div>
                        <div class="external-event p-2 mb-2 rounded bg-danger text-white"
                             data-description="Time blocked for focusing on deliverables"
                             data-location="Personal Workspace" data-category="task">Focus Time
                        </div>
                        <div class="external-event p-2 mb-2 rounded bg-info text-white"
                             data-description="Lunch with colleagues" data-location="Cafeteria">Lunch
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="removeAfterDrop">
                            <label class="form-check-label" for="removeAfterDrop">
                                Remove after drop
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Filter by Category</h5>
                    <button class="btn btn-xs btn-link p-0" id="toggleAllFilters">
                        All
                    </button>
                </div>
                <div class="card-body">
                    <div id="category-filters">
                        <!-- Category filters will be populated by JS -->
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Toast Container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100" id="toast-container"></div>

    <!-- Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalTitle">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="eventForm">
                    <div class="modal-body">
                        <input type="hidden" id="eventId">
                        <div class="mb-3">
                            <label for="eventTitle" class="form-label">Event Title</label>
                            <input type="text" class="form-control" id="eventTitle" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="eventStart" class="form-label">Start</label>
                                <input type="datetime-local" class="form-control" id="eventStart" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="eventEnd" class="form-label">End</label>
                                <input type="datetime-local" class="form-control" id="eventEnd">
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="eventAllDay">
                            <label class="form-check-label" for="eventAllDay">All Day</label>
                        </div>
                        <div class="mb-3">
                            <label for="eventCategory" class="form-label">Category</label>
                            <select class="form-select" id="eventCategory">
                                <!-- Categories will be populated by JS -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="eventColor" class="form-label">Color (Custom)</label>
                            <select class="form-select" id="eventColor">
                                <option value="var(--primary-500)">Primary</option>
                                <option value="var(--success-500)">Success</option>
                                <option value="var(--danger-500)">Danger</option>
                                <option value="var(--warning-500)">Warning</option>
                                <option value="var(--info-500)">Info</option>
                                <option value="var(--dark)">Dark</option>
                                <option value="var(--secondary)">Secondary</option>
                            </select>
                            <small class="form-text text-muted">Custom color will be overridden if a category is
                                selected.</small>
                        </div>
                        <div class="mb-3">
                            <label for="eventDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="eventDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="eventLocation" class="form-label">Location</label>
                            <input type="text" class="form-control" id="eventLocation">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger me-auto" id="deleteEvent" style="display: none;">
                            Delete
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark bg-opacity-50 shadow-5 translucent-dark">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title text-white" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn btn-system btn-system-light ms-auto" data-bs-dismiss="modal"
                            aria-label="Close">
                        <svg class="sa-icon sa-icon-2x">
                            <use href="/img/sprite.svg#x"></use>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="deleteEventId">
                    <div
                        class="alert alert-danger bg-danger border-danger text-light border-opacity-50 bg-opacity-10 mb-0">
                        Are you sure you want to delete record <span class="fw-600 text-danger">Event Title</span> <br>
                        This action cannot be undone.
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Yes, delete</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Category Management Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Manage Categories</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="categoryForm" class="mb-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="categoryName" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="categoryName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="categoryColor" class="form-label">Color</label>
                                <select class="form-select" id="categoryColor">
                                    <option value="var(--primary-500)">Blue</option>
                                    <option value="var(--success-500)">Green</option>
                                    <option value="var(--danger-500)">Red</option>
                                    <option value="var(--warning-500)">Yellow</option>
                                    <option value="var(--info-500)">Cyan</option>
                                    <option value="var(--secondary-900)">Dark</option>
                                    <option value="var(--secondary-300)">Secondary</option>
                                    <option value="var(--bs-purple)">Purple</option>
                                    <option value="var(--bs-indigo)">Indigo</option>
                                    <option value="var(--bs-teal)">Teal</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </form>

                    <h6>Existing Categories</h6>
                    <ul class="list-group" id="categoryList">
                        <!-- Categories will be populated by JS -->
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/fullcalendar.js'])
@endsection
