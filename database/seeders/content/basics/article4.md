PageRank: How Brin and Page Replaced Word-Counting with Link-Counting

Meta description: PageRank converts hyperlinks into weighted votes. The random surfer model turns those votes into a probability score. This covers the formula, damping factor, spider traps, and what a 2024 Google leak confirmed about PageRank today. (156 chars)

Series: How Search Engines Work | Article 4 of 10 | Module: Search Engine Fundamentals
Previous article: TF-IDF and BM25: The Mathematics of Keyword Relevance (And Why Repetition Stops Helping)

---

PageRank is the algorithm that transformed search by treating the web as a voting system: every hyperlink from one page to another counts as a weighted vote of confidence. A page earns authority not by repeating a keyword, but by receiving links from pages that are themselves well-linked. In 1998, Larry Page and Sergey Brin introduced this idea in a paper titled “The Anatomy of a Large-Scale Hypertextual Web Search Engine,” and it became the founding logic of Google. This article covers what PageRank is, how the random surfer model produces the formula, what the damping factor controls, why spider traps broke the naive version, how teleportation fixed it, and what the 2024 Google internal document leak confirmed about whether PageRank still runs today.

---

What Is PageRank, and What Problem Did It Solve?

PageRank is a method that gives each web page a number showing how important it is by counting the quality and number of links pointing to it. A link from a trusted page gives more value than one from a less trusted page, and a page with fewer links going out gives more value per link than one with many. Larry Page and Sergey Brin created this method at Stanford University, published it in 1998, and named it after Larry Page and the idea of ranking web pages.

Before PageRank, search engines like AltaVista and Yahoo ranked pages mostly by how often keywords appeared. As article 1.3 explained, TF-IDF measures how often and how specifically a word shows up. But just counting keywords could not tell the real Stanford University homepage apart from a random page that mentioned “Stanford University” fifty times. TF-IDF looks at content. PageRank looks at trust.

Brin and Page’s key idea was to borrow from how academic papers are judged. In research, a paper cited by many respected papers is seen as important. Web links work the same way: a link from a trusted page shows that the linked page should be trusted too. The big improvement over just counting citations was that PageRank is recursive: a link from a highly trusted page adds more value than one from a less trusted page, even if both link to the same page.

The one-sentence definition: PageRank is the probability that a person randomly clicking links on the web will land on a given page after an indefinitely long session.

---

How Does a Link Become a Vote? The Logic Behind Link Authority

A hyperlink points from one page to another. When Page A links to Page B, it passes some of its own PageRank to Page B. How much it passes depends on two things: Page A’s PageRank and how many links Page A has going out.

If Page A has a PageRank of 1.0 and links to five pages, each page gets about 0.20 of that value. If Page A links to only two pages, each gets 0.50. The more links a page has going out, the less value each link passes.

Why Not All Links Carry Equal Weight

The value passed is recursive, meaning the scores for all pages are calculated together before final results appear. A page with many links from low-value pages might have a lower PageRank than a page with just one link from a very trusted source. This recursive weighting is what made PageRank very different from earlier link-counting methods.

Column 1	Column 2
What a link represents	What determines its authority value
A vote of confidence from Page A to Page B	The PageRank score of Page A
A fraction of Page A’s total rank	Divided by the number of outgoing links on Page A
More valuable when fewer links compete	A page with 3 outlinks passes more per link than one with 30


This is why a single link from a major news publication often carries more ranking authority than fifty links from obscure blog directories. PageRank does not count votes. It weighs them.

---

What Is the Random Surfer Model?

The random surfer model is a way to turn the idea of link authority into a score you can calculate. Imagine a person who starts on a random web page and keeps clicking links chosen at random. At each page, the surfer picks one link to follow without any goal or preference. This goes on forever.

After a long time, some pages get visited much more than others. Pages with many good links pointing to them get visited more often. A page’s PageRank is the share of time the random surfer spends on that page once the visits settle into a steady pattern.

In practice, if 1,000 random surfers started clicking links on the web and never stopped, the share of surfers on any page at any time would match that page’s PageRank. A page with a PageRank of 0.05 would have about 5% of the surfers on it at any moment.

This framing is not just a teaching tool. It is the actual mathematical definition of PageRank: the stationary distribution of a Markov chain defined by the web’s link graph. PageRank at page i equals the long-run probability of finding the random surfer at page i.

---

What Is the PageRank Formula, and What Does Each Part Mean?

The iterative PageRank formula for a page i, as published by Brin and Page, is:

PR(i) = (1 - d) / N  +  d × Σ [ PR(j) / L(j) ]

Where:

