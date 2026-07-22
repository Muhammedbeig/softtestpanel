<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Services\ArticleContentNormalizer;
use App\Services\PublicContentCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class NormalizeArticleImages extends Command
{
    protected $signature = 'content:normalize-article-images {slug : Exact article slug} {--apply : Persist the scoped repair}';

    protected $description = 'Canonicalize panel storage image URLs for one exact article without changing its text or metadata';

    public function handle(ArticleContentNormalizer $normalizer): int
    {
        $slug = trim((string) $this->argument('slug'));
        $blog = Blog::query()->where('slug', $slug)->first();

        if (! $blog) {
            $this->error("Article not found: {$slug}");
            return self::FAILURE;
        }

        $original = (string) $blog->getRawOriginal('description');
        $relativeImages = $normalizer->countRelativeStorageImages($original);
        $normalized = $normalizer->canonicalizeStorageImages($original);

        $this->line("Article ID: {$blog->id}");
        $this->line("Relative panel images found: {$relativeImages}");

        if ($original === $normalized) {
            $this->info('No image URL repair is needed.');
            return self::SUCCESS;
        }

        if (! $this->option('apply')) {
            $this->warn('Dry run only. Re-run with --apply to persist this one-row repair.');
            return self::SUCCESS;
        }

        $expectedHash = hash('sha256', $original);
        $backupPath = 'backups/article-content/'.$slug.'-'.now()->format('Ymd-His').'.html';
        if (! Storage::disk('local')->put($backupPath, $original)) {
            throw new RuntimeException('Could not write the article backup; refusing to update the database.');
        }

        DB::transaction(function () use ($blog, $slug, $expectedHash, $normalized): void {
            $locked = Blog::query()->whereKey($blog->id)->where('slug', $slug)->lockForUpdate()->firstOrFail();
            $current = (string) $locked->getRawOriginal('description');

            if (! hash_equals($expectedHash, hash('sha256', $current))) {
                throw new RuntimeException('Article changed after the dry-run check; refusing to overwrite it.');
            }

            DB::table('blogs')->where('id', $locked->id)->update(['description' => $normalized]);
        });

        PublicContentCacheService::invalidate();
        $this->info("Repaired {$relativeImages} image URL(s). Backup: storage/app/{$backupPath}");

        return self::SUCCESS;
    }
}
