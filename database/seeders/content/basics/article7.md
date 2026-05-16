From Strings to Things: How Google’s Knowledge Graph and Hummingbird Update Changed What “Relevant” Means

Meta description: The 2012 Knowledge Graph and 2013 Hummingbird rewrite moved Google from keyword matching to entity understanding. This covers nodes, edges, entity salience, disambiguation, and what topical authority actually means. (152 chars)

Series: How Search Engines Work | Article 7 of 10 | Module: Search Engine Fundamentals
Previous article: Crawl, Index, Rank: The Search Engine Pipeline That Decides Whether Your Page Exists to Google

---

The 2012 Knowledge Graph and the 2013 Hummingbird update changed Google’s relevance model, which worked like every other text search system: a query is a string, a document is a string, and relevance is a mathematical link between the two strings. Matching the word “Einstein” to pages containing “Einstein” was the whole model. Google stopped treating queries as just strings to match and started treating them as real things to understand, with details, connections, and context that no string-matching system could handle. This article explains what an entity is in Google’s system, how the Knowledge Graph stores entities as points and relationships as links, why the Hummingbird update was needed to use that graph during searches, what entity salience means and how it is measured, and what the shift from strings to things means for content strategy.

---

What Is Wrong with Matching Strings?

To understand why Google needed to move beyond string matching, it helps to see precisely where string matching fails. The failure is not subtle. It shows up in any query that is ambiguous, lacks context, or uses natural language phrasing.

Consider the query “seal.” TF-IDF and BM25 (explained in article 1.3) return documents that have the word “seal” often across many pages. But “seal” means at least four very different things: the sea animal, the singer, a government security label, and the action of closing something. A string-matching system has to either guess from the nearby words (which are often missing) or return a mix of all four meanings, leaving the user to figure it out.

Now think about a longer query: “scientist who won two Nobel Prizes in different fields.” No page in Google’s index probably has those exact words in that order. A pure string-matching system finds nothing useful. But anyone reading that query knows the answer is Marie Curie, who won the 1903 Nobel Prize in Physics and the 1911 Nobel Prize in Chemistry. That answer isn’t found by counting keywords on a page. It comes from a structured set of facts about a real person.

This is the precise gap the Knowledge Graph was built to fill. String matching retrieves documents. Entity understanding retrieves facts. The two systems solve different problems, and by 2012, Google had accumulated enough user behavior data to know that a large and growing fraction of its queries needed facts, not blue links.

The main problem: A string-matching system can show which pages use a word the most. It can’t tell you that the word means a specific thing, that the thing has details, that those details link it to other things, or that the user’s question is really about one of those links and not just the word itself.

---

What Is the Google Knowledge Graph?

The Google Knowledge Graph is a structured database of entities and their relationships, launched on May 16, 2012. Google’s then-senior VP Amit Singhal announced it with a phrase that became the field’s shorthand for the entire paradigm shift: the system was built to understand “things, not strings.”

An entity in the Knowledge Graph is any clearly identifiable real-world thing: a person, place, group, idea, creative work, event, or physical object. The Knowledge Graph shows each entity as a point in a graph. Each point holds the entity’s details (properties that describe it) and links to other points through connections (relationships that show how two entities relate).

The structure can be written as subject-predicate-object triples, a format known in database engineering as RDF (Resource Description Framework):

Column 1	Column 2	Column 3
Subject (Entity)	Predicate (Relationship)	Object (Entity or Value)
Marie Curie	is a	Person
Marie Curie	born in	Warsaw
Marie Curie	won	Nobel Prize in Physics (1903)
Marie Curie	won	Nobel Prize in Chemistry (1911)
Marie Curie	field	Radioactivity
Nobel Prize in Physics	awarded by	Royal Swedish Academy of Sciences


