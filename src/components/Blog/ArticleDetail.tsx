import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import './ArticleDetail.css';

const API = import.meta.env.VITE_API_URL as string;

type ArticleBlog = {
  id: number;
  titre: string;
  contenu: string;
  date: string;
  auteur: any;
  media: any;
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
        let data = await res.json();
        
        // Résoudre auteur si IRI
        if (typeof data.auteur === 'string' && data.auteur.startsWith('/api/')) {
          try {
            const auteurRes = await fetch(`${API}${data.auteur}`, { credentials: 'omit' });
            if (auteurRes.ok) data.auteur = await auteurRes.json();
          } catch (err) {
            console.error('Erreur résolution auteur:', err);
          }
        }

        // Résoudre media si IRI
        if (typeof data.media === 'string' && data.media.startsWith('/api/')) {
          try {
            const mediaRes = await fetch(`${API}${data.media}`, { credentials: 'omit' });
            if (mediaRes.ok) data.media = await mediaRes.json();
          } catch (err) {
            console.error('Erreur résolution media:', err);
          }
        }
        
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
        ← Retour 
      </Link>

      <article className="article-detail">
        {/* En-tête avec image */}
        <div className="article-header">
          {article.media?.filename && (
            <img 
              src={`${API}/uploads/media/${article.media.filename}`} 
              alt={article.titre}
              className="article-header-image"
            />
          )}
          
          <div className="article-header-overlay">
            <h1 className="article-detail-title">{article.titre}</h1>
            
            <div className="article-detail-meta">
              <span className="article-detail-author">
                Par {article.auteur?.pseudo || 'Anonyme'}
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
          <img 
            src={`${API}/images/separateur.png`}
            alt="séparateur"
          />
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