<?php

namespace Tests\Feature;

use App\Models\CoachingBatch;
use App\Models\CoachingCourse;
use App\Models\CoachingFeeCollection;
use App\Models\CoachingStudent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

class CoachingFeeFlowTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithTenancy;

    private function makeStudent(): CoachingStudent
    {
        return $this->inTenant(function () {
            $course = CoachingCourse::create(['name' => 'Math', 'monthly_fee' => 2000]);
            $batch  = CoachingBatch::create(['course_id' => $course->id, 'name' => 'Morning']);

            return CoachingStudent::create([
                'batch_id'         => $batch->id,
                'name'             => 'Student One',
                'enrollment_date'  => now()->toDateString(),
                'discount_percent' => 10, // effective fee: 2000 - 10% = 1800
                'status'           => 'active',
            ]);
        });
    }

    public function test_fees_index_auto_generates_current_month_fee_rows(): void
    {
        $this->createTenant(['shop_type' => 'coaching']);
        $student = $this->makeStudent();

        $this->actingAs($this->owner)
            ->get($this->tenantUrl('/coaching/fees'))
            ->assertOk();

        $this->inTenant(function () use ($student) {
            $fee = CoachingFeeCollection::where('student_id', $student->id)->first();
            $this->assertNotNull($fee, 'Fee row was not auto-generated');
            $this->assertSame(now()->format('Y-m-01'), $fee->month->format('Y-m-d'));
            $this->assertSame(1800.0, $fee->amount_due); // effectiveFee()
            $this->assertSame(1800.0, $fee->balance_due);
            $this->assertSame('pending', $fee->status);
        });
    }

    public function test_full_payment_marks_fee_paid_with_first_receipt_number(): void
    {
        $this->createTenant(['shop_type' => 'coaching']);
        $student = $this->makeStudent();

        // Generate this month's fee row
        $this->actingAs($this->owner)->get($this->tenantUrl('/coaching/fees'))->assertOk();
        $fee = $this->inTenant(fn () => CoachingFeeCollection::where('student_id', $student->id)->firstOrFail());

        $this->actingAs($this->owner)
            ->post($this->tenantUrl("/coaching/fees/{$fee->id}/collect"), [
                'amount_paid'    => 1800,
                'payment_method' => 'cash',
            ])
            ->assertRedirect();

        $fee = $this->inTenant(fn () => $fee->fresh());
        $this->assertSame('paid', $fee->status);
        $this->assertSame(1800.0, $fee->amount_paid);
        $this->assertSame(0.0, $fee->balance_due);
        $this->assertSame('RCP-0001', $fee->receipt_number);
    }
}