Each row is a connection linking two points (or a point to a detail). The graph now has, according to Google’s API documents, millions of entries covering entities from all areas of human knowledge. When someone searches “scientist who won two Nobel Prizes in different fields,” Google moves through this graph: it finds entities whose “won” connections point to Nobel Prize entries in two different categories, and confidently returns Marie Curie. No keyword matching needed.

Where Does the Knowledge Graph Get Its Data?

Google has never shared a full list of its data sources, but many known sources add to the graph’s content. Wikipedia and its structured-data partner, Wikidata, are among the biggest contributors; Wikidata stores facts in a format that Google can directly use. The CIA World Factbook and Freebase (a structured database Google bought and later added to the graph) provided early data. Google also accepts structured data from website owners using Schema.org markup, uses licensed data for things like stock prices and sports scores, and keeps extracting entity relationships from the web pages it crawls.

One important detail: Google checks many sources before adding any fact to the Knowledge Graph with high confidence. A single mention of a fact on one web page is not enough. The graph shows information confirmed by many independent sources, which is why consistent representation of an entity across the web matters more than any single page’s claim.

A major 2025 event: In June 2025, Google made the biggest cut to the Knowledge Graph in ten years, removing over 3 billion entities in one week, about 6.26% of the whole database. This was not a mistake. Google called it a deliberate “clarity cleanup,” removing entries whose identity was unclear, poorly supported, or uncertain. This shows that having many entities is not the goal. Clear, consistent, and well-supported entities are what keep a stable place in the graph.

---

What Is the Hummingbird update, and Why Did It Require a Full Algorithm Rewrite?

The Knowledge Graph gave Google a structured database of facts about entities. But the search algorithm still needed to be rebuilt from the ground up to actually use that database at query time. That rewrite is what Hummingbird accomplished.

Google Hummingbird was a complete rewrite of Google’s core search algorithm, deployed in August 2013 and officially announced on September 26, 2013, at an event marking Google’s 15th anniversary. Google’s then-search chief Amit Singhal described it as the most significant change to the algorithm since 2001. Former Google software engineer Matt Cutts confirmed it was a rewrite of the entire core algorithm, not an incremental update or filter.

What Did the Old Algorithm Do?

Before Hummingbird, the main algorithm looked at each word in a query mostly on its own. A query like “best place to get coffee near a library” was broken into keywords: “best,” “place,” “coffee,” “near,” “library.” The algorithm found pages with many matches to those words. It did not understand the phrase as a whole question. This worked okay when users typed short, two- to three-word keyword phrases, which was how early search was used. But it failed badly as queries got longer and more like natural speech.

The rise of voice search on smartphones made this problem urgent. A user talking to Google Assistant does not say “coffee library nearby.” They say, “Where can I get a good coffee near a library?” The old method split that natural sentence into six separate keyword searches and lost the meaning of the whole question.

What Did Hummingbird Change?

Hummingbird changed the algorithm from looking at individual query words to understanding the whole query as a single expression of intent, focusing on entities and the connections between them.

Instead of asking “what documents have these words?”, the algorithm now asks “what entity or connection is this query about, and which documents best answer that?” The Knowledge Graph helps clear up which entity is meant: when the new algorithm sees a query that is about a known entity, it gets facts from the graph to help pick the best documents.

This change affected about 90% of all search queries, according to Google. Most queries changed slightly: pages that truly covered the topic (not just repeated keywords) ranked better. Most users didn’t notice because the algorithm made results more precise, so the right pages were already showing up.

Column 1	Column 2	Column 3
Dimension	Pre-Hummingbird	Post-Hummingbird
Unit of analysis	Individual query words	Whole-query meaning and entity intent
Disambiguation	Absent (all meanings equal weight)	Entity graph resolves ambiguous terms
Long queries	Decomposed to keyword fragments	Understood as unified expressions of intent
Voice search	Poor (penalizes conversational phrasing)	Designed for natural language queries
Content signal	Keyword frequency in document	Topic coverage and entity relationships


---

What Is Entity Salience and Why Does It Replace Keyword Density?

