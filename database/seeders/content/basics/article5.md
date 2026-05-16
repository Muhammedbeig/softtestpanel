Hubs and Authorities: How Kleinberg’s HITS Algorithm Explains Why Niche Links Beat Generic Ones

Meta description: HITS assigns two scores to every page: a hub score for linking to good authorities and an authority score for being linked by good hubs. This covers the formula, the update rule, the TKC effect, and what all of this means for topical link strategy. (157 chars)

Series: How Search Engines Work | Article 5 of 10 | Module: Search Engine Fundamentals
Previous article: PageRank: How Brin and Page Replaced Word-Counting with Link-Counting

---

HITS (Hyperlink-Induced Topic Search) gives each web page two scores: an authority score, which reflects how many trusted curators link to it, and a hub score, which shows how well it links to trusted sources. Jon Kleinberg created HITS in 1999 to address something PageRank missed: the web has two main roles, and a page trusted by experts in a specific topic matters more for that topic than a page with lots of links but no clear focus. This article covers how hubs and authorities work together, how the scores are updated, how the root and base sets are built, a step-by-step example, the TKC weakness, and what all this means for organizing your content and building niche link strategies.

---

What Is the HITS Algorithm, and What Problem Did It Solve?

The HITS algorithm is a search-based method that ranks web pages by calculating two connected scores: an authority score based on how many good hub pages link to it, and a hub score based on how many good authority pages it links to. Jon Kleinberg developed HITS at IBM Almaden Research Center while on leave from Cornell University. It was first published as an IBM report in 1997 and later in the Journal of the ACM in 1999.

To understand why HITS was needed, think back to what earlier methods could and could not do. TF-IDF and BM25 rank pages by how well they use the right keywords. PageRank ranks pages by how many important pages link to them, no matter the topic. Both treat the web as one big group. For example, a page about molecular biology that gets a link from a popular entertainment site still gets credit, even if the topics are unrelated. A general page with thousands of links from all over the web can outrank a focused, well-cited page in a small expert community, even if experts agree it is the best resource.

Kleinberg saw a pattern in the early web. When a topic became important, two types of pages showed up. First, there were detailed directories that gathered and linked to the best resources on a subject. Second, there were the resource pages themselves, which had the actual content and were important because curators linked to them. These two roles were different, but earlier algorithms treated them the same.

One-sentence Definition: HITS finds the most important pages on a topic by identifying the pages that curators in that topic link to most often. A curator is considered trustworthy if they link to many important pages.

---

What Are Hubs and Authorities, and How Do They Reinforce Each Other?

In HITS, every page can act as both a hub and an authority, with separate scores for each role.

A hub is a page that links to many good authorities. The prototypical hub is a well-maintained resource directory: a page like “The Best Cardiology Research Journals” that exists to point the reader toward expert sources. A hub page does not need to contain authoritative content itself. Its value comes entirely from the quality of its outgoing links.

An authority is a page that is linked to by many good hubs. A peer-reviewed cardiology journal indexed by multiple research directories, recommended by multiple university library pages, and cited on multiple medical school reading lists accumulates a high authority score because many well-regarded curators have independently chosen to endorse it.

What makes HITS unique is that hub scores and authority scores depend on each other. You can’t calculate one without the other.

* A good hub is one that links to good authorities. So hub scores depend on authority scores.
* A good authority is one that is linked to by good hubs. So authority scores depend on hub scores.

You can’t figure out either score without the other, and both change together as the calculation goes on. This isn’t a problem, it’s what makes HITS work. The algorithm keeps updating until both scores settle at a stable point.

In practice, a link from a respected industry curator is worth more than a link from a high-authority page with no topic focus. For example, if a software comparison site links to your SaaS product page, it boosts your authority in that topic more than a link from a popular news site that covers many unrelated topics. HITS explains this: the comparison site is a trusted hub in enterprise software, while the news site isn’t a hub for any specific topic.

