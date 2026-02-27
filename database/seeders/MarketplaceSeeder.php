<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\PartnerMarketplaceEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

class MarketplaceSeeder extends Seeder
{
    /**
     * Marketplace profile data for specific partners.
     * Partners not listed here stay as 'draft' with no profile data.
     *
     * @var array<string, array{status: string, headline: string, description: string, emails: string, entries: list<array{category: string, title: string, link: string, description: string}>}>
     */
    private const PROFILES = [
        'Accenture' => [
            'status' => 'approved',
            'headline' => 'Global Leader in Connected Planning Solutions',
            'description' => 'Accenture delivers enterprise-grade planning transformations, combining deep industry expertise with Anaplan platform mastery to drive connected planning across Finance, Supply Chain, and Sales.',
            'emails' => 'anaplan-leads@accenture.com;planning-practice@accenture.com',
            'entries' => [
                ['category' => 'finance_solutions', 'title' => 'Connected FP&A Accelerator', 'link' => 'https://accenture.com/anaplan-fpa', 'description' => 'Pre-built FP&A models with rolling forecasts, driver-based planning, and automated consolidation.'],
                ['category' => 'finance_customer_stories', 'title' => 'Global Bank Transforms Planning', 'link' => 'https://accenture.com/stories/bank-planning', 'description' => 'How a top-10 bank reduced close cycle by 40% with connected financial planning.'],
                ['category' => 'supply_chain_solutions', 'title' => 'Integrated Supply Planning Suite', 'link' => 'https://accenture.com/anaplan-supply', 'description' => 'End-to-end supply chain planning from demand sensing to S&OP with real-time scenario modeling.'],
                ['category' => 'supply_chain_customer_stories', 'title' => 'CPG Leader Achieves Demand Visibility', 'link' => 'https://accenture.com/stories/cpg-demand', 'description' => 'Fortune 100 CPG company improved forecast accuracy by 25% using Anaplan demand planning.'],
                ['category' => 'sales_marketing_solutions', 'title' => 'Territory & Quota Planning', 'link' => 'https://accenture.com/anaplan-tqm', 'description' => 'Automated territory carving, quota allocation, and incentive compensation management.'],
            ],
        ],
        'Deloitte' => [
            'status' => 'approved',
            'headline' => 'Transforming Enterprise Planning with Anaplan',
            'description' => 'Deloitte brings decades of consulting expertise to help organizations modernize their planning processes with Anaplan, spanning finance, workforce, and operational planning.',
            'emails' => 'anaplan@deloitte.com',
            'entries' => [
                ['category' => 'finance_solutions', 'title' => 'Finance Transformation Suite', 'link' => 'https://deloitte.com/anaplan-finance', 'description' => 'Comprehensive finance planning solution covering budgeting, forecasting, and profitability analysis.'],
                ['category' => 'hr_workforce_solutions', 'title' => 'Strategic Workforce Planning', 'link' => 'https://deloitte.com/anaplan-workforce', 'description' => 'Align workforce capacity with business strategy through skills-based planning and scenario analysis.'],
                ['category' => 'hr_workforce_customer_stories', 'title' => 'Tech Company Right-Sizes Workforce', 'link' => 'https://deloitte.com/stories/workforce-tech', 'description' => 'Technology firm used Anaplan to model 15 workforce scenarios and optimize headcount across regions.'],
                ['category' => 'manufacturing_solutions', 'title' => 'Production Planning Optimizer', 'link' => 'https://deloitte.com/anaplan-manufacturing', 'description' => 'Optimize production schedules, capacity utilization, and material requirements in real-time.'],
            ],
        ],
        'PwC' => [
            'status' => 'submitted',
            'headline' => 'Connected Planning Advisory & Implementation',
            'description' => 'PwC combines strategy consulting with deep Anaplan technical expertise to deliver planning transformations that connect finance, operations, and commercial teams.',
            'emails' => 'anaplan-practice@pwc.com;planning@pwc.com',
            'entries' => [
                ['category' => 'finance_solutions', 'title' => 'Integrated Business Planning', 'link' => 'https://pwc.com/anaplan-ibp', 'description' => 'Cross-functional planning platform connecting financial and operational plans in real-time.'],
                ['category' => 'consumer_solutions', 'title' => 'Retail Revenue Planning', 'link' => 'https://pwc.com/anaplan-retail', 'description' => 'Merchandise financial planning, assortment optimization, and promotional planning for retail.'],
                ['category' => 'consumer_customer_stories', 'title' => 'Retailer Optimizes Category Planning', 'link' => 'https://pwc.com/stories/retail-planning', 'description' => 'National retailer improved margin by 3% through Anaplan-powered category planning.'],
            ],
        ],
        'KPMG' => [
            'status' => 'submitted',
            'headline' => 'Driving Planning Excellence Across Industries',
            'description' => 'KPMG helps organizations of all sizes implement connected planning with Anaplan, from rapid proof-of-concept deployments to enterprise-wide transformations.',
            'emails' => 'anaplan@kpmg.com',
            'entries' => [
                ['category' => 'financial_business_services_solutions', 'title' => 'Insurance Planning Accelerator', 'link' => 'https://kpmg.com/anaplan-insurance', 'description' => 'Premium forecasting, claims reserves planning, and regulatory capital modeling for insurers.'],
                ['category' => 'financial_business_services_customer_stories', 'title' => 'Insurer Streamlines Capital Planning', 'link' => 'https://kpmg.com/stories/insurance-capital', 'description' => 'Top-20 insurer reduced planning cycle from 6 weeks to 5 days with Anaplan.'],
            ],
        ],
        'EY' => [
            'status' => 'rejected',
            'headline' => 'Planning Solutions for the Connected Enterprise',
            'description' => 'EY delivers Anaplan solutions that connect strategy to execution.',
            'emails' => 'anaplan@ey.com',
            'rejection_reason' => 'Company description is too brief. Please expand to include specific capabilities, industries served, and differentiators.',
            'entries' => [
                ['category' => 'tech_media_telecom_solutions', 'title' => 'Telecom Revenue Assurance', 'link' => 'https://ey.com/anaplan-telecom', 'description' => 'Revenue forecasting, subscriber modeling, and capacity planning for telecom operators.'],
            ],
        ],
        'Capgemini' => [
            'status' => 'approved',
            'headline' => 'Intelligent Enterprise Planning',
            'description' => 'Capgemini blends AI-powered insights with Anaplan platform capabilities to deliver next-generation planning solutions across Finance, Supply Chain, and Commercial Operations.',
            'emails' => 'anaplan-coe@capgemini.com;planning@capgemini.com',
            'entries' => [
                ['category' => 'supply_chain_solutions', 'title' => 'AI-Enhanced Demand Sensing', 'link' => 'https://capgemini.com/anaplan-demand', 'description' => 'Machine learning-powered demand forecasting integrated with Anaplan planning models.'],
                ['category' => 'supply_chain_customer_stories', 'title' => 'Automotive OEM Optimizes Supply', 'link' => 'https://capgemini.com/stories/auto-supply', 'description' => 'Major automotive OEM reduced supply chain disruptions by 35% with connected planning.'],
                ['category' => 'manufacturing_customer_stories', 'title' => 'Pharma Company Transforms S&OP', 'link' => 'https://capgemini.com/stories/pharma-sop', 'description' => 'Global pharma achieved 95% forecast accuracy through integrated S&OP on Anaplan.'],
            ],
        ],
        'Infosys' => [
            'status' => 'approved',
            'headline' => 'Scalable Planning Solutions for Global Enterprises',
            'description' => 'Infosys brings a unique combination of industry solutions and technical excellence to deliver Anaplan implementations that scale from pilot to enterprise-wide rollout.',
            'emails' => 'anaplan@infosys.com',
            'entries' => [
                ['category' => 'sales_marketing_solutions', 'title' => 'Revenue Performance Management', 'link' => 'https://infosys.com/anaplan-rpm', 'description' => 'Integrated sales forecasting, territory management, and incentive compensation.'],
                ['category' => 'sales_marketing_customer_stories', 'title' => 'SaaS Company Scales GTM Planning', 'link' => 'https://infosys.com/stories/saas-gtm', 'description' => 'Enterprise SaaS company unified go-to-market planning across 40 countries with Anaplan.'],
            ],
        ],
        'Wipro' => [
            'status' => 'submitted',
            'headline' => 'Next-Gen Connected Planning',
            'description' => 'Wipro delivers cost-effective, high-quality Anaplan implementations with a focus on automation, integration, and user adoption to maximize planning ROI.',
            'emails' => 'anaplan-solutions@wipro.com',
            'entries' => [
                ['category' => 'finance_solutions', 'title' => 'Zero-Based Budgeting Solution', 'link' => 'https://wipro.com/anaplan-zbb', 'description' => 'Implement zero-based budgeting with Anaplan to drive cost discipline and strategic resource allocation.'],
            ],
        ],
    ];