After Hummingbird, the question “Does this page have the keyword?” became much less important than “Does this page truly cover this entity?” But “covering the entity” needs a clear definition to be useful.

Entity salience is Google’s way of measuring how important a recognized entity is to the main meaning of a piece of content, shown as a score between 0 and 1. A score of 1.0 means the entity is the main subject of the whole document. A score of 0.05 means the entity is only mentioned briefly and is not the focus. Google’s own language processing system gives this score to every entity it finds on every indexed page.

This is not just theory. The Google Cloud Natural Language API shows this scoring publicly. Sending the text of any web page to the API returns a list of every entity Google finds in the text, along with each entity’s type, Knowledge Graph ID (called a Machine-Generated Identifier or MID), and salience score. A page about quantum mechanics that mentions Albert Einstein as one example will score Einstein with a salience of about 0.08. A biography page focused on Einstein will score him at 0.85 or higher.

What Drives Entity Salience Higher?

Entity salience is not based on how often a keyword appears. Saying an entity’s name twenty times in a 500-word article does not give it high salience if the rest of the content doesn’t explain the entity’s details and connections. Google’s language system looks at how important the entity is in the structure and how rich the context is.

Four factors drive salience up:

Structural placement. An entity mentioned in the title, main heading (H1), first paragraph, and at least one subheading (H2) is seen as the main subject of the document. These structural signals count more than mentions in the body text.

Attribute coverage. Content that explains the entity’s details, history, relationships, and background creates a richer picture of the entity. A page about “Marie Curie” that covers her nationality, field, prizes, collaborators, and methods has a stronger entity signal than one that just repeats her name.

Related entity co-occurrence. Entities that often appear together in the Knowledge Graph boost each other’s salience when they show up naturally together. A page about “radioactivity” that also talks about Marie Curie, Pierre Curie, polonium, and radium is seen as covering the radioactivity topic deeply. One that only mentions radioactivity alone does not create the same rich context.

No competing main entities. A page that focuses clearly on one main entity will have that entity score higher than a page that covers many unrelated topics with similar word counts. This is why content cluster design (one page, one main entity) works better than broad pages covering many topics.

In practice: Keyword density measures how often a word appears. Entity salience measures how clearly a page is about a thing. These are different questions and give different results. Optimizing for one while ignoring the other is why pages with “good keyword optimization” often do worse than pages that just explain a topic well.

---

How Does Entity Disambiguation Work?

Entity disambiguation is the process of determining which specific entity a query or piece of content refers to when the same word could map to multiple entities.

“Apple” could mean the tech company, the fruit, the record label, or several place names. Before the Knowledge Graph, Google used nearby query words as weak clues to decide. After the Knowledge Graph, disambiguation uses the full entity graph: Google checks which entity fits the query context based on the user’s search history, location, language, and the links between query words and known entity details.

This works by matching entity types and likely relationships. For the query “Apple earnings report Q3,” the Knowledge Graph knows that “earnings report” relates to organizations, not fruit. The tech company matches; the fruit does not. Disambiguation picks Apple Inc. without the user needing to clarify.

This disambiguation explains a puzzle that confused SEOs in the mid-2010s: pages could rank for queries they never targeted, and pages with perfect keyword use could rank below pages that never used those exact words. The ranking system was focusing more on entity coverage, not just word matching.

---

What Does the String-to-Thing Shift Mean for Content Strategy?

The Knowledge Graph and Hummingbird together change the basic focus of content strategy. The old way targeted keywords: a page exists to rank for a keyword. The new way targets entities: a page exists to be the most trusted, clearly organized resource on a specific entity.

These sound similar. They produce very different decisions.

What Is Topical Authority in Entity Terms?

Topical authority, in the entity system, is how much Google sees a site as a trusted, well-supported source on a specific group of related entities. It is not a score Google shares. But it works in ways we can observe.

