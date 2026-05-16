MAP, MRR, and NDCG: The Metrics That Define What “Better Rankings” Actually Mean

Meta description: MAP, MRR, and NDCG each capture different models of how users read search results. This covers the formulas, the behavioral assumptions behind each metric, three worked examples, and what happens when AI consumes search results instead of humans. (158 chars)

Series: How Search Engines Work | Article 9 of 10 | Module: Search Engine Fundamentals
Previous article: Learning-to-Rank: How Machine Learning Replaced the 200-Factor Checklist

---

To improve its rankings, a search engine first needs to define what “better” actually means. Imagine two result lists for the same query, each containing the same three relevant documents. In one list, they appear at positions 1, 2, and 3; in the other, at positions 3, 7, and 12. Most people would agree that the first list is better. However, intuition alone cannot run A/B tests, train models, or compare algorithms; metrics are needed for that. The metrics discussed here, Mean Average Precision, Mean Reciprocal Rank, and Normalized Discounted Cumulative Gain, turn ranked lists into single scores. Each metric is based on a specific, testable idea about how users read search results. Picking the wrong metric means optimizing for the wrong user. This article explains what each metric measures, how its formula works, the user behavior it assumes, where it falls short, and why NDCG became the main standard for web search and the training target for LambdaRank and LambdaMART, as discussed in article 1.8.

---

Why Do Search Engines Need Evaluation Metrics at All?

Evaluation metrics are practical engineering tools, not just theoretical ideas. Every search engine update begins with a hypothesis, such as: “If we increase the weight on entity salience signals, rankings will improve.” To test this, you need a control (the current algorithm), a test version (the modified algorithm), and a way to measure which one performs better. Without a metric, there is no reliable way to compare results.

Metrics are used at three key stages in a search system’s lifecycle. First, in offline evaluation, engineers test changes against a labeled dataset of queries and human relevance judgments, and the metric determines which algorithm performs best. Second, during the training of learning-to-rank models like LambdaRank and LambdaMART (see article 1.8), NDCG is used directly as the training target. Here, the metric is not just for measuring results; it is built into the training process itself. Third, in live A/B testing, when Google compares two ranking versions with real user traffic, the metric decides if the change should be launched.

The three metrics discussed here, MAP, MRR, and NDCG, are the main ways experts have defined what a ranked list score should measure. The key difference between them is the model of user reading behavior each one assumes. Understanding this difference helps explain why each metric exists and why NDCG became the standard.

At the heart of every metric is this question: If a user gets a ranked list of results, what does it cost to put a relevant document at position 5 instead of position 1? MAP, MRR, and NDCG each answer this question differently.

---

Building Blocks: Precision, Recall, and the Precision-Recall Tradeoff

Before the three main metrics, two foundational concepts from article 1.1 need precise definitions in the context of ranked lists.

Precision is the fraction of retrieved documents that are relevant:

Precision = (Number of relevant documents retrieved) / (Total documents retrieved)

If a search engine returns 10 results and 4 of them are relevant to the query, precision is 4/10 = 0.4. Precision measures how clean the retrieved set is.

Recall is the fraction of all relevant documents in the collection that were actually retrieved:

Recall = (Number of relevant documents retrieved) / (Total relevant documents in collection)

If 20 relevant documents exist for a query and the search engine returned 4 of them, recall is 4/20 = 0.2. Recall measures whether the retrieved set is complete.

There is a basic tradeoff between precision and recall: improving one usually lowers the other. Returning more results increases recall but also brings in more irrelevant documents, which lowers precision. Returning only the most confident matches improves precision but can miss relevant documents, reducing recall. MAP was created to combine both of these properties into a single score.

Precision and recall do not consider the position of results. Getting 4 relevant documents at positions 1, 2, 3, and 4 gives the same precision and recall as getting them at positions 7, 8, 9, and 10. Both lists have 4 relevant documents out of 10, but users always prefer the first list. MAP, MRR, and NDCG each address this gap in their own way.

---

What Is Mean Average Precision (MAP)?

