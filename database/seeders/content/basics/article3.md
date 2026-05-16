TF-IDF and BM25: The Mathematics of Keyword Relevance (And Why Repetition Stops Helping)

Meta description: TF-IDF scores how specific a term is to a document. BM25 fixes two problems TF-IDF never solved: term frequency saturation and document length bias. Both explain exactly why keyword stuffing fails mathematically. (159 chars)

Series: How Search Engines Work | Article 3 of 10 | Module: Search Engine Fundamentals
Previous article: What Is the Vector Space Model? How Documents Become Numbers (and Why That Changes Everything)

---

In article 1.2, you learned that the Vector Space Model gives each term in a document a weight using TF-IDF scores, then measures relevance by the angle between vectors. This article explains in detail how TF-IDF calculates those weights, where the formula fails, and how BM25 (Best Matching 25) fixes the two biggest problems. Understanding these two methods clearly shows why search engines rank results as they do, and why repeating a keyword many times adds almost no extra ranking benefit. This article covers the TF-IDF formula, its known weaknesses, the BM25 formula and its two settings, a worked example, and what BM25F means for how your page title is weighted differently from your body text.

---

What Is TF-IDF, and What Problem Did It Solve?

TF-IDF (Term Frequency times Inverse Document Frequency) is a way to give each word in a document a score showing how well that word describes this document compared to all others. It was officially described by Gerard Salton and Christopher Buckley in a 1988 paper, based on ideas Karen Spärck Jones introduced in 1972.

The one-sentence definition: TF-IDF assigns a term a high weight when it appears often in this specific document but rarely across the entire collection, rewarding specificity rather than frequency alone.

The formula has two components that are multiplied together:

Column 1	Column 2	Column 3
Component	What it measures	Formula (simplified)
TF (Term Frequency)	How often the term appears in this document	term count in document / total terms in document
IDF (Inverse Document Frequency)	How rare the term is across the entire collection	log(total documents / documents containing the term)


Multiplying the two parts is the key idea. A word that shows up 20 times in a document (high TF) but appears in every document in the collection (very low IDF) gets a near-zero TF-IDF score. The word “the” is a classic example. A word that appears 5 times in a document but only in 0.1% of all documents gets a very high TF-IDF score because both parts are strong.

Who Actually Invented IDF?

Most explanations credit TF-IDF to Salton, but the IDF component, the part that actually makes the formula discriminative, was introduced independently by Karen Spärck Jones in a 1972 paper titled “A statistical interpretation of term specificity and its application in retrieval,” published in the Journal of Documentation. Spärck Jones recognized that a term’s usefulness as a retrieval signal depended on how many documents it appeared in, not just how many times it appeared in one document. Her specificity measure is what the field later called IDF.

Robertson and Spärck Jones then built their Probabilistic Relevance Framework on this foundation through the 1970s and 1980s, which eventually became BM25. IDF, as a concept, is at least three years older than TF-IDF as a formula, and it predates BM25 by nearly two decades.

---

Why Did TF-IDF Score Poorly on Long Documents?

TF-IDF has two structural weaknesses that matter enormously for real-world search.

Problem 1: Linear Term Frequency Growth

In TF-IDF, relevance increases directly with how often a word appears. A document that says “machine learning” 40 times scores twice as high as one that says it 20 times. But in reality, the 21st time a word appears adds almost no extra value. A 500-word focused article on machine learning is more helpful to most readers than a 5,000-word article that mentions it 40 times but covers many other topics. TF-IDF can’t distinguish between these cases.

Your initial instinct might be that this is what keyword stuffing exploited: repeat the term more often, score higher. This worked briefly on early web search engines because they had shallow implementations of TF-IDF. Once proper IDF weighting was in place, stuffing a common term raised TF, but IDF stayed near zero. The product barely moved. Stuffing a rare term raised TF, but that rarely reflected genuine relevance.

Problem 2: No Document Length Correction

A 10,000-word document about “deep learning” will almost always have more mentions of “deep learning” than a 400-word definition. TF-IDF does not correct for this. The longer document gets an unfair edge, not because it is more relevant but just because it is longer. Someone looking for a short definition would prefer the 400-word document, but TF-IDF would rank the 10,000-word document higher based solely on term count.