* PR(i) is the PageRank score of the page being scored.
* d is the damping factor (typically 0.85)
* N is the total number of pages in the collection.
* The summation runs over every page j that links to page i
* PR(j) is the PageRank score of each linking page.
* L(j) is the number of outgoing links on that linking page.

The formula has two parts. The first part, (1 - d) / N, is the basic chance of visiting a page even if no links point to it, because the surfer might jump there randomly. The second part, d × Σ [ PR(j) / L(j) ], adds up the value passed from every page that links to page i.

What Does the Damping Factor Actually Control?

The damping factor d is the chance that the random surfer clicks a link on the current page instead of jumping to a random page. With d = 0.85, the surfer follows a link 85% of the time and jumps to a random page 15% of the time.

Brin and Page picked 0.85 based on testing. It balanced two goals: accuracy and speed of calculation. A value near 1.0 would better match the real web’s links, but the calculation would take longer to finish. Using 0.85 lets the calculation finish quickly. They reported that on a network with 322 million links, the algorithm settled on a stable result after just 52 rounds.

This speed of calculation is an important detail often left out in SEO explanations: the 0.85 value was chosen partly to make the math easier, not because it perfectly matches how people browse the web. Studies of real user click data in 2010 showed the actual average damping factor is between 0.60 and 0.72, much lower than 0.85. For Wikipedia, it’s even lower, between 0.33 and 0.43. Brin and Page’s 0.85 value gives a stable, good result, but it shows how their model works, not how people really surf the web.

Column 1	Column 2	Column 3	Column 4
Parameter	What it controls	Effect at 0.85 (default)	Effect near 1.0
Damping factor (d)	Probability of following a link vs teleporting	Fast convergence, slight teleportation smoothing	Slower convergence, more faithful to raw link structure
(1 - d)	Teleportation probability	15% chance of random jump	Near-zero random jump chance


---

What Are Spider Traps and Dangling Nodes, and Why Does Teleportation Fix Both?

The basic random surfer model without teleportation fails in two main ways. These problems happen often in real web structures.

A spider trap is a group of pages that only link to each other and have no links going out to the rest of the web. Once the random surfer enters a spider trap, all the surfing time gets stuck there. After enough time, the pages in the trap take all the PageRank, while pages outside get almost none. The web has many spider traps: company internal sites, closed blog groups, and even single pages that only link to themselves.

A dangling node is a page with no links going out at all: like a PDF, an image, or a page not set up to link anywhere else. When the random surfer lands on a dangling node, there’s nowhere to go. In a model that only follows links, the PageRank on a dangling node just disappears, making the total probability across the web drop below 1.0. Eventually, the calculation stops working.

The fix for both problems is teleportation. With a small chance (1 - d), the surfer leaves the current page and jumps to any random page on the web. This small chance to escape means:

* A surfer in a spider trap will eventually teleport out, preventing probability mass from being permanently trapped.
* A surfer who lands on a dangling node immediately teleports, preventing probability mass from disappearing.

The teleportation mechanism is what the (1 - d) / N term in the formula represents. Without it, the PageRank calculation diverges for any real-world web graph.

Column 1	Column 2	Column 3
Problem	What happens without teleportation	How teleportation resolves it
Spider trap	All PageRank absorbed into the trap; outside pages score zero	Surfer escapes with probability (1 - d) at each step
Dangling node	Probability mass leaks out of the system; scores become invalid	Surfer immediately jumps to any page with probability 1.0


---

A Worked Example: PageRank on a 4-Page Web

Consider a small web with four pages: A, B, C, and D. The link structure is:

* Page A links to B and C.
* Page B links to C
* Page C links to A
* Page D links to C

Using the simplified formula (ignoring the damping factor for now and treating each link as equal weight), here is how PageRank propagates across three iterations, starting with all pages at score 0.25:

Column 1	Column 2	Column 3	Column 4	Column 5
Page	PR after 0 iterations	PR after 1 iteration	PR after 2 iterations	PR at convergence
A	0.25	0.25 × (1/1) from C = 0.25	0.17	0.21
B	0.25	0.25 × (1/2) from A = 0.13	0.11	0.12
C	0.25	0.25 × (1/2) from A + 0.25 from B + 0.25 from D = 0.50	0.52	0.50
D	0.25	0 (no pages link to D)	0.00	0.17*


*With full damping factor applied, D retains a baseline score from the teleportation term.

Notice how Page C becomes the highest-ranked page. It gets links from three of the four pages (A, B, and D) even though it only links out to one page. Page D, while linking to C and adding value to the network, gets no links from A, B, or C. Without teleportation, D’s score would drop to zero. With the damping factor, D keeps a basic score of (1 - d) / N, which keeps it visible even without any links pointing to it.