Mean Average Precision (MAP) is a metric that summarizes precision across all recall levels for a query, averaged across multiple queries. It rewards ranking systems that place relevant documents high and penalizes those that bury them below irrelevant ones.

How Is MAP Calculated Step by Step?

MAP is built from three nested calculations: Precision@K, Average Precision (AP) per query, and the mean of AP across all queries.

Step 1: Precision@K. For any rank position K, Precision@K is the precision of the top K results:

Precision@K = (Number of relevant documents in top K positions) / K

Step 2: Average Precision for one query. AP is computed only at the rank positions where relevant documents actually appear, then averaged:

AP = (1 / R) × Σ Precision@K_i

where R is the total number of relevant documents for the query and K_i is the position of the i-th relevant document. The sum runs only over positions that contain a relevant document.

Step 3: MAP across all queries. MAP is the arithmetic mean of AP scores across all queries in the evaluation set.

A Worked Example

Consider the query “best cardiology research methods.” The collection contains 4 relevant documents. The search engine returns a ranked list where relevant documents appear at positions 1, 3, 5, and 9, and all other positions contain irrelevant documents.

Column 1	Column 2	Column 3	Column 4	Column 5
Position	Document	Relevant?	Precision@K	Included in AP sum?
1	Doc A	Yes	1/1 = 1.00	Yes
2	Doc B	No	—	No
3	Doc C	Yes	2/3 = 0.67	Yes
4	Doc D	No	—	No
5	Doc E	Yes	3/5 = 0.60	Yes
6	Doc F	No	—	No
7	Doc G	No	—	No
8	Doc H	No	—	No
9	Doc I	Yes	4/9 = 0.44	Yes
10	Doc J	No	—	No


AP = (1/4) × (1.00 + 0.67 + 0.60 + 0.44) = (1/4) × 2.71 = 0.678

Now suppose a second query “Mediterranean diet studies” has AP = 0.510. MAP across both queries is (0.678 + 0.510) / 2 = 0.594.

The formula rewards putting relevant documents at the top. For example, a relevant document at position 1 adds Precision@1 = 1.00 to the total. If that same document is at position 5, it only adds Precision@5 = 0.20 (if there are no relevant documents above it). MAP strongly favors finding relevant documents early in the list.

What Does MAP Assume About Users?

MAP is based on a simple user model: users read the entire ranked list, care about every relevant document, and treat each relevant result as equally valuable. There is no difference between documents that are more or less relevant; each is either relevant (1) or not (0). MAP uses binary relevance labels as input.

This assumption does not fit most web searches. For example, if someone searches for “how to appeal an insurance claim denial,” they want the single best answer, not five equally relevant results. Also, some documents are much better than others, not just relevant or not. MAP cannot capture these differences.

---

What Is Mean Reciprocal Rank (MRR)?

Mean Reciprocal Rank (MRR) is a metric that measures how quickly a user finds the first relevant result. It assigns the entire score for a query to a single number: the reciprocal of the rank of the first relevant document.

How Is MRR Calculated?

For a single query, the Reciprocal Rank (RR) is:

RR = 1 / (rank of the first relevant document)

If the first relevant document is at position 1, RR = 1/1 = 1.0. If it is at position 2, RR = 1/2 = 0.5. Position 3 gives RR = 0.33. Position 5 gives RR = 0.20. MRR is the mean of RR across all queries in the evaluation set.

A Worked Example

Three queries, each with a different position for the first relevant result:

Column 1	Column 2	Column 3
Query	First relevant document at position	Reciprocal Rank
“who founded Google”	1	1/1 = 1.00
“best Python ORM library”	3	1/3 = 0.33
“cardiology residency requirements”	2	1/2 = 0.50


MRR = (1.00 + 0.33 + 0.50) / 3 = 0.61

What Does MRR Assume About Users?

MRR uses the simplest user model of the three: users stop reading as soon as they find the first relevant result. Anything after the first relevant hit does not count. There is no reward for placing a second relevant document anywhere in the list.

This model fits a narrow but real category of queries: navigational queries with a single correct destination (“Google homepage”), factual lookup queries with a single correct answer (“Albert Einstein birthplace”), and voice assistant queries where only one result is ever surfaced. For those query types, MRR is exactly the right metric.

