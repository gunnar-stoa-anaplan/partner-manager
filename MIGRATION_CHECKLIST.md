# Partner Manager 2 → 3 Migration Checklist

## Overview
Migrating ONLY your backend logic and data to fresh SmartAdmin Laravel template.

**Time Estimate:** 2-3 hours

---

## Phase 1: Database Setup (30 min)

### Step 1.1: Copy Database Configuration
```bash
# Copy database settings from old .env
# From: partner-manager-2/.env
# To: Partner-Manager-3/.env
```

Update Partner-Manager-3/.env:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=partner_manager_3  # NEW database name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 1.2: Create New Database
```bash
# Create fresh database for Partner-Manager-3
mysql -u root -p -e "CREATE DATABASE partner_manager_3;"
```

### Step 1.3: Copy ALL Migrations
```bash
# Copy from partner-manager-2 to Partner-Manager-3
cp -r ../partner-manager-2/database/migrations/*.php database/migrations/
```

**Files to copy:**
- ✅ All migration files from `database/migrations/`
- Includes: users, partners, opportunities, resources, imports, snapshots, etc.

### Step 1.4: Run Migrations
```bash
cd /Users/gunnar.stoa/Library/CloudStorage/OneDrive-Anaplan/Desktop/Partner-Manager-3
php artisan migrate
```

---

## Phase 2: Export & Import Data (30 min)

### Step 2.1: Export Data from partner-manager-2
```bash
cd ../partner-manager-2

# Export all data
php artisan db:export --path=database/export_$(date +%Y%m%d).sql

# OR use mysqldump directly:
mysqldump -u root -p partner_manager > database/full_export_$(date +%Y%m%d).sql
```

### Step 2.2: Import Data to Partner-Manager-3
```bash
# Import into new database
mysql -u root -p partner_manager_3 < ../partner-manager-2/database/full_export_*.sql
```

**What you're preserving:**
- ✅ All 40 weeks of historical snapshots
- ✅ All partners, opportunities, resources
- ✅ All imports and their history
- ✅ All users and authentication data

---

## Phase 3: Copy Models (15 min)

### Step 3.1: Copy All Models
```bash
cd /Users/gunnar.stoa/Library/CloudStorage/OneDrive-Anaplan/Desktop/Partner-Manager-3

# Copy all models
cp ../partner-manager-2/app/Models/*.php app/Models/
```

