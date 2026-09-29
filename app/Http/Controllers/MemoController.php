<?php

namespace App\Http\Controllers;

use App\Models\DisciplineRecord;
use App\Models\Employee;
use App\Models\MemoTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class MemoController extends Controller
{
    private function companyId(): int
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('manage-discipline'), 403);

        return (int) auth()->user()->getCurrentCompanyId();
    }

    public function index()
    {
        $records = DisciplineRecord::with(['employee', 'template', 'issuedBy'])
            ->whereHas('employee', fn ($query) => $query
                ->where('company_id', $this->companyId())
                ->whereIn('employee_type', auth()->user()->getAllowedEmployeeTypes()))
            ->latest('date_issued')
            ->paginate(15);

        return view('memos.index', compact('records'));
    }

    public function create()
    {
        $employees = Employee::where('company_id', $this->companyId())
            ->whereIn('employee_type', auth()->user()->getAllowedEmployeeTypes())
            ->orderBy('full_name')
            ->get(['id', 'employee_number', 'full_name']);
        $templates = MemoTemplate::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('category')->orWhereNotIn('category', ['Purpose', 'Reason', 'Decision']);
            })
            ->orderBy('name')->get();
        $purposeTemplates = MemoTemplate::where('is_active', true)->where('category', 'Purpose')->orderBy('name')->get();
        $reasonTemplates = MemoTemplate::where('is_active', true)->where('category', 'Reason')->orderBy('name')->get();
        $decisionTemplates = MemoTemplate::where('is_active', true)->where('category', 'Decision')->orderBy('name')->get();

        return view('memos.create', compact('employees', 'templates', 'purposeTemplates', 'reasonTemplates', 'decisionTemplates'));
    }

    public function templates()
    {
        $this->companyId();
        $templateGroups = MemoTemplate::whereIn('category', ['Purpose', 'Reason', 'Decision'])
            ->orderBy('category')->orderBy('name')->get()->groupBy('category');

        return view('memos.templates', compact('templateGroups'));
    }

    public function storeTemplate(Request $request)
    {
        $this->companyId();
        $data = $request->validate([
            'category' => ['required', Rule::in(['Purpose', 'Reason', 'Decision'])],
            'name' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:10000'],
        ]);

        MemoTemplate::create([
            ...$data,
            'title' => $data['name'],
            'content' => $data['content'] ?? '',
            'is_active' => true,
        ]);

        return redirect()->route('memos.templates.index')->with('success', 'Template added.');
    }

    public function updateTemplate(Request $request, MemoTemplate $memoTemplate)
    {
        $this->companyId();
        abort_unless(in_array($memoTemplate->category, ['Purpose', 'Reason', 'Decision'], true), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:10000'],
            'is_active' => ['required', 'boolean'],
        ]);

        $memoTemplate->update([
            ...$data,
            'title' => $data['name'],
            'content' => $data['content'] ?? '',
        ]);

        return redirect()->route('memos.templates.index')->with('success', 'Template updated.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer'],
            'memo_template_id' => ['required', 'integer', 'exists:memo_templates,id'],
            'purpose_template_id' => ['required', 'integer', 'exists:memo_templates,id'],
            'reason_1_template_id' => ['nullable', 'integer', 'exists:memo_templates,id'],
            'reason_2_template_id' => ['nullable', 'integer', 'exists:memo_templates,id'],
            'reason_3_template_id' => ['nullable', 'integer', 'exists:memo_templates,id'],
            'decision_template_id' => ['required', 'integer', 'exists:memo_templates,id'],
            'date_issued' => ['required', 'date'],
            'effectivity_date' => ['nullable', 'date'],
            'offense_description' => ['required', 'string', 'max:10000'],
            'action_taken' => ['required', 'string', 'max:10000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:date_issued'],
            'remarks' => ['nullable', 'string', 'max:10000'],
            'template_values' => ['nullable', 'array'],
            'template_values.*' => ['nullable', 'string', 'max:5000'],
        ]);

        $employee = Employee::where('company_id', $this->companyId())
            ->whereIn('employee_type', auth()->user()->getAllowedEmployeeTypes())
            ->findOrFail($data['employee_id']);
        $template = MemoTemplate::where('is_active', true)->findOrFail($data['memo_template_id']);
        $purpose = $this->templateContent($data['purpose_template_id'], 'Purpose');
        $reason1 = $this->templateContent($data['reason_1_template_id'] ?? null, 'Reason');
        $reason2 = $this->templateContent($data['reason_2_template_id'] ?? null, 'Reason');
        $reason3 = $this->templateContent($data['reason_3_template_id'] ?? null, 'Reason');
        $decision = $this->templateContent($data['decision_template_id'], 'Decision');
        $values = $data['template_values'] ?? [];
        $values = array_merge($values, [
            'employee_name' => $employee->full_name,
            'employee_number' => $employee->employee_number,
            'date_issued' => \Illuminate\Support\Carbon::parse($data['date_issued'])->format('d F Y'),
            'offense' => $data['offense_description'],
            'reason' => $data['offense_description'],
            'allegations' => $data['offense_description'],
            'action' => $data['action_taken'],
            'issued_by' => auth()->user()->name,
            'response_due_date' => $data['follow_up_date'] ?? '',
            'purpose' => $purpose,
            'reason_1' => $reason1,
            'reason_2' => $reason2,
            'reason_3' => $reason3,
            'decision' => $decision,
            'effectivity_date' => isset($data['effectivity_date'])
                ? \Illuminate\Support\Carbon::parse($data['effectivity_date'])->format('d F Y')
                : '',
        ], $values);
        $memoContent = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',
            fn ($match) => (string) ($values[$match[1]] ?? ''),
            $template->content);
        unset($data['template_values']);
        unset(
            $data['purpose_template_id'], $data['reason_1_template_id'], $data['reason_2_template_id'],
            $data['reason_3_template_id'], $data['decision_template_id']
        );
        $data['purpose'] = $purpose;
        $data['reason_1'] = $reason1;
        $data['reason_2'] = $reason2;
        $data['reason_3'] = $reason3;
        $data['decision'] = $decision;

        $record = DisciplineRecord::create([
            ...$data,
            'memo_number' => $this->nextMemoNumber(),
            'issued_by' => auth()->id(),
        ]);

        $pdf = Pdf::loadView('memos.document', [
            'record' => $record->load(['employee.company', 'template', 'issuedBy']),
            'memoContent' => $memoContent,
        ])->setPaper('a4');

        $path = 'memos/' . $record->memo_number . '.pdf';
        Storage::disk('local')->put($path, $pdf->output());
        $record->update(['document_path' => $path]);

        return redirect()->route('memos.index')->with('success', "Memo {$record->memo_number} issued and recorded.");
    }

    public function document(DisciplineRecord $disciplineRecord)
    {
        abort_unless($disciplineRecord->employee()
            ->where('company_id', $this->companyId())
            ->whereIn('employee_type', auth()->user()->getAllowedEmployeeTypes())
            ->exists(), 404);
        abort_unless($disciplineRecord->document_path && Storage::disk('local')->exists($disciplineRecord->document_path), 404);

        return Storage::disk('local')->download($disciplineRecord->document_path, $disciplineRecord->memo_number . '.pdf');
    }

    private function nextMemoNumber(): string
    {
        do {
            $number = 'MEM-' . now()->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        } while (DisciplineRecord::where('memo_number', $number)->exists());

        return $number;
    }

    private function templateContent(?int $templateId, string $category): ?string
    {
        if (!$templateId) {
            return null;
        }

        return MemoTemplate::where('is_active', true)
            ->where('category', $category)
            ->findOrFail($templateId)
            ->content;
    }

}