MRR does not work well for informational or transactional queries, or any situation where users want to compare several results. For example, if the first relevant document is at position 1 but the next relevant ones are at positions 50, 60, and 70, MRR still gives a perfect score of 1.0. This is not helpful for users who want to see and compare multiple options.

---

What Is Normalized Discounted Cumulative Gain (NDCG)?

Normalized Discounted Cumulative Gain (NDCG) is a metric that does two things MAP and MRR cannot: it allows for graded relevance (documents can be more or less relevant on a scale), and it uses a logarithmic discount for lower positions, reflecting that users pay less attention as they go down the list.

How Is NDCG Built? The Three-Step Construction

NDCG is assembled from three components: Cumulative Gain, Discounted Cumulative Gain, and normalization against the Ideal DCG.

Step 1: Cumulative Gain (CG). CG is simply the sum of relevance scores in the returned list up to rank K. For a five-point relevance scale (0 = irrelevant, 4 = perfectly relevant), a result list of [3, 2, 0, 1, 4] has CG@5 = 3 + 2 + 0 + 1 + 4 = 10. So does [0, 1, 2, 3, 4]. CG is position-blind: the order does not affect the score.

Step 2: Discounted Cumulative Gain (DCG). DCG applies a logarithmic penalty to each relevance score based on its position:

DCG@K = Σ (rel_i / log₂(i + 1))   for i = 1 to K

The denominator for position 1 is log₂(2) = 1, so the relevance score counts fully. For position 2, the denominator is log₂(3) ≈ 1.58. For position 5, it is log₂(6) ≈ 2.58. For position 10, it is log₂(11) ≈ 3.46. A highly relevant document at position 10 contributes roughly one-third of what it would have contributed at position 1.

The logarithmic discount is based on real user behavior. Studies of eye-tracking and clicks show that users pay much less attention to lower positions, and this drop follows a logarithmic pattern. DCG’s discount function is designed to match this observation.

An alternative DCG formula used by major web search companies and Kaggle competitions places stronger emphasis on relevance differences at the top:

DCG@K = Σ ((2^rel_i - 1) / log₂(i + 1))   for i = 1 to K

This version, often called the “exponential gain” formula, makes highly relevant documents count much more than moderately relevant ones. For example, a document with relevance 4 adds 15 gain units (2⁴ − 1), while one with relevance 2 adds only 3 (2² − 1). This big difference is why the exponential formula is used when it is important to separate excellent results from average ones.

Step 3: Normalization by Ideal DCG (IDCG). DCG scores cannot be compared across queries because different queries have different numbers of relevant documents and different relevance score distributions. Normalizing by the IDCG — the DCG that a perfect ranking of all available relevant documents would produce — puts every query on a 0-to-1 scale:

NDCG@K = DCG@K / IDCG@K

IDCG is computed by sorting all relevant documents for the query in descending order of relevance and computing DCG for that perfect ordering. An NDCG of 1.0 means the returned ranking is identical to the ideal. An NDCG of 0.0 means no relevant documents were returned.

A Worked Example

Query: “Mediterranean diet research.” Five documents returned. Relevance scores on a 0-3 scale (0 = irrelevant, 3 = highly relevant) as assigned by human raters: [2, 3, 0, 1, 3].

Actual ranking DCG@5 (using the standard log₂ formula):

Column 1	Column 2	Column 3	Column 4
Position	Relevance	log₂(pos + 1)	Contribution
1	2	log₂(2) = 1.00	2.00 / 1.00 = 2.000
2	3	log₂(3) = 1.585	3.00 / 1.585 = 1.893
3	0	log₂(4) = 2.00	0.00 / 2.00 = 0.000
4	1	log₂(5) = 2.322	1.00 / 2.322 = 0.431
5	3	log₂(6) = 2.585	3.00 / 2.585 = 1.161


DCG@5 = 2.000 + 1.893 + 0.000 + 0.431 + 1.161 = 5.485

Ideal ranking DCG@5 — sort relevance scores [3, 3, 2, 1, 0] in descending order:

