<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = (User::whereRelation('role', 'slug', 'admin')->first() ?? User::first())->id;
        $templates = [
            [
                'event_type' => 'blueprint_assigned',
                'channel' => 'email',
                'subject' => 'New Blueprint Assigned: {{blueprint_name}}',
                'body' => '<p>Hi {{recipient_name}},</p><p>A new enablement blueprint has been assigned to {{partner_name}} for the <strong>{{role}}</strong> role.</p><p><strong>Blueprint:</strong> {{blueprint_name}}<br><strong>Product Area:</strong> {{blueprint_product_area}}</p><p>Please log in to review the blueprint and begin working through the content items.</p><p>Best regards,<br>{{psm_name}}</p>',
            ],
            [
                'event_type' => 'resource_enrolled_blueprint',
                'channel' => 'email',
                'subject' => 'You have been enrolled in: {{blueprint_name}}',
                'body' => '<p>Hi {{recipient_name}},</p><p>You have been enrolled in the <strong>{{blueprint_name}}</strong> enablement blueprint for {{partner_name}}.</p><p><strong>Role:</strong> {{role}}<br><strong>Product Area:</strong> {{blueprint_product_area}}</p><p>Please begin working through the content items at your earliest convenience.</p><p>Best regards,<br>{{psm_name}}</p>',
            ],
            [
                'event_type' => 'blueprint_completed',
                'channel' => 'both',
                'subject' => 'Blueprint Completed: {{blueprint_name}} — {{partner_name}}',
                'body' => '<p>Hi {{recipient_name}},</p><p>Great news! A resource at {{partner_name}} has completed the <strong>{{blueprint_name}}</strong> blueprint.</p><p><strong>Completion:</strong> 100%<br><strong>Role:</strong> {{role}}</p><p>This resource is now qualified for the {{blueprint_product_area}} product area.</p>',
            ],
            [
                'event_type' => 'blueprint_overdue',
                'channel' => 'in_app',
                'subject' => 'Overdue Blueprint: {{blueprint_name}} at {{partner_name}}',
                'body' => '<p>The {{blueprint_name}} blueprint assigned to {{partner_name}} for the {{role}} role is overdue.</p><p><strong>Progress:</strong> {{progress_pct}}%<br><strong>Enrolled:</strong> {{enrolled_date}}</p><p>Please follow up with the enrolled resources to accelerate completion.</p>',
            ],
            [
                'event_type' => 'event_registration',
                'channel' => 'email',
                'subject' => 'Event Registration Confirmed: {{event_title}}',
                'body' => '<p>Hi {{recipient_name}},</p><p>Your registration for the following enablement event has been confirmed:</p><p><strong>Event:</strong> {{event_title}}<br><strong>Date:</strong> {{session_date}}<br><strong>Time:</strong> {{session_time}}<br><strong>Location:</strong> {{session_location}}</p><p>If applicable, use this meeting link: {{session_url}}</p><p>Best regards,<br>Partner Enablement Team</p>',
            ],
            [
                'event_type' => 'certification_earned',
                'channel' => 'both',
                'subject' => 'Certification Earned: {{certification_type}} — {{recipient_name}}',
                'body' => '<p>Congratulations {{recipient_name}}!</p><p>You have earned the <strong>{{certification_type}}</strong> certification at the <strong>{{certification_level}}</strong> level.</p><p><strong>Product Area:</strong> {{product_area}}</p><p>This certification contributes to {{partner_name}}\'s path progression and scorecard.</p>',
            ],
            [
                'event_type' => 'certification_expiring',
                'channel' => 'email',
                'subject' => 'Certification Expiring: {{certification_type}} — {{days_until_expiry}} days remaining',
                'body' => '<p>Hi {{recipient_name}},</p><p>Your <strong>{{certification_type}}</strong> certification is expiring on <strong>{{expiry_date}}</strong> ({{days_until_expiry}} days remaining).</p><p><strong>Product Area:</strong> {{product_area}}<br><strong>Level:</strong> {{certification_level}}</p><p>Please schedule your certification renewal through Anaplan Academy to maintain your credentials.</p>',
            ],
            [
                'event_type' => 'task_assigned',
                'channel' => 'in_app',
                'subject' => 'New Task Assigned: {{task_title}}',
                'body' => '<p>A new task has been assigned to you:</p><p><strong>Task:</strong> {{task_title}}<br><strong>Category:</strong> {{task_category}}<br><strong>Due Date:</strong> {{task_due_date}}<br><strong>Owner:</strong> {{task_owner}}</p>',
            ],
            [
                'event_type' => 'task_overdue',
                'channel' => 'both',
                'subject' => 'Overdue Task: {{task_title}} — {{partner_name}}',
                'body' => '<p>The following task is past its due date:</p><p><strong>Task:</strong> {{task_title}}<br><strong>Partner:</strong> {{partner_name}}<br><strong>Due Date:</strong> {{task_due_date}}<br><strong>Owner:</strong> {{task_owner}}</p><p>Please take action to complete this task or update its status.</p>',
            ],
            [
                'event_type' => 'path_stage_advanced',
                'channel' => 'both',
                'subject' => '{{partner_name}} has advanced to {{stage_name}} stage!',
                'body' => '<p>Great news! <strong>{{partner_name}}</strong> has met all gate requirements and has been advanced to the <strong>{{stage_name}}</strong> stage in the partner path.</p><p>New tasks and requirements for this stage have been automatically generated. Please review the task board.</p>',
            ],
            [
                'event_type' => 'qbr_scheduled',
                'channel' => 'email',
                'subject' => 'QBR Scheduled: {{partner_name}} — {{fiscal_quarter}}',
                'body' => '<p>Hi {{recipient_name}},</p><p>A Quarterly Business Review has been scheduled:</p><p><strong>Partner:</strong> {{partner_name}}<br><strong>Quarter:</strong> {{fiscal_quarter}}<br><strong>Date:</strong> {{session_date}}</p><p>Please prepare relevant materials ahead of the session.</p>',
            ],
            [
                'event_type' => 'alert_generated',
                'channel' => 'in_app',
                'subject' => '[{{alert_severity}}] {{alert_title}}',
                'body' => '<p><strong>{{alert_title}}</strong></p><p>{{alert_description}}</p><p><strong>Recommended Action:</strong> {{recommended_action}}</p>',
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::updateOrCreate(
                ['event_type' => $template['event_type']],
                array_merge($template, [
                    'is_active' => true,
                    'created_by' => $adminId,
                ])
            );
        }
    }
}
