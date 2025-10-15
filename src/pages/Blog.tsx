import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import './Blog.css';

const API = import.meta.env.VITE_API_URL as string;

type ArticleBlog = {
  id: number;
  titre: string;
  contenu: string;
  date: string;
  auteur: {
    prenom: string;
    nom: string;
  };
  media: {
    filename: string;
  };
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
        
        const items = data.member || data['hydra:member'] || [];
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

  return (
    <div className="blog-container">
      <h1 className="blog-title">Blog</h1>
      
      <div className="articles-list">
        {articles.map((article, index) => (
          <div key={article.id}>
            <Link to={`/blog/${article.id}`} className="article-card">
              {/* Séparateur décoratif coin */}
              <div className="separator-corner">
                <svg viewBox="0 0 150 150" xmlns="http://www.w3.org/2000/svg">
                  <path d="M10,10 L75,75 M75,75 L140,10 M75,75 L75,140" stroke="#F7F0BA" strokeWidth="2" fill="none"/>
                  <circle cx="75" cy="75" r="8" fill="#F7F0BA"/>
                </svg>
              </div>

              {/* Image */}
              <div className="article-image">
                <img 
                  src={`${API}/uploads/media/${article.media?.filename}`} 
                  alt={article.titre}
                />
              </div>

              {/* Contenu */}
              <div className="article-info">
                <h2 className="article-card-title">{article.titre}</h2>
                
                <div 
                  className="article-excerpt"
                  dangerouslySetInnerHTML={{ 
                    __html: article.contenu.substring(0, 200) + '...' 
                  }}
                />

                <div className="article-meta">
                  <span className="article-author">
                    Par {article.auteur?.prenom || 'Anonyme'}
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
                <svg viewBox="0 0 200 60" xmlns="http://www.w3.org/2000/svg">
                  <path d="M20,30 L80,30 M120,30 L180,30" stroke="#F7F0BA" strokeWidth="1"/>
                  <circle cx="100" cy="30" r="5" fill="#F7F0BA"/>
                  <path d="M85,20 Q100,30 85,40 M115,20 Q100,30 115,40" stroke="#F7F0BA" strokeWidth="1" fill="none"/>
                </svg>
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}