These two problems, unbounded frequency growth and length bias, are precisely what BM25 was built to correct.

---

What Is BM25, and Where Did the “25” Come From?

BM25 (Best Matching 25) is a probabilistic ranking function built on the Probabilistic Relevance Framework developed by Stephen Robertson and Karen Spärck Jones between the 1970s and 1990s. The full name is Okapi BM25, where “Okapi” refers to the retrieval system at City University London that first implemented it in TREC competitions during the 1990s.

The “25” is not a count of algorithm iterations, as one popular misconception holds. It is the 25th numbered model in a series of empirical experiments run by the Robertson-Spärck Jones research group. Earlier formulations in the family (BM11, BM15, and others) were tested and found to perform worse. BM25 emerged as the most effective combination of components and was given the next number in the sequence. The number has no other special significance.

BM25 is currently the default ranking function in Apache Lucene (which powers Elasticsearch, Solr, and OpenSearch), the search infrastructure used by Amazon, Netflix, Wikipedia, and thousands of enterprise search deployments worldwide.

---

How Does the BM25 Formula Work?

BM25 solves both TF-IDF problems by adding two mechanisms: a saturation function for term frequency and a document length normalizer.

The BM25 score for a single query term t in document d is:

BM25(t, d) = IDF(t) × [ TF(t,d) × (k1 + 1) ] / [ TF(t,d) + k1 × (1 - b + b × (|d| / avgdl)) ]

Where:

* IDF(t) is the inverse document frequency (how rare the term is across the collection)
* TF(t,d) is the raw term count in document d
* |d| is the length of document d in tokens
* avgdl is the average document length across the entire collection
* k1 is the term frequency saturation parameter (default: 1.2)
* b is the document length normalization parameter (default: 0.75)

The full query score is the sum of BM25 scores across all terms in the query.

What k1 Does: The Saturation Curve

The k1 setting controls how fast repeated words stop adding value. With k1 = 1.2 (the usual default), the BM25 score for a word rises quickly with the first few times it appears, then levels off. After a while, the 20th time adds almost no more than the 5th.

To see this numerically, compare how TF-IDF and BM25 score a term that appears 1, 5, 10, and 40 times in a document (holding IDF constant at 1.0):

Column 1	Column 2	Column 3
TF (occurrences)	TF-IDF score	BM25 score (k1=1.2)
1	1.00	0.91
5	5.00	1.65
10	10.00	1.83
40	40.00	1.97


Notice that in TF-IDF, going from 1 to 40 occurrences multiplies the score by 40x. In BM25 with k1 = 1.2, the same jump only doubles the score. The 40th occurrence is worth almost nothing compared to the 5th. This is the saturation function in action, and this is the mathematical proof that keyword repetition beyond a natural density stops generating ranking benefit.

What b Does: Document Length Normalization

The b setting controls how much a document’s length, compared to the average, changes its score. When b = 0.75 (the default), the formula lowers the term count if the document is longer than average and raises it if it is shorter.

In practice, a document that mentions your search term three times in 300 words scores higher than one that mentions it three times in 3,000 words because the term appears more densely. This directly fixes the length bias that TF-IDF did not handle.

Column 1	Column 2	Column 3	Column 4
Parameter	Default	Effect at max value	Effect at zero
k1	1.2	Term frequency has more influence (higher ceiling)	All TF collapsed to a constant; only IDF matters
b	0.75	Full document length normalization applied	Document length ignored; only raw TF counts


---

A Worked Example: Comparing TF-IDF and BM25 on the Same Documents

Suppose a collection has 10,000 documents. The term “neural network” appears in 200 of them.

The IDF for “neural network”:

* TF-IDF IDF: log(10,000 / 200) = log(50) ≈ 3.91
* BM25 IDF: log((10,000 - 200 + 0.5) / (200 + 0.5)) ≈ log(49.5) ≈ 3.90

Now consider two documents matching a query for “neural network”:

