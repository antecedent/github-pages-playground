<?php

declare(strict_types=1);

function readReleaseMetadata(): array
{
    $metadata = [
        'tag' => getenv('RELEASE_TAG') ?: 'unreleased',
        'name' => getenv('RELEASE_NAME') ?: '',
        'body' => getenv('RELEASE_BODY') ?: '',
        'url' => getenv('RELEASE_URL') ?: '',
        'publishedAt' => getenv('RELEASE_PUBLISHED_AT') ?: '',
        'target' => getenv('RELEASE_TARGET_COMMITISH') ?: '',
    ];

    $eventPath = getenv('GITHUB_EVENT_PATH');
    if (!$eventPath || !is_file($eventPath)) {
        return $metadata;
    }

    $event = json_decode((string) file_get_contents($eventPath), true);
    if (!is_array($event) || !isset($event['release']) || !is_array($event['release'])) {
        return $metadata;
    }

    $release = $event['release'];

    return [
        'tag' => (string) ($release['tag_name'] ?? $metadata['tag']),
        'name' => (string) ($release['name'] ?? $metadata['name']),
        'body' => (string) ($release['body'] ?? $metadata['body']),
        'url' => (string) ($release['html_url'] ?? $metadata['url']),
        'publishedAt' => (string) ($release['published_at'] ?? $metadata['publishedAt']),
        'target' => (string) ($release['target_commitish'] ?? $metadata['target']),
    ];
}
