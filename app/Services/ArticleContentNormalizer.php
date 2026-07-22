<?php

namespace App\Services;

final class ArticleContentNormalizer
{
    public function normalize(string $html, ?int $articleNumber = null): string
    {
        return $this->normalizeCitations(
            $this->canonicalizeStorageImages($html),
            $articleNumber
        );
    }

    public function canonicalizeStorageImages(string $html): string
    {
        $origin = $this->panelOrigin();

        return preg_replace_callback(
            '~(<img\b[^>]*?\bsrc=)(["\'])(.*?)(\2)~i',
            static function (array $matches) use ($origin): string {
                $source = trim($matches[3]);
                $storagePath = null;

                if (preg_match('~^(?:\.\./|\./)*\/?storage/(.+)$~i', $source, $pathMatch)) {
                    $storagePath = $pathMatch[1];
                } elseif (preg_match('~^https?://[^/]+/storage/(.+)$~i', $source, $pathMatch)) {
                    $sourceHost = parse_url($source, PHP_URL_HOST);
                    $panelHost = parse_url($origin, PHP_URL_HOST);

                    if ($sourceHost === $panelHost) {
                        $storagePath = $pathMatch[1];
                    }
                }

                if ($storagePath === null) {
                    return $matches[0];
                }

                return $matches[1].$matches[2].$origin.'/storage/'.$storagePath.$matches[4];
            },
            $html
        ) ?? $html;
    }

    public function countRelativeStorageImages(string $html): int
    {
        preg_match_all(
            '~<img\b[^>]*?\bsrc=(["\'])(?:\.\./|\./)*\/?storage/.*?\1~i',
            $html,
            $matches
        );

        return count($matches[0] ?? []);
    }

    private function normalizeCitations(string $html, ?int $articleNumber): string
    {
        $sourceMap = [];
        $sourceDetails = [];
        $prefix = $this->sourcePrefix($html, $articleNumber);

        $normalized = preg_replace_callback(
            '~<section\b[^>]*class=(["\'])[^"\']*\barticle-sources\b[^"\']*\1[^>]*>[\s\S]*?</section>~i',
            function (array $sectionMatch) use (&$sourceMap, &$sourceDetails, $prefix): string {
                $number = 0;

                return preg_replace_callback(
                    '~<li\b([^>]*)>([\s\S]*?)</li>~i',
                    function (array $itemMatch) use (&$number, &$sourceMap, &$sourceDetails, $prefix): string {
                        $number++;
                        $attributes = $itemMatch[1];
                        $content = $itemMatch[2];
                        $oldId = null;

                        if (preg_match('~\bid=(["\'])(.*?)\1~i', $attributes, $idMatch)) {
                            $oldId = html_entity_decode($idMatch[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        }

                        $newId = $prefix.$number;
                        if ($oldId !== null && $oldId !== '') {
                            $sourceMap[$oldId] = ['id' => $newId, 'number' => $number];
                        } else {
                            // An id-less source may already be cited by the canonical ID it is about to receive.
                            $sourceMap[$newId] = ['id' => $newId, 'number' => $number];
                        }

                        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
                        $url = null;
                        if (preg_match('~<a\b[^>]*href=(["\'])(https?://.*?)\1~i', $content, $urlMatch)) {
                            $url = html_entity_decode($urlMatch[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        }
                        $sourceDetails[$newId] = ['text' => $text, 'url' => $url];

                        if ($oldId !== null) {
                            $attributes = preg_replace(
                                '~\bid=(["\']).*?\1~i',
                                'id="'.htmlspecialchars($newId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"',
                                $attributes,
                                1
                            ) ?? $attributes;
                        } else {
                            $attributes .= ' id="'.htmlspecialchars($newId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"';
                        }

                        return '<li'.$attributes.'>'.$content.'</li>';
                    },
                    $sectionMatch[0]
                ) ?? $sectionMatch[0];
            },
            $html
        ) ?? $html;

        $normalized = preg_replace_callback(
            '~<a\b(?=[^>]*class=(["\'])[^"\']*\bcitation-ref\b[^"\']*\1)[^>]*href=(["\'])#(.*?)\2[^>]*>[\s\S]*?</a>~i',
            static function (array $referenceMatch) use ($sourceMap, $sourceDetails): string {
                $oldTarget = html_entity_decode($referenceMatch[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $mapped = $sourceMap[$oldTarget] ?? null;
                if (! $mapped) {
                    return '';
                }

                $detail = $sourceDetails[$mapped['id']] ?? ['text' => '', 'url' => null];
                $target = htmlspecialchars($mapped['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $number = (int) $mapped['number'];
                $preview = htmlspecialchars($detail['text'] ?: 'Source '.$number, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $sourceLink = $detail['url']
                    ? '<span class="citation-popover-link" data-href="'.htmlspecialchars($detail['url'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'">View source &#8599;</span>'
                    : '';

                return '<a class="citation-ref" href="#'.$target.'"><sup>['.$number.']</sup>'
                    .'<span class="citation-popover"><span class="citation-popover-title">Source '.$number.'</span>'
                    .$preview.$sourceLink.'</span></a>';
            },
            $normalized
        ) ?? $normalized;

        return preg_replace(
            '~<span\b[^>]*class=(["\'])[^"\']*\bcitation-cluster\b[^"\']*\1[^>]*>\s*</span>~i',
            '',
            $normalized
        ) ?? $normalized;
    }

    private function sourcePrefix(string $html, ?int $articleNumber): string
    {
        if ($articleNumber !== null && $articleNumber > 0) {
            return 'source-article-'.$articleNumber.'-';
        }

        if (preg_match('~\bid=(["\'])source-article-(\d+)-\d+\1~i', $html, $match)) {
            return 'source-article-'.$match[2].'-';
        }

        return 'source-';
    }

    private function panelOrigin(): string
    {
        if (! app()->runningInConsole() && request()?->getHost()) {
            return rtrim(request()->getSchemeAndHttpHost(), '/');
        }

        return rtrim((string) config('app.url'), '/');
    }
}