**Models to verify copied:**
- ✅ Partner.php
- ✅ Opportunity.php
- ✅ Resource.php
- ✅ Import.php
- ✅ OpportunitySnapshot.php
- ✅ ResourceSnapshot.php
- ✅ Contact.php
- ✅ Project.php
- ✅ User.php (merge with SmartAdmin's if needed)

---

## Phase 4: Copy Controllers (20 min)

### Step 4.1: Copy Partner Controller
```bash
# Copy the entire PartnerController
cp ../partner-manager-2/app/Http/Controllers/PartnerController.php app/Http/Controllers/
```

### Step 4.2: Copy Other Controllers
```bash
# Copy all your custom controllers
cp ../partner-manager-2/app/Http/Controllers/ImportController.php app/Http/Controllers/
cp ../partner-manager-2/app/Http/Controllers/ContactController.php app/Http/Controllers/
cp ../partner-manager-2/app/Http/Controllers/ProjectController.php app/Http/Controllers/
# ... copy any other controllers you have
```

**Controllers to copy:**
- ✅ PartnerController.php (has all your data queries)
- ✅ ImportController.php
- ✅ ContactController.php
- ✅ ProjectController.php
- ✅ Any other custom controllers

---

## Phase 5: Copy Routes (10 min)

### Step 5.1: Merge Routes
Open both files side-by-side:
- OLD: `partner-manager-2/routes/web.php`
- NEW: `Partner-Manager-3/routes/web.php`

**Copy these route sections:**
```php
// Partner Routes
Route::get('/my-partners', [PartnerController::class, 'myPartners'])->name('partners.my');
Route::get('/partners/{id}', [PartnerController::class, 'show'])->name('partners.show');
Route::get('/partners/{id}/summary', [PartnerController::class, 'summary'])->name('partners.summary');
Route::get('/partners/{id}/insights', [PartnerController::class, 'insights'])->name('partners.insights');
Route::get('/full-summary', [PartnerController::class, 'fullSummary'])->name('partners.full-summary');
Route::get('/full-insights', [PartnerController::class, 'fullInsights'])->name('partners.full-insights');

// Import Routes
Route::get('/imports', [ImportController::class, 'index'])->name('imports.index');
// ... etc
```

---

## Phase 6: Copy Seeders & Commands (15 min)

### Step 6.1: Copy Seeders
```bash
# Copy your seeders
cp ../partner-manager-2/database/seeders/HistoricalDataSeeder.php database/seeders/
cp ../partner-manager-2/database/seeders/QuickTestSeeder.php database/seeders/
```

### Step 6.2: Copy Artisan Commands
```bash
# Copy your custom commands
cp ../partner-manager-2/app/Console/Commands/GenerateHistoricalSnapshots.php app/Console/Commands/
```

---

## Phase 7: Copy Helpers & Services (10 min)

### Step 7.1: Copy Helper Files
```bash
# If you have helpers
cp -r ../partner-manager-2/app/Helpers app/ 2>/dev/null || true
```

### Step 7.2: Copy Service Providers
```bash
# Copy any custom service providers
cp ../partner-manager-2/app/Providers/ViewServiceProvider.php app/Providers/ 2>/dev/null || true
```

### Step 7.3: Register Service Providers
Edit `bootstrap/providers.php` and add:
```php
App\Providers\ViewServiceProvider::class,
```

---

## Phase 8: Install Dependencies (15 min)

### Step 8.1: Install PHP Dependencies
```bash
cd /Users/gunnar.stoa/Library/CloudStorage/OneDrive-Anaplan/Desktop/Partner-Manager-3
composer install
```

### Step 8.2: Install Node Dependencies
```bash
npm install
```

### Step 8.3: Build Assets
```bash
npm run build
```

---

## Phase 9: Adapt Views to SmartAdmin (Main Work - 1-2 hours)

### Step 9.1: Create Partner Dashboard View
Edit: `resources/views/dashboard/subscription.blade.php`

Replace the sample data with your backend data:

```blade
@extends('layouts.vertical', ['pageTitle' => 'Partner Insights'])

@section('content')
    <div class="row sortable-off">
        <!-- Total Projects -->
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Total Projects</p>
                            <span class="h5 text-nowrap">
                                {{ $totalProjects }}
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-project-diagram h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Opportunities -->
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Total Opportunities</p>
                            <span class="h5 text-nowrap">
                                {{ $totalOpportunities }}
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-bullseye-arrow h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Certified Resources -->
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Certified Resources</p>
                            <span class="h5 text-nowrap">
                                {{ $totalResources }}
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-user-graduate h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pipeline Value -->
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Pipeline Value</p>
                            <span class="h5 text-nowrap">
                                ${{ number_format($totalPipelineValue / 1000, 0) }}K
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-chart-line h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Panel -->
    <div class="row">
        <div class="col-lg-12">
            <div id="panel-1" class="panel panel-icon panel-locked">
                <div class="panel-hdr">
                    <h2> Performance <span class="fw-light"><i>Trends</i></span></h2>
                </div>
                <div class="panel-container border-faded border-start-0 border-end-0 border-bottom-0">
                    <div class="panel-content p-0">
                        <div class="row row-grid g-0">
                            <!-- Left: Partner Quotas -->
                            <div class="col-sm-4 p-3" style="background: var(--app-nav-item-active-bg);">
                                @foreach($topPartners->take(5) as $quotaPartner)
                                    @php
                                        $totalAmount = $quotaPartner->opportunities->sum('amount');
                                        $target = 1000000;
                                        $percentage = $target > 0 ? min(100, ($totalAmount / $target) * 100) : 0;
                                        $color = $percentage >= 100 ? 'primary-700' : ($percentage >= 75 ? 'warning-500' : 'danger-500');
                                    @endphp

                                    <div class="d-flex {{ $loop->first ? 'mt-3' : '' }}">
                                        <span class="h6 m-0 fw-300">{{ $quotaPartner->name }} Quota</span>
                                        <span class="h6 d-inline-block ms-auto">{{ number_format($percentage) }}%</span>
                                    </div>
                                    <div class="progress progress mb-4 border shadow-inset-3">
                                        <div class="progress-bar bg-{{ $color }} progress-bar-striped"
                                             role="progressbar"
                                             style="width: {{ $percentage }}%;"
                                             aria-valuenow="{{ $percentage }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100"></div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Right: Chart -->
                            <div class="col-sm-8 p-3 bg-subtlelight-fade">
                                <div id="subscription-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
```

### Step 9.2: Update Controller Route
Edit SmartAdmin's `routes/web.php`, change:
```php
Route::get('/dashboard-subscription', function() {
    // OLD: return view with sample data
});
```

To:
```php
Route::get('/dashboard-subscription', [PartnerController::class, 'fullInsights'])->name('dashboard.subscription');
```

---

## Phase 10: Test & Verify (20 min)

### Checklist
- [ ] Visit `/dashboard-subscription` - sees your real data
- [ ] 4 stat cards show correct numbers
- [ ] Partner quotas display with your partners
- [ ] Charts render (using ApexCharts from template)
- [ ] Navigation works
- [ ] Database queries return data

---

## What You Get

✅ **Exact SmartAdmin template** - Pixel-perfect views
✅ **Your backend logic** - All controllers and models preserved
✅ **Your data** - All 40 weeks of snapshots intact
✅ **ApexCharts** - Native template charts (not Chart.js)
✅ **Clean start** - No approximations, exact template match

---

## Quick Commands Reference

```bash
# Navigate to new project
cd /Users/gunnar.stoa/Library/CloudStorage/OneDrive-Anaplan/Desktop/Partner-Manager-3

# Install dependencies
composer install
npm install

# Run migrations
php artisan migrate

# Build assets
npm run build

# Start server
php artisan serve
```

---

## Need Help?

I can help you with any step. Just let me know where you are in the process!
