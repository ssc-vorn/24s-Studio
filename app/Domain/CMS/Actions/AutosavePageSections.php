<?php

namespace App\Domain\CMS\Actions;

use App\Models\PageSection;
use App\Models\PageVersion;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class AutosavePageSections
{
    /** @param array<int,array<string,mixed>> $sections */
    public function handle(PageVersion $version, int $expectedRevision, array $sections): int
    {
        return DB::transaction(function () use ($version, $expectedRevision, $sections): int {
            $locked = PageVersion::query()->lockForUpdate()->findOrFail($version->getKey());

            if ((int) $locked->revision !== $expectedRevision) {
                throw new ConflictHttpException('The page version changed on the server. Reload before saving again.');
            }

            $ids = collect($sections)->pluck('id')->values();
            $existing = PageSection::query()
                ->where('page_version_id', $locked->getKey())
                ->pluck('id');

            if ($ids->duplicates()->isNotEmpty() || $ids->diff($existing)->isNotEmpty() || $existing->diff($ids)->isNotEmpty()) {
                throw new ConflictHttpException('The builder snapshot does not match the current page version sections.');
            }

            foreach ($sections as $section) {
                if (($section['parent_id'] ?? null) === $section['id']) {
                    throw new ConflictHttpException('A section cannot be its own parent.');
                }

                if (($section['parent_id'] ?? null) !== null && ! $ids->contains($section['parent_id'])) {
                    throw new ConflictHttpException('A section parent must belong to the same page version.');
                }
            }

            foreach ($sections as $section) {
                PageSection::query()
                    ->whereKey($section['id'])
                    ->update([
                        'parent_id' => $section['parent_id'] ?? null,
                        'type' => $section['type'],
                        'variant' => $section['variant'] ?? null,
                        'position' => (int) $section['position'],
                        'content' => $section['content'] ?? [],
                        'styles' => $section['styles'] ?? [],
                        'responsive' => $section['responsive'] ?? [],
                        'animation' => $section['animation'] ?? [],
                        'visibility' => (bool) $section['visibility'],
                    ]);
            }

            $locked->increment('revision');
            return (int) $locked->fresh()->revision;
        });
    }
}
