<?php

namespace Tests\Unit;

use App\Http\Controllers\BlogController;
use App\Models\Blog;
use App\Services\ArticleContentNormalizer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class BlogTranslationPersistenceTest extends TestCase
{
    public function test_clearing_every_translation_field_removes_the_stale_translation(): void
    {
        $originalConnection = config('database.default');
        config([
            'database.default' => 'translation_test',
            'database.connections.translation_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);

        DB::purge('translation_test');

        try {
            Schema::connection('translation_test')->create('blog_translations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('blog_id');
                $table->unsignedBigInteger('language_id');
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->text('tags')->nullable();
                $table->timestamps();
            });

            DB::connection('translation_test')->table('blog_translations')->insert([
                'blog_id' => 7,
                'language_id' => 2,
                'title' => 'Stale title',
                'description' => '<p>Stale translation</p>',
                'tags' => 'stale',
            ]);

            $blog = new Blog();
            $blog->setAttribute('id', 7);
            $request = Request::create('/', 'POST', [
                'languages' => [2],
                'title' => [2 => ''],
                'blog_description' => [2 => '<p><br></p>'],
                'tags' => [2 => []],
            ]);
            $controller = new BlogController(app(ArticleContentNormalizer::class));
            $method = new ReflectionMethod($controller, 'saveTranslations');
            $method->setAccessible(true);
            $method->invoke($controller, $request, $blog);

            $this->assertSame(0, DB::connection('translation_test')->table('blog_translations')->count());
        } finally {
            DB::purge('translation_test');
            config(['database.default' => $originalConnection]);
        }
    }
}
