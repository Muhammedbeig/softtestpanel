<?php

namespace Tests\Unit;

use App\Services\ArticleContentNormalizer;
use Tests\TestCase;

class ArticleContentNormalizerTest extends TestCase
{
    public function test_it_canonicalizes_only_panel_storage_image_paths(): void
    {
        config(['app.url' => 'https://panel.softwaretestingbasics.io']);
        $normalizer = app(ArticleContentNormalizer::class);
        $html = '<p><img src="../../storage/blog/editor/example.avif" alt="Example"></p>'
            .'<p><img src="https://images.example.com/storage/leave-me.jpg" alt="External"></p>';

        $normalized = $normalizer->canonicalizeStorageImages($html);

        $this->assertStringContainsString(
            'src="https://panel.softwaretestingbasics.io/storage/blog/editor/example.avif"',
            $normalized
        );
        $this->assertStringContainsString(
            'src="https://images.example.com/storage/leave-me.jpg"',
            $normalized
        );
        $this->assertSame(1, $normalizer->countRelativeStorageImages($html));
        $this->assertSame(0, $normalizer->countRelativeStorageImages($normalized));
    }

    public function test_removing_a_middle_source_renumbers_sources_and_citations(): void
    {
        config(['app.url' => 'https://panel.softwaretestingbasics.io']);
        $normalizer = app(ArticleContentNormalizer::class);
        $citation = static fn (int $number): string => '<span class="citation-cluster"><a class="citation-ref" href="#source-article-7-'.$number.'"><sup>['.$number.']</sup><span class="citation-popover"><span class="citation-popover-title">Source '.$number.'</span>Old preview</span></a></span>';
        $html = '<p>One'.$citation(1).' two'.$citation(2).' removed'.$citation(3).' four'.$citation(4).' five'.$citation(5).'</p>'
            .'<section class="article-sources"><h2>Sources</h2><ol>'
            .'<li id="source-article-7-1"><p><a href="https://example.com/1">First source</a></p></li>'
            .'<li id="source-article-7-2"><p><a href="https://example.com/2">Second source</a></p></li>'
            .'<li id="source-article-7-4"><p><a href="https://example.com/4">Fourth source</a></p></li>'
            .'<li id="source-article-7-5"><p><a href="https://example.com/5">Fifth source</a></p></li>'
            .'</ol></section>';

        $normalized = $normalizer->normalize($html, 7);

        $this->assertSame(4, substr_count($normalized, 'class="citation-ref"'));
        $this->assertSame(4, substr_count($normalized, '<li id="source-article-7-'));
        foreach (range(1, 4) as $number) {
            $this->assertStringContainsString('id="source-article-7-'.$number.'"', $normalized);
            $this->assertStringContainsString('href="#source-article-7-'.$number.'"', $normalized);
        }
        $this->assertStringNotContainsString('source-article-7-5', $normalized);
        $this->assertStringContainsString('Source 3</span>Fourth source', $normalized);
        $this->assertStringContainsString('Source 4</span>Fifth source', $normalized);
    }

    public function test_it_preserves_a_citation_when_its_source_item_has_no_id(): void
    {
        config(['app.url' => 'https://panel.softwaretestingbasics.io']);
        $normalizer = app(ArticleContentNormalizer::class);
        $html = '<p>Cited fact <span class="citation-cluster"><a class="citation-ref" href="#source-1"><sup>[1]</sup>'
            .'<span class="citation-popover"><span class="citation-popover-title">Source 1</span>Preview</span></a></span></p>'
            .'<section class="article-sources"><h2>Sources</h2><ol>'
            .'<li><p><a href="https://example.com/source">Source without an ID</a></p></li>'
            .'</ol></section>';

        $normalized = $normalizer->normalize($html);

        $this->assertSame(1, substr_count($normalized, 'class="citation-ref"'));
        $this->assertStringContainsString('href="#source-1"', $normalized);
        $this->assertStringContainsString('<li id="source-1">', $normalized);
    }
}
