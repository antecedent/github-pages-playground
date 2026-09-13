<?php

declare(strict_types=1);

require __DIR__ . '/release_metadata.php';

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$sourceFile = dirname(__DIR__) . '/source/site.json';
$pagesDirectory = dirname(__DIR__);
$outputFile = $pagesDirectory . '/index.html';

if (!is_file($sourceFile) || !is_readable($sourceFile)) {
    fwrite(STDERR, "Unable to read site data from {$sourceFile}\n");
    exit(1);
}

$sourceJson = file_get_contents($sourceFile);
if ($sourceJson === false) {
    fwrite(STDERR, "Unable to read site data from {$sourceFile}\n");
    exit(1);
}

try {
    $site = json_decode($sourceJson, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    fwrite(STDERR, "Invalid JSON in {$sourceFile}: {$exception->getMessage()}\n");
    exit(1);
}

if (!is_array($site)) {
    fwrite(STDERR, "Site data in {$sourceFile} must decode to an object.\n");
    exit(1);
}

$release = readReleaseMetadata();
$projectName = (string) ($site['projectName'] ?? 'Project');
$description = (string) ($site['description'] ?? '');
$tagline = (string) ($site['tagline'] ?? '');
$releaseTitle = $release['name'] !== '' ? $release['name'] : $release['tag'];
$releaseBody = trim($release['body']);
$escapedProjectName = escapeHtml($projectName);
$escapedDescription = escapeHtml($description);
$escapedTagline = escapeHtml($tagline);
$escapedReleaseTag = escapeHtml($release['tag']);
$escapedReleaseTitle = escapeHtml($releaseTitle);
$escapedReleaseTarget = escapeHtml($release['target']);
$escapedReleasePublishedAt = escapeHtml($release['publishedAt']);
$escapedReleaseBody = escapeHtml($releaseBody);
$releaseUrl = $release['url'] !== ''
    ? '<a href="' . escapeHtml($release['url']) . '">' . escapeHtml($release['url']) . '</a>'
    : 'N/A';

if (!is_dir($pagesDirectory) && !mkdir($pagesDirectory, 0777, true) && !is_dir($pagesDirectory)) {
    fwrite(STDERR, "Unable to create pages directory at {$pagesDirectory}\n");
    exit(1);
}

$html = <<<HTML
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$escapedProjectName}</title>
  <style>
    body { font-family: system-ui, sans-serif; line-height: 1.5; margin: 2rem auto; max-width: 48rem; padding: 0 1rem; }
    code { background: #f3f4f6; padding: 0.1rem 0.3rem; border-radius: 0.25rem; }
    .card { border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 1rem; margin-top: 1.5rem; }
  </style>
</head>
<body>
  <main>
    <h1>{$escapedProjectName}</h1>
    <p>{$escapedDescription}</p>
    <p>{$escapedTagline}</p>
    <div class="card">
      <h2>Latest release</h2>
      <p><strong>Version:</strong> {$escapedReleaseTag}</p>
      <p><strong>Title:</strong> {$escapedReleaseTitle}</p>
      <p><strong>Target:</strong> {$escapedReleaseTarget}</p>
      <p><strong>Published:</strong> {$escapedReleasePublishedAt}</p>
      <p><strong>Release URL:</strong> {$releaseUrl}</p>
      <h3 id="release-notes-heading">Release notes</h3>
      <pre aria-labelledby="release-notes-heading">{$escapedReleaseBody}</pre>
    </div>
  </main>
</body>
</html>
HTML;

if (file_put_contents($outputFile, $html) === false) {
    fwrite(STDERR, "Unable to write generated page to {$outputFile}\n");
    exit(1);
}
