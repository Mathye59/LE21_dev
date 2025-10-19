import { useEffect, useMemo, useRef } from 'react';
import './ScrollReveal.css';

type Props = {
  html?: string;            // string (peut contenir entités & balises)
  children?: string;        // alternative: texte brut
  className?: string;
  delay?: number;           // ms entre mots
};

function decodeHtmlToText(input: string) {
  // décode les entités HTML et enlève les balises
  const div = document.createElement('div');
  div.innerHTML = input ?? '';
  return div.textContent || '';
}

export default function ScrollReveal({ html, children, className = '', delay = 50 }: Props) {
  const elRef = useRef<HTMLDivElement>(null);

  // 1) source → texte (décodé), on garde les retours
  const plainText = useMemo(() => {
    const src = html ?? children ?? '';
    const text = decodeHtmlToText(src.replace(/<br\s*\/?>/gi, '\n'));
    return text;
  }, [html, children]);

  // 2) tokenisation en mots ET espaces (pour ne pas “coller”)
  const tokens = useMemo(() => {
    if (!plainText.trim()) return [<span key="empty" />]; // évite un DIV vide
    return plainText.split(/(\s+)/).map((part, i) => {
      if (part === '') return null;
      if (/^\s+$/.test(part)) {
        return <span key={`s-${i}`} style={{ whiteSpace: 'pre' }}>{part}</span>;
      }
      return <span key={`w-${i}`} className="reveal-word">{part}</span>;
    });
  }, [plainText]);

  // 3) IO → anime mot par mot
  useEffect(() => {
    const el = elRef.current;
    if (!el) return;
    const io = new IntersectionObserver(
      ([entry]) => {
        if (!entry.isIntersecting) return;
        const words = el.querySelectorAll<HTMLElement>('.reveal-word');
        words.forEach((w, i) => setTimeout(() => w.classList.add('reveal-word-visible'), i * delay));
        io.unobserve(entry.target);
      },
      { threshold: 0.1, rootMargin: '-50px' }
    );
    io.observe(el);
    return () => io.disconnect();
  }, [delay, plainText]);

  return (
    <div ref={elRef} className={`scroll-reveal ${className}`} aria-hidden={false}>
      {tokens}
    </div>
  );
}

