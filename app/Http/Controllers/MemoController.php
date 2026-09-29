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

    public function create(Request $request)
    {
        $employees = Employee::where('company_id', $this->companyId())
            ->whereIn('employee_type', auth()->user()->getAllowedEmployeeTypes())
            ->orderBy('full_name')
            ->get(['id', 'employee_number', 'full_name']);
        $purposeTemplates = MemoTemplate::where('is_active', true)->where('category', 'Purpose')->orderBy('name')->get();
        $reasonTemplates = MemoTemplate::where('is_active', true)->where('category', 'Reason')->orderBy('name')->get();
        $decisionTemplates = MemoTemplate::where('is_active', true)->where('category', 'Decision')->orderBy('name')->get();
        $memoRecord = null;
        if ($request->filled('memo')) {
            $memoRecord = DisciplineRecord::with('issuedBy')->findOrFail($request->integer('memo'));
            $this->authorizeRecord($memoRecord);
        }
        $selectedPurposeTemplateId = $memoRecord
            ? $this->findActiveTemplateId('Purpose', $memoRecord->purpose_template_name, $memoRecord->purpose)
            : null;
        $selectedReasonTemplateIds = $memoRecord ? [
            1 => $this->findActiveTemplateId('Reason', $memoRecord->reason_1_template_name, $memoRecord->reason_1),
            2 => $this->findActiveTemplateId('Reason', $memoRecord->reason_2_template_name, $memoRecord->reason_2),
            3 => $this->findActiveTemplateId('Reason', $memoRecord->reason_3_template_name, $memoRecord->reason_3),
        ] : [];
        $selectedDecisionTemplateId = $memoRecord
            ? $this->findActiveTemplateId('Decision', $memoRecord->decision_template_name, $memoRecord->decision)
            : null;

        return view('memos.create', compact(
            'employees', 'purposeTemplates', 'reasonTemplates', 'decisionTemplates', 'memoRecord',
            'selectedPurposeTemplateId', 'selectedReasonTemplateIds', 'selectedDecisionTemplateId'
        ));
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

    public function destroyTemplate(MemoTemplate $memoTemplate)
    {
        $this->companyId();
        abort_unless(in_array($memoTemplate->category, ['Purpose', 'Reason', 'Decision'], true), 404);

        $memoTemplate->delete();

        return redirect()->route('memos.templates.index')->with('success', 'Template deleted.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer'],
            'purpose_template_id' => ['required', 'integer', 'exists:memo_templates,id'],
            'reason_1_template_id' => ['nullable', 'integer', 'exists:memo_templates,id'],
            'reason_2_template_id' => ['nullable', 'integer', 'exists:memo_templates,id'],
            'reason_3_template_id' => ['nullable', 'integer', 'exists:memo_templates,id'],
            'decision_template_id' => ['required', 'integer', 'exists:memo_templates,id'],
            'date_issued' => ['required', 'date'],
            'issuer_name' => ['required', 'string', 'max:255'],
            'effectivity_date' => ['nullable', 'date'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:date_issued'],
        ]);

        $employee = Employee::where('company_id', $this->companyId())
            ->whereIn('employee_type', auth()->user()->getAllowedEmployeeTypes())
            ->findOrFail($data['employee_id']);
        $purposeTemplate = $this->templateRecord($data['purpose_template_id'], 'Purpose');
        $reason1Template = $this->templateRecord($data['reason_1_template_id'] ?? null, 'Reason');
        $reason2Template = $this->templateRecord($data['reason_2_template_id'] ?? null, 'Reason');
        $reason3Template = $this->templateRecord($data['reason_3_template_id'] ?? null, 'Reason');
        $decisionTemplate = $this->templateRecord($data['decision_template_id'], 'Decision');
        $purpose = $purposeTemplate->content ?: $purposeTemplate->name;
        $reason1 = $reason1Template?->content ?: $reason1Template?->name;
        $reason2 = $reason2Template?->content ?: $reason2Template?->name;
        $reason3 = $reason3Template?->content ?: $reason3Template?->name;
        $decision = $decisionTemplate->content ?: $decisionTemplate->name;
        unset(
            $data['purpose_template_id'], $data['reason_1_template_id'], $data['reason_2_template_id'],
            $data['reason_3_template_id'], $data['decision_template_id']
        );
        $data['purpose'] = $purpose;
        $data['reason_1'] = $reason1;
        $data['reason_2'] = $reason2;
        $data['reason_3'] = $reason3;
        $data['decision'] = $decision;
        $data['purpose_template_name'] = $purposeTemplate->name;
        $data['reason_1_template_name'] = $reason1Template?->name;
        $data['reason_2_template_name'] = $reason2Template?->name;
        $data['reason_3_template_name'] = $reason3Template?->name;
        $data['decision_template_name'] = $decisionTemplate->name;
        $data['offense_description'] = '';
        $data['action_taken'] = '';

        $record = DisciplineRecord::create([
            ...$data,
            'memo_template_id' => null,
            'memo_number' => $this->nextMemoNumber(),
            'issued_by' => auth()->id(),
        ]);

        $pdf = Pdf::loadView('memos.document', [
            'record' => $record->load(['employee.company', 'template', 'issuedBy']),
        ])->setPaper('a4');

        $path = 'memos/' . $record->memo_number . '.pdf';
        Storage::disk('local')->put($path, $pdf->output());
        $record->update(['document_path' => $path]);

        return redirect()->route('memos.index')->with('success', "Memo {$record->memo_number} issued and recorded.");
    }

    public function document(DisciplineRecord $disciplineRecord)
    {
        $this->authorizeRecord($disciplineRecord);
        abort_unless($disciplineRecord->document_path && Storage::disk('local')->exists($disciplineRecord->document_path), 404);

        return Storage::disk('local')->download($disciplineRecord->document_path, $disciplineRecord->memo_number . '.pdf');
    }

    public function edit(DisciplineRecord $disciplineRecord)
    {
        $this->authorizeRecord($disciplineRecord);
        return redirect()->route('memos.create', ['memo' => $disciplineRecord->id]);
    }

    public function update(Request $request, DisciplineRecord $disciplineRecord)
    {
        $this->authorizeRecord($disciplineRecord);
        $data = $request->validate([
            'employee_id' => ['required', 'integer'],
            'purpose_template_id' => ['required', 'integer', 'exists:memo_templates,id'],
            'reason_1_template_id' => ['nullable', 'integer', 'exists:memo_templates,id'],
            'reason_2_template_id' => ['nullable', 'integer', 'exists:memo_templates,id'],
            'reason_3_template_id' => ['nullable', 'integer', 'exists:memo_templates,id'],
            'decision_template_id' => ['required', 'integer', 'exists:memo_templates,id'],
            'date_issued' => ['required', 'date'],
            'issuer_name' => ['required', 'string', 'max:255'],
            'effectivity_date' => ['nullable', 'date'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:date_issued'],
        ]);

        $employee = Employee::where('company_id', $this->companyId())
            ->whereIn('employee_type', auth()->user()->getAllowedEmployeeTypes())
            ->findOrFail($data['employee_id']);

        $purposeTemplate = $this->templateRecord($data['purpose_template_id'], 'Purpose');
        $reason1Template = $this->templateRecord($data['reason_1_template_id'] ?? null, 'Reason');
        $reason2Template = $this->templateRecord($data['reason_2_template_id'] ?? null, 'Reason');
        $reason3Template = $this->templateRecord($data['reason_3_template_id'] ?? null, 'Reason');
        $decisionTemplate = $this->templateRecord($data['decision_template_id'], 'Decision');
        unset(
            $data['purpose_template_id'], $data['reason_1_template_id'], $data['reason_2_template_id'],
            $data['reason_3_template_id'], $data['decision_template_id']
        );

        $disciplineRecord->update([
            ...$data,
            'purpose' => $purposeTemplate->content ?: $purposeTemplate->name,
            'reason_1' => $reason1Template?->content ?: $reason1Template?->name,
            'reason_2' => $reason2Template?->content ?: $reason2Template?->name,
            'reason_3' => $reason3Template?->content ?: $reason3Template?->name,
            'decision' => $decisionTemplate->content ?: $decisionTemplate->name,
            'purpose_template_name' => $purposeTemplate->name,
            'reason_1_template_name' => $reason1Template?->name,
            'reason_2_template_name' => $reason2Template?->name,
            'reason_3_template_name' => $reason3Template?->name,
            'decision_template_name' => $decisionTemplate->name,
            'offense_description' => '',
            'action_taken' => '',
        ]);

        $disciplineRecord->load(['employee.company', 'template', 'issuedBy']);
        $pdf = Pdf::loadView('memos.document', ['record' => $disciplineRecord])->setPaper('a4');
        $documentPath = $disciplineRecord->document_path ?: 'memos/' . $disciplineRecord->memo_number . '.pdf';
        Storage::disk('local')->put($documentPath, $pdf->output());
        if (!$disciplineRecord->document_path) {
            $disciplineRecord->update(['document_path' => $documentPath]);
        }

        return redirect()->route('memos.index')->with('success', "Memo {$disciplineRecord->memo_number} updated.");
    }

    public function destroy(DisciplineRecord $disciplineRecord)
    {
        $this->authorizeRecord($disciplineRecord);
        if ($disciplineRecord->document_path) {
            Storage::disk('local')->delete($disciplineRecord->document_path);
        }
        $disciplineRecord->delete();

        return redirect()->route('memos.index')->with('success', 'Memo deleted.');
    }

    private function nextMemoNumber(): string
    {
        do {
            $number = 'MEM-' . now()->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        } while (DisciplineRecord::where('memo_number', $number)->exists());

        return $number;
    }

    private function templateRecord(?int $templateId, string $category): ?MemoTemplate
    {
        if (!$templateId) {
            return null;
        }

        return MemoTemplate::where('is_active', true)
            ->where('category', $category)
            ->findOrFail($templateId);
    }

    private function findActiveTemplateId(string $category, ?string $name, ?string $content): ?int
    {
        $query = MemoTemplate::where('is_active', true)->where('category', $category);
        if ($name) {
            $id = (clone $query)->where('name', $name)->value('id');
            if ($id) {
                return (int) $id;
            }
        }
        if ($content) {
            $id = $query->where('content', $content)->value('id');
            return $id ? (int) $id : null;
        }

        return null;
    }

    private function authorizeRecord(DisciplineRecord $record): void
    {
        abort_unless($record->employee()
            ->where('company_id', $this->companyId())
            ->whereIn('employee_type', auth()->user()->getAllowedEmployeeTypes())
            ->exists(), 404);
    }

}