Column 1	Column 2	Column 3
Role	What it represents	How its score is determined
Authority	A trusted source of content on a topic	Sum of hub scores of all pages that link to it
Hub	A trusted curator that points to authorities	Sum of authority scores of all pages it links to
Mutual reinforcement	Neither score is meaningful without the other	Both are computed simultaneously in each iteration
		


---

How Does the HITS Update Rule Work?

HITS works by updating hub and authority scores over and over until they stop changing. It uses a matrix to show how pages in a focus group link to each other.

Starting with all hub scores and all authority scores initialized to 1.0, each iteration performs two updates in sequence.

The Authority Update

Every page’s new authority score is the sum of the current hub scores of every page that links to it:

authority(i) = Σ hub(j)   for every page j that links to page i

Pages pointed to by many strong hubs see their authority scores rise. Pages pointed to by weak hubs see little gain. A page pointed to by nobody gains nothing from the authority update.

The Hub Update

Every page’s new hub score is the sum of the current authority scores of every page it links to:

hub(i) = Σ authority(j)   for every page j that page i links to

Pages that link to many strong authorities gain a hub score. A page that links to no pages or only to pages with low authority gains little hub score, regardless of its content.

After each update, all scores are divided by the total sum of scores. Without this step, the numbers would keep growing and become meaningless. This adjustment keeps the scores consistent as the process goes on.

The process keeps repeating these updates, adjusting the scores each time, until the changes are tiny. In mathematical terms, the scores settle into stable patterns based on how the pages are linked. These stable patterns always exist and are unique as long as the link graph is connected, as Kleinberg showed in his original paper.

---

What Are the Root Set and Base Set, and Why Doesn’t HITS Run on the Whole Web?

Unlike PageRank, which gives every page a global score all at once, HITS looks at a small, focused part of the web for each search. This makes HITS good at handling topics, but also too slow to use on a large scale.

When a user submits a query, HITS begins by retrieving the top results from a conventional text-based search engine. This initial set is called the root set. It typically contains around 200 pages that text-based ranking identifies as relevant.

The root set by itself is not enough because the really important pages on a topic often do not use the topic’s keywords very often. A respected technical reference might use exact, rare terms that score low in text-based ranking. It might not show up in the root set at all. To find these pages, HITS expands the root set in two ways.

First, it adds every page that the root set of pages links to. These are possible authorities: if many root set pages link to the same page, that page is likely a real authority. Second, it adds pages that link to the root set, up to a limit per root set page. These are possible hubs: pages that were already collecting links on the topic before this search.

The resulting expanded collection is called the base set, and it is the base set, not the whole web, on which the HITS update rule runs.

This setup has two main effects. First, since the base set is created for each search, the same page can get different scores depending on the search terms. For example, a page about the “Python programming language” might rank high for “Python tutorials” but not show up at all for “snake habitats.” Second, because HITS builds the base set and calculates scores during each search, it is much slower than PageRank for search engines that handle millions of searches every day.

---

A Worked Example: Three Iterations of HITS on a 5-Page Topic Graph

Consider a topic graph of five pages about nutrition science, assembled into a base set for the query “Mediterranean diet research”:

* Page A: A university library reading list linking to B, C, and D
* Page B: A peer-reviewed meta-analysis linked to by A and E
* Page C: A clinical trial registry page linked to by A and E
* Page D: A nutrition news blog linked to by A only
* Page E: A medical school course page linking to B, C, and D

All scores start at 1.0. Here is how hub and authority scores evolve across the first three iterations (rounded to two decimals after normalization):

Column 1	Column 2	Column 3	Column 4	Column 5	Column 6	Column 7
Page	Initial H	Initial A	H after iter 1	A after iter 1	H after iter 3	A after iter 3
A	1.00	1.00	0.68	0.10	0.61	0.05
B	1.00	1.00	0.10	0.58	0.05	0.57
C	1.00	1.00	0.10	0.58	0.05	0.57
D	1.00	1.00	0.10	0.40	0.05	0.34
E	1.00	1.00	0.68	0.10	0.61	0.05