* Document A: “neural network” appears 8 times, document length 400 words (focused article)
* Document B: “neural network” appears 12 times, document length 4,000 words (broad survey)
* Average document length in collection: 600 words

TF-IDF scores (raw TF × IDF, simplified):

* Document A: (8/400) × 3.91 = 0.078
* Document B: (12/4000) × 3.91 = 0.012

In this case, TF-IDF happens to rank Document A higher, but only because the raw TF/length ratio works out. If Document B had 120 occurrences, TF-IDF would rank it first with a score of 0.117, even though those 120 occurrences in a 4,000-word survey may represent a weaker topical focus than 8 in a 400-word article.

BM25 scores (with k1=1.2, b=0.75):

* Document A: 3.90 × [8 × 2.2] / [8 + 1.2 × (0.25 + 0.75 × 400/600)] = 3.90 × 17.6 / 9.3 ≈ 7.38
* Document B: 3.90 × [12 × 2.2] / [12 + 1.2 × (0.25 + 0.75 × 4000/600)] = 3.90 × 26.4 / 18.2 ≈ 5.66

BM25 ranks Document A higher (7.38 vs. 5.66) because the length normalization correctly penalizes Document B’s lower occurrence density. Notice how BM25 rewards the shorter, more focused document even though Document B has more absolute occurrences of the query term.

This is what the mathematics of BM25 means for content strategy: a focused, specific document on one topic scores higher than a long document that covers many topics and mentions each one proportionally fewer times.

---

What Is BM25F, and Why Does Title Text Score Differently From Body Text?

Standard BM25 treats an entire document as a single stream of text. A term in the page title gets the same weight as a term buried in paragraph 47. This does not reflect how search engines actually work.

BM25F (BM25 with Extension to Multiple Weighted Fields) is a variant introduced by Robertson, Zaragoza, and Taylor in 2004. It treats each document as a set of distinct fields, such as title, body, URL, and anchor text, and applies separate k1, b, and weight parameters to each field.

The formula combines field scores into a single pseudo-TF before computing the final BM25 score:

pseudo-TF(t, d) = sum over fields [ weight_f × TF(t, field_f) / length_normalization_f ]

This has a direct SEO implication that most articles about TF-IDF never explain: a keyword in your page title is weighted with a higher field weight than the same keyword in body text. The exact multipliers are proprietary to Google’s implementation, but the mathematical structure of BM25F is publicly documented and confirmed. This is why keyword presence in the HTML title tag, H1, and meta description carries more relevance signal per occurrence than the same keyword in paragraph text.

---

Frequently Asked Questions

What is the difference between TF-IDF and BM25?

TF-IDF multiplies raw term frequency by inverse document frequency. It grows linearly with term frequency and does not adjust for document length. BM25 uses the same IDF component but adds a saturation function that caps the benefit of repeated terms (controlled by k1) and a length normalizer that reduces scores for terms in unusually long documents (controlled by b). BM25 consistently outperforms TF-IDF across most retrieval benchmarks and is the default in Lucene and Elasticsearch.

Why does keyword stuffing fail mathematically?

BM25’s saturation function is the direct mathematical reason. With a default k1 of 1.2, the first few occurrences of a term contribute most of the available score. By the 10th occurrence, additional repetition produces diminishing returns approaching zero. Stuffing a term 50 times in a document yields a BM25 score only marginally higher than 10 occurrences, whereas unnatural density increases the likelihood of poor engagement signals, which suppress rankings through behavioral quality scoring.

What do the k1 and b parameters in BM25 control?

k1 controls how quickly term frequency saturates. A higher k1 (around 2.0) means repetition continues to help for longer. Lower k1 (around 1.2) means the score flattens quickly after the first few occurrences. The parameter b controls document length normalization on a scale from 0 to 1: at b=1, the score is fully adjusted for document length; at b=0, length has no effect. The defaults of k1=1.2 and b=0.75, validated empirically across many TREC test collections, work well for most general text corpora.

Is BM25 still used in modern search engines?

