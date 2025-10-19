import { useEffect, useState } from 'react';
import './ArticlesAccueil.css';
import ScrollReveal from './ScrollReveal';

const API = import.meta.env.VITE_API_URL as string;

type ArticleAccueil = {
  id: number;
  titre: string;
  contenu: string;
  media: string | { filename: string };
};

export default function ArticlesAccueil() {
  const [articles, setArticles] = useState<ArticleAccueil[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancel = false;

    (async () => {
      try {
        const res = await fetch(`${API}/api/article_accueils`, { credentials: 'omit' });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        
        let items = data.member || data['hydra:member'] || [];
        
        items = await Promise.all(
          items.map(async (article: ArticleAccueil) => {
            if (typeof article.media === 'string') {
              try {
                const mediaRes = await fetch(`${API}${article.media}`, { credentials: 'omit' });
                if (mediaRes.ok) {
                  article.media = await mediaRes.json();
                }
              } catch (err) {
                console.error('Erreur résolution media:', err);
              }
            }
            return article;
          })
        );
        
        if (!cancel) setArticles(items);
      } catch (err) {
        console.error('[ArticlesAccueil] Erreur:', err);
      } finally {
        if (!cancel) setLoading(false);
      }
    })();

    return () => { cancel = true; };
  }, []);

  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            
            // Anime les mots un par un
            const words = entry.target.querySelectorAll('.word');
            words.forEach((word, index) => {
              setTimeout(() => {
                word.classList.add('visible');
              }, index * 50);
            });
          }
        });
      },
      { threshold: 0.1, rootMargin: '-50px' }
    );

    const articles = document.querySelectorAll('.article-item');
    articles.forEach((article) => observer.observe(article));

    return () => {
      observer.disconnect();
    };
  }, [articles]);

  // Fonction pour diviser le texte en mots avec espaces
  const splitTextIntoWords = (html: string) => {
    // Enlève les balises HTML
    const text = html.replace(/<[^>]*>/g, ' ');
    // Split en gardant les espaces et ponctuation
    return text.split(/(\s+)/).filter(part => part.trim()).map((word, index) => (
      <span key={index} className="word">
        {word}
      </span>
    ));
  };

  if (loading) return <div className="articles-loading">Chargement...</div>;
  if (!articles.length) return null;

  return (
    <section className="articles-accueil">
      {articles.map((article, index) => {
        const isLeft = index % 2 === 0;
        
        return (
          <div key={article.id}>
            <article 
              className={`article-item ${isLeft ? 'article-left' : 'article-right'}`}
            >
              {/* Image coin */}
              <img 
                src={`${API}/images/${isLeft ? 'coin-gauche-h.png' : 'coin-droit-b.png'}`}
                alt="coin décoratif"
                className={`corner-image ${isLeft ? 'top-left' : 'bottom-right'}`}
              />
              
              <div className="article">
                {/* Titre sans ScrollReveal */}
                <h3 className="article-title ">{article.titre}</h3>               
                <div className="article-flex">
                  {/* Image */}
                  <div className="article-image">
                    <img 
                      src={`${API}/uploads/media/${typeof article.media === 'object' ? article.media?.filename : ''}`} 
                      alt={article.titre}
                    />
                  </div>
                  
                  {/* Contenu avec ScrollReveal */}
                  <div className="article-content">
                    <ScrollReveal html={article.contenu} delay={60} className="article-text" />
                      
                  </div>
                </div>
              </div>
            </article>

            {/* Séparateur horizontal entre articles */}
            {index < articles.length - 1 && (
              <div className="separator-horizontal">
                <img 
                  src={`${API}/images/separateur.png`}
                  alt="séparateur"
                />
              </div>
            )}
          </div>
        );
      })}
    </section>
  );
}