Column 1	Column 2	Column 3
Position	Relevance	Contribution
1	3	3.00 / 1.00 = 3.000
2	3	3.00 / 1.585 = 1.893
3	2	2.00 / 2.00 = 1.000
4	1	1.00 / 2.322 = 0.431
5	0	0.00 / 2.585 = 0.000


IDCG@5 = 3.000 + 1.893 + 1.000 + 0.431 + 0.000 = 6.324

NDCG@5 = 5.485 / 6.324 = 0.867

A score of 0.867 means this ranking achieved 86.7% of the best possible relevance value for the documents available. The main reason for the lower score was putting the highly relevant document (score 3) at position 5 instead of at the top. If that document had been at position 1, NDCG would have been about 0.95.

What Does NDCG Assume About Users?

NDCG encodes the most empirically grounded behavioral model of the three. It assumes users scan the result list with logarithmically declining attention, meaning position 1 receives full weight, and each lower position receives progressively less, but users never fully stop reading. It also assumes relevance is graded, not binary, recognizing that some documents answer a query perfectly while others answer it partially.

These two assumptions together make NDCG appropriate for the full range of informational queries that dominate web search. They are also exactly the two assumptions that LambdaRank and LambdaMART use in their training signal: the lambda gradient (covered in article 1.8) is scaled by the NDCG delta from swapping each document pair, which operationalizes both graded relevance and position weighting simultaneously.

---

How Do the Three Metrics Compare?

Column 1	Column 2	Column 3	Column 4
Dimension	MAP	MRR	NDCG
Relevance model	Binary (relevant / not)	Binary (relevant / not)	Graded (multi-point scale)
Position sensitivity	Implicit (via Precision@K)	Only cares about first hit	Explicit logarithmic discount
User behavioral model	Reads full list, values every hit	Stops at first relevant hit	Scans with decreasing attention
Best query type	Recall-critical, multiple relevant docs	Single-answer, navigational	Informational, comparative, multi-answer
Used in LTR training	Yes (some models)	Rare	Yes (LambdaRank, LambdaMART)
Industry standard	TREC benchmarks, academic IR	QA systems, voice search	Web search, recommendation, RAG evaluation
Key weakness	Binary labels miss relevance degrees	Blind to quality of positions 2+	Requires accurate graded relevance labels


The most important row in this table is the behavioral model. MAP treats a user who extracts equal value from every relevant result regardless of position. That model describes a researcher systematically reviewing literature, not a person using Google on a phone. MRR treats a user who needs one answer and leaves. That model describes a voice assistant interaction perfectly, but describes almost nothing else. NDCG treats a user who pays diminishing attention to lower results and assigns different values to different degrees of relevance. That model is the closest approximation to the population of users a production web search engine must serve.

---

Where Do All Three Metrics Break Down?

Each metric was designed with a human user in mind, reading a sequentially ranked list, clicking on links, and forming satisfaction judgments about the results they see. That design assumption held for every major search evaluation context from the Cranfield experiments in the 1960s through the 2010s.

It is now breaking.

In AI-generated search features (Google’s AI Overviews, Bing’s Copilot-integrated results, and RAG-based enterprise search), the “user” consuming the ranked list is an LLM, not a human. A 2025 paper from researchers at Sapienza University and the Technology Innovation Institute identified two specific points where all three metrics fail in this context.

The position discount mismatch: MAP, MRR, and NDCG all assume that results at lower positions are seen less. This holds for human users who read top-to-bottom. LLMs process all retrieved documents simultaneously as a batch, regardless of their order. A document at position 10 in the retrieved context window is not discounted by the LLM in the way it is discounted by a human reader. The logarithmic penalty at the core of NDCG and the position-sensitivity of MAP both encode an assumption that does not apply to the LLM consumer.

The negative document problem: All three metrics treat non-relevant documents as neutral: a document that is not relevant contributes zero to the score. For a human user, a non-relevant result is simply ignored. For an LLM generating an answer from retrieved context, a confident but wrong document in the retrieved set can actively poison the generated answer. It is not neutral; it is harmful. None of the three metrics assigns negative scores to non-relevant documents that might degrade generation quality.

