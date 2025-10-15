import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import './ArticleDetail.css';

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

export default function ArticleDetail() {
  const { id } = useParams<{ id: string }>();
  const [article, setArticle] = useState<ArticleBlog | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancel = false;

    (async () => {
      try {
        const res = await fetch(`${API}/api/article_blogs/${id}`, { credentials: 'omit' });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        
        if (!cancel) setArticle(data);
      } catch (err) {
        console.error('[ArticleDetail] Erreur:', err);
      } finally {
        if (!cancel) setLoading(false);
      }
    })();

    return () => { cancel = true; };
  }, [id]);

  if (loading) {
    return <div className="article-loading">Chargement...</div>;
  }

  if (!article) {
    return <div className="article-error">Article introuvable</div>;
  }

  return (
    <div className="article-detail-container">
      <Link to="/blog" className="back-link">
        ← Retour au blog
      </Link>

      <article className="article-detail">
        {/* En-tête avec image */}
        <div className="article-header">
          <img 
            src={`${API}/uploads/media/${article.media?.filename}`} 
            alt={article.titre}
            className="article-header-image"
          />
          
          <div className="article-header-overlay">
            <h1 className="article-detail-title">{article.titre}</h1>
            
            <div className="article-detail-meta">
              <span className="article-detail-author">
                Par {article.auteur?.prenom} {article.auteur?.nom}
              </span>
              <span className="article-detail-date">
                {new Date(article.date).toLocaleDateString('fr-FR', {
                  day: 'numeric',
                  month: 'long',
                  year: 'numeric'
                })}
              </span>
            </div>
          </div>
        </div>

        {/* Séparateur décoratif */}
        <div className="separator-decorative">
          <svg viewBox="0 0 300 80" xmlns="http://www.w3.org/2000/svg">
            <path d="M50,40 L130,40 M170,40 L250,40" stroke="#F7F0BA" strokeWidth="1"/>
            <circle cx="150" cy="40" r="8" fill="#F7F0BA"/>
            <path d="M130,25 Q150,40 130,55 M170,25 Q150,40 170,55" stroke="#F7F0BA" strokeWidth="1.5" fill="none"/>
          </svg>
        </div>

        {/* Contenu */}
        <div 
          className="article-detail-content"
          dangerouslySetInnerHTML={{ __html: article.contenu }}
        />
      </article>
    </div>
  );
}