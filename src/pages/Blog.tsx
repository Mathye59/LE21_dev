import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import './Blog.css';

const API = import.meta.env.VITE_API_URL as string;

type ArticleBlog = {
  id: number;
  titre: string;
  contenu: string;
  resume?: string;
  date: string;
  auteur: any;
  media: any;
};

export default function Blog() {
  const [articles, setArticles] = useState<ArticleBlog[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancel = false;

    (async () => {
      try {
        const res = await fetch(`${API}/api/article_blogs`, { credentials: 'omit' });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        
        let items = data.member || data['hydra:member'] || [];
        
        // Résoudre les IRIs
        items = await Promise.all(
          items.map(async (article: any) => {
            // Résoudre auteur si IRI
            if (typeof article.auteur === 'string' && article.auteur.startsWith('/api/')) {
              try {
                const auteurRes = await fetch(`${API}${article.auteur}`, { credentials: 'omit' });
                if (auteurRes.ok) article.auteur = await auteurRes.json();
              } catch (err) {
                console.error('Erreur résolution auteur:', err);
              }
            }

            // Résoudre media si IRI
            if (typeof article.media === 'string' && article.media.startsWith('/api/')) {
              try {
                const mediaRes = await fetch(`${API}${article.media}`, { credentials: 'omit' });
                if (mediaRes.ok) article.media = await mediaRes.json();
              } catch (err) {
                console.error('Erreur résolution media:', err);
              }
            }
            
            return article;
          })
        );
        
        if (!cancel) setArticles(items);
      } catch (err) {
        console.error('[Blog] Erreur:', err);
      } finally {
        if (!cancel) setLoading(false);
      }
    })();

    return () => { cancel = true; };
  }, []);

  if (loading) {
    return <div className="blog-loading">Chargement des articles...</div>;
  }

  if (articles.length === 0) {
    return (
      <div className="blog-container">
        <h1 className="blog-title">Blog</h1>
        <div className="blog-loading">Aucun article disponible</div>
      </div>
    );
  }

  return (
    <div className="blog-container">

      
      <div className="articles-list">
        {articles.map((article, index) => (
          <div key={article.id}>
            <Link to={`/blog/${article.id}`} className="article-card">
              {/* Séparateur décoratif coin */}
              <div className="separator-corner">
                <img 
                  src={`${API}/images/coin-gauche-h.png`}
                  alt="coin décoratif"
                  className="corner-image"
                />
              </div>

              {/* Image */}
              {article.media?.filename && (
                <div className="article-image">
                  <img 
                    src={`${API}/uploads/media/${article.media.filename}`} 
                    alt={article.titre}
                  />
                </div>
              )}

              {/* Contenu */}
              <div className="article-info">
                <h2 className="article-card-title">{article.titre}</h2>
                
                <div 
                  className="article-excerpt"
                  dangerouslySetInnerHTML={{ 
                    __html: article.resume || article.contenu.substring(0, 200) + '...' 
                  }}
                />

                <div className="article-meta">
                  <span className="article-author">
                    Par {article.auteur?.pseudo || 'Anonyme'}
                  </span>
                  <span className="article-date">
                    {new Date(article.date).toLocaleDateString('fr-FR')}
                  </span>
                </div>
              </div>
            </Link>

            {/* Séparateur entre articles */}
            {index < articles.length - 1 && (
              <div className="separator-horizontal">
                <img 
                  src={`${API}/images/separateur.png`}
                  alt="séparateur"
                />
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}