This is not a reason to abandon MAP, MRR, and NDCG. They remain the correct metrics for evaluating human-facing ranked lists, which still represent the majority of search interactions. It is a reason to understand that every metric encodes assumptions, and those assumptions have expiration dates as the technology they measure changes.

---

What Do These Metrics Mean for Content Strategy?

Evaluation metrics are engineering tools. But the decisions engineers make using those tools shape what content ranks. Understanding the metrics clarifies why certain patterns in rankings look the way they do.

NDCG’s graded relevance scale explains the quality ceiling. Because the metric that drives LambdaRank training is graded, Google’s training signal distinguishes between a page that partially answers a query and a page that fully answers it. A page that covers a topic with surface-level accuracy but misses depth, nuance, or actionability is not merely less good; it scores lower on the relevance scale that feeds directly into the training objective. The metric is why “comprehensive coverage” is not a content cliché but a mathematical requirement.

MAP’s precision-at-position sensitivity explains why rankings are stable for strong results. When a page earns Precision@1 for multiple queries over time, those queries contribute full Precision@K = 1.0 to MAP in the evaluation set used to validate algorithm changes. Displacing a page that consistently earns position 1 across many queries requires strong evidence that the replacement improves MAP overall, not just on the target query. Position stability at the top is self-reinforcing in MAP-optimized systems.

MRR’s single-answer model explains featured snippets and AI Overviews. These SERP features extract the single most direct answer to a query and surface it above the organic results. They are the literal engineering implementation of the MRR behavioral model: serve one highly confident answer first. Pages that directly, concisely, and accurately answer a specific question in a format that a snippet extraction system can isolate are contributing to the MRR of the query. That structural clarity is what earns featured snippet positions.

---

Frequently Asked Questions

What is the difference between MAP, MRR, and NDCG?

MAP (Mean Average Precision) evaluates how well a ranked list retrieves all relevant documents across all positions, using binary relevance labels. MRR (Mean Reciprocal Rank) measures only how quickly the first relevant document appears, making it appropriate for single-answer queries. NDCG (Normalized Discounted Cumulative Gain) evaluates the full ranked list with graded relevance and a logarithmic position discount, making it the most comprehensive metric for informational web search queries.

What is the formula for NDCG?

NDCG is computed in three steps. First, calculate DCG@K as the sum of each document’s relevance score divided by log₂(position + 1) across the top K positions. Second, calculate IDCG@K as the DCG of a perfect ranking (documents sorted by descending relevance). Third, NDCG@K = DCG@K / IDCG@K. The result is always between 0 and 1, where 1.0 means the returned ranking is identical to the ideal ranking. The alternative exponential formula DCG = Σ((2^rel − 1) / log₂(pos + 1)) is used in most commercial search and competition settings to amplify differences between high and low relevance grades.

Why is NDCG the standard metric for web search?

NDCG is the standard because it models the two properties of web search that MAP and MRR cannot capture simultaneously: users pay logarithmically decreasing attention to lower positions, and documents vary in how relevant they are, rather than being simply relevant or not. These two properties make NDCG the closest available approximation to actual user satisfaction with a ranked list. NDCG is also the metric directly encoded in the LambdaRank gradient used to train LambdaMART, the dominant production LTR algorithm at major search engines.

What is the behavioral model behind MRR?

MRR assumes a user who submits a query, scans the result list until they find the first relevant document, and stops. Everything below the first relevant document is invisible to the metric. This model is accurate for navigational queries with a single correct destination and factual lookup queries with a single correct answer. It is a poor model for informational, comparative, or multi-result queries where users benefit from seeing multiple relevant results.

What does MAP measure that precision and recall do not?

Precision measures the fraction of returned documents that are relevant. Recall measures the fraction of all relevant documents that were returned. Neither is position-sensitive: returning four relevant documents at positions 1-4 and returning them at positions 7-10 produce the same precision and recall. MAP extends both metrics by computing precision only at positions where relevant documents appear (giving higher scores to systems that surface relevant documents early) and averaging across all recall levels and all queries. MAP is position-sensitive, where precision and recall are not.

