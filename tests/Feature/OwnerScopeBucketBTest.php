<?php

namespace Tests\Feature;

use App\Models\JourneyAssignment;
use App\Models\SalesforceCredential;
use App\Models\SavedFilter;
use App\Models\StepProgress;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Increment 3, Bucket B: user-owned personal records brought under ScopedToOwner
 * (owner column user_id). Each must be scoped to the owner (+ hierarchy), hide
 * others, and be unrestricted for bypass roles / no actor.
 *
 * @param  class-string<Model>  $model
 */
class OwnerScopeBucketBTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{class-string<Model>}> */
    public static function ownerScopedModels(): array
    {
        return [
            'SavedFilter' => [SavedFilter::class],
            'SalesforceCredential' => [SalesforceCredential::class],
            'StepProgress' => [StepProgress::class],
            'JourneyAssignment' => [JourneyAssignment::class],
        ];
    }

    /** @dataProvider ownerScopedModels */
    public function test_scoped_to_owner(string $model): void
    {
        $me = User::factory()->psm()->create();
        $other = User::factory()->psm()->create();

        $mine = $model::factory()->create(['user_id' => $me->id]);
        $model::factory()->create(['user_id' => $other->id]);

        $this->actingAs($me);
        $rows = $model::all();

        $this->assertCount(1, $rows, "$model should be scoped to its owner");
        $this->assertSame($mine->id, $rows->first()->id);
    }

    /** @dataProvider ownerScopedModels */
    public function test_out_of_scope_owner_sees_nothing(string $model): void
    {
        $me = User::factory()->psm()->create();
        $other = User::factory()->psm()->create();
        $model::factory()->create(['user_id' => $other->id]);

        $this->actingAs($me);

        $this->assertSame(0, $model::count(), "$model must leak nothing across owners");
    }

    /** @dataProvider ownerScopedModels */
    public function test_unrestricted_actor_sees_all(string $model): void
    {
        $admin = User::factory()->superAdmin()->create();
        $model::factory()->count(3)->create();

        $this->actingAs($admin);

        $this->assertCount(3, $model::all());
    }
}
