<?php

namespace Tests\Unit;

use App\Models\CoachingBatch;
use App\Models\CoachingCourse;
use App\Models\CoachingStudent;
use Tests\TestCase;

class CoachingFeeTest extends TestCase
{
    private function makeStudent(?float $customFee, int $discountPercent, float $courseMonthlyFee): CoachingStudent
    {
        $course = new CoachingCourse();
        $course->monthly_fee = $courseMonthlyFee;

        $batch = new CoachingBatch();
        $batch->setRelation('course', $course);

        $student = new CoachingStudent([
            'custom_fee'       => $customFee,
            'discount_percent' => $discountPercent,
        ]);
        $student->setRelation('batch', $batch);

        return $student;
    }

    public function test_custom_fee_with_discount(): void
    {
        $student = $this->makeStudent(2000, 10, 5000);

        $this->assertSame(1800.0, $student->effectiveFee());
    }

    public function test_null_custom_fee_falls_back_to_course_monthly_fee(): void
    {
        $student = $this->makeStudent(null, 0, 3500);

        $this->assertSame(3500.0, $student->effectiveFee());
    }

    public function test_null_custom_fee_with_discount_applies_to_course_fee(): void
    {
        $student = $this->makeStudent(null, 25, 4000);

        $this->assertSame(3000.0, $student->effectiveFee());
    }
}
