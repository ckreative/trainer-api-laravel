<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\TrainerInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    // =====================================================
    // Token Validation Tests (GET /api/invitations/{token})
    // =====================================================

    public function test_can_validate_valid_invitation_token(): void
    {
        $invitation = TrainerInvitation::factory()->create([
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $response = $this->getJson("/api/invitations/{$invitation->token}");

        $response->assertOk()
            ->assertJson([
                'valid' => true,
                'email' => 'test@example.com',
                'firstName' => 'John',
                'lastName' => 'Doe',
            ])
            ->assertJsonStructure(['expiresAt']);
    }

    public function test_returns_404_for_invalid_token(): void
    {
        $response = $this->getJson('/api/invitations/invalid-token-12345');

        $response->assertNotFound()
            ->assertJson([
                'error' => 'NOT_FOUND',
                'message' => 'Invitation not found',
            ]);
    }

    public function test_returns_error_for_expired_invitation(): void
    {
        $invitation = TrainerInvitation::factory()->expired()->create();

        $response = $this->getJson("/api/invitations/{$invitation->token}");

        $response->assertBadRequest()
            ->assertJson([
                'error' => 'EXPIRED',
                'message' => 'This invitation has expired',
            ]);
    }

    public function test_returns_error_for_already_accepted_invitation(): void
    {
        $invitation = TrainerInvitation::factory()->accepted()->create();

        $response = $this->getJson("/api/invitations/{$invitation->token}");

        $response->assertBadRequest()
            ->assertJson([
                'error' => 'ALREADY_ACCEPTED',
                'message' => 'This invitation has already been accepted',
            ]);
    }

    // =====================================================
    // Accept Invitation Tests (POST /api/invitations/{token}/accept)
    // =====================================================

    public function test_can_accept_invitation_and_create_account(): void
    {
        $invitation = TrainerInvitation::factory()->create([
            'email' => 'trainer@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        $response = $this->postJson("/api/invitations/{$invitation->token}/accept", [
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'accessToken',
                'tokenType',
                'expiresIn',
                'user' => [
                    'id',
                    'email',
                    'firstName',
                    'lastName',
                    'role',
                ],
            ]);

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => 'trainer@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'role' => Role::TRAINER->value,
            'email_verified' => true,
        ]);

        // Verify invitation was marked as accepted
        $invitation->refresh();
        $this->assertNotNull($invitation->accepted_at);
    }

    public function test_can_accept_invitation_with_custom_name(): void
    {
        $invitation = TrainerInvitation::factory()->create([
            'email' => 'trainer@example.com',
            'first_name' => 'Original',
            'last_name' => 'Name',
        ]);

        $response = $this->postJson("/api/invitations/{$invitation->token}/accept", [
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'firstName' => 'Updated',
            'lastName' => 'Username',
        ]);

        $response->assertOk();

        // Verify user was created with custom name
        $this->assertDatabaseHas('users', [
            'email' => 'trainer@example.com',
            'first_name' => 'Updated',
            'last_name' => 'Username',
        ]);
    }

    public function test_creates_user_with_trainer_role(): void
    {
        $invitation = TrainerInvitation::factory()->create();

        $this->postJson("/api/invitations/{$invitation->token}/accept", [
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', $invitation->email)->first();
        $this->assertEquals(Role::TRAINER, $user->role);
    }

    public function test_returns_auth_token_on_success(): void
    {
        $invitation = TrainerInvitation::factory()->create();

        $response = $this->postJson("/api/invitations/{$invitation->token}/accept", [
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['accessToken', 'tokenType', 'expiresIn']);

        $this->assertNotEmpty($response->json('accessToken'));
    }

    public function test_marks_invitation_as_accepted(): void
    {
        $invitation = TrainerInvitation::factory()->create();

        $this->postJson("/api/invitations/{$invitation->token}/accept", [
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $invitation->refresh();
        $this->assertTrue($invitation->isAccepted());
    }

    public function test_cannot_accept_expired_invitation(): void
    {
        $invitation = TrainerInvitation::factory()->expired()->create();

        $response = $this->postJson("/api/invitations/{$invitation->token}/accept", [
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertBadRequest()
            ->assertJson([
                'error' => 'INVALID_INVITATION',
            ]);

        // Verify no user was created
        $this->assertDatabaseMissing('users', [
            'email' => $invitation->email,
        ]);
    }

    public function test_cannot_accept_already_accepted_invitation(): void
    {
        $invitation = TrainerInvitation::factory()->accepted()->create();

        $response = $this->postJson("/api/invitations/{$invitation->token}/accept", [
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertBadRequest()
            ->assertJson([
                'error' => 'INVALID_INVITATION',
            ]);
    }

    public function test_validates_password_minimum_length(): void
    {
        $invitation = TrainerInvitation::factory()->create();

        $response = $this->postJson("/api/invitations/{$invitation->token}/accept", [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_validates_password_confirmation(): void
    {
        $invitation = TrainerInvitation::factory()->create();

        $response = $this->postJson("/api/invitations/{$invitation->token}/accept", [
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    // =====================================================
    // Admin Invitation Management Tests
    // =====================================================

    public function test_admin_can_create_invitation(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/invitations', [
                'email' => 'newtrainer@example.com',
                'firstName' => 'New',
                'lastName' => 'Trainer',
            ]);

        $response->assertCreated()
            ->assertJson([
                'message' => 'Invitation sent successfully',
            ])
            ->assertJsonPath('data.email', 'newtrainer@example.com')
            ->assertJsonPath('data.firstName', 'New')
            ->assertJsonPath('data.lastName', 'Trainer');

        $this->assertDatabaseHas('trainer_invitations', [
            'email' => 'newtrainer@example.com',
            'first_name' => 'New',
            'last_name' => 'Trainer',
        ]);
    }

    public function test_admin_can_list_pending_invitations(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        // Create some invitations
        TrainerInvitation::factory()->count(3)->create(['invited_by' => $admin->id]);
        TrainerInvitation::factory()->expired()->create(['invited_by' => $admin->id]);
        TrainerInvitation::factory()->accepted()->create(['invited_by' => $admin->id]);

        $response = $this->actingAs($admin)
            ->getJson('/api/admin/invitations');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('total', 3);
    }

    public function test_admin_can_cancel_invitation(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $invitation = TrainerInvitation::factory()->create(['invited_by' => $admin->id]);

        $response = $this->actingAs($admin)
            ->deleteJson("/api/admin/invitations/{$invitation->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Invitation cancelled successfully',
            ]);

        $this->assertDatabaseMissing('trainer_invitations', [
            'id' => $invitation->id,
        ]);
    }

    public function test_admin_can_resend_invitation(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $invitation = TrainerInvitation::factory()->create([
            'invited_by' => $admin->id,
            'expires_at' => now()->addDays(1),
        ]);

        $originalExpiry = $invitation->expires_at;

        $response = $this->actingAs($admin)
            ->postJson("/api/admin/invitations/{$invitation->id}/resend");

        $response->assertOk()
            ->assertJson([
                'message' => 'Invitation resent successfully',
            ]);

        $invitation->refresh();
        $this->assertTrue($invitation->expires_at->gt($originalExpiry));
    }

    public function test_non_admin_cannot_access_invitation_endpoints(): void
    {
        $trainer = User::factory()->create(['role' => Role::TRAINER]);

        // Test create
        $response = $this->actingAs($trainer)
            ->postJson('/api/admin/invitations', [
                'email' => 'test@example.com',
                'firstName' => 'Test',
                'lastName' => 'User',
            ]);
        $response->assertForbidden();

        // Test list
        $response = $this->actingAs($trainer)
            ->getJson('/api/admin/invitations');
        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_admin_endpoints(): void
    {
        $response = $this->postJson('/api/admin/invitations', [
            'email' => 'test@example.com',
            'firstName' => 'Test',
            'lastName' => 'User',
        ]);

        $response->assertUnauthorized();
    }
}
