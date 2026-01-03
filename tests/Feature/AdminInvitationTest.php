<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\AdminInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    // =====================================================
    // List Admin Users and Invitations Tests
    // =====================================================

    public function test_admin_can_list_admin_users_and_invitations(): void
    {
        $superAdmin = User::factory()->create([
            'role' => Role::ADMIN,
            'email' => 'super@admin.com',
        ]);

        // Create another admin user
        User::factory()->create([
            'role' => Role::ADMIN,
            'email' => 'other@admin.com',
        ]);

        // Create a trainer (should not appear in list)
        User::factory()->create([
            'role' => Role::TRAINER,
            'email' => 'trainer@example.com',
        ]);

        // Create pending admin invitations
        AdminInvitation::factory()->count(2)->create(['invited_by' => $superAdmin->id]);

        // Create expired and accepted invitations (should not appear)
        AdminInvitation::factory()->expired()->create(['invited_by' => $superAdmin->id]);
        AdminInvitation::factory()->accepted()->create(['invited_by' => $superAdmin->id]);

        $response = $this->actingAs($superAdmin)
            ->getJson('/api/admin/users');

        $response->assertOk()
            ->assertJsonStructure([
                'users' => [
                    '*' => ['id', 'email', 'firstName', 'lastName', 'role', 'createdAt'],
                ],
                'invitations' => [
                    '*' => ['id', 'email', 'firstName', 'lastName', 'expiresAt', 'createdAt', 'invitedBy'],
                ],
            ])
            ->assertJsonCount(2, 'users') // Only admin users
            ->assertJsonCount(2, 'invitations'); // Only pending invitations
    }

    // =====================================================
    // Create Admin Invitation Tests
    // =====================================================

    public function test_admin_can_invite_new_admin(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/users/invite', [
                'email' => 'newadmin@example.com',
                'firstName' => 'New',
                'lastName' => 'Admin',
            ]);

        $response->assertCreated()
            ->assertJson([
                'message' => 'Invitation sent successfully',
            ])
            ->assertJsonPath('data.email', 'newadmin@example.com')
            ->assertJsonPath('data.firstName', 'New')
            ->assertJsonPath('data.lastName', 'Admin');

        $this->assertDatabaseHas('admin_invitations', [
            'email' => 'newadmin@example.com',
            'first_name' => 'New',
            'last_name' => 'Admin',
        ]);

        Mail::assertSent(\App\Mail\AdminInvitationMail::class);
    }

    public function test_cannot_invite_existing_user(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        User::factory()->create(['email' => 'existing@admin.com']);

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/users/invite', [
                'email' => 'existing@admin.com',
                'firstName' => 'Existing',
                'lastName' => 'User',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_invite_pending_invitation(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        AdminInvitation::factory()->create([
            'email' => 'pending@admin.com',
            'invited_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/users/invite', [
                'email' => 'pending@admin.com',
                'firstName' => 'Pending',
                'lastName' => 'User',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    // =====================================================
    // Resend Admin Invitation Tests
    // =====================================================

    public function test_admin_can_resend_admin_invitation(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $invitation = AdminInvitation::factory()->create([
            'invited_by' => $admin->id,
            'expires_at' => now()->addDays(1),
        ]);

        $originalExpiry = $invitation->expires_at;

        $response = $this->actingAs($admin)
            ->postJson("/api/admin/users/invitations/{$invitation->id}/resend");

        $response->assertOk()
            ->assertJson([
                'message' => 'Invitation resent successfully',
            ]);

        $invitation->refresh();
        $this->assertTrue($invitation->expires_at->gt($originalExpiry));

        Mail::assertSent(\App\Mail\AdminInvitationMail::class);
    }

    public function test_cannot_resend_expired_invitation(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $invitation = AdminInvitation::factory()->expired()->create([
            'invited_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->postJson("/api/admin/users/invitations/{$invitation->id}/resend");

        $response->assertNotFound();
    }

    public function test_cannot_resend_accepted_invitation(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $invitation = AdminInvitation::factory()->accepted()->create([
            'invited_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->postJson("/api/admin/users/invitations/{$invitation->id}/resend");

        $response->assertNotFound();
    }

    // =====================================================
    // Cancel Admin Invitation Tests
    // =====================================================

    public function test_admin_can_cancel_admin_invitation(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $invitation = AdminInvitation::factory()->create(['invited_by' => $admin->id]);

        $response = $this->actingAs($admin)
            ->deleteJson("/api/admin/users/invitations/{$invitation->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Invitation cancelled successfully',
            ]);

        $this->assertDatabaseMissing('admin_invitations', [
            'id' => $invitation->id,
        ]);
    }

    public function test_cannot_cancel_nonexistent_invitation(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        $response = $this->actingAs($admin)
            ->deleteJson('/api/admin/users/invitations/nonexistent-uuid');

        $response->assertNotFound();
    }

    // =====================================================
    // Authorization Tests
    // =====================================================

    public function test_non_admin_cannot_access_admin_user_endpoints(): void
    {
        $trainer = User::factory()->create(['role' => Role::TRAINER]);

        // Test list
        $response = $this->actingAs($trainer)
            ->getJson('/api/admin/users');
        $response->assertForbidden();

        // Test invite
        $response = $this->actingAs($trainer)
            ->postJson('/api/admin/users/invite', [
                'email' => 'test@example.com',
                'firstName' => 'Test',
                'lastName' => 'User',
            ]);
        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_admin_user_endpoints(): void
    {
        $response = $this->getJson('/api/admin/users');
        $response->assertUnauthorized();

        $response = $this->postJson('/api/admin/users/invite', [
            'email' => 'test@example.com',
            'firstName' => 'Test',
            'lastName' => 'User',
        ]);
        $response->assertUnauthorized();
    }

    // =====================================================
    // Token Validation Tests (Public Endpoint)
    // =====================================================

    public function test_can_validate_valid_admin_invitation_token(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $invitation = AdminInvitation::factory()->create([
            'email' => 'newadmin@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'invited_by' => $admin->id,
        ]);

        $response = $this->getJson("/api/admin-invitations/{$invitation->token}");

        $response->assertOk()
            ->assertJson([
                'valid' => true,
                'email' => 'newadmin@example.com',
                'firstName' => 'John',
                'lastName' => 'Doe',
            ])
            ->assertJsonStructure(['expiresAt']);
    }

    public function test_returns_404_for_invalid_admin_token(): void
    {
        $response = $this->getJson('/api/admin-invitations/invalid-token-12345');

        $response->assertNotFound()
            ->assertJson([
                'error' => 'NOT_FOUND',
                'message' => 'Invitation not found',
            ]);
    }

    public function test_returns_error_for_expired_admin_invitation(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $invitation = AdminInvitation::factory()->expired()->create([
            'invited_by' => $admin->id,
        ]);

        $response = $this->getJson("/api/admin-invitations/{$invitation->token}");

        $response->assertBadRequest()
            ->assertJson([
                'error' => 'EXPIRED',
                'message' => 'This invitation has expired',
            ]);
    }

    // =====================================================
    // Accept Admin Invitation Tests
    // =====================================================

    public function test_can_accept_admin_invitation_and_create_account(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $invitation = AdminInvitation::factory()->create([
            'email' => 'newadmin@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'invited_by' => $admin->id,
        ]);

        $response = $this->postJson("/api/admin-invitations/{$invitation->token}/accept", [
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

        // Verify user was created with ADMIN role
        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'role' => Role::ADMIN->value,
            'email_verified' => true,
        ]);

        // Verify invitation was marked as accepted
        $invitation->refresh();
        $this->assertNotNull($invitation->accepted_at);
    }

    public function test_cannot_accept_expired_admin_invitation(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $invitation = AdminInvitation::factory()->expired()->create([
            'invited_by' => $admin->id,
        ]);

        $response = $this->postJson("/api/admin-invitations/{$invitation->token}/accept", [
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

    public function test_validates_password_on_admin_invitation_accept(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $invitation = AdminInvitation::factory()->create([
            'invited_by' => $admin->id,
        ]);

        $response = $this->postJson("/api/admin-invitations/{$invitation->token}/accept", [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