When a site covers an entity with many pages, each about a different detail or related sub-entity, and those pages link to each other using consistent internal links that show entity connections, Google’s language systems build a stronger model of the site’s focus. Each new page that fits the entity group makes the signal stronger. Each page about an unrelated entity weakens it.

The content cluster model that most SEO practitioners have adopted since around 2016 is essentially a practical implementation of entity graph architecture:

* The pillar page covers the primary entity comprehensively, establishing the node.
* Cluster pages cover related sub-entities and attributes, establishing the edges.
* Internal links between pillars and clusters are the graph’s edge connections, made machine-readable.
* Schema.org structured data on each page is the explicit machine-readable layer that removes ambiguity from the entity type and relationships.

This structure does not require abandoning keyword targeting. It requires that keyword targeting be understood as a proxy for entity coverage: the goal is not to rank for the word but to be the clearest resource on the entity the word refers to.

How Should You Think About Keyword Density After This Shift?

Keyword density as a goal is not just old-fashioned. It actually misleads in an entity-based system because it focuses on the wrong thing.

A page that says “project management” forty times in 800 words has high keyword density. A page that says “project management” fifteen times but also talks about task scheduling, team collaboration, milestone tracking, resource allocation, Gantt charts, Agile methods, and Basecamp as a tool has built a real entity cluster around project management. The second page has lower keyword density but much higher entity salience. It also ranks better on competitive project management searches, as content strategists who tested this have seen.

The actionable framework that replaces keyword density is entity coverage depth. For any target topic, the questions to ask are:

1. What is the primary entity this page is about, and is it structurally prominent (title, H1, opening paragraph, at least one H2)?
2. What attributes does this entity have that a knowledgeable, useful page would address?
3. What related entities would naturally co-occur with the primary entity in expert coverage of this topic?
4. Are those related entities present in the content with enough context for Google’s NLP to recognize the relationships?
5. Does Schema.org structured data explicitly identify the entity type and key attributes?

A page that answers yes to all five is doing entity-based content optimization. A page that answers yes only to “does it contain the keyword?” is doing 2009-era optimization.

---

Frequently Asked Questions

What is the Google Knowledge Graph in simple terms?

The Google Knowledge Graph is a database of facts about real-world entities: people, places, organizations, concepts, and their relationships to each other. Launched in 2012, it allows Google to understand that a query about “Einstein” refers to a specific physicist with specific attributes (birthplace, field, prizes) rather than a string of characters to match against web page text. It powers knowledge panels, direct answers, voice search responses, and AI-generated search features.

What did the Hummingbird update actually change?

Hummingbird, deployed in August 2013, was a complete rewrite of Google’s core search algorithm. It shifted the algorithm from processing individual query words independently to understanding whole queries as expressions of intent about entities. A query like “where was the scientist who discovered radioactivity born?” could now be answered by traversing entity relationships in the Knowledge Graph to find Marie Curie’s birthplace, rather than matching those exact words against documents. Google estimated it affected approximately 90% of searches.

What is the difference between a keyword and an entity in SEO?

A keyword is a string of text: the literal words a user types. An entity is a distinct, identifiable real-world thing with attributes and relationships that Google has modeled in its Knowledge Graph. “Coffee shops near libraries” is a keyword phrase. “Starbucks” is an entity with a type (Organization), attributes (founded, headquarters, CEO), and relationships (operates in, sells, founded by). Optimizing for keywords means matching words. Optimizing for entities means establishing that a page covers a thing thoroughly and accurately.

What is entity salience, and how does it affect rankings?

Entity salience is a score between 0 and 1 that Google’s NLP assigns to each entity it detects on a page, indicating how central that entity is to the document’s overall meaning. A salience of 0.9 means the entity is the primary subject of the page. A salience of 0.05 indicates a passing mention. Salience is driven by structural placement (title, H1, headings), attribute coverage, and co-occurrence with related entities. Google does not rank by salience directly, but high salience for the target entity makes the page a stronger candidate when that entity is the query subject.