Notice how the scores split apart. Pages A and E, which link to many authorities, get high hub scores but low authority scores. Pages B and C, which are linked by these hubs, get high authority scores but low hub scores. Page D, which is only linked by A, scores low because it has just one hub supporting it, and its own links don’t point to other pages in the group, so it gets no hub score from the base set.

Also, notice how the scores help each other grow over time: A and E get higher hub scores as B and C get higher authority scores, and B and C’s authority scores rise as A and E’s hub scores go up. These effects build on each other until they level out. If you remove A or E, B and C’s authority scores drop because they lose a supporter. This shows why a focused niche hub that links only within your topic gives more HITS value per link than a general hub that links to many unrelated areas.

---

What Is the TKC Effect, and Why Did It Make HITS Vulnerable?

The TKC effect (Tightly Knit Community) is the most serious structural weakness of the HITS algorithm, and it is almost never discussed in SEO-focused explanations of HITS.

A tightly-knit community is a group of pages that all link to each other and to one main page, mainly to boost scores rather than share real information. In HITS, if pages link to each other as hubs and all support one page as an authority, the system sees this as real proof of authority. It can’t tell the difference between true expert agreement and coordinated score boosting because both look the same in the link data.

The TKC effect is worse in HITS than in PageRank for a reason. PageRank spreads authority across the web and lowers it if a page has many links. So, a group trying to boost its own PageRank quickly hits limits because each page loses some score by linking out. In HITS, hub and authority scores add up within the base set. Every extra link in a coordinated group directly increases the target’s authority score, with no reduction like in PageRank.

A related problem is topic drift: when the base set accidentally includes pages from a tightly-knit different topic that uses the same keywords, those pages can take over the hub and authority scores, pushing out the truly relevant pages. For example, if the root set for “Python tutorials” includes pages from a group of financial modeling sites that use the word Python for data pipelines, that group can get high scores even though it is not about coding tutorials.

These weaknesses are why HITS was never used as the main ranking algorithm by major search engines, even though its hub-authority idea is smart and insightful.

---

How Does HITS Compare to PageRank?

HITS and PageRank were the two main link analysis algorithms developed in 1998 and 1999. Both are based on the idea that links show authority, but they use very different approaches.

Column 1	Column 2	Column 3
Dimension	PageRank	HITS
Score type	Single score per page	Two scores per page (hub + authority)
Query sensitivity	Query-independent: scores are global	Query-dependent: scores change with each query
Computation timing	Pre-computed offline at indexing time	Computed at query time on a fresh base set
Operating graph	Entire web graph	Small focused subgraph (base set)
Primary signal	Inbound links, weighted by linker authority	Mutual reinforcement between curators and sources
Spam resistance	Moderate (diluted by outgoing links)	Lower (TKC effect enables coordinated inflation)
Key insight	A page matters if important pages cite it	A page is authoritative if expert curators endorse it on this specific topic


The key difference for modern SEO is how they depend on the search. PageRank gives a global authority score: a page is either generally important or not, based on all its links. HITS gives authority based on context: a page might be the top authority for “chest physiotherapy research” but not important for other topics. This is why HITS is the basis for what SEO experts call topical authority, while PageRank supports general domain authority scores.

Neither algorithm by itself matches how modern search engines rank pages. Google combines global authority signals from PageRank, topic-sensitive signals similar to HITS, and user behavior signals that neither the 1990s algorithm predicted.

---

What Does HITS Mean for Content Cluster Architecture and Link Strategy Today?

Even though HITS was never used as a live search algorithm, the hub-authority framework gives the clearest explanation for two of the most reliable patterns in modern SEO.

