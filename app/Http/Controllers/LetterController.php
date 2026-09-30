<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Company;
use App\Models\LetterIssuance;
use App\Models\MemoTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LetterController extends Controller
{
    private function companyId(): int
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('manage-discipline'), 403);
        return (int) auth()->user()->getCurrentCompanyId();
    }

    public function create()
    {
        $defaultCompany = Company::find($this->companyId());
        $memoTemplate = MemoTemplate::firstOrCreate(
            ['name' => 'General Memo', 'category' => 'Letter'],
            ['title' => 'General Memo', 'content' => "This memo is issued to {employee_name} (Employee No. {employee_number}).\n\nThis memorandum is provided for official communication and record purposes.\n\nPlease be guided accordingly.", 'is_active' => true]
        );
        if (!str_contains($memoTemplate->content, 'Yours sincerely,')) {
            $memoTemplate->update(['content' => rtrim($memoTemplate->content) . "\n\nYours sincerely,\n____________________________\n**{signatory_name}**\n{signatory_title}\n**{company_name}**"]);
        }
        $employmentTemplate = MemoTemplate::firstOrCreate(
            ['name' => 'Employment Confirmation', 'category' => 'Letter'],
            ['title' => 'Confirmation of Employment', 'content' => "**CONFIRMATION OF EMPLOYMENT**\n\nThis is to confirm that **{employee_name}** is a bona fide employee of **{company_name}**. The employee currently holds the position of **{position}**.\n\n**{employee_name}** is a permanent employee of the organisation and their employment remains active and in good standing as of the date of this letter.\n\nThis letter is issued upon the request of the employee for whatever lawful purpose it may serve.\n\nYours sincerely,\n____________________________\n**{signatory_name}**\n{signatory_title}\n**{company_name}**", 'is_active' => true]
        );
        $employmentContent = str_replace('**E-Lojiks Technology**', '**{signatory_name}**', $employmentTemplate->content);
        $employmentContent = str_replace("Yours sincerely,\n**{signatory_name}**", "Yours sincerely,\n____________________________\n**{signatory_name}**", $employmentContent);
        if ($employmentContent !== $employmentTemplate->content) {
            $employmentTemplate->update(['content' => $employmentContent]);
        }
        MemoTemplate::firstOrCreate(
            ['name' => 'Salary Confirmation', 'category' => 'Letter'],
            ['title' => 'Confirmation of Salary', 'content' => "This is to confirm that **{employee_name}** is currently employed by **{company_name}** as **{position}**.\n\nThe employee's fortnightly gross salary is **K{fortnightly_salary}**. This salary is paid directly into the employee's nominated bank account.\n\nThis letter is issued upon the employee's request for salary confirmation purposes.\n\nYours sincerely,\n____________________________\n**{signatory_name}**\n{signatory_title}\n**{company_name}**", 'is_active' => true]
        );
        MemoTemplate::firstOrCreate(
            ['name' => 'Bank / Loan Support', 'category' => 'Letter'],
            ['title' => 'Letter of Confirmation for Banking Purposes', 'content' => "This letter is to confirm that **{employee_name}** is a permanent and active employee of **{company_name}**, currently holding the position of **{position}**.\n\nThe employee receives a fortnightly gross salary of **K{fortnightly_salary}**, credited directly to their nominated bank account.\n\nThe company has no objection to the employee entering into financial arrangements with your institution. The employee is in good standing with the organisation.\n\nShould you require any further information, please do not hesitate to contact us.", 'is_active' => true]
        );
        MemoTemplate::firstOrCreate(
            ['name' => 'Visa / Travel Support', 'category' => 'Letter'],
            ['title' => 'Letter of Support for Visa Application', 'content' => "This is to confirm that **{employee_name}** is employed by **{company_name}** as **{position}**. The employee is in good standing with the organisation.\n\nWe confirm that **{employee_name}** has been granted approval to travel for the purpose of **[travel purpose]**. The employee is expected to resume duties upon return.\n\nWe confirm that all travel-related costs and expenses will be borne by the employee. Employment status will be maintained throughout the period of travel.\n\nWe respectfully request that the relevant authorities grant **{employee_name}** the necessary visa or travel documentation to facilitate this trip.", 'is_active' => true]
        );
        MemoTemplate::firstOrCreate(
            ['name' => 'Custom Letter', 'category' => 'Letter'],
            ['title' => 'Confirmation Letter', 'content' => "[Write the custom letter content above this sign-off.]\n\nYours sincerely,\n____________________________\n**{signatory_name}**\n{signatory_title}\n**{company_name}**", 'is_active' => true]
        );
        $employees = Employee::where('company_id', auth()->user()->getCurrentCompanyId())->whereIn('employee_type', auth()->user()->getAllowedEmployeeTypes())->with(['position', 'company'])->orderBy('full_name')->get();
        $templates = MemoTemplate::where('category', 'Letter')->where('is_active', true)->orderBy('name')->get();
        return view('memos.letter-create', compact('employees', 'templates', 'defaultCompany'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer'],
            'letter_type' => ['required', Rule::in(['Memo', 'Employment', 'Salary', 'Bank / Loan', 'Visa / Travel', 'Custom Letter'])], 'date_issued' => ['required', 'date'],
            'body' => ['required', 'string', 'max:30000'],
            'signatory_name' => ['required', 'string', 'max:255'], 'signatory_title' => ['nullable', 'string', 'max:255'],
        ]);
        $employee = Employee::where('company_id', $this->companyId())->whereIn('employee_type', auth()->user()->getAllowedEmployeeTypes())->findOrFail($data['employee_id']);
        $templateName = ['Memo' => 'General Memo', 'Employment' => 'Employment Confirmation', 'Salary' => 'Salary Confirmation', 'Bank / Loan' => 'Bank / Loan Support', 'Visa / Travel' => 'Visa / Travel Support', 'Custom Letter' => 'Custom Letter'][$data['letter_type']];
        $template = MemoTemplate::where('category', 'Letter')->where('is_active', true)->where('name', $templateName)->first();
        $body = strtr($data['body'], [
            '{employee_name}' => $employee->full_name,
            '{employee_number}' => $employee->employee_number,
            '{position}' => $employee->position_name ?? '',
            '{salary}' => number_format((float) ($employee->monthly_salary ?? $employee->base_salary ?? 0), 2),
            '{fortnightly_salary}' => number_format((float) ($employee->base_salary ?? 0), 2),
            '{date}' => date('F j, Y', strtotime($data['date_issued'])),
            '{company_name}' => $employee->company?->name ?? config('app.name'),
            '{signatory_name}' => $data['signatory_name'],
            '{signatory_title}' => $data['signatory_title'] ?? '[Title]',
        ]);
        $number = 'LTR-' . now()->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $record = LetterIssuance::create([
            'employee_id' => $employee->id, 'memo_template_id' => $template?->id, 'issued_by' => auth()->id(),
            'letter_number' => $number, 'letter_type' => $data['letter_type'], 'template_name' => $templateName,
            'date_issued' => $data['date_issued'], 'addressed_to' => 'To Whom It May Concern', 'body' => $body,
            'signatory_name' => $data['signatory_name'], 'signatory_title' => $data['signatory_title'] ?? null,
        ]);
        $record->load('employee.company');
        $logoDataUri = $this->companyLogoDataUri($record->employee->company);
        $pdf = Pdf::loadView('memos.letter-document', compact('record', 'logoDataUri'))->setPaper('a4');
        $path = 'letters/' . $number . '.pdf';
        Storage::disk(config('filesystems.memo_disk', 'local'))->put($path, $pdf->output());
        $record->update(['document_path' => $path]);
        return redirect()->route('memos.index')->with('success', "{$data['letter_type']} letter {$number} issued.");
    }

    public function templates(Request $request)
    {
        $this->companyId();
        $data = $request->validate(['category' => ['required', Rule::in(['Letter'])], 'name' => ['required', 'string', 'max:255'], 'content' => ['nullable', 'string', 'max:30000']]);
        MemoTemplate::create(['name' => $data['name'], 'title' => $data['name'], 'content' => $data['content'] ?? '', 'category' => 'Letter', 'is_active' => true]);
        return redirect()->route('memos.templates.index')->with('success', 'Letter template added.');
    }

    public function document(LetterIssuance $letterIssuance)
    {
        $this->companyId();
        abort_unless($letterIssuance->employee()->where('company_id', auth()->user()->getCurrentCompanyId())->whereIn('employee_type', auth()->user()->getAllowedEmployeeTypes())->exists(), 404);
        $letterIssuance->load('employee.company');
        $logoDataUri = $this->companyLogoDataUri($letterIssuance->employee->company);
        $pdf = Pdf::loadView('memos.letter-document', ['record' => $letterIssuance, 'logoDataUri' => $logoDataUri])->setPaper('a4');
        $disk = Storage::disk(config('filesystems.memo_disk', 'local'));
        $path = $letterIssuance->document_path ?: 'letters/' . $letterIssuance->letter_number . '.pdf';
        $disk->put($path, $pdf->output());
        if (!$letterIssuance->document_path) {
            $letterIssuance->update(['document_path' => $path]);
        }
        return $disk->download($path, $letterIssuance->letter_number . '.pdf');
    }

    private function companyLogoDataUri(?Company $company): ?string
    {
        if (!$company?->logo_path) {
            return null;
        }
        $publicRoot = realpath(public_path());
        $logoFile = realpath(public_path(ltrim($company->logo_path, '/\\')));
        if (!$publicRoot || !$logoFile || !str_starts_with($logoFile, $publicRoot) || !is_file($logoFile)) {
            return null;
        }
        $mime = mime_content_type($logoFile) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoFile));
    }
}
