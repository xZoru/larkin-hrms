<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private array $templates = [
        'Bank / Loan Support' => [
            'old' => "This letter confirms that **{employee_name}** (Employee No. {employee_number}) is employed by **{company_name}** as **{position}**. This is issued in support of a bank or loan application.\n\nYours sincerely,\n____________________________\n**{signatory_name}**\n{signatory_title}\n**{company_name}**",
            'new' => "This letter is to confirm that **{employee_name}** is a permanent and active employee of **{company_name}**, currently holding the position of **{position}**.\n\nThe employee receives a fortnightly gross salary of **K{fortnightly_salary}**, credited directly to their nominated bank account.\n\nThe company has no objection to the employee entering into financial arrangements with your institution. The employee is in good standing with the organisation.\n\nShould you require any further information, please do not hesitate to contact us.",
        ],
        'Visa / Travel Support' => [
            'old' => "This is to confirm that **{employee_name}** (Employee No. {employee_number}) is employed by **{company_name}** as **{position}**. This letter is issued in support of the employee's visa or travel application.\n\nYours sincerely,\n____________________________\n**{signatory_name}**\n{signatory_title}\n**{company_name}**",
            'new' => "This is to confirm that **{employee_name}** is employed by **{company_name}** as **{position}**. The employee is in good standing with the organisation.\n\nWe confirm that **{employee_name}** has been granted approval to travel for the purpose of **[travel purpose]**. The employee is expected to resume duties upon return.\n\nWe confirm that all travel-related costs and expenses will be borne by the employee. Employment status will be maintained throughout the period of travel.\n\nWe respectfully request that the relevant authorities grant **{employee_name}** the necessary visa or travel documentation to facilitate this trip.",
        ],
    ];

    public function up(): void
    {
        foreach ($this->templates as $name => $content) {
            DB::table('memo_templates')
                ->where('category', 'Letter')
                ->where('name', $name)
                ->where('content', $content['old'])
                ->update(['content' => $content['new']]);
        }
    }

    public function down(): void
    {
        foreach ($this->templates as $name => $content) {
            DB::table('memo_templates')
                ->where('category', 'Letter')
                ->where('name', $name)
                ->where('content', $content['new'])
                ->update(['content' => $content['old']]);
        }
    }
};
