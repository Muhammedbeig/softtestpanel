<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Blog;
use App\Models\BlogFaq;
use App\Models\Category;
use App\Models\SeoSetting;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SoftwareTestingBasicsSeeder extends Seeder
{
    private const ACCENT = '#00F4C8';

    public function run(): void
    {
        if (app()->environment('production') && Blog::query()->exists()) {
            return;
        }

        $articles = $this->articles();
        $authors = $this->seedAuthors();
        $categories = $this->seedCategories($articles);
        $this->seedSettings();
        $this->seedHomeSeo();
        $this->seedArticles($articles, $authors, $categories);
        $this->seedPermissions();
    }

    private function articles(): array
    {
        $path = database_path('seeders/content/software-testing-basics/articles.json');
        if (! is_file($path)) {
            throw new RuntimeException('Software Testing Basics article export is missing: '.$path);
        }

        $payload = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        return $payload['articles'] ?? [];
    }

    private function seedAuthors(): array
    {
        $definitions = [
            'muhammad-baig' => [
                'name' => 'Muhammad Baig',
                'role' => 'Software Engineer & Technical Reviewer',
                'website_url' => 'https://www.linkedin.com/in/muhammedbeig/',
                'image' => 'Muhammad Baig.jpg',
                'bio' => 'Muhammad Baig is a software engineer who reviews testing guidance, automation examples, technical claims, and practical QA workflows for correctness and reproducibility.',
            ],
            'imdad-ullah-khan-phd' => [
                'name' => 'Imdad Ullah Khan, Ph.D.',
                'role' => 'Data Science & ML Researcher | Content Evaluator & Approver',
                'website_url' => 'https://www.linkedin.com/in/imdadk/',
                'image' => 'Imdad Ullah Khan, Ph.D..jpg',
                'bio' => 'Imdad Ullah Khan holds a Ph.D. in Computer Science from Rutgers University and evaluates technical material for accuracy, depth, methodological soundness, and intellectual honesty.',
            ],
            'muhammad-furquan' => [
                'name' => 'Muhammad Furquan',
                'role' => 'Legal & Compliance Reviewer',
                'website_url' => 'https://www.linkedin.com/in/muhammad-furquan-baig-52bb01305/',
                'image' => 'Muhammad Furquan, Barrister.jpg',
                'bio' => 'Muhammad Furquan is a qualified barrister who reviews published material for copyright, compliance, consumer-protection, and digital-publishing concerns.',
            ],
        ];

        $authors = [];
        foreach ($definitions as $slug => $definition) {
            $avatar = $this->copyAuthorAsset($definition['image']);
            $authors[$slug] = Author::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'role' => $definition['role'],
                    'bio' => $definition['bio'],
                    'avatar' => $avatar,
                    'website_url' => $definition['website_url'],
                    'social_links' => ['linkedin' => $definition['website_url']],
                    'status' => true,
                ]
            );
        }

        Author::where('slug', 'search-engine-basics-team')->update([
            'name' => 'Software Testing Basics Editorial Team',
            'slug' => 'software-testing-basics-team',
            'role' => 'Editorial Team',
            'bio' => 'The Software Testing Basics editorial team maintains the testing knowledge base and its practical examples.',
            'status' => false,
        ]);

        return $authors;
    }

    private function seedCategories(array $articles): array
    {
        $categories = [];
        foreach ($articles as $article) {
            $name = $article['cat'] ?? 'Fundamentals';
            if (isset($categories[$name])) {
                continue;
            }

            $categories[$name] = Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'series_title' => $name,
                    'accent_color' => $article['catColor'] ?? self::ACCENT,
                    'status' => true,
                    'is_coming_soon' => false,
                    'show_in_header_nav' => true,
                    'show_in_mobile_nav' => true,
                    'header_nav_order' => count($categories) + 1,
                    'mobile_nav_order' => count($categories) + 1,
                    'description' => $name.' articles from Software Testing Basics.',
                    'series_description' => $name.' articles from Software Testing Basics.',
                    'meta_title' => $name.' Software Testing Guides',
                    'meta_description' => 'Read Software Testing Basics articles about '.Str::lower($name).'.',
                ]
            );
        }

        return $categories;
    }

    private function seedSettings(): void
    {
        $settings = [
            'company_name' => ['Software Testing Basics', 'string'],
            'website_url' => ['https://softwaretestingbasics.io', 'string'],
            'company_email' => ['hello@softwaretestingbasics.io', 'string'],
            'mail_from_address' => ['hello@softwaretestingbasics.io', 'string'],
            'depp_link_scheme' => ['softwaretestingbasics', 'string'],
            'web_theme_color' => [self::ACCENT, 'string'],
        ];

        foreach ($settings as $name => [$value, $type]) {
            Setting::updateOrCreate(['name' => $name], ['value' => $value, 'type' => $type]);
        }
    }

    private function seedHomeSeo(): void
    {
        SeoSetting::updateOrCreate(
            ['page' => 'home'],
            [
                'title' => 'Software Testing Basics: Practical QA Guides',
                'description' => 'Learn software testing fundamentals, test types, techniques, automation tools, QA processes, and practical quality-engineering workflows.',
                'keywords' => '',
            ]
        );
    }

    private function seedPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = [
            'blog-list', 'blog-create', 'blog-update', 'blog-delete',
            'author-list', 'author-create', 'author-update', 'author-delete',
            'category-list', 'category-create', 'category-update', 'category-delete',
            'company-page-list', 'company-page-create', 'company-page-update', 'company-page-delete',
            'settings-update', 'seo-setting-list', 'seo-setting-create', 'seo-setting-update', 'seo-setting-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findByName('Super Admin', 'web')->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function seedArticles(array $articles, array $authors, array $categories): void
    {
        foreach ($articles as $article) {
            $categoryName = $article['cat'] ?? 'Fundamentals';
            $category = $categories[$categoryName] ?? null;
            $blog = Blog::updateOrCreate(
                ['slug' => $article['slug']],
                [
                    'category_id' => $category?->id,
                    'sort_order' => (int) ($article['sortOrder'] ?? 0),
                    'author_id' => $authors['muhammad-baig']->id,
                    'title' => $article['title'],
                    'description' => $article['content'],
                    'excerpt' => $article['subtitle'] ?: $article['description'],
                    'image' => $this->copyArticleAsset($article['heroImage'] ?? null),
                    'tags' => $article['tags'] ?? [$categoryName],
                    'category' => $categoryName,
                    'read_time' => $article['readTime'] ?? $this->estimateReadTime($article['content']),
                    'accent_color' => $article['catColor'] ?? self::ACCENT,
                    'content_attributes' => [
                        ['label' => $categoryName, 'color' => $article['catColor'] ?? self::ACCENT],
                    ],
                    'is_featured' => (bool) ($article['isFeatured'] ?? false),
                    'status' => 'published',
                    'published_at' => Carbon::parse($article['publishedAt'] ?? now()),
                    'updated_on' => $article['updatedOn'] ?? now()->toDateString(),
                    'updated_by_author_id' => $authors['imdad-ullah-khan-phd']->id,
                    'meta_title' => $article['seoTitle'] ?? $article['title'],
                    'meta_description' => $article['description'] ?? $article['subtitle'],
                ]
            );

            DB::table('blog_contributors')->where('blog_id', $blog->id)->delete();
            foreach (['imdad-ullah-khan-phd' => 'reviewer', 'muhammad-furquan' => 'editor'] as $slug => $type) {
                DB::table('blog_contributors')->insert([
                    'blog_id' => $blog->id,
                    'author_id' => $authors[$slug]->id,
                    'contribution_type' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            BlogFaq::where('blog_id', $blog->id)->delete();
            foreach ($article['faqs'] ?? [] as $index => $faq) {
                BlogFaq::create([
                    'blog_id' => $blog->id,
                    'question' => $faq['question'],
                    'answer' => $faq['answer'],
                    'sort_order' => $faq['sortOrder'] ?? $index,
                    'is_visible' => true,
                    'include_in_schema' => $faq['includeInSchema'] ?? true,
                    'schema_question' => $faq['schemaQuestion'] ?? null,
                    'schema_answer' => $faq['schemaAnswer'] ?? null,
                    'options' => [],
                ]);
            }
        }
    }

    private function copyAuthorAsset(string $fileName): ?string
    {
        $source = database_path('seeders/content/basics/assets/authors/'.$fileName);
        if (! is_file($source)) {
            return null;
        }

        $destination = 'authors/'.$fileName;
        Storage::disk('public')->put($destination, file_get_contents($source));
        return $destination;
    }

    private function copyArticleAsset(?string $publicPath): ?string
    {
        if (! $publicPath || ! str_starts_with($publicPath, '/images/')) {
            return null;
        }

        $relativePath = Str::after($publicPath, '/images/');
        $source = database_path('seeders/content/software-testing-basics/assets/articles/'.$relativePath);
        if (! is_file($source)) {
            return null;
        }

        $destination = 'blog/'.$relativePath;
        Storage::disk('public')->put($destination, file_get_contents($source));
        return $destination;
    }

    private function estimateReadTime(string $html): string
    {
        $words = str_word_count(strip_tags($html));
        return max(1, (int) ceil($words / 220)).' min';
    }
}
