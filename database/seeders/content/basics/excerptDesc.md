
1.3
TF-IDF and BM25 — the mathematics of keyword relevance

TF-IDF rewards terms that appear often in a document but rarely across the collection. BM25 (Best Match 25) extends this with diminishing returns on term frequency and document-length normalisation. Both remain the baseline every modern ranking model is measured against — and understanding them explains why keyword stuffing has never worked.

Concept
Salton & Buckley, 1988 — TF-IDF Weighting, ACM SIGIR
Robertson & Spärck Jones, 1976 — BM25 origins, J. Documentation
1.4
PageRank — the hyperlink as a vote and the random surfer model

In 1998, Brin and Page made the leap from word-counting to link-counting. PageRank models a "random surfer" who clicks links with probability d (the damping factor, ~0.85) and occasionally jumps to a random page — the probability of ending up on any page is its rank score. A link from a high-PageRank page passes more authority than one from a low-PageRank page. This lesson covers the formula, convergence, and why this changed the web.

Concept
Brin & Page, 1998 — "Anatomy of a Large-Scale Hypertextual Web Search Engine," Computer Networks
Page, Brin, Motwani & Winograd, 1999 — "The PageRank Citation Ranking," Stanford TR
1.5
Hubs and authorities — Kleinberg's HITS algorithm

Published the same year as PageRank, HITS computes two scores per page iteratively: an authority score (pages pointed to by many good hubs) and a hub score (pages that point to many good authorities). The eigenvector update converges to a stable ranking. HITS explains why topical link clusters matter — and why a link from a domain authority in your niche outweighs a generic high-PR link.

Concept
Kleinberg, 1999 — "Authoritative Sources in a Hyperlinked Environment," Journal of the ACM
1.6
The three-stage pipeline — crawl, index, rank as a system

Google officially describes three stages: crawling (URL discovery and page fetching), indexing (analysis and storage), and serving (ranking and result delivery). This lesson treats the pipeline as an engineering system with inputs, processes, queues, and failure modes — not just a list of stages. Understanding the whole system before studying each part prevents the tunnel-vision that most SEO courses suffer from.

Concept
Google Search Central — How Search Works
1.7
From strings to things — Knowledge Graph, Hummingbird, and entity-based search

The 2012 Knowledge Graph and 2013 Hummingbird update marked the transition from keyword matching to entity understanding. Google now models people, places, organisations, and concepts as nodes in a graph — a query about "Einstein" retrieves the entity, not the string. This lesson explains what entity-based search means for content strategy: topic authority replaces keyword density.

Concept
Google — Knowledge Graph announcement, 2012
Google — Hummingbird algorithm update, 2013
1.8
Introduction to learning-to-rank — how ML replaced hand-crafted formulas New ↑

Modern search engines don't hard-code ranking rules — they train machine-learning models on query-document pairs. The learning-to-rank (LTR) field divides into three approaches: pointwise (score each document independently), pairwise (learn which of two documents is better), and listwise (optimise the entire ranked list). RankNet (2005) was the first major neural pairwise model. This lesson introduces the framework that modules 4.4 and 4.5 build on.

Concept
New ↑
Burges et al., 2005 — "Learning to Rank using Gradient Descent," ICML
1.9
Search evaluation metrics — MAP, MRR, NDCG, and why they matter New ↑

Before you can improve a ranking system you need to measure it. Mean Average Precision (MAP) averages precision at every recall level. Mean Reciprocal Rank (MRR) measures how high the first correct result appears. Normalized Discounted Cumulative Gain (NDCG) accounts for graded relevance — a result in position 1 is worth more than position 5. These metrics drive every A/B test at Google and every LambdaRank training objective.

Concept
New ↑
Manning, Raghavan & Schütze, 2008 — Introduction to Information Retrieval, Ch. 8
1.10
Ethics, the business model of search, and what SEO actually is

Brin and Page wrote in 1998 that ad-funded search engines have incentives misaligned with user quality. Google's guidelines explicitly separate organic ranking (algorithmic, unpaid) from ads. This lesson covers the ethical framework of SEO — quality, user experience, long-term trust — and debunks the most persistent myths before they take root. It also sets up Google Search Console as the student's ground-truth monitoring tool.

Concept
Practical
Brin & Page, 1998 — Appendix A: Advertising and Mixed Motives
Google Search Quality Rater Guidelines (public, 176pp)