Yes. BM25 is the default ranking function in Apache Lucene (the foundation of Elasticsearch, Solr, and OpenSearch) and is used in the sparse retrieval component of Google’s and Bing’s hybrid search pipelines. Elasticsearch used TF-IDF until version 5.0 (2016), then switched to BM25 as the default because of its consistently better performance. Despite advances in neural retrieval, BM25 remains the standard first-stage retrieval step because it is fast, interpretable, and computationally cheap at scale.

What is BM25F, and why does it matter for on-page SEO?

BM25F treats each document field (title, body, anchor text, URL) as a separate scoring stream with its own parameters and weighting. Terms in high-weight fields (like the title) receive a higher effective contribution to the document’s final score than terms in lower-weight fields (like footer text). This is the formal mathematical basis for why keyword presence in page titles generates stronger relevance signals per occurrence than the same keyword in body copy.

Who invented TF-IDF and BM25?

The IDF component was introduced by Karen Spärck Jones in a 1972 paper on term specificity. The TF-IDF combination was formalized by Gerard Salton and Christopher Buckley in 1988. BM25 was developed by Stephen Robertson and Karen Spärck Jones through the 1970s-1990s as part of the Probabilistic Relevance Framework, and was first deployed in the Okapi retrieval system at City University London during TREC competitions in the early 1990s. Robertson’s 2009 paper “The Probabilistic Relevance Framework: BM25 and Beyond” remains the standard reference.

---

Key Takeaways

Key takeaways from this article:

* TF-IDF weights terms by how frequent they are in this document, multiplied by how rare they are across the collection. Specificity, not repetition, produces high scores.
* IDF was invented by Karen Spärck Jones in 1972, three years before TF-IDF was formalized. It is the component that makes the entire family of keyword relevance work.
* BM25 adds two fixes to TF-IDF: a saturation function (k1=1.2 by default) that caps the benefit of additional term occurrences, and a length normalizer (b=0.75 by default) that penalizes terms in unusually long documents relative to the collection average.
* The saturation curve explains why keyword stuffing fails. After the first 5-10 occurrences of a term, BM25 scores additional repetitions at near-zero incremental value.
* BM25F extends BM25 to multiple document fields with different weights. Keywords in the page title generate stronger relevance signals per occurrence than keywords in body text, a direct consequence of field weighting, not an arbitrary convention.

Your next step: Open any page on your site and count how many times your target keyword phrase appears in the title, H1, and first paragraph versus the full body. If you see fewer than 2 occurrences in those high-signal fields and more than 8 in the full body, the distribution is working against BM25F field weighting. Redistribution to title-forward structures typically improves relevance scoring more than increasing the total body count.

Coming up next: Article 1.4 covers PageRank — how Brin and Page replaced word-counting with link-counting, what the random surfer model actually calculates, and why a link from a high-authority page is worth more than a link from a low-authority page.

---

Sources: 
1. Spärck Jones, K. (1972). A statistical interpretation of term specificity and its application in retrieval. Journal of Documentation, 28(1), 11–21.
🔗 DOI: https://doi.org/10.1108/eb026526
📄 Full text (PDF): https://www.staff.city.ac.uk/~sbrp622/idfpapers/ksj_orig.pdf

2. Salton, G., & Buckley, C. (1988). Term-weighting approaches in automatic text retrieval. Information Processing & Management, 24(5), 513–523.
🔗 DOI: https://doi.org/10.1016/0306-4573(88)90021-0
📄 Publisher page: https://www.sciencedirect.com/science/article/abs/pii/0306457388900210

3. Robertson, S., & Zaragoza, H. (2009). The probabilistic relevance framework: BM25 and beyond. Foundations and Trends in Information Retrieval, 3(4), 333–389.
🔗 DOI: https://doi.org/10.1561/1500000019
📄 Full text (PDF): https://www.staff.city.ac.uk/~sbrp622/papers/foundations_bm25_review.pdf

4. Robertson, S., Zaragoza, H., & Taylor, M. (2004). Simple BM25 extension to multiple weighted fields. In Proceedings of the 13th ACM International Conference on Information and Knowledge Management (CIKM '04) (pp. 42–49). ACM.
🔗 DOI: https://doi.org/10.1145/1031171.1031181
📄 Publisher page: https://dl.acm.org/doi/10.1145/1031171.1031181