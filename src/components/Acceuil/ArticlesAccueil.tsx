import { useEffect, useState } from 'react';
import './ArticlesAccueil.css';
import ScrollReveal from './ScrollReveal';

const API = import.meta.env.VITE_API_URL as string;

type ArticleAccueil = {
  id: number;
  titre: string;
  contenu: string;
  media: string | { filename: string }; // IRI ou objet
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
        
        console.log('📦 Data ArticlesAccueil:', data);
        
        let items = data.member || data['hydra:member'] || [];
        console.log('📋 Articles avant resolve:', items);
        
        // Résoudre les IRIs des médias
        items = await Promise.all(
          items.map(async (article: ArticleAccueil) => {
            if (typeof article.media === 'string') {
              // C'est une IRI, on la résout
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
        
        console.log('📋 Articles après resolve:', items);
        
        if (!cancel) setArticles(items);
      } catch (err) {
        console.error('[ArticlesAccueil] Erreur:', err);
      } finally {
        if (!cancel) setLoading(false);
      }
    })();

    return () => { cancel = true; };
  }, []);

  if (loading) return <div className="articles-loading">Chargement...</div>;
  if (!articles.length) return null;

  return (
        <ScrollReveal
      baseOpacity={0}
      enableBlur={true}
      baseRotation={5}
      blurStrength={10}
    >
    <section className="articles-accueil">
      {articles.map((article, index) => {
        const isLeft = index % 2 === 0;
        console.log('🖼️ Article:', article);
        console.log('📸 Media:', article.media);
        console.log('🔗 URL image:', `${API}/uploads/media/${article.media?.filename}`);
        
        return (
          <div key={article.id}>
            <article className={`article-item ${isLeft ? 'article-left' : 'article-right'}`}>
              {/* Image coin */}
              <img 
                src={`${API}/images/${isLeft ? 'coin-gauche-h.png' : 'coin-droit-b.png'}`}
                alt="coin décoratif"
                className={`corner-image ${isLeft ? 'top-left' : 'bottom-right'}`}
              />
              <div className="article">
              {/* Titre */}
              <h3 className="article-title inner">{article.titre}<span></span></h3>
            <div className='article-flex'>
             {/* Image */}
              <div className="article-image">
                <img 
                  src={`${API}/uploads/media/${article.media?.filename}`} 
                  alt={article.titre}
                  onError={(e) => {
                    console.error('Erreur chargement image:', `${API}/uploads/media/${article.media?.filename}`);
                  }}
                />
              </div>
              {/* Contenu */}
              <div className="article-content">
                
                <div 
                  className="article-text"
                  dangerouslySetInnerHTML={{ __html: article.contenu }}
                />
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
    </section></ScrollReveal>
  );
}