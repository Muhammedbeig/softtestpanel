<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Blog;
use App\Services\PublicContentCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SyncSoftwareTestingArticles extends Command
{
    protected $signature = 'content:sync-software-testing-articles {--force : Allow the sync in production}';

    protected $description = 'Refresh the cited Software Testing Basics articles and their contributor relationships';

    private const ARTICLE_SLUGS = [
        'testing-tools-frameworks',
        'types-of-software-testing',
    ];

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Use --force to sync article content in production.');

            return self::FAILURE;
        }

        $path = database_path('seeders/content/software-testing-basics/articles.json');
        if (! is_file($path)) {
            throw new RuntimeException('Software Testing Basics article export is missing: '.$path);
        }

        $payload = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $articles = collect($payload['articles'] ?? [])->keyBy('slug');
        $authors = Author::query()
            ->whereIn('slug', ['muhammad-baig', 'imdad-ullah-khan-phd', 'muhammad-furquan'])
            ->get()
            ->keyBy('slug');

        foreach (['muhammad-baig', 'imdad-ullah-khan-phd', 'muhammad-furquan'] as $authorSlug) {
            if (! $authors->has($authorSlug)) {
                throw new RuntimeException('Required article contributor is missing: '.$authorSlug);
            }
        }

        DB::transaction(function () use ($articles, $authors): void {
            $authors['muhammad-baig']->update([
                'website_url' => 'https://www.linkedin.com/in/muhammedbeig/',
            ]);
            $authors['imdad-ullah-khan-phd']->update([
                'website_url' => 'https://www.linkedin.com/in/imdadk/',
            ]);
            $authors['muhammad-furquan']->update([
                'website_url' => 'https://www.linkedin.com/in/muhammad-furquan-baig-52bb01305/',
            ]);

            foreach (self::ARTICLE_SLUGS as $slug) {
                $article = $articles->get($slug);
                if (! $article) {
                    throw new RuntimeException('Required article export is missing: '.$slug);
                }

                $blog = Blog::query()->where('slug', $slug)->first();
                if (! $blog) {
                    throw new RuntimeException('Required published article is missing: '.$slug);
                }

                $blog->update([
                    'author_id' => $authors['muhammad-baig']->id,
                    'updated_by_author_id' => $authors['imdad-ullah-khan-phd']->id,
                    'title' => $article['title'],
                    'description' => $article['content'],
                    'excerpt' => $article['subtitle'] ?: $article['description'],
                    'read_time' => $article['readTime'],
                    'updated_on' => $article['updatedOn'],
                    'meta_title' => $article['seoTitle'],
                    'meta_description' => $article['description'] ?: $article['subtitle'],
                ]);

                foreach (['imdad-ullah-khan-phd' => 'reviewer', 'muhammad-furquan' => 'editor'] as $authorSlug => $type) {
                    DB::table('blog_contributors')->updateOrInsert(
                        [
                            'blog_id' => $blog->id,
                            'author_id' => $authors[$authorSlug]->id,
                            'contribution_type' => $type,
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        });

        PublicContentCacheService::invalidate(array_map(
            static fn (string $slug): string => '/articles/'.$slug,
            self::ARTICLE_SLUGS
        ));

        $this->info('Refreshed '.count(self::ARTICLE_SLUGS).' Software Testing Basics articles.');

        return self::SUCCESS;
    }
}