See how the recursive effect works: B passes a value to C, but B has a low value because only A links to B. C becomes highly ranked because D also links to it, and B’s small contribution adds up. A page in the middle of a group of linking pages gathers value from all of them at once.

---

Why Does a Link from a High-Authority Page Pass More Rank?

This happens because of the formula’s recursive setup. When the calculation repeats, a page with many good incoming links gets a high PR(j) score. That high score is divided by how many links it has going out and passed to each linked page. The pages receiving the links get a large value divided by the number of outgoing links.

A link from a page with PR = 0.80 and 4 outgoing links passes 0.80 divided by 4, which is 0.20 authority per link. A link from a page with PR = 0.04 and 2 outgoing links passes only 0.04 divided by 2, which is 0.02. The first link is worth ten times more, even though the second page links to fewer places.

This math proves a common idea in link building: the value of a link depends on how trusted the linking page is, not just that the link exists. Getting one link from a trusted site adds more rank than getting twenty links from low-value directories, because each link’s value depends on the trust the linking page has built up.

---

What Does PageRank Mean for Link Building and Internal Linking Today?

PageRank remains a live, actively used component of Google’s ranking systems. In May 2024, internal Google Search API documentation confirmed that multiple PageRank variants are running simultaneously inside Google’s algorithm. The documents referenced at least four named variants: RawPageRank (the foundational score), PageRank2 (an updated version), PageRank_NS (a nearest-seed variant used for content clustering and low-quality detection), and FirstCoveragePageRank (the score assigned when Google first indexes a page). Google stopped publishing public PageRank scores in 2016, but the algorithm itself never stopped running.

For a practical content strategy, the PageRank formula has three direct implications.

First, the more links a page has going out, the less authority each link passes. A page with 100 outgoing links passes about one-tenth the authority per link compared to a page with 10 outgoing links, if their PageRank is the same. Linking out too much from your most trusted pages lowers the value each linked page gets.

Second, internal links spread PageRank within your own site. Your homepage usually gets the most links from other sites. Every internal link from the homepage to a deeper page passes some of that authority inward. Pages without internal links from trusted pages on your site miss out on this flow of value. Studies show that focusing internal links on key category pages can boost their PageRank by about 35% compared to flat link setups.

Third, nofollow and the 2005 rel attribute. In 2005, Google added the rel="nofollow" tag, which tells search engines not to pass PageRank through a link. Before nofollow, comment spam on linked pages gave PageRank to spammers. Nofollow stopped that automatic flow. Later, Google changed nofollow to a suggestion instead of a rule, so sometimes Google still follows and counts nofollowed links.

The practical point: internal linking is more than just navigation. It spreads PageRank. Pages you link to from your most trusted pages get some of that rank. Pages with no internal links pointing to them get only the basic random chance score and may never build real authority.

---

Frequently Asked Questions

Is PageRank still used by Google today?

Yes. Google stopped publishing public PageRank scores in December 2013 and retired the public toolbar score in 2016, but the algorithm has continued running internally. A 2024 leak of Google’s internal API documentation confirmed multiple active PageRank variants, including RawPageRank, PageRank2, and PageRank_NS. Google’s updated SEO Starter Guide also confirms that link-based authority signals derived from PageRank calculations remain among its most important ranking signals.

What is the damping factor in PageRank, and why is it 0.85?

The damping factor is the probability that the random surfer follows a link instead of jumping to a random page. At 0.85, the surfer follows a link 85% of the time and teleports 15% of the time. Brin and Page chose 0.85 empirically because it balances accuracy with computational speed: values closer to 1.0 are more accurate but converge more slowly. Researchers who analyzed real human browsing data found that the actual web-average damping factor is closer to 0.60 to 0.72.

What is the random surfer model in PageRank?

The random surfer model is a probabilistic thought experiment that defines PageRank: a person who starts at a random page and clicks links indefinitely without any goal. The fraction of time this surfer would spend on any given page, after enough time passes for visiting frequencies to stabilize, equals that page’s PageRank score. The model is the conceptual foundation for the PageRank formula and is mathematically equivalent to computing the stationary distribution of a Markov chain.

What is a spider trap, and how does PageRank handle it?

A spider trap is a set of web pages that link only to each other with no outgoing links to the broader web. Without a fix, a random surfer who enters the trap never escapes, causing all PageRank probability mass to accumulate inside it and every page outside to approach a score of zero. PageRank solves this by introducing teleportation via the damping factor: with probability (1 - d), the surfer jumps to a random page regardless of which page they are currently on, allowing escape from any trap.

