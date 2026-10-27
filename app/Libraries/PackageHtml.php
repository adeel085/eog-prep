<?php

namespace App\Libraries;

/** A deliberately small CKEditor-compatible HTML vocabulary for package content. */
class PackageHtml
{
    public static function clean(string $html, array $images): string
    {
        if (strlen($html) > 60000 || !mb_check_encoding($html, 'UTF-8') || str_contains($html, "\0")) {
            throw new \InvalidArgumentException('HTML must be valid UTF-8 and at most 60,000 bytes.');
        }
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $doc->loadHTML('<?xml encoding="UTF-8"><div id="package-root">' . $html . '</div>', LIBXML_NONET | LIBXML_HTML_NODEFDTD);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $root = $doc->getElementById('package-root');
        if (!$root) {
            throw new \InvalidArgumentException('Invalid HTML.');
        }
        $allowed = ['p', 'br', 'span', 'div', 'strong', 'em', 'b', 'i', 'u', 's', 'sub', 'sup', 'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'caption', 'blockquote', 'img', 'h2', 'h3', 'h4'];
        foreach (iterator_to_array($root->getElementsByTagName('*')) as $node) {
            if (!in_array($node->tagName, $allowed, true)) {
                throw new \InvalidArgumentException('Unsupported HTML element: ' . $node->tagName . '.');
            }
            foreach (iterator_to_array($node->attributes) as $attribute) {
                $name = $attribute->name;
                $value = $attribute->value;
                if ($node->tagName === 'img' && $name === 'src') {
                    if (isset($images[$value])) {
                        $node->setAttribute('src', $images[$value]);
                    } elseif (!in_array($value, $images, true)) {
                        throw new \InvalidArgumentException('Image src must reference a filename listed in image_files: ' . $value . '.');
                    }
                } elseif ($name === 'class' && $value === 'math-tex' && in_array($node->tagName, ['span', 'div'], true)) {
                    // CKEditor MathJax plugin markup.
                } elseif ($node->tagName === 'img' && in_array($name, ['alt', 'title'], true)) {
                    continue;
                } elseif (in_array($name, ['width', 'height', 'colspan', 'rowspan'], true) && preg_match('/\A[1-9][0-9]{0,3}\z/', $value)) {
                    continue;
                } else {
                    throw new \InvalidArgumentException('Unsupported HTML attribute: ' . $name . '.');
                }
            }
            if ($node->tagName === 'img' && !$node->hasAttribute('src')) {
                throw new \InvalidArgumentException('Every image needs a src.');
            }
        }
        // Remove comments, including the tags metadata, before storing rendered content.
        $xpath = new \DOMXPath($doc);
        foreach ($xpath->query('.//comment()', $root) as $comment) {
            $comment->parentNode->removeChild($comment);
        }
        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $doc->saveHTML($child);
        }
        if (trim(html_entity_decode(strip_tags($output), ENT_QUOTES | ENT_HTML5, 'UTF-8')) === '' && !str_contains($output, '<img')) {
            throw new \InvalidArgumentException('Content cannot be empty.');
        }
        return $output;
    }
}
