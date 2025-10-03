<?php
namespace App\Security;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class ArticleHtmlSanitizer
{
    private HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $config = (new HtmlSanitizerConfig())
            ->allowSafeElements()
            // Équivalent de allowElements([...])
            ->allowElement('h2')
            ->allowElement('h3')
            ->allowElement('h4')
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('a')
            // schémas de liens
            ->allowLinkSchemes(['http', 'https', 'mailto'])
            // Équivalent de allowAttributes(['href','target','rel'], 'a')
            ->allowAttribute('href', 'a')
            ->allowAttribute('target', 'a')
            ->allowAttribute('rel', 'a')
            ->forceAttribute('rel', 'a', 'nofollow noopener noreferrer')
            ->allowRelativeLinks()
        ;

        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function sanitize(string $html): string
    {
        return $this->sanitizer->sanitize($html);
    }
}

