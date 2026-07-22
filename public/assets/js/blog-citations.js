(function (global) {
    'use strict';

    function sourceItems(root) {
        return Array.from(root.querySelectorAll('section.article-sources > ol > li'));
    }

    function sourceDetails(item) {
        var paragraph = item.querySelector('p') || item;
        var link = paragraph.querySelector('a[href^="http://"],a[href^="https://"]');

        return {
            text: (paragraph.textContent || '').replace(/\s+/g, ' ').trim(),
            url: link ? link.href : ''
        };
    }

    function removeReference(reference) {
        var cluster = reference.closest('.citation-cluster');
        reference.remove();

        if (cluster && !cluster.querySelector('.citation-ref')) {
            cluster.remove();
        }
    }

    function refreshPopover(reference, number, detail) {
        var popover = reference.querySelector('.citation-popover');
        if (!popover) return false;

        var documentRef = reference.ownerDocument;
        var title = documentRef.createElement('span');
        title.className = 'citation-popover-title';
        title.textContent = 'Source ' + number;
        var children = [title, documentRef.createTextNode(detail.text || ('Source ' + number))];

        if (detail.url) {
            var link = documentRef.createElement('span');
            link.className = 'citation-popover-link';
            link.dataset.href = detail.url;
            link.textContent = 'View source \u2197';
            children.push(link);
        }

        var previous = popover.innerHTML;
        popover.replaceChildren.apply(popover, children);
        return previous !== popover.innerHTML;
    }

    function normalize(root, options) {
        options = options || {};
        var targetForNumber = typeof options.targetForNumber === 'function'
            ? options.targetForNumber
            : function (number) { return 'source-' + number; };
        var items = sourceItems(root);
        var mapping = new Map();
        var details = new Map();
        var changed = false;
        var removed = 0;

        items.forEach(function (item, index) {
            var number = index + 1;
            var oldId = item.id || '';
            var newId = targetForNumber(number);

            if (oldId) {
                mapping.set(oldId, { id: newId, number: number });
            } else {
                // Preserve a citation that already points to the canonical ID assigned below.
                mapping.set(newId, { id: newId, number: number });
            }
            details.set(newId, sourceDetails(item));

            if (item.id !== newId) {
                item.id = newId;
                changed = true;
            }
        });

        Array.from(root.querySelectorAll('a.citation-ref[href^="#"]')).forEach(function (reference) {
            var oldTarget = (reference.getAttribute('href') || '').slice(1);
            var mapped = mapping.get(oldTarget);

            if (!mapped) {
                removeReference(reference);
                removed++;
                changed = true;
                return;
            }

            var nextHref = '#' + mapped.id;
            if (reference.getAttribute('href') !== nextHref) {
                reference.setAttribute('href', nextHref);
                changed = true;
            }

            var superscript = reference.querySelector('sup');
            var nextLabel = '[' + mapped.number + ']';
            if (!superscript) {
                superscript = reference.ownerDocument.createElement('sup');
                reference.prepend(superscript);
                changed = true;
            }
            if (superscript.textContent !== nextLabel) {
                superscript.textContent = nextLabel;
                changed = true;
            }

            if (refreshPopover(reference, mapped.number, details.get(mapped.id) || {})) {
                changed = true;
            }
        });

        return {
            changed: changed,
            sources: items.length,
            citations: root.querySelectorAll('a.citation-ref[href^="#"]').length,
            removed: removed
        };
    }

    global.BlogCitationManager = { normalize: normalize };
}(window));