How is PageRank different from Domain Authority or Ahrefs DR?

PageRank is Google’s internal algorithm and is the actual signal used in Google’s ranking system. Domain Authority (Moz) and Domain Rating (Ahrefs) are third-party metrics that attempt to approximate PageRank-like link authority. They use similar logic but are calculated from independently crawled data and do not reflect Google’s internal scores. They are useful proxies for comparing sites, but they are estimates, not the real values.

Does internal linking affect PageRank the same way backlinks do?

Yes. The PageRank formula does not distinguish between internal and external links. A link from a high-authority page on your own site passes the same fraction of authority as an external link from an equivalent page elsewhere. This is why internal linking strategy matters for SEO: your homepage typically holds the highest accumulated PageRank from external backlinks, and internal links from the homepage pass a portion of that authority to destination pages. Orphan pages that receive no internal links from high-authority pages on the site accumulate only the baseline teleportation score.

---

Key Takeaways

Key takeaways from this article:

* PageRank treats every hyperlink as a weighted vote: the authority passed per link equals the PageRank of the linking page divided by the number of its outgoing links. Recursive weighting means a link from a well-linked page is worth more than a link from a poorly-linked page.
* The random surfer model defines PageRank as the fraction of time an indefinitely surfing, randomly clicking visitor would spend on a given page. This is not just a metaphor; it is the mathematical definition of the algorithm’s output.
* The damping factor (d = 0.85) controls the probability of following a link versus teleporting to a random page. It was chosen as an empirical compromise between accuracy and convergence speed, and it enables Brin and Page’s algorithm to converge on 322 million links in just 52 iterations.
* Spider traps and dangling nodes are structural failure modes that break the naive link-following model. The teleportation component of the damping factor solves both by preventing probability mass from being trapped or lost.
* PageRank still runs inside Google today. The 2024 Google internal document leak confirmed at least four active PageRank variants, including RawPageRank, PageRank2, and PageRank_NS.
* Internal linking distributes PageRank. Your highest-authority pages pass rank to every page they link to. Orphaned pages receive only the minimal baseline score and struggle to accumulate authority regardless of their content quality.

Your next step: Open Google Search Console and look at your site’s top-linked pages (those with the most external backlinks). Now check how many internal links from those pages point toward your most strategically important content. If your high-authority entry points link primarily to your homepage or navigation pages rather than to your target content, you are allowing link equity to dissipate instead of directing it. Redirecting even two or three internal links from your most-linked pages toward content you want to rank often produces measurable position changes within 30 to 60 days.

Coming up next: Article 1.5 covers Kleinberg’s HITS algorithm, published the same year as PageRank. HITS separates the link graph into two recursive scores, hubs and authorities, and explains why a link from a domain authority in your specific niche is worth more than a generic high-PageRank link from an unrelated source.

---

Sources: 
1. Page, L., Brin, S., Motwani, R., & Winograd, T. (1999). The PageRank citation ranking: Bringing order to the web. Technical Report No. 1999-66. Stanford InfoLab.
🔗 Full text (PDF): http://ilpubs.stanford.edu:8090/422/1/1999-66.pdf
🔗 Record page: http://ilpubs.stanford.edu:8090/422/

Correction: The report number is 1999-66 and the publisher is Stanford InfoLab — not simply "Stanford Technical Report."


2. Brin, S., & Page, L. (1998). The anatomy of a large-scale hypertextual web search engine. Computer Networks and ISDN Systems, 30(1–7), 107–117.
🔗 DOI: https://doi.org/10.1016/S0169-7552(98)00110-X
🔗 Publisher page: https://www.sciencedirect.com/science/article/abs/pii/S016975529800110X

Corrections: Author order is Brin first, then Page (your list had it reversed). The full journal title is Computer Networks and ISDN Systems, not just Computer Networks.


5. Google Search Central. (n.d.). How search works. Google for Developers.
🔗 https://developers.google.com/search/docs/fundamentals/how-search-works

Correction: This is a living web document with no fixed publication date — do not cite it as "2024." Use "n.d." (no date) and add a retrieved date, e.g.: Retrieved May 16, 2026.


4. Robertson, S. and Sparck Jones, K. (2009). Referenced indirectly via BM25 framework context in article 1.3.
This is not a citation at all — it is a note about indirect reference. Robertson and Sparck Jones have no joint 2009 publication. If you need to cite their foundational work, the correct source is:

Robertson, S. E., & Jones, K. S. (1976). Relevance weighting of search terms. Journal of the American Society for Information Science, 27(3), 129–146. DOI: https://doi.org/10.1002/asi.4630270302