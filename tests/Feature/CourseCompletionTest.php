<?php

namespace Tests\Feature;

use App\Models\CourseCompletion;
use App\Models\Import;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Course completions are loaded from the Anaplan Docebo export: one row per person (resource code) per course,
 * attached to a partner and the import that brought them in.
 */
class CourseCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_completion_belongs_to_partner_and_import(): void
    {
        $partner = Partner::factory()->create();
        $import = Import::factory()->create();

        $completion = CourseCompletion::factory()->forPartner($partner->id)->create([
            'import_id' => $import->id,
            'completed_on' => '2026-06-22',
        ]);

        $this->assertTrue($completion->partner->is($partner));
        $this->assertTrue($completion->import->is($import));
        $this->assertSame('2026-06-22', $completion->completed_on->toDateString());
        $this->assertTrue($partner->courseCompletions()->first()->is($completion));
    }

    public function test_same_person_cannot_complete_the_same_course_twice(): void
    {
        CourseCompletion::factory()->create([
            'resource_code' => 'jane@example.com',
            'course_name' => 'Certified Model Builder',
        ]);

        $this->expectException(QueryException::class);

        CourseCompletion::factory()->create([
            'resource_code' => 'jane@example.com',
            'course_name' => 'Certified Model Builder',
        ]);
    }

    public function test_same_person_can_complete_different_courses(): void
    {
        CourseCompletion::factory()->create(['resource_code' => 'jane@example.com', 'course_name' => 'Course A']);
        CourseCompletion::factory()->create(['resource_code' => 'jane@example.com', 'course_name' => 'Course B']);

        $this->assertSame(2, CourseCompletion::where('resource_code', 'jane@example.com')->count());
    }

    public function test_deleting_partner_keeps_completion_without_partner(): void
    {
        $completion = CourseCompletion::factory()->create();

        $completion->partner->delete();

        $this->assertNull($completion->fresh()->partner_id);
    }

    public function test_psm_only_sees_completions_for_their_partners(): void
    {
        $psm = User::factory()->psm()->create();
        $owned = Partner::factory()->create(['user_id' => $psm->id]);
        $other = Partner::factory()->create();

        $visible = CourseCompletion::factory()->forPartner($owned->id)->create();
        CourseCompletion::factory()->forPartner($other->id)->create();

        $this->actingAs($psm);

        $this->assertSame([$visible->id], CourseCompletion::pluck('id')->all());
    }

    public function test_unrestricted_actor_sees_all_completions(): void
    {
        $admin = User::factory()->superAdmin()->create();
        CourseCompletion::factory()->count(3)->create();

        $this->actingAs($admin);

        $this->assertCount(3, CourseCompletion::all());
    }
}
