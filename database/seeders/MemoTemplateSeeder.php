<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MemoTemplate;

class MemoTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'name' => 'Verbal Warning',
                'title' => 'Verbal Warning Notice',
                'content' => "This is a verbal warning issued to {{employee_name}} ({{employee_number}}) on {{date_issued}}.\n\nReason: {{offense}}\n\nAction Required: {{action}}\n\nIssued By: {{issued_by}}",
                'category' => 'Warning',
                'is_active' => true
            ],
            [
                'name' => 'Written Warning',
                'title' => 'Written Warning Notice',
                'content' => "This is a formal written warning issued to {{employee_name}} ({{employee_number}}) on {{date_issued}}.\n\nOffense: {{offense}}\n\nPrevious Warnings: {{previous_warnings}}\n\nAction Required: {{action}}\n\nConsequences of Further Violations: {{consequences}}\n\nIssued By: {{issued_by}}",
                'category' => 'Warning',
                'is_active' => true
            ],
            [
                'name' => 'Final Warning',
                'title' => 'Final Warning Notice',
                'content' => "This is a FINAL warning issued to {{employee_name}} ({{employee_number}}) on {{date_issued}}.\n\nOffense: {{offense}}\n\nPrevious Warnings: {{previous_warnings}}\n\nAction Required: {{action}}\n\nConsequences: {{consequences}}\n\nIssued By: {{issued_by}}",
                'category' => 'Warning',
                'is_active' => true
            ],
            [
                'name' => 'Suspension Notice',
                'title' => 'Suspension Notice',
                'content' => "This is to notify {{employee_name}} ({{employee_number}}) that you are suspended from work effective {{suspension_date}}.\n\nReason: {{reason}}\n\nDuration: {{duration}}\n\nTerms: {{terms}}\n\nIssued By: {{issued_by}}",
                'category' => 'Disciplinary',
                'is_active' => true
            ],
            [
                'name' => 'Termination Notice',
                'title' => 'Termination of Employment Notice',
                'content' => "This is to notify {{employee_name}} ({{employee_number}}) that your employment is terminated effective {{termination_date}}.\n\nReason: {{reason}}\n\nFinal Pay: {{final_pay}}\n\nOutstanding Balances: {{balances}}\n\nIssued By: {{issued_by}}",
                'category' => 'Disciplinary',
                'is_active' => true
            ],
            [
                'name' => 'Show Cause Notice',
                'title' => 'Show Cause Notice',
                'content' => "This is a Show Cause Notice issued to {{employee_name}} ({{employee_number}}) on {{date_issued}}.\n\nAllegations: {{allegations}}\n\nYou are required to show cause within {{days}} days why disciplinary action should not be taken.\n\nResponse Due By: {{response_due_date}}\n\nIssued By: {{issued_by}}",
                'category' => 'Disciplinary',
                'is_active' => true
            ],
            [
                'name' => 'Performance Improvement Plan',
                'title' => 'Performance Improvement Plan',
                'content' => "This is a Performance Improvement Plan for {{employee_name}} ({{employee_number}}).\n\nArea of Concern: {{area_of_concern}}\n\nImprovement Goals: {{goals}}\n\nTimeline: {{timeline}}\n\nSupport Provided: {{support}}\n\nReview Dates: {{review_dates}}\n\nIssued By: {{issued_by}}",
                'category' => 'Performance',
                'is_active' => true
            ],
            [
                'name' => 'Attendance and punctuality',
                'title' => 'Attendance and punctuality',
                'content' => 'To address attendance and punctuality concerns.',
                'category' => 'Purpose',
                'is_active' => true,
            ],
            [
                'name' => 'Conduct and policy compliance',
                'title' => 'Conduct and policy compliance',
                'content' => 'To address workplace conduct or policy compliance concerns.',
                'category' => 'Purpose',
                'is_active' => true,
            ],
            [
                'name' => 'Performance improvement',
                'title' => 'Performance improvement',
                'content' => 'To set clear expectations and support improvement in work performance.',
                'category' => 'Purpose',
                'is_active' => true,
            ],
            [
                'name' => 'Repeated lateness',
                'title' => 'Repeated lateness',
                'content' => 'Repeated lateness has been recorded on the dates described below.',
                'category' => 'Reason',
                'is_active' => true,
            ],
            [
                'name' => 'Failure to follow procedure',
                'title' => 'Failure to follow procedure',
                'content' => 'An established work procedure was not followed as described below.',
                'category' => 'Reason',
                'is_active' => true,
            ],
            [
                'name' => 'Performance below expectations',
                'title' => 'Performance below expectations',
                'content' => 'Work performance did not meet the stated expectations as described below.',
                'category' => 'Reason',
                'is_active' => true,
            ],
            [
                'name' => 'Verbal warning',
                'title' => 'Verbal warning',
                'content' => 'Verbal warning',
                'category' => 'Decision',
                'is_active' => true,
            ],
            [
                'name' => 'Written warning',
                'title' => 'Written warning',
                'content' => 'Written warning',
                'category' => 'Decision',
                'is_active' => true,
            ],
            [
                'name' => 'Final written warning',
                'title' => 'Final written warning',
                'content' => 'Final written warning',
                'category' => 'Decision',
                'is_active' => true,
            ],
            [
                'name' => 'No disciplinary action',
                'title' => 'No disciplinary action',
                'content' => 'No disciplinary action',
                'category' => 'Decision',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            MemoTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }

        $this->command->info('Memo templates seeded successfully!');
    }
}