Why do traditional search metrics break down in AI-generated search?

MAP, MRR, and NDCG were designed assuming a human user reading results sequentially, paying less attention to lower positions. In RAG and AI-generated answer systems, an LLM processes all retrieved documents simultaneously, regardless of rank order, so the logarithmic position discount is no longer an accurate model. Additionally, traditional metrics treat non-relevant documents as neutral, contributing a score of zero. In LLM-generated answer contexts, a confidently wrong document actively degrades answer quality, making it not neutral but harmful. A 2025 paper from Sapienza University and the Technology Innovation Institute documented both misalignments formally and proposed utility-based annotation schemas that quantify negative document contributions.

---

Key Takeaways

Key takeaways from this article:

* Evaluation metrics are behavioral models. MAP, MRR, and NDCG do not just measure ranking quality differently — they encode different assumptions about how users read result lists. Choosing the wrong metric means optimizing for the wrong user model.
* MAP uses binary relevance labels and rewards systems that surface all relevant documents early. It is sensitive to every relevant document’s position and averages precision at every recall level. Its central weakness is binary relevance, which cannot represent the real differences between a perfect answer and a partial one.
* MRR measures only where the first relevant result appears, making it perfect for single-answer and navigational queries. It is blind to everything after position 1, making it a poor choice for any query where users compare multiple results.
* NDCG combines graded relevance with a logarithmic position discount, encoding that users pay more attention to the top and that not all relevant documents are equally good. It is the dominant standard in web search because it models real user behavior most accurately of the three and is the direct training signal in LambdaRank and LambdaMART.
* The NDCG formula is computed in three steps: compute DCG@K by summing relevance/log₂(pos+1) across the top K positions; compute IDCG@K for the ideal ranking; divide DCG by IDCG. The result is always 0-to-1, comparable across queries.
* All three metrics break in AI-generated search contexts because they assume human sequential reading (so position discounts are appropriate) and neutral non-relevant documents (so irrelevant results score zero). LLMs process retrieved documents in batches and can be poisoned by confidently wrong results, neither of which the classical metrics account for.
* For content strategy: NDCG’s graded relevance scale means the training objective explicitly distinguishes partial answers from complete ones. MRR’s single-answer model explains why featured snippets are designed for direct answers. MAP’s position sensitivity explains why pages consistently ranking at position 1 are self-reinforcing in evaluation-driven algorithm development.

Your next step: Look at the queries on which your most important pages rank between positions 5 and 15 in Google Search Console. For each query, manually assess whether your page gives a complete answer (relevant score 3-4 on NDCG’s scale) or a partial answer (score 1-2). If most of those mid-ranking pages give partial answers covering the topic but missing depth, missing sub-questions, or stopping short of actionable specifics, you have identified an NDCG-relevant quality gap. Complete answers rank higher, not because of keyword density but because the metric that drives the training objective rewards them.

Coming up next: Article 1.10 closes the module. It covers the ethics of search, the business model that funds it, how organic ranking and paid advertising are architecturally separated, the history of quality rater guidelines, and why SEO done correctly aligns with Google’s training objective rather than working against it. It also sets up Google Search Console as the student’s ground-truth monitoring tool and separates the durable principles covered in articles 1.1 through 1.9 from the tactics that change with every algorithm update.

---

Sources: 
1. Manning, C. D., Raghavan, P., & Schütze, H. (2008). Introduction to information retrieval. Cambridge University Press.
🔗 Publisher page: https://www.cambridge.org/9780521865715
📄 Full text (free online): https://nlp.stanford.edu/IR-book/information-retrieval-book.html
ISBN: 978-0-521-86571-5

Corrections: Author initials should include middle initials where known — Christopher D. Manning, Prabhakar Raghavan, Hinrich Schütze. The book is 482 pages. Chapter 8, which covers evaluation of IR systems, is the correct chapter to cite; if citing a specific chapter, APA format for a book chapter would be: Manning, C. D., Raghavan, P., & Schütze, H. (2008). Evaluation in information retrieval. In Introduction to information retrieval (Ch. 8). Cambridge University Press.


