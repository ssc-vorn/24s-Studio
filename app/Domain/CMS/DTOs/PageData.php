<?php

namespace App\Domain\CMS\DTOs;

final readonly class PageData
{
    public function __construct(
        public string $organizationId,
        public string $title,
        public string $slug,
        public string $status = 'draft',
        public ?string $template = null,
        public bool $isHomepage = false,
        public ?array $metadata = null,
    ) {}

    /** @param array<string,mixed> $data */
    public static function fromArray(array $data, string $organizationId): self
    {
        return new self(
            organizationId: $organizationId,
            title: (string) $data['title'],
            slug: (string) $data['slug'],
            status: (string) ($data['status'] ?? 'draft'),
            template: isset($data['template']) ? (string) $data['template'] : null,
            isHomepage: (bool) ($data['is_homepage'] ?? false),
            metadata: isset($data['metadata']) ? (array) $data['metadata'] : null,
        );
    }
}