    public function run(): void
    {
        $reviewer = User::whereHas('role', fn ($q) => $q->where('slug', 'admin'))->first();

        foreach (self::PROFILES as $partnerName => $profile) {
            $partner = Partner::where('name', $partnerName)->first();

            if (! $partner) {
                continue;
            }

            $updateData = [
                'marketplace_status' => $profile['status'],
                'headline' => $profile['headline'],
                'company_description' => $profile['description'],
                'partner_locator_emails' => $profile['emails'],
            ];

            if (in_array($profile['status'], ['approved', 'rejected']) && $reviewer) {
                $updateData['marketplace_reviewed_by'] = $reviewer->id;
                $updateData['marketplace_reviewed_at'] = now()->subDays(rand(1, 14));
            }

            if ($profile['status'] === 'rejected' && isset($profile['rejection_reason'])) {
                $updateData['marketplace_rejection_reason'] = $profile['rejection_reason'];
            }

            $partner->update($updateData);

            foreach ($profile['entries'] as $index => $entry) {
                PartnerMarketplaceEntry::create([
                    'partner_id' => $partner->id,
                    'category' => $entry['category'],
                    'title' => $entry['title'],
                    'link' => $entry['link'],
                    'description' => $entry['description'],
                    'sort_order' => $index + 1,
                ]);
            }
        }
    }
}