2. Järvelin, K., & Kekäläinen, J. (2002). Cumulated gain-based evaluation of IR techniques. ACM Transactions on Information Systems, 20(4), 422–446.
🔗 DOI: https://doi.org/10.1145/582415.582418
🔗 Publisher page: https://dl.acm.org/doi/10.1145/582415.582418

Corrections: Full first names are Kalervo Järvelin and Jaana Kekäläinen. Title capitalisation in APA should be sentence case: "Cumulated gain-based evaluation of IR techniques." Published October 2002. All volume, issue, and page details confirmed accurate.


3. Wikipedia contributors. (n.d.). Discounted cumulative gain. Wikipedia, The Free Encyclopedia.
🔗 https://en.wikipedia.org/wiki/Discounted_cumulative_gain

Correction: Your list titled this "Discounted Cumulative Gain" — confirmed, but the Wikipedia article is titled "Discounted cumulative gain" (sentence case). Note also: the article covers both DCG and NDCG; it is not exclusively about NDCG. Standard Wikipedia caveat applies: tertiary source, unsuitable for academic citation; add Retrieved May 16, 2026 if used.


4. Trappolini, G., Cuconasu, F., Filice, S., Maarek, Y., & Silvestri, F. (2025, October 24). Redefining retrieval evaluation in the era of LLMs. arXiv.
🔗 arXiv: https://arxiv.org/abs/2510.21440
🔗 DOI: https://doi.org/10.48550/arXiv.2510.21440

Corrections and notes: The submission date is October 24, 2025. The institutional affiliations are split: Trappolini, Cuconasu, and Silvestri are at Sapienza University of Rome; Filice, Maarek (and Cuconasu as a research intern) are at Technology Innovation Institute (Abu Dhabi). Your list's "Sapienza University of Rome / Technology Innovation Institute" is accurate but belongs in the affiliation notes of a paper, not the citation itself. As a preprint, it has not been peer-reviewed; note this where relevant.


5. Manning, C. D., & Nayak, P. (2019). Evaluation [Lecture slides, CS276: Information Retrieval and Web Search, Lecture 8]. Stanford University.
🔗 Course page: https://web.stanford.edu/class/cs276/
📄 Lecture 8 slides (PDF): http://web.stanford.edu/class/cs276/19handouts/lecture8-evaluation-6per.pdf

Corrections: Course materials are not cited as just a URL — they require authors, year, and format. The lecturers for CS276 are Christopher D. Manning and Pandu Nayak (confirmed from the slides themselves). The most recent publicly accessible version of these slides is from 2019 (Spring quarter). Cite as lecture slides or course materials, not as a standalone publication. The course is also offered as CS276/LING 286.


6. Monigatti, L. (2024, May 28). Evaluation metrics for search and recommendation systems. Weaviate Blog.
🔗 https://weaviate.io/blog/retrieval-evaluation-metrics

Corrections: The author is Leonie Monigatti, Machine Learning Engineer at Weaviate. The exact date is May 28, 2024. Your list provided no author or date.


7. Evidently AI. (n.d.). Normalized Discounted Cumulative Gain (NDCG) explained. Evidently AI.
🔗 https://www.evidentlyai.com/ranking-metrics/ndcg-metric

Notes: No individual author or publication date is credited on the page — it is a product documentation/explainer page published by Evidently AI as a corporate author. Cite with n.d. and add Retrieved May 16, 2026. The title on the page is "Normalized Discounted Cumulative Gain (NDCG) explained."


8. Marqo. (2026, April 14). What is NDCG? The metric that measures whether your search is actually good. Marqo Blog.
🔗 https://www.marqo.ai/blog/what-is-normalized-discounted-cumulative-gain-ndcg

Corrections: The exact date is April 14, 2026. The full title is "What is NDCG? The metric that measures whether your search is actually good" — considerably longer than the abbreviated "What Is NDCG?" in your list. No individual author is credited publicly on the page, so cite as corporate author (Marqo). The article is specifically framed around ecommerce search, not general IR evaluation.