<?php

namespace Tests\Feature\CMS;

use App\Models\Media;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MediaUploadValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_upload_requires_create_permission(): void
    {
        [$user, $organization] = $this->fixture();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/organizations/{$organization->id}/media", [
                'file' => UploadedFile::fake()->image('design.png'),
            ])
            ->assertForbidden();
    }

    public function test_media_upload_rejects_executable_and_svg_content_types(): void
    {
        [$user, $organization] = $this->fixture();
        $this->grant($user, 'media.create');
        Storage::fake('local');

        foreach ([
            UploadedFile::fake()->createWithContent('script.svg', '<svg onload="alert(1)"></svg>'),
            UploadedFile::fake()->createWithContent('shell.php', '<?php echo "owned";'),
        ] as $file) {
            $this->actingAs($user, 'sanctum')
                ->postJson("/api/v1/organizations/{$organization->id}/media", ['file' => $file])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('file');
        }

        $this->assertDatabaseCount('media', 0);
    }

    public function test_media_upload_rewrites_client_extension_to_safe_extension(): void
    {
        [$user, $organization] = $this->fixture();
        $this->grant($user, 'media.create');
        Storage::fake('local');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/organizations/{$organization->id}/media", [
                'file' => UploadedFile::fake()->create('campaign.exe', 10, 'image/png'),
            ])
            ->assertCreated();

        $media = Media::query()->firstOrFail();
        $this->assertSame('png', pathinfo($media->filename, PATHINFO_EXTENSION));
        $this->assertStringEndsWith('.png', $media->path);
        $this->assertTrue(Storage::disk('local')->exists($media->path));
        $response->assertJsonPath('data.filename', $media->filename);
    }

    public function test_media_upload_is_stored_under_the_target_organization_path(): void
    {
        [$user, $organization] = $this->fixture();
        $this->grant($user, 'media.create');
        Storage::fake('local');

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/organizations/{$organization->id}/media", [
                'file' => UploadedFile::fake()->image('hero.png'),
            ])
            ->assertCreated();

        $media = Media::query()->firstOrFail();
        $this->assertStringStartsWith("organizations/{$organization->id}/media/", $media->path);
    }

    private function fixture(): array
    {
        $user = User::factory()->create();
        $organization = Organization::query()->create([
            'name' => 'Media Test Org',
            'slug' => 'media-test-' . uniqid(),
            'status' => 'active',
            'settings' => [],
        ]);
        $organization->users()->attach($user->id, ['is_owner' => false]);

        return [$user, $organization];
    }

    private function grant(User $user, string $permission): void
    {
        Permission::findOrCreate($permission, 'web');
        $user->givePermissionTo($permission);
    }
}
