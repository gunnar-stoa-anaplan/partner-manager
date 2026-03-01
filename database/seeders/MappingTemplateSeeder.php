<?php

namespace Database\Seeders;

use App\Models\MappingTemplate;
use Illuminate\Database\Seeder;

class MappingTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = $this->templates();

        foreach ($templates as $data) {
            MappingTemplate::updateOrCreate(
                ['slug' => $data['slug'], 'version' => $data['version']],
                $data,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function templates(): array
    {
        return [
            // ── Core (no license feature required) ──────────────
            [
                'slug' => 'partner-import',
                'name' => 'Partner Import',
                'description' => 'Standard partner record import — company details, geography, and owner assignment.',
                'target_model' => 'Partner',
                'version' => '1.0',
                'required_feature' => null,
                'is_current' => true,
                'fields' => [
                    ['key' => 'name', 'source_field' => 'partner_name', 'target_field' => 'name', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Partner company name'],
                    ['key' => 'salesforce_id', 'source_field' => 'sfdc_id', 'target_field' => 'salesforce_id', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Salesforce account ID for deduplication'],
                    ['key' => 'website', 'source_field' => 'website', 'target_field' => 'website', 'transform' => 'lowercase', 'default_value' => null, 'is_required' => false, 'description' => 'Partner website URL'],
                    ['key' => 'industry', 'source_field' => 'industry', 'target_field' => 'industry', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Industry vertical'],
                    ['key' => 'region', 'source_field' => 'region', 'target_field' => 'region', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Sales region'],
                    ['key' => 'geography', 'source_field' => 'geography', 'target_field' => 'geography', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Geographic territory (NA, EMEA, APJ, etc.)'],
                ],
            ],
            [
                'slug' => 'contact-import',
                'name' => 'Contact Import',
                'description' => 'Import partner contacts — names, roles, and communication details.',
                'target_model' => 'Contact',
                'version' => '1.0',
                'required_feature' => null,
                'is_current' => true,
                'fields' => [
                    ['key' => 'first_name', 'source_field' => 'first_name', 'target_field' => 'first_name', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Contact first name'],
                    ['key' => 'last_name', 'source_field' => 'last_name', 'target_field' => 'last_name', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Contact last name'],
                    ['key' => 'email', 'source_field' => 'email', 'target_field' => 'email', 'transform' => 'lowercase', 'default_value' => null, 'is_required' => true, 'description' => 'Email address'],
                    ['key' => 'phone', 'source_field' => 'phone', 'target_field' => 'phone', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Phone number'],
                    ['key' => 'title', 'source_field' => 'job_title', 'target_field' => 'title', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Job title'],
                    ['key' => 'contact_type', 'source_field' => 'type', 'target_field' => 'contact_type', 'transform' => 'lowercase', 'default_value' => 'general', 'is_required' => false, 'description' => 'Contact type (general, influencer, enablement, resource)'],
                    ['key' => 'organization', 'source_field' => 'organization', 'target_field' => 'organization', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Organization or department'],
                ],
            ],
            [
                'slug' => 'account-import',
                'name' => 'Account Import',
                'description' => 'Import customer accounts that partners are associated with.',
                'target_model' => 'Account',
                'version' => '1.0',
                'required_feature' => null,
                'is_current' => true,
                'fields' => [
                    ['key' => 'name', 'source_field' => 'account_name', 'target_field' => 'name', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Customer account name'],
                ],
            ],
            [
                'slug' => 'lead-import',
                'name' => 'Lead Import',
                'description' => 'Import leads for partner assignment and tracking.',
                'target_model' => 'Lead',
                'version' => '1.0',
                'required_feature' => null,
                'is_current' => true,
                'fields' => [
                    ['key' => 'name', 'source_field' => 'lead_name', 'target_field' => 'name', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Lead contact name'],
                    ['key' => 'email', 'source_field' => 'email', 'target_field' => 'email', 'transform' => 'lowercase', 'default_value' => null, 'is_required' => true, 'description' => 'Lead email address'],
                    ['key' => 'phone', 'source_field' => 'phone', 'target_field' => 'phone', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Phone number'],
                    ['key' => 'company', 'source_field' => 'company', 'target_field' => 'company', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Company name'],
                    ['key' => 'title', 'source_field' => 'job_title', 'target_field' => 'title', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Job title'],
                    ['key' => 'source', 'source_field' => 'lead_source', 'target_field' => 'source', 'transform' => 'lowercase', 'default_value' => 'other', 'is_required' => false, 'description' => 'Lead source (website, partner, webcast, linkedin, referral, other)'],
                    ['key' => 'status', 'source_field' => 'status', 'target_field' => 'status', 'transform' => 'lowercase', 'default_value' => 'unassigned', 'is_required' => false, 'description' => 'Lead status'],
                    ['key' => 'notes', 'source_field' => 'notes', 'target_field' => 'notes', 'transform' => null, 'default_value' => null, 'is_required' => false, 'description' => 'Additional notes'],
                ],
            ],

            // ── Analytics feature ───────────────────────────────
            [
                'slug' => 'opportunity-import',
                'name' => 'Opportunity Import',
                'description' => 'Import sales opportunities for pipeline analytics and revenue tracking.',
                'target_model' => 'Opportunity',
                'version' => '1.0',
                'required_feature' => 'partner_management',
                'is_current' => true,
                'fields' => [
                    ['key' => 'salesforce_id', 'source_field' => 'sfdc_opportunity_id', 'target_field' => 'salesforce_id', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Salesforce opportunity ID'],
                    ['key' => 'opportunity_name', 'source_field' => 'opportunity_name', 'target_field' => 'opportunity_name', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Opportunity name'],
                    ['key' => 'account_name', 'source_field' => 'account_name', 'target_field' => 'account_name', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Associated customer account'],
                    ['key' => 'stage', 'source_field' => 'stage', 'target_field' => 'stage', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Pipeline stage'],
                    ['key' => 'amount', 'source_field' => 'amount', 'target_field' => 'amount', 'transform' => 'float', 'default_value' => '0', 'is_required' => true, 'description' => 'Deal value'],
                    ['key' => 'probability', 'source_field' => 'probability', 'target_field' => 'probability', 'transform' => 'float', 'default_value' => '0', 'is_required' => false, 'description' => 'Win probability (0-100)'],
                    ['key' => 'close_date', 'source_field' => 'close_date', 'target_field' => 'close_date', 'transform' => 'date_iso', 'default_value' => null, 'is_required' => true, 'description' => 'Expected close date'],
                    ['key' => 'type', 'source_field' => 'opportunity_type', 'target_field' => 'type', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Opportunity type (New, Renewal, Expansion)'],
                    ['key' => 'owner', 'source_field' => 'owner', 'target_field' => 'owner', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Opportunity owner'],
                ],
            ],
            [
                'slug' => 'deal-reg-import',
                'name' => 'Deal Registration Import',
                'description' => 'Import deal registration records for pipeline co-sell tracking.',
                'target_model' => 'DealRegistration',
                'version' => '1.0',
                'required_feature' => 'partner_management',
                'is_current' => true,
                'fields' => [
                    ['key' => 'deal_name', 'source_field' => 'deal_name', 'target_field' => 'deal_name', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Deal registration name'],
                    ['key' => 'customer_name', 'source_field' => 'customer_name', 'target_field' => 'customer_name', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Customer company name'],
                    ['key' => 'customer_contact_email', 'source_field' => 'customer_email', 'target_field' => 'customer_contact_email', 'transform' => 'lowercase', 'default_value' => null, 'is_required' => false, 'description' => 'Customer contact email'],
                    ['key' => 'estimated_value', 'source_field' => 'estimated_value', 'target_field' => 'estimated_value', 'transform' => 'float', 'default_value' => '0', 'is_required' => true, 'description' => 'Estimated deal value'],
                    ['key' => 'expected_close_date', 'source_field' => 'expected_close_date', 'target_field' => 'expected_close_date', 'transform' => 'date_iso', 'default_value' => null, 'is_required' => true, 'description' => 'Expected close date'],
                    ['key' => 'product_area', 'source_field' => 'product_area', 'target_field' => 'product_area', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Product or solution area'],
                ],
            ],

            // ── Enablement feature ──────────────────────────────
            [
                'slug' => 'resource-import',
                'name' => 'Resource Import',
                'description' => 'Import certified resources and training records by program.',
                'target_model' => 'Resource',
                'version' => '1.0',
                'required_feature' => 'enablement',
                'is_current' => true,
                'fields' => [
                    ['key' => 'name', 'source_field' => 'resource_name', 'target_field' => 'name', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Resource full name'],
                    ['key' => 'role', 'source_field' => 'role', 'target_field' => 'role', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Certification role or specialization'],
                    ['key' => 'program', 'source_field' => 'program', 'target_field' => 'program', 'transform' => 'uppercase', 'default_value' => null, 'is_required' => true, 'description' => 'Training program (IFP, I&D, SPM, OWP)'],
                    ['key' => 'status', 'source_field' => 'status', 'target_field' => 'status', 'transform' => 'lowercase', 'default_value' => null, 'is_required' => false, 'description' => 'Certification status'],
                    ['key' => 'date_certified', 'source_field' => 'date_certified', 'target_field' => 'date_certified', 'transform' => 'date_iso', 'default_value' => null, 'is_required' => false, 'description' => 'Date certification was earned'],
                ],
            ],

            // ── QBR feature ─────────────────────────────────────
            [
                'slug' => 'project-import',
                'name' => 'Project Import',
                'description' => 'Import delivery projects for health tracking and QBR preparation.',
                'target_model' => 'Project',
                'version' => '1.0',
                'required_feature' => 'qbr',
                'is_current' => true,
                'fields' => [
                    ['key' => 'name', 'source_field' => 'project_name', 'target_field' => 'name', 'transform' => 'trim', 'default_value' => null, 'is_required' => true, 'description' => 'Project name'],
                    ['key' => 'start_date', 'source_field' => 'start_date', 'target_field' => 'start_date', 'transform' => 'date_iso', 'default_value' => null, 'is_required' => true, 'description' => 'Project start date'],
                    ['key' => 'end_date', 'source_field' => 'end_date', 'target_field' => 'end_date', 'transform' => 'date_iso', 'default_value' => null, 'is_required' => false, 'description' => 'Projected end date'],
                    ['key' => 'status', 'source_field' => 'status', 'target_field' => 'status', 'transform' => 'lowercase', 'default_value' => 'planning', 'is_required' => false, 'description' => 'Project status'],
                    ['key' => 'acv', 'source_field' => 'acv', 'target_field' => 'acv', 'transform' => 'float', 'default_value' => '0', 'is_required' => false, 'description' => 'Annual contract value'],
                    ['key' => 'phase', 'source_field' => 'phase', 'target_field' => 'phase', 'transform' => 'trim', 'default_value' => null, 'is_required' => false, 'description' => 'Current delivery phase'],
                    ['key' => 'percent_complete', 'source_field' => 'percent_complete', 'target_field' => 'percent_complete', 'transform' => 'integer', 'default_value' => '0', 'is_required' => false, 'description' => 'Completion percentage (0-100)'],
                ],
            ],
        ];
    }
}