How does the Knowledge Graph relate to knowledge panels?

A knowledge panel is the visual surface that the Knowledge Graph displays in search results when Google identifies a clear entity behind a query. It shows the entity’s key attributes: a description, image, related entities, and factual data. Knowledge panels are not earned by any single page’s optimization. They appear when Google has sufficient cross-source corroboration to model an entity with high confidence. A brand, person, or organization gains a knowledge panel not by adding structured data to their own site, but by being accurately and consistently represented across multiple authoritative independent sources.

Does keyword density still matter after Hummingbird?

Keyword density as a target metric is no longer a meaningful optimization goal. After Hummingbird, Google evaluates whether a page covers an entity with depth and clarity, not whether it repeats a word at a specific percentage frequency. A page that thoroughly covers an entity’s attributes, related entities, and practical applications will outperform a page with high keyword density but shallow entity coverage on most competitive queries. Keywords remain important as signals of topical direction, but they serve as proxies for entity intent, not the target itself.

What is the connection between the Knowledge Graph and AI search features?

Google’s AI Overviews, voice search responses, and generative search features all draw directly on the Knowledge Graph for the factual layer of their answers. When an AI Overview summarizes “who was Marie Curie?”, it assembles entity attributes from the graph. When Google Assistant answers a spoken question about a local business, it retrieves entity attributes (address, hours, phone number) from the graph. The Knowledge Graph is the structured factual layer beneath every AI-generated answer Google produces. This is why entity clarity and cross-source consistency have become increasingly important as AI search features expand: an unclear or ambiguous entity representation in the graph yields inaccurate AI answers.

---

Key Takeaways

Key takeaways from this article:

* The Knowledge Graph launched in 2012 as Google’s structured database of entities and their relationships. It models the world as nodes (entities) and edges (relationships), representing facts as subject-predicate-object triples rather than as document text.
* Hummingbird (August 2013) was a complete rewrite of Google’s core algorithm, not a filter or penalty update. It shifted query processing from individual keyword matching to whole-query entity understanding, affecting approximately 90% of all searches. It required rebuilding the core algorithm because string-matching systems cannot traverse entity graphs.
* Entity disambiguation is how Google resolves which specific entity a query refers to when multiple entities share the same surface string. It draws on entity type matching, relationship plausibility, and user context signals from the Knowledge Graph.
* Entity salience is a 0-to-1 score assigned by Google’s NLP pipeline to every entity it extracts from a page, measuring how central that entity is to the document’s meaning. Structural placement (title, H1, headings) and attribute coverage drive salience higher; keyword repetition alone does not.
* Keyword density is not a useful target metric in an entity-based system. The relevant question is the depth of entity coverage: does the page establish the primary entity structurally, cover its attributes, and include related entities that co-occur with it in the Knowledge Graph?
* Topical authority is the measurable outcome of entity-consistent content strategy: a site that covers an entity cluster comprehensively through linked pillar and cluster pages, with structured data reinforcing entity types, builds Google’s confidence that the domain is a high-quality source on that entity.
* The June 2025 Knowledge Graph pruning (3 billion entities deleted) demonstrated that entity confidence and cross-source corroboration matter more than entity presence. Ambiguous or poorly supported entity entries were removed to improve the graph’s reliability as a factual layer for AI-generated answers.

Your next step: Pick any page on your site that targets a competitive topic. Submit its text to the Google Cloud Natural Language API (cloud.google.com/natural-language). Review which entities it detects, their types, and their salience scores. If your target entity does not appear with a salience above 0.5, or if unrelated entities score higher than your target, you have identified a concrete entity clarity problem. Restructure the title, H1, and opening paragraph to make the primary entity structurally dominant, then retest.

