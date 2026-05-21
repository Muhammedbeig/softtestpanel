<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SERIES_SLUG = 'search-engine-basics';

    public function up(): void
    {
        $this->renameArticleSlugs();
        $this->updateArticleMeta();
        $this->removeAdditionalAuthors();
        $this->setArticle10Author();
        $this->updateCategoryMeta();
        $this->replaceEmDashesInHomeArticle();
    }

    public function down(): void
    {
        // Data-only migration — rollback not supported
    }

    private function renameArticleSlugs(): void
    {
        $renames = [
            'what-is-the-vector-space-model-how-documents-become-numbers-and-why-that-changes-everything' => 'vector-space-model',
            'tf-idf-and-bm25-the-mathematics-of-keyword-relevance-and-why-repetition-stops-helping'       => 'tf-idf-bm25-explained',
            'pagerank-how-brin-and-page-replaced-word-counting-with-link-counting'                         => 'pagerank-algorithm-explained',
            'hubs-and-authorities-how-kleinbergs-hits-algorithm-explains-why-niche-links-beat-generic-ones' => 'hits-algorithm-explained',
            'crawl-index-rank-the-search-engine-pipeline-that-decides-whether-your-page-exists-to-google'  => 'crawl-index-rank-pipeline',
            'from-strings-to-things-how-googles-knowledge-graph-and-hummingbird-update-changed-what-relevant-means' => 'knowledge-graph-hummingbird',
            'learning-to-rank-how-machine-learning-replaced-the-200-factor-checklist'                      => 'learning-to-rank',
            'map-mrr-and-ndcg-the-metrics-that-define-what-better-rankings-actually-mean'                  => 'map-mrr-ndcg-explained',
            'the-ethics-of-search-the-business-model-that-funds-it-and-what-seo-actually-is'               => 'seo-ethics-explained',
        ];

        foreach ($renames as $old => $new) {
            $blog = DB::table('blogs')->where('slug', $old)->first();
            if (! $blog) {
                continue;
            }
            DB::table('blogs')->where('id', $blog->id)->update(['slug' => $new]);
            DB::table('article_share_links')
                ->where('blog_id', $blog->id)
                ->update(['target_url' => DB::raw("REPLACE(target_url, '/".addslashes($old)."', '/".addslashes($new)."')")]);
        }
    }

    private function updateArticleMeta(): void
    {
        $meta = [
            'what-is-information-retrieval' => [
                'meta_title'       => 'What is information retrieval? (2026 guide)',
                'meta_description' => 'Information retrieval is the science of finding relevant documents. Learn precision, recall, relevance, and why these concepts explain how search engines work.',
            ],
            'vector-space-model' => [
                'meta_title'       => 'What is the vector space model? (2026 guide)',
                'meta_description' => 'The vector space model turns documents into vectors. Learn cosine similarity, TF weighting, and why this 1975 idea still powers BERT and modern search.',
            ],
            'tf-idf-bm25-explained' => [
                'meta_title'       => 'TF-IDF and BM25: keyword relevance formulas explained',
                'meta_description' => 'TF-IDF scores term specificity; BM25 adds frequency saturation and length bias correction. Learn both formulas and exactly why keyword stuffing fails.',
            ],
            'pagerank-algorithm-explained' => [
                'meta_title'       => 'What is PageRank? How links replaced word-counting (2026)',
                'meta_description' => 'PageRank converts links into weighted votes using the random surfer model. Learn the formula, damping factor, and what the 2024 Google leak confirmed.',
            ],
            'hits-algorithm-explained' => [
                'meta_title'       => 'Hubs and authorities: HITS algorithm explained (2026)',
                'meta_description' => "HITS assigns hub and authority scores to every page. Learn Kleinberg's formula, the TKC effect, and what this means for topical link building strategy.",
            ],
            'crawl-index-rank-pipeline' => [
                'meta_title'       => 'Crawl, index, rank: the search engine pipeline explained',
                'meta_description' => "The crawl-index-rank pipeline is Google's three-stage system. Learn URL discovery, crawl budget, the inverted index, canonicalization, and the serving layer.",
            ],
            'knowledge-graph-hummingbird' => [
                'meta_title'       => 'Google Knowledge Graph and Hummingbird: entities explained',
                'meta_description' => 'The Knowledge Graph (2012) and Hummingbird moved Google from keywords to entities. Learn entity salience, disambiguation, and what topical authority means.',
            ],
            'learning-to-rank' => [
                'meta_title'       => 'Learning to rank: how ML replaced manual ranking formulas',
                'meta_description' => 'Learning-to-rank uses ML to weigh hundreds of signals at once. Learn why hand-crafted formulas failed, the three LTR frameworks, and what it means for SEO.',
            ],
            'map-mrr-ndcg-explained' => [
                'meta_title'       => 'MAP, MRR, and NDCG: search ranking metrics explained (2026)',
                'meta_description' => 'MAP, MRR, and NDCG each model how users scan search results. Learn the formulas, worked examples, and what these metrics mean for your SEO strategy.',
            ],
            'seo-ethics-explained' => [
                'meta_title'       => 'What is SEO? The ethics of search and its business model',
                'meta_description' => 'Brin and Page warned in 1998 that ad-funded search would bias results. Learn the organic-paid wall, the rater guidelines, and what SEO actually means.',
            ],
        ];

        foreach ($meta as $slug => $fields) {
            DB::table('blogs')->where('slug', $slug)->update($fields);
        }
    }

    private function removeAdditionalAuthors(): void
    {
        DB::table('blog_contributors')->where('contribution_type', 'author')->delete();
    }

    private function setArticle10Author(): void
    {
        $furquan = DB::table('authors')->where('slug', 'muhammad-furquan')->first();
        if ($furquan) {
            DB::table('blogs')->where('sort_order', 10)->update(['author_id' => $furquan->id]);
        }
    }

    private function updateCategoryMeta(): void
    {
        $meta = [
            'search-engine-basics' => [
                'meta_title'       => 'Search engine basics: foundations of how search works',
                'meta_description' => 'The Search Engine Basics series covers information retrieval, PageRank, the Knowledge Graph, and learning-to-rank. Start here for structured SEO foundations.',
            ],
            'search-engine-crawling' => [
                'meta_title'       => 'What is search engine crawling? (2026 guide)',
                'meta_description' => 'Search engine crawling is how Google discovers your pages. Learn how Googlebot works, crawl budget, and how to ensure important pages get crawled.',
            ],
            'search-engine-indexing' => [
                'meta_title'       => 'What is search engine indexing? (2026 guide)',
                'meta_description' => 'Search engine indexing is how Google stores and organizes your content. Learn the inverted index, canonicalization, and what determines if a page gets indexed.',
            ],
            'search-engine-ranking' => [
                'meta_title'       => 'What is search engine ranking? (2026 guide)',
                'meta_description' => 'Search engine ranking decides which pages appear first. Learn the signals Google weighs, from PageRank to machine learning, and how to improve your positions.',
            ],
        ];

        foreach ($meta as $slug => $fields) {
            DB::table('categories')->where('slug', $slug)->update($fields);
        }
    }

    private function replaceEmDashesInHomeArticle(): void
    {
        DB::table('settings')
            ->where('name', 'home_main_article_markdown')
            ->update(['value' => DB::raw("REPLACE(`value`, '—', ',')")]);
    }
};