First: a link from within your niche is more valuable than a general link from a high-authority site outside your topic. In HITS terms, a link from a real hub page in your topic’s base set directly increases your authority score for that topic. A link from a page with high overall PageRank but no topic connection would not even be in your topic’s base set. It helps your general PageRank but does not boost your topical authority. This is why a backlink from the top professional group in your field usually improves rankings more than a link from a popular site with much higher domain authority.

Second, the way content clusters are built matches the hub-authority model. A main page that links to many detailed articles on subtopics in your niche acts as a hub page in HITS. The subtopic pages that get links from several main pages and trusted outside curators act as authorities. Sites that create two-way internal links between main pages and topic clusters build a structure that strengthens topical authority signals in both directions.

The practical architecture that follows from HITS logic is:

1. Publish a hub page that comprehensively covers a topic and links outward to every major subtopic you address.
2. Publish authority pages on each subtopic that link back to the hub and receive links from the hub.
3. Earn links from established hub pages in your niche (directories, resource lists, trusted curators) to the authority pages, not just to the homepage.
4. Links from your hub page to outside expert sources are not a problem. Linking to real authorities is how a hub page earns its hub score in HITS. Curators are supposed to link out—that’s their job.

---

Frequently Asked Questions

What is the HITS algorithm in simple terms?

HITS gives every web page two scores: a hub score and an authority score. A hub page links to many trusted sources on a topic. An authority page is linked to by many trusted curators. These two scores support each other: pages with high authority raise the hub scores of pages linking to them, and pages with high hub scores raise the authority scores of pages they link to. The algorithm keeps updating these scores until they settle.

How is HITS different from PageRank?

PageRank gives each page one global importance score, calculated ahead of time using the whole web. HITS gives two topic-specific scores (hub and authority) calculated during the search on a small, focused group of pages. The same page can have a high authority score for one topic and none for another. PageRank does not depend on the search; HITS does. PageRank is faster and better at resisting spam; HITS is more accurate for specific topics.

What is a hub page in the HITS algorithm?

A hub page is one whose value comes from the quality and relevance of the links it points to, not from its own content. Well-kept resource directories, reading lists, and curated link collections are typical hub pages. A hub page raises its hub score by linking to important pages on a topic. A page that links to many unrelated or low-quality pages gets a low hub score even if it has many outgoing links.

What is the TKC effect, and why does it matter?

TKC means Tightly Knit Community. It describes a group of pages that link to each other and to one main page in a coordinated way that boosts hub and authority scores without real topic support. Because HITS adds up hub and authority scores across the base set, a group of mutual links creates the same signal as real expert agreement. PageRank is less affected by this because it reduces the value of each link based on how many links a page has.

Did Google ever use the HITS algorithm?

Google’s main ranking system is based on PageRank and its updates, not HITS. HITS is too slow for Google because it needs to do complex calculations for each search on a new set of pages. Still, the idea from HITS—that topical authority is different from global authority—helped shape topic-sensitive versions of PageRank and improved how Google understands domain authority within topic groups. The hub-authority difference still best explains why backlinks relevant to a topic work better than just high domain authority links.

Why does a niche backlink often outperform a high-authority backlink from an unrelated domain?

In HITS terms, a backlink from a page that acts as a real hub in your topic’s base set directly increases your authority score for that topic. A backlink from a high-PageRank page on an unrelated topic would not be in your topic’s base set at all. It adds to your overall link value but does not show topic support. A link from the top association, directory, or resource list in your field tells the ranking system that trusted curators in that topic see your page as an authority, which is exactly what HITS was made to detect.

---

Key Takeaways

Key takeaways from this article:

