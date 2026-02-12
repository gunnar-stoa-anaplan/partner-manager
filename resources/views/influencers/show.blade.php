@extends('layouts.vertical', ['pageTitle' => $contact->first_name . ' ' . $contact->last_name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $contact->first_name . ' ' . $contact->last_name, 'pageSubTitle1' => 'Influencers', 'pageSubTitle1Url' => route('influencers.index')])
@endsection

@section('content')
<div class="row mt-3">
    {{-- Profile Card --}}
    <div class="col-lg-4">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <div class="text-center mb-3">
                    @if($contact->linkedin_image_url)
                        <img src="{{ $contact->linkedin_image_url }}" class="rounded-circle mb-2" width="80" height="80" style="object-fit: cover;" alt="{{ $contact->full_name }}">
                    @else
                        <div class="width-8 height-8 rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2">
                            <span class="h3 text-primary mb-0">{{ strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1)) }}</span>
                        </div>
                    @endif
                    <h4 class="mb-1">{{ $contact->first_name }} {{ $contact->last_name }}</h4>
                    <span class="badge bg-primary bg-opacity-25 text-primary mb-2">
                        {{ \App\Models\Contact::INFLUENCER_ROLES[$contact->influencer_role] ?? $contact->influencer_role }}
                    </span>
                    @if($contact->title)
                        <p class="text-muted mb-0">{{ $contact->title }}</p>
                    @endif
                    @if($contact->linkedin_headline)
                        <p class="text-muted small mb-0 fst-italic">{{ $contact->linkedin_headline }}</p>
                    @endif
                </div>

                <hr>

                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Partner</dt>
                    <dd class="col-7">
                        <a href="{{ route('partners.show', $contact->partner_id) }}">{{ $contact->partner->name ?? '-' }}</a>
                    </dd>

                    @if($contact->email)
                    <dt class="col-5 text-muted">Email</dt>
                    <dd class="col-7"><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></dd>
                    @endif

                    @if($contact->phone)
                    <dt class="col-5 text-muted">Phone</dt>
                    <dd class="col-7">{{ $contact->phone }}</dd>
                    @endif

                    @if($contact->organization)
                    <dt class="col-5 text-muted">Organization</dt>
                    <dd class="col-7">{{ $contact->organization }}</dd>
                    @endif

                    <dt class="col-5 text-muted">Interactions</dt>
                    <dd class="col-7">{{ $contact->interactions_count }}</dd>
                </dl>

                @if($contact->notes)
                <hr>
                <p class="text-muted small mb-0">{{ $contact->notes }}</p>
                @endif

                {{-- LinkedIn Section --}}
                <hr>
                <h6 class="text-muted fw-500 mb-2 small">LinkedIn</h6>
                @if($contact->linkedin_url)
                    <div class="d-flex align-items-center mb-2">
                        <i class="fab fa-linkedin text-info fa-lg me-2"></i>
                        <a href="{{ $contact->linkedin_url }}" target="_blank" rel="noopener" class="text-truncate small">
                            {{ Str::limit($contact->linkedin_url, 40) }}
                        </a>
                    </div>
                    @if($contact->linkedin_synced_at)
                        <p class="text-muted fs-nano mb-2">Linked {{ $contact->linkedin_synced_at->diffForHumans() }}</p>
                    @endif
                    <form method="POST" action="{{ route('influencers.linkedin.remove', $contact) }}" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-outline-danger" onclick="return confirm('Unlink LinkedIn profile?');">
                            <i class="fal fa-unlink me-1"></i>Unlink
                        </button>
                    </form>
                @else
                    <div id="linkedin-lookup-area">
                        <p class="text-muted fs-sm mb-2">No LinkedIn profile linked.</p>
                        <div class="d-flex gap-2 mb-2">
                            <a href="{{ $contact->getLinkedInSearchUrl() }}" target="_blank" rel="noopener" class="btn btn-xs btn-info">
                                <i class="fab fa-linkedin me-1"></i>Search LinkedIn
                            </a>
                            <button type="button" class="btn btn-xs btn-outline-primary" id="linkedin-paste-btn" onclick="document.getElementById('linkedin-link-section').style.display='block'; this.style.display='none';">
                                <i class="fal fa-link me-1"></i>Paste URL
                            </button>
                        </div>
                        <div id="linkedin-link-section" style="display:none;">
                            <form method="POST" action="{{ route('influencers.linkedin.store', $contact) }}">
                                @csrf
                                <div class="input-group input-group-sm mb-2">
                                    <input type="url" class="form-control" name="linkedin_url" placeholder="https://linkedin.com/in/..." required>
                                    <button type="submit" class="btn btn-primary"><i class="fal fa-check"></i></button>
                                </div>
                                <p class="text-muted fs-nano">Paste the LinkedIn profile URL after finding it via search.</p>
                            </form>
                        </div>
                    </div>
                @endif

                <hr>
                <div class="d-flex gap-2">
                    <a href="{{ route('influencers.edit', $contact) }}" class="btn btn-sm btn-outline-primary flex-grow-1">Edit Profile</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Interaction Timeline --}}
    <div class="col-lg-8">
        {{-- Log Interaction Form --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Log Interaction</div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('influencers.interactions.store', $contact) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="interaction_type">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="interaction_type" name="interaction_type" required>
                                <option value="">Select...</option>
                                @foreach(\App\Models\ContactInteraction::TYPES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="int_title">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="int_title" name="title" required placeholder="e.g., SPM Product Update">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="interaction_date">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="interaction_date" name="interaction_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="int_location">Location</label>
                            <input type="text" class="form-control" id="int_location" name="location" placeholder="e.g., Virtual, NYC Office">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="int_outcome">Outcome</label>
                            <input type="text" class="form-control" id="int_outcome" name="outcome" placeholder="Brief summary">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="int_description">Description</label>
                        <textarea class="form-control" id="int_description" name="description" rows="2" placeholder="Details about the interaction..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="int_next_steps">Next Steps</label>
                        <textarea class="form-control" id="int_next_steps" name="next_steps" rows="2" placeholder="Follow-up actions..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Log Interaction</button>
                </form>
            </div>
        </div>

        {{-- Interaction History --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Interaction History ({{ $contact->interactions_count }})</div>
            </div>
            <div class="card-body p-0">
                @forelse($interactions as $int)
                <div class="border-bottom px-4 py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-info bg-opacity-25 text-info me-2">
                                {{ \App\Models\ContactInteraction::TYPES[$int->interaction_type] ?? $int->interaction_type }}
                            </span>
                            <span class="fw-500">{{ $int->title }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small">{{ $int->interaction_date->format('M j, Y') }}</span>
                            <form method="POST" action="{{ route('interactions.destroy', $int) }}" onsubmit="return confirm('Remove this interaction?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fal fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    @if($int->description)
                        <p class="mb-1 mt-2 fs-sm">{{ $int->description }}</p>
                    @endif
                    @if($int->outcome)
                        <p class="mb-0 fs-sm"><span class="text-muted">Outcome:</span> {{ $int->outcome }}</p>
                    @endif
                    @if($int->next_steps)
                        <p class="mb-0 fs-sm"><span class="text-muted">Next Steps:</span> {{ $int->next_steps }}</p>
                    @endif
                    @if($int->location)
                        <span class="text-muted fs-nano"><i class="fal fa-map-marker-alt me-1"></i>{{ $int->location }}</span>
                    @endif
                    <span class="text-muted fs-nano ms-2">Logged by {{ $int->loggedBy->name ?? 'Unknown' }}</span>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fal fa-handshake fa-2x mb-2 opacity-50"></i>
                    <p>No interactions logged yet. Use the form above to log your first one.</p>
                </div>
                @endforelse

                @if($interactions->hasPages())
                <div class="px-4 py-3">
                    {{ $interactions->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
