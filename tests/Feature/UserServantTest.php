<?php

namespace Tests\Feature;

use App\Models\Servant;
use App\Models\User;
use App\Models\UserServant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserServantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeaders(['Origin' => 'http://localhost:3000']);
    }

    public function test_guest_cannot_access_user_servants_endpoints(): void
    {
        $servant = Servant::factory()->create();

        $this->getJson('/api/servants')->assertStatus(401);
        $this->postJson('/api/servants', ['servant_id' => $servant->id])->assertStatus(401);
        $this->patchJson("/api/servants/{$servant->id}", ['level' => 90])->assertStatus(401);
        $this->deleteJson("/api/servants/{$servant->id}")->assertStatus(401);
    }

    public function test_index_returns_only_authenticated_users_servants(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $servant = Servant::factory()->create();
        $otherServant = Servant::factory()->create();

        UserServant::factory()->for($user)->for($servant)->create();
        UserServant::factory()->for($otherUser)->for($otherServant)->create();

        $response = $this->actingAs($user)->getJson('/api/servants');

        $response->assertStatus(200)->assertJsonCount(1);
        $response->assertJsonPath('0.servant_id', $servant->id);
        $response->assertJsonPath('0.servant.name', $servant->name);
    }

    public function test_user_can_add_a_servant_to_their_collection(): void
    {
        $user = User::factory()->create();
        $servant = Servant::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/servants', [
            'servant_id' => $servant->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('servant_id', $servant->id)
            ->assertJsonPath('level', 1)
            ->assertJsonPath('skill_1', 1)
            ->assertJsonPath('bond_level', 0)
            ->assertJsonPath('servant.name', $servant->name);

        $this->assertDatabaseHas('user_servants', [
            'user_id' => $user->id,
            'servant_id' => $servant->id,
        ]);
    }

    public function test_cannot_add_the_same_servant_twice(): void
    {
        $user = User::factory()->create();
        $servant = Servant::factory()->create();

        UserServant::factory()->for($user)->for($servant)->create();

        $this->actingAs($user)->postJson('/api/servants', [
            'servant_id' => $servant->id,
        ])->assertStatus(422)->assertJsonValidationErrors('servant_id');

        $this->assertDatabaseCount('user_servants', 1);
    }

    public function test_cannot_add_a_nonexistent_servant(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/servants', [
            'servant_id' => 999999,
        ])->assertStatus(422)->assertJsonValidationErrors('servant_id');
    }

    public function test_user_can_remove_a_servant_from_their_collection(): void
    {
        $user = User::factory()->create();
        $servant = Servant::factory()->create();

        UserServant::factory()->for($user)->for($servant)->create();

        $this->actingAs($user)
            ->deleteJson("/api/servants/{$servant->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('user_servants', [
            'user_id' => $user->id,
            'servant_id' => $servant->id,
        ]);
    }

    public function test_cannot_remove_a_servant_the_user_does_not_own(): void
    {
        $user = User::factory()->create();
        $servant = Servant::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/servants/{$servant->id}")
            ->assertStatus(404);
    }

    public function test_cannot_remove_another_users_servant(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $servant = Servant::factory()->create();

        UserServant::factory()->for($otherUser)->for($servant)->create();

        $this->actingAs($user)
            ->deleteJson("/api/servants/{$servant->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('user_servants', [
            'user_id' => $otherUser->id,
            'servant_id' => $servant->id,
        ]);
    }

    public function test_user_can_update_growth_status_of_owned_servant(): void
    {
        $user = User::factory()->create();
        $servant = Servant::factory()->create();
        UserServant::factory()->for($user)->for($servant)->create();

        $response = $this->actingAs($user)->patchJson("/api/servants/{$servant->id}", [
            'level' => 120,
            'skill_1' => 10,
            'skill_2' => 9,
            'skill_3' => 1,
            'np_level' => 5,
            'bond_level' => 16,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('level', 120)
            ->assertJsonPath('skill_2', 9)
            ->assertJsonPath('np_level', 5)
            ->assertJsonPath('bond_level', 16)
            ->assertJsonPath('servant.id', $servant->id);

        $this->assertDatabaseHas('user_servants', [
            'user_id' => $user->id,
            'servant_id' => $servant->id,
            'level' => 120,
            'skill_1' => 10,
            'skill_2' => 9,
            'skill_3' => 1,
            'np_level' => 5,
            'bond_level' => 16,
        ]);
    }

    public function test_update_only_changes_sent_fields_and_ignores_ascension(): void
    {
        $user = User::factory()->create();
        $servant = Servant::factory()->create();
        UserServant::factory()->for($user)->for($servant)->create([
            'level' => 80,
            'skill_1' => 4,
            'ascension' => 0,
        ]);

        $this->actingAs($user)
            ->patchJson("/api/servants/{$servant->id}", ['level' => 90, 'ascension' => 4])
            ->assertStatus(200)
            ->assertJsonPath('level', 90)
            ->assertJsonPath('skill_1', 4)
            ->assertJsonPath('ascension', 0);
    }

    public function test_update_validates_growth_status_ranges(): void
    {
        $user = User::factory()->create();
        $servant = Servant::factory()->create();
        UserServant::factory()->for($user)->for($servant)->create();

        $this->actingAs($user)
            ->patchJson("/api/servants/{$servant->id}", [
                'level' => 121,
                'skill_1' => 0,
                'skill_2' => 11,
                'skill_3' => 'abc',
                'np_level' => 6,
                'bond_level' => -1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['level', 'skill_1', 'skill_2', 'skill_3', 'np_level', 'bond_level'])
            ->assertJsonPath('errors.level.0', 'レベルは1〜120の範囲で入力してください。');
    }

    public function test_cannot_update_a_servant_the_user_does_not_own(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $servant = Servant::factory()->create();
        UserServant::factory()->for($otherUser)->for($servant)->create(['level' => 1]);

        $this->actingAs($user)
            ->patchJson("/api/servants/{$servant->id}", ['level' => 90])
            ->assertStatus(404);

        $this->assertDatabaseHas('user_servants', [
            'user_id' => $otherUser->id,
            'servant_id' => $servant->id,
            'level' => 1,
        ]);
    }
}