* HITS gives each page two scores: an authority score (earned by being linked to by trusted curators) and a hub score (earned by linking to trusted sources). Both scores are calculated together by supporting each other.
* Mutual reinforcement means neither score can be found alone. Authority scores affect hub scores, and hub scores affect authority scores. The algorithm repeats until both scores settle, reaching stable patterns based on the link structure.
* Query dependency is HITS’s defining architectural difference from PageRank. Scores are computed on a freshly assembled base set for each query, so the same page can have radically different authority scores for different topics.
* The TKC effect (Tightly Knit Community) is HITS’s primary vulnerability: coordinated mutual linking produces the same mathematical signal as genuine expert consensus, making HITS more gameable than PageRank at scale.
* Content clusters mirror the hub-authority structure. A pillar page linking to subtopic pages behaves as a hub. Subtopic pages receiving links from multiple pillar pages and external curators accumulate authority scores. Outbound links from your hub to external authorities are not penalties; they are the mechanism that gives a hub its credibility.
* Niche topical links beat generic high-authority links because topically focused backlinks operate within the relevant base set, contributing directly to topic-specific authority scores, while generic links contribute only to undifferentiated global authority.

Your next step: Map the incoming backlinks to your five most important pages using any backlink tool. For each link, note whether the linking page is a topical hub (a curator or directory within your niche) or a generic high-authority link from an unrelated domain. If fewer than 30% of your links come from pages that themselves link specifically to resources in your field, your backlink profile has topical authority gaps that volume cannot compensate for.

Coming up next: Article 1.6 covers the three-stage pipeline: crawl, index, and rank as an engineering system. Understanding how Googlebot discovers pages, what the indexing queue prioritizes, and how ranking signals are applied at serving time ties together everything covered in articles 1.1 through 1.5.

---

Sources: 
1. Kleinberg, J. M. (1999). Authoritative sources in a hyperlinked environment. Journal of the ACM, 46(5), 604–632.
🔗 DOI: https://doi.org/10.1145/324133.324140
🔗 Publisher page: https://dl.acm.org/doi/10.1145/324133.324140
📄 Full text (PDF): https://www.cs.cornell.edu/home/kleinber/auth.pdf

Correction on preliminary version note: The preliminary version appeared in the Proceedings of the ACM-SIAM Symposium on Discrete Algorithms, 1998 — not just as an IBM report. The IBM Research Report RJ 10076 (May 1997) is correct. Both are confirmed in the paper's own footnote.


2. Lempel, R., & Moran, S. (2001). SALSA: The stochastic approach for link-structure analysis. ACM Transactions on Information Systems, 19(2), 131–160.
🔗 DOI: https://doi.org/10.1145/382979.383041
🔗 Publisher page: https://dl.acm.org/doi/abs/10.1145/382979.383041

Confirmed: All details — authors, year, volume, issue, pages, and journal — are accurate. The parenthetical note about the TKC effect is factually correct but belongs in your body text, not the reference entry itself.


3. Ng, A. Y., Zheng, A. X., & Jordan, M. I. (2001). Stable algorithms for link analysis. In Proceedings of the 24th Annual International ACM SIGIR Conference on Research and Development in Information Retrieval (pp. 258–266). ACM.
🔗 DOI: https://doi.org/10.1145/383952.384003
🔗 Publisher page: https://dl.acm.org/doi/10.1145/383952.384003
📄 Full text (PDF): https://ai.stanford.edu/~ang/papers/sigir01-stablelinkanalysis.pdf

Corrections: Author order is Ng, Zheng, Jordan (your list had Ng and Zheng transposed). The correct page range is 258–266 (your list omitted this). As a conference paper it needs the full proceedings title, pages, and publisher. The conference was held in New Orleans, September 9–12, 2001.


4. Borodin, A., Roberts, G. O., Rosenthal, J. S., & Tsaparas, P. (2005). Link analysis ranking: Algorithms, theory, and experiments. ACM Transactions on Internet Technology, 5(1), 231–297.
🔗 DOI: https://doi.org/10.1145/1052934.1052942
🔗 Publisher page: https://dl.acm.org/doi/abs/10.1145/1052934.1052942

All details confirmed accurate. Minor style note: sentence case should be used for the title in APA — Link analysis ranking: Algorithms, theory, and experiments — with only the first word and proper nouns capitalised.