Coming up next: Article 1.8 covers learning-to-rank: how machine learning models replaced hand-crafted ranking formulas. Once Google understood entities (articles 1.7), the next problem was how to train a model that could weigh hundreds of signals simultaneously to produce the optimal ranked list for any query. That is the learning-to-rank framework, and its three architectural approaches (pointwise, pairwise, and listwise) explain why modern ranking cannot be reverse-engineered from any finite checklist of factors.

---

Sources: 
1. Singhal, A. (2012, May 16). Introducing the Knowledge Graph: Things, not strings. Google Blog.
🔗 https://blog.google/products-and-platforms/products/search/introducing-knowledge-graph-things-not/

Correction: The correct publisher is Google Blog (also referred to as "The Official Google Blog"), not just "Google Blog" in a vague sense. Singhal's title at the time was SVP, Engineering. This is the canonical URL — an older Blogger-hosted URL (googleblog.blogspot.com) also exists but redirects here.


2. Montti, R. (2022, April 12). Google's Hummingbird update: How it changed search. Search Engine Journal.
🔗 https://www.searchenginejournal.com/google-algorithm-history/hummingbird-update/

Correction: The publication date is April 12, 2022, not unspecified. Note this article is part of SEJ's larger Google Algorithm History guide series, so the URL path reflects that parent structure.


3. Wikipedia contributors. (n.d.). Google Hummingbird. Wikipedia, The Free Encyclopedia.
🔗 https://en.wikipedia.org/wiki/Google_Hummingbird

Standard caveat applies: Wikipedia is a tertiary source not acceptable in most academic or professional reference lists. If you need it, add Retrieved May 16, 2026 since the content changes. Strongly recommended to trace Wikipedia's own footnotes and cite the primary sources instead (e.g. the original Search Engine Land coverage by Danny Sullivan from September 2013).


4. Google. (2024, April 26). Knowledge Graph Search API. Google for Developers.
🔗 https://developers.google.com/knowledge-graph

Corrections: The page was last updated April 26, 2024. Note that Google has been actively migrating this API to Cloud Enterprise Knowledge Graph — the documentation now contains a prominent warning about this migration. If you are citing the newer enterprise version, the correct URL is https://cloud.google.com/enterprise-knowledge-graph/docs/overview. Clarify in your writing which version you are referring to.


5. Ghimire, S. (2026, April 2). Entity SEO: The master guide to entity-based SEO strategy. Outpace SEO.
🔗 https://outpaceseo.com/article/entity-seo/

Corrections: The author is Summit Ghimire, founder of Outpace. The publication date is April 2, 2026 (not a vague "April 2026"). The full title is "Entity SEO: The master guide to entity-based SEO strategy."


6. Słowik, S. (2025, October 30). Entity salience in SEO: How to build clear topical focus and authority. Szymon Słowik.
🔗 https://www.szymonslowik.com/entity-salience-in-seo/

Corrections: The full title is "Entity salience in SEO: How to build clear topical focus and authority." The publication date is October 30, 2025 — your list provided no date. Note the correct diacritic spelling: Szymon Słowik (with ł).


7. Weyant, C. (2025, August 18). What is the Knowledge Graph? How it affects SEO and visibility. Search Engine Land.
🔗 https://searchengineland.com/guide/knowledge-graph

Corrections: The author is Curtis Weyant. The publication date is August 18, 2025. This is a living guide page that is updated periodically — add a retrieved date: Retrieved May 16, 2026. The title is "What is the Knowledge Graph? How it affects SEO and visibility."


8. Google Cloud. (n.d.). Cloud Natural Language API documentation. Google Cloud.
🔗 https://cloud.google.com/natural-language/docs

Correction: The URL you listed (cloud.google.com/natural-language) is the product marketing page, not the documentation. The documentation root is the link above (last updated May 8, 2026). Since this is a living technical reference with no single author or fixed date, cite it as a corporate author (Google Cloud) with n.d. and a retrieved date: Retrieved May 16, 2026. If you are citing a specific feature (e.g. entity analysis, salience scoring), link to that specific sub-page instead.