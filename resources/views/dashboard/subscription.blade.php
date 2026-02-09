@extends('layouts.vertical', ['pageTitle' => 'Subscription Dashboard'])


@section('page-title')
    <div class="d-flex align-items-end mb-4">
        <div>
            @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Subscription Dashboard', 'pageSubTitle1' => 'Insights', 'pageSubTitle2'=> 'Dashboards'])
        </div>
        <div class="ms-auto d-none d-sm-flex align-items-center ">
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                    data-bs-target="#buildCampaignModal">Build Campaign
            </button>
        </div>
    </div>

    <!-- Build Campaign Modal -->
    <div class="modal fade" id="buildCampaignModal" tabindex="-1" aria-labelledby="buildCampaignModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Build New Subscription Campaign</h5>
                    <button type="button" class="btn btn-system btn-system-light ms-auto" data-bs-dismiss="modal"
                            aria-label="Close">
                        <svg class="sa-icon sa-icon-2x">
                            <use href="/icons/sprite.svg#x"></use>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="campaignForm">
                        <!-- Campaign Basics Section -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Campaign Basics</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="campaignName" class="form-label">Campaign Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg" id="campaignName"
                                               placeholder="Enter campaign name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="campaignType" class="form-label">Campaign Type <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select form-select-lg" id="campaignType" required>
                                            <option selected disabled value="">Select type...</option>
                                            <option value="acquisition">New Customer Acquisition</option>
                                            <option value="retention">Customer Retention</option>
                                            <option value="winback">Win-back Campaign</option>
                                            <option value="upgrade">Subscription Upgrade</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="startDate" class="form-label">Start Date <span
                                                class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-lg" id="startDate" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="endDate" class="form-label">End Date</label>
                                        <input type="date" class="form-control form-control-lg" id="endDate">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="campaignDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="campaignDescription" rows="2"
                                              placeholder="Brief description of campaign goals..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Audience & Offer Section -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Audience & Offer</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <label for="targetAudience" class="form-label">Target Audience <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select form-select-lg" id="targetAudience" required>
                                        <option selected disabled value="">Select audience...</option>
                                        <option value="all">All Subscribers</option>
                                        <option value="active">Active Subscribers</option>
                                        <option value="inactive">Inactive Subscribers</option>
                                        <option value="expired">Recently Expired</option>
                                        <option value="custom">Custom Segment</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Offer Type <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check card p-3 offer-card">
                                                <input class="form-check-input" type="radio" name="offerType"
                                                       id="offerDiscount" value="discount" checked>
                                                <label class="form-check-label" for="offerDiscount">
                                                    <i class="bi bi-percent text-primary me-2"></i>
                                                    <strong>Discount</strong>
                                                    <p class="text-muted small mb-0">Percentage or fixed amount
                                                        discount</p>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check card p-3 offer-card">
                                                <input class="form-check-input" type="radio" name="offerType"
                                                       id="offerFreeTrial" value="freeTrial">
                                                <label class="form-check-label" for="offerFreeTrial">
                                                    <i class="bi bi-clock-history text-success me-2"></i>
                                                    <strong>Free Trial</strong>
                                                    <p class="text-muted small mb-0">Temporary free access period</p>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check card p-3 offer-card">
                                                <input class="form-check-input" type="radio" name="offerType"
                                                       id="offerUpgrade" value="upgrade">
                                                <label class="form-check-label" for="offerUpgrade">
                                                    <i class="bi bi-arrow-up-circle text-warning me-2"></i>
                                                    <strong>Upgrade Promotion</strong>
                                                    <p class="text-muted small mb-0">Special pricing for upgrades</p>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check card p-3 offer-card">
                                                <input class="form-check-input" type="radio" name="offerType"
                                                       id="offerBundle" value="bundle">
                                                <label class="form-check-label" for="offerBundle">
                                                    <i class="bi bi-box-seam text-info me-2"></i>
                                                    <strong>Bundle Offer</strong>
                                                    <p class="text-muted small mb-0">Combined products/services</p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3" id="discountSection">
                                    <label for="discountAmount" class="form-label">Discount Amount</label>
                                    <div class="d-flex align-items-center">
                                        <input type="range" class="form-range flex-grow-1 me-3" min="5" max="50"
                                               step="5" id="discountAmount" value="15">
                                        <span class="badge bg-primary fs-6" id="discountAmountValue">15%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Channels & Budget Section -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Channels & Budget</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <label class="form-label">Campaign Channels <span
                                            class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="form-check card p-2 channel-card">
                                                <input class="form-check-input" type="checkbox" value="email"
                                                       id="channelEmail" checked>
                                                <label class="form-check-label d-flex flex-column align-items-center"
                                                       for="channelEmail">
                                                    <i class="bi bi-envelope-fill text-danger fs-4 mb-1"></i>
                                                    <span>Email</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="form-check card p-2 channel-card">
                                                <input class="form-check-input" type="checkbox" value="sms"
                                                       id="channelSMS">
                                                <label class="form-check-label d-flex flex-column align-items-center"
                                                       for="channelSMS">
                                                    <i class="bi bi-chat-left-text-fill text-success fs-4 mb-1"></i>
                                                    <span>SMS</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="form-check card p-2 channel-card">
                                                <input class="form-check-input" type="checkbox" value="push"
                                                       id="channelPush">
                                                <label class="form-check-label d-flex flex-column align-items-center"
                                                       for="channelPush">
                                                    <i class="bi bi-bell-fill text-warning fs-4 mb-1"></i>
                                                    <span>Push</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="form-check card p-2 channel-card">
                                                <input class="form-check-input" type="checkbox" value="inapp"
                                                       id="channelInApp">
                                                <label class="form-check-label d-flex flex-column align-items-center"
                                                       for="channelInApp">
                                                    <i class="bi bi-phone-fill text-info fs-4 mb-1"></i>
                                                    <span>In-App</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Budget Allocation</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-primary text-white">$</span>
                                            <input type="number" class="form-control form-control-lg"
                                                   id="campaignBudget" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Priority</label>
                                        <select class="form-select form-select-lg">
                                            <option>Low</option>
                                            <option selected>Medium</option>
                                            <option>High</option>
                                            <option>Urgent</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Goals Section -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Performance Goals</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="kpiConversions" class="form-label">Conversion Target</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="kpiConversions"
                                                   placeholder="#">
                                            <span class="input-group-text">subscribers</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="kpiRevenue" class="form-label">Revenue Target</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="kpiRevenue"
                                                   placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="kpiROI" class="form-label">ROI Target</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="kpiROI" placeholder="0">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="saveDraftBtn">
                        <i class="bi bi-file-earmark me-1"></i>Save Draft
                    </button>
                    <button type="button" class="btn btn-success" id="launchCampaignBtn">
                        <i class="bi bi-rocket-takeoff me-1"></i>Launch Campaign
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row sortable-off">
        <!-- Active Subscriptions -->
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Active Subscriptions</p>
                            <span class="h5 text-nowrap">
                                12,580
                                <sup class="text-success">+4.2%</sup>
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div
                                class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-check-circle h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- New Subscriptions -->
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">New Subscriptions</p>
                            <span class="h5 text-nowrap">
                                1,145
                                <sup class="text-success">+7.8%</sup>
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div
                                class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-user-plus h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Cancellations -->
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Cancellations</p>
                            <span class="h5 text-nowrap">
                                326
                                <sup class="text-danger">-1.5%</sup>
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div
                                class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-user-times h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- MRR (Monthly Recurring Revenue) -->
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Monthly Recurring Revenue</p>
                            <span class="h5 text-nowrap">
                                $42,860.00
                                <sup class="text-success">+6.1%</sup>
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div
                                class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-chart-line h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-icon panel-locked" id="panel-1">
                <div class="panel-hdr">
                    <h2> Subscription <span class="fw-light"><i>Feed</i></span>
                    </h2>
                </div>
                <div class="panel-container border-faded border-start-0 border-end-0 border-bottom-0">
                    <div class="panel-content p-0">
                        <div class="row row-grid g-0">
                            <div class="col-sm-4 p-3" style="background: var(--app-nav-item-active-bg);">
                                <div class="d-flex mt-3">
                                    <span class="h6 m-0 fw-300">Conversion Rate</span>
                                    <span class="h6 d-inline-block ms-auto">65%</span>
                                </div>
                                <div class="progress progress mb-4 border shadow-inset-3">
                                    <div aria-label="65%" aria-labelledby="Conversion Rate" aria-valuemax="100"
                                         aria-valuemin="0" aria-valuenow="65"
                                         class="progress-bar bg-primary-700 progress-bar-striped" role="progressbar"
                                         style="width: 65%;"></div>
                                </div>
                                <div class="d-flex">
                                    <span class="h6 m-0 fw-300">Completion Rate</span>
                                    <span class="h6 d-inline-block ms-auto">50%</span>
                                </div>
                                <div class="progress progress mb-4 border shadow-inset-3">
                                    <div aria-label="50%" aria-labelledby="Completion Rate" aria-valuemax="100"
                                         aria-valuemin="0" aria-valuenow="50"
                                         class="progress-bar bg-primary-700 progress-bar-striped" role="progressbar"
                                         style="width: 50%;"></div>
                                </div>
                                <div class="d-flex">
                                    <span class="h6 m-0 fw-300">Retention Rate</span>
                                    <span class="h6 d-inline-block ms-auto">75%</span>
                                </div>
                                <div class="progress progress mb-4 border shadow-inset-3">
                                    <div aria-label="75%" aria-labelledby="Retention Rate" aria-valuemax="100"
                                         aria-valuemin="0" aria-valuenow="75"
                                         class="progress-bar bg-primary-700 progress-bar-striped" role="progressbar"
                                         style="width: 75%;"></div>
                                </div>
                                <div class="d-flex">
                                    <span class="h6 m-0 fw-300">Engagement Rate</span>
                                    <span class="h6 d-inline-block ms-auto">84%</span>
                                </div>
                                <div class="progress progress mb-4 border shadow-inset-3">
                                    <div aria-label="84%" aria-labelledby="Engagement Rate" aria-valuemax="100"
                                         aria-valuemin="0" aria-valuenow="84"
                                         class="progress-bar bg-primary-700 progress-bar-striped" role="progressbar"
                                         style="width: 84%;"></div>
                                </div>
                                <div class="d-flex">
                                    <span class="h6 m-0 fw-300">Marketing Budget</span>
                                    <span class="h6 d-inline-block ms-auto">97%</span>
                                </div>
                                <div class="progress progress mb-4 border shadow-inset-3">
                                    <div aria-label="97%" aria-labelledby="Marketing Budget" aria-valuemax="100"
                                         aria-valuemin="0" aria-valuenow="97"
                                         class="progress-bar bg-danger-500 progress-bar-striped" role="progressbar"
                                         style="width: 97%;"></div>
                                </div>
                            </div>
                            <div class="col-sm-8 p-3 bg-subtlelight-fade">
                                <div id="subscription-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel panel-icon panel-locked" id="panel-2">
                <div class="panel-hdr">
                    <h2> User <span class="fw-light"><i>Activity</i></span>
                    </h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="user-activity-chart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2> Data <span class="fw-light"><i>Stream</i></span>
                    </h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="data-stream-chart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2> Demographic <span class="fw-light"><i>Marketing</i></span>
                    </h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="demographic-marketing-chart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/subscriptiondashboard.js'])
@endsection
