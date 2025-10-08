<?php
namespace App\Security;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * ArticleHtmlSanitizer
 * ------------------------------------------------------------
 * Rôle :
 *  - Nettoyer le HTML saisi dans les articles (éditeur riche) pour éviter
 *    les injections XSS tout en autorisant une mise en forme basique.
 *
 * Principe :
 *  - On part d’une base sûre avec ->allowSafeElements() (paragraphe, <br>, <b>,
 *    <strong>, <em>, etc. — cf. doc Symfony).
 *  - On ajoute explicitement quelques éléments supplémentaires utiles au blog :
 *    titres (h2..h4), listes (ul/ol/li) et liens (a).
 *  - On limite les schémas autorisés pour les liens (http/https/mailto),
 *    on autorise les liens relatifs, et on force les attributs de sécurité sur <a>.
 *
 * Notes de sécurité :
 *  - Les attributs "on*" (onerror, onclick…), les iframes, scripts, styles inline,
 *    et les URL dangereuses (javascript:, data: pour <a>) sont bloqués par défaut.
 *  - Les <img> NE sont pas autorisées ici (bon choix si l’image passe via votre entité Media).
 *  - Forcer rel="nofollow noopener noreferrer" sur <a> protège contre le tab-nabbing
 *    et réduit les signaux SEO issus des contenus saisis par les utilisateurs.
 */
class ArticleHtmlSanitizer
{
    /** Sanitizeur compilé/immutable à réutiliser pour chaque appel. */
    private HtmlSanitizer $sanitizer;

    public function __construct()
    {
        // Configuration "whitelist" : on précise ce qui est permis, tout le reste est filtré.
        $config = (new HtmlSanitizerConfig())

            // 1) Point de départ sûr : éléments et attributs jugés "safe" par Symfony
            //    (p, br, b, strong, em, code, blockquote, etc. — sans liens/scripts dangereux)
            ->allowSafeElements()

            // 2) Éléments supplémentaires explicitement autorisés
            ->allowElement('h2')
            ->allowElement('h3')
            ->allowElement('h4')
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('a')

            // 3) Liens : schémas autorisés pour href (bloque javascript:, data:, vbscript:, etc.)
            ->allowLinkSchemes(['http', 'https', 'mailto'])

            // 4) Attributs autorisés sur <a>
            ->allowAttribute('href', 'a')
            ->allowAttribute('target', 'a')
            ->allowAttribute('rel', 'a')

            // 5) Défense en profondeur : impose rel de sécurité sur tous les liens
            //    (même si l’éditeur ne les met pas)
            ->forceAttribute('rel', 'a', 'nofollow noopener noreferrer')

            // 6) Autorise les liens relatifs (ex: /contact), utile pour pointer en interne
            ->allowRelativeLinks()
        ;

        // On construit l’instance HtmlSanitizer une fois pour toutes.
        $this->sanitizer = new HtmlSanitizer($config);
    }

    /**
     * Nettoie un fragment HTML selon la config ci-dessus.
     * - Retourne une chaîne "safe" à stocker en base / afficher.
     * - Idempotent : re-sanitiser un contenu déjà nettoyé ne pose pas de souci.
     */
    public function sanitize(string $html): string
    {
        return $this->sanitizer->sanitize($html);
    }
}


