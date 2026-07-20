<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class SoftwareTestingBasicsSchemaSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [
            'seo_settings_translations', 'seo_settings', 'search_queries', 'newsletter_subscribers',
            'contact_us', 'company_pages', 'article_share_links', 'blog_faqs', 'blog_contributors',
            'blog_attribute_presets', 'blog_translations', 'blogs', 'authors', 'category_translations',
            'categories', 'setting_translations', 'settings', 'languages', 'role_has_permissions',
            'model_has_permissions', 'model_has_roles', 'permissions', 'roles', 'password_resets', 'users',
        ];
        $existingTables = array_values(array_filter($tables, fn (string $table) => Schema::hasTable($table)));

        if (count($existingTables) === count($tables)) {
            return;
        }

        if ($existingTables !== []) {
            throw new RuntimeException(
                'Refusing to initialize a partial Software Testing Basics schema. Existing tables: '.implode(', ', $existingTables)
            );
        }

        Schema::disableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('profile')->nullable();
            $table->string('type')->default('admin');
            $table->string('password');
            $table->string('fcm_id')->nullable();
            $table->boolean('notification')->default(true);
            $table->string('firebase_id')->nullable();
            $table->text('address')->nullable();
            $table->string('country_code', 10)->nullable();
            $table->boolean('show_personal_details')->default(true);
            $table->boolean('is_verified')->default(true);
            $table->boolean('auto_approve_item')->default(false);
            $table->string('region_code')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->boolean('custom_role')->default(false);
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('name_in_english')->nullable();
            $table->string('app_file')->nullable();
            $table->string('panel_file')->nullable();
            $table->string('web_file')->nullable();
            $table->boolean('rtl')->default(false);
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('setting_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setting_id')->constrained('settings')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->longText('translated_value')->nullable();
            $table->timestamps();
            $table->unique(['setting_id', 'language_id']);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->integer('sequence')->default(0);
            $table->string('name');
            $table->string('series_title')->nullable();
            $table->foreignId('parent_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('image')->nullable();
            $table->text('icon')->nullable();
            $table->string('accent_color', 20)->default('#00F4C8');
            $table->string('slug')->unique();
            $table->boolean('status')->default(true);
            $table->boolean('is_coming_soon')->default(false);
            $table->text('description')->nullable();
            $table->text('series_description')->nullable();
            $table->longText('series_content')->nullable();
            $table->boolean('show_in_header_nav')->default(false);
            $table->integer('header_nav_order')->default(0);
            $table->boolean('show_in_mobile_nav')->default(false);
            $table->integer('mobile_nav_order')->default(0);
            $table->string('meta_title', 512)->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_job_category')->default(false);
            $table->boolean('price_optional')->default(false);
            $table->timestamps();
        });

        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['category_id', 'language_id']);
        });

        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('role')->nullable();
            $table->longText('bio')->nullable();
            $table->string('email')->nullable();
            $table->string('avatar')->nullable();
            $table->string('website_url')->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->foreignId('author_id')->nullable()->constrained('authors')->nullOnDelete();
            $table->string('title', 512);
            $table->string('slug', 512)->unique();
            $table->longText('description')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('image', 512)->nullable();
            $table->text('tags')->nullable();
            $table->string('category', 120)->nullable();
            $table->string('read_time', 40)->nullable();
            $table->string('accent_color', 20)->default('#00F4C8');
            $table->json('content_attributes')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status', 40)->default('published');
            $table->timestamp('published_at')->nullable();
            $table->date('updated_on')->nullable();
            $table->foreignId('updated_by_author_id')->nullable()->constrained('authors')->nullOnDelete();
            $table->string('meta_title', 512)->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });

        Schema::create('blog_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title', 512);
            $table->longText('description')->nullable();
            $table->text('tags')->nullable();
            $table->timestamps();
            $table->unique(['blog_id', 'language_id']);
        });

        Schema::create('blog_attribute_presets', function (Blueprint $table) {
            $table->id();
            $table->string('label')->unique();
            $table->string('color', 20)->default('#00F4C8');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('blog_contributors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('authors')->cascadeOnDelete();
            $table->string('contribution_type', 30);
            $table->timestamps();
            $table->unique(['blog_id', 'author_id', 'contribution_type']);
        });

        Schema::create('blog_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();
            $table->string('question', 512);
            $table->longText('answer')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('include_in_schema')->default(true);
            $table->string('schema_question', 512)->nullable();
            $table->longText('schema_answer')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();
        });

        Schema::create('article_share_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();
            $table->string('platform', 30);
            $table->string('code')->unique();
            $table->text('target_url');
            $table->unsignedBigInteger('click_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['blog_id', 'platform']);
        });

        Schema::create('company_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('meta_title', 512)->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('subscribed');
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('search_queries', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            $table->string('page')->nullable();
            $table->string('source')->nullable();
            $table->unsignedInteger('results_count')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_us', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->longText('message');
            $table->timestamps();
        });

        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page')->unique();
            $table->string('title', 512)->nullable();
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('seo_settings_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_setting_id')->constrained('seo_settings')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title', 512)->nullable();
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }
}
