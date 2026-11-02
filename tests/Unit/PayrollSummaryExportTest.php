<?php

namespace Tests\Unit;

use App\Exports\PayrollSummaryExport;
use App\Models\PayrollItem;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class PayrollSummaryExportTest extends TestCase
{
    public function test_export_filters_manual_and_zero_hour_items(): void
    {
        $export = new TestablePayrollSummaryExport();
        $items = new Collection([
            $this->item(1, 80, []),
            $this->item(2, 0, []),
            $this->item(3, 0, ['type' => 'manual_entry']),
            $this->item(4, 12, ['type' => 'manual_entry']),
        ]);

        $filtered = $export->filterItems($items);

        $this->assertSame([1], $filtered->pluck('id')->all());
    }

    private function item(int $id, float $hours, array $details): PayrollItem
    {
        $item = new PayrollItem(['hours_worked' => $hours, 'details' => $details]);
        $item->id = $id;

        return $item;
    }
}

class TestablePayrollSummaryExport extends PayrollSummaryExport
{
    public function __construct()
    {
    }

    public function filterItems(Collection $items): Collection
    {
        return $this->filterPayrollItems($items);
    }
}
