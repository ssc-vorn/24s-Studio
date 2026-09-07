<?php

namespace App\Http\Controllers\Api\V1\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StoreMediaRequest;
use App\Http\Requests\CMS\UpdateMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request, Organization $organization): mixed
    {
        $this->authorize('viewAny', [Media::class, (string) $organization->getKey()]);

        $query = Media::query()->where('organization_id', $organization->getKey());

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('filename', 'ilike', "%{$search}%")
                    ->orWhere('alt', 'ilike', "%{$search}%");
            });
        }

        if ($mime = trim((string) $request->query('mime_type', ''))) {
            $query->where('mime_type', 'like', $mime . '%');
        }

        $media = $query->latest('created_at')->paginate(min((int) $request->query('per_page', 24), 100));
        $media->getCollection()->transform(fn (Media $item) => $this->withUrl($item));

        return MediaResource::collection($media);
    }

    public function store(StoreMediaRequest $request, Organization $organization): MediaResource
    {
        $this->authorize('create', [Media::class, (string) $organization->getKey()]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = Str::of($file->getClientOriginalName())->basename()->replaceMatches('/[^A-Za-z0-9._-]+/', '-')->trim('-')->value() ?: ('asset.' . $extension);
        $path = 'organizations/' . $organization->getKey() . '/media/' . (string) Str::uuid() . '/' . $filename;
        $disk = config('filesystems.media_disk', config('filesystems.default'));

        Storage::disk($disk)->putFileAs(dirname($path), $file, basename($path), [
            'visibility' => 'public',
            'ContentType' => $file->getMimeType(),
        ]);

        $dimensions = null;
        if (str_starts_with((string) $file->getMimeType(), 'image/') && @getimagesize($file->getRealPath())) {
            $dimensions = @getimagesize($file->getRealPath());
        }

        $media = Media::query()->create([
            'organization_id' => $organization->getKey(),
            'path' => $path,
            'filename' => $filename,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'alt' => $request->validated('alt'),
            'metadata' => $request->validated('metadata', []),
            'created_by' => $request->user()?->getKey(),
        ]);

        return new MediaResource($this->withUrl($media));
    }

    public function update(UpdateMediaRequest $request, Organization $organization, Media $media): MediaResource
    {
        $this->assertScope($organization, $media);
        $this->authorize('update', $media);
        $media->update($request->validated());

        return new MediaResource($this->withUrl($media->refresh()));
    }

    public function destroy(Organization $organization, Media $media): JsonResponse
    {
        $this->assertScope($organization, $media);
        $this->authorize('delete', $media);

        $disk = config('filesystems.media_disk', config('filesystems.default'));
        Storage::disk($disk)->delete($media->path);
        $media->delete();

        return response()->json(null, 204);
    }

    private function assertScope(Organization $organization, Media $media): void
    {
        abort_unless((string) $media->organization_id === (string) $organization->getKey(), 404);
    }

    private function withUrl(Media $media): Media
    {
        $disk = config('filesystems.media_disk', config('filesystems.default'));
        $media->setAttribute('url', Storage::disk($disk)->url($media->path));
        return $media;
    }
}
