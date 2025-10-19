// @ts-nocheck
import { useEffect, useMemo, useState } from "react";
import "./Flash.css";
import FlashFilters from '../components/Flash/FlashFilters';

const API = import.meta.env.VITE_API_URL as string;

type Card = {
  id: string | number;
  titre: string;
  prix: number;
  categorie: string;
  duree: number;
  badge?: string;
  src: string;
  auteur?: string;
};

export default function Flash({ parPage = 12 }: { parPage?: number }) {
  const [brut, setBrut] = useState<Card[]>([]);
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState<string | null>(null);
  const [page, setPage] = useState(1);
  const [resetKey, setResetKey] = useState(0);
  const [showFiltersMobile, setShowFiltersMobile] = useState(false);

  // Filtres
  const [filters, setFilters] = useState({
    prixMax: 300,
    dureeMax: 10,
    categories: [] as string[],
  });

  useEffect(() => {
    let cancel = false;
    
    (async () => {
      setLoading(true);
      setErr(null);
      try {
        // Appel API Flash
        const res = await fetch(`${API}/api/flashes`, { credentials: 'omit' });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();

        let items = data.member || data['hydra:member'] || [];

        // Résoudre les IRIs si nécessaire
        items = await Promise.all(
          items.map(async (flash: any) => {
            // Résoudre categories (tableau d'IRIs)
            if (Array.isArray(flash.categories) && flash.categories.length > 0) {
              flash.categories = await Promise.all(
                flash.categories.map(async (catIri: string) => {
                  if (typeof catIri === 'string' && catIri.startsWith('/api/')) {
                    try {
                      const catRes = await fetch(`${API}${catIri}`, { credentials: 'omit' });
                      if (catRes.ok) return await catRes.json();
                    } catch (e) {
                      console.error('Erreur résolution categorie:', e);
                    }
                  }
                  return catIri;
                })
              );
            }

            // Résoudre tatoueur si IRI
            if (typeof flash.tatoueur === 'string' && flash.tatoueur.startsWith('/api/')) {
              try {
                const tatRes = await fetch(`${API}${flash.tatoueur}`, { credentials: 'omit' });
                if (tatRes.ok) flash.tatoueur = await tatRes.json();
              } catch (e) {
                console.error('Erreur résolution tatoueur:', e);
              }
            }

            return flash;
          })
        );

        // Mapper vers format Card
        const cards: Card[] = items
          .map((it: any) => {
            console.log('🔍 Flash item:', it);
            
            const filename = it.imageName;
            console.log('📁 Filename:', filename);
            
            if (!filename) {
              console.warn('⚠️ Pas de imageName pour:', it);
              return null;
            }

            // Extraire la première catégorie
            const firstCat = Array.isArray(it.categories) && it.categories.length > 0 
              ? (typeof it.categories[0] === 'object' ? it.categories[0].nom : it.categories[0])
              : 'Divers';

            // Parser la durée depuis "3h" -> 3
            const duree = typeof it.temps === 'string' 
              ? parseFloat(it.temps.replace(/[^0-9.]/g, '')) || 1
              : Number(it.temps || 1);

            const card = {
              id: it.id,
              titre: it.titre || `Flash #${it.id}`,
              prix: Number(it.prix || 0),
              categorie: firstCat,
              duree,
              badge: it.statut || undefined,
              src: `${API}/uploads/flashes/${filename}`,
              auteur: typeof it.tatoueur === 'object' ? it.tatoueur?.pseudo : undefined,
            };
            
            console.log('✅ Card créée:', card);
            return card;
          })
          .filter(Boolean) as Card[];

        console.log('📦 Total cards:', cards.length);

        // Calculer bornes dynamiques
        const pMax = Math.max(...cards.map((c) => c.prix), 300);
        const dMax = Math.max(...cards.map((c) => c.duree), 10);

        if (!cancel) {
          setBrut(cards);
          setFilters(prev => ({
            ...prev,
            prixMax: pMax || 300,
            dureeMax: dMax || 10,
          }));
        }
      } catch (e: any) {
        if (!cancel) setErr(e.message || "Erreur inconnue");
      } finally {
        if (!cancel) setLoading(false);
      }
    })();

    return () => { cancel = true; };
  }, []);

  const filtrés = useMemo(() => {
    return brut.filter((c) => {
      if (c.prix > filters.prixMax) return false;
      if (c.duree > filters.dureeMax) return false;
      if (filters.categories.length && !filters.categories.includes(c.categorie)) return false;
      return true;
    });
  }, [brut, filters]);

  const totalPages = Math.max(1, Math.ceil(filtrés.length / parPage));
  const pageSafe = Math.min(page, totalPages);
  const start = (pageSafe - 1) * parPage;
  const pageItems = filtrés.slice(start, start + parPage);

  if (loading) {
    return <div className="flash-wrap"><div className="loading">Chargement…</div></div>;
  }
  if (err) {
    return <div className="flash-wrap"><div className="error">Erreur : {err}</div></div>;
  }

  return (
    <section  id="flash" className="flash">
      <div className="flash-wrap">
      {/* Filtres */}
       <button 
          className="filters-mobile-toggle"
          onClick={() => setShowFiltersMobile(!showFiltersMobile)}
        >
          {showFiltersMobile ? '✕ Masquer' : ' Filtrer'}
        </button>
      <div className={`filters-container ${showFiltersMobile ? 'open' : ''}`}>
        <FlashFilters
          filters={filters}
          setFilters={(newFilters) => {
            setFilters(newFilters);
            setPage(1);
          }}
          resetKey={resetKey}
        />
      </div>

      {/* Grille */}
      <div className="grid">
        {pageItems.map((c) => (
          <article className="card" key={c.id}>
        {c.badge && <div className={`ribbon ${slug(c.badge)}`}>{c.badge}</div>}
        <div className="thumb">
          <img src={c.src} alt={c.titre} />
          {c.auteur && <span className="author">by {c.auteur}</span>}
        </div>
        <div className="meta">
          <div className="row">
            <span className="duration">{c.duree}h</span>
            <span className="price">{c.prix.toFixed(0)} €</span>
          </div>
          
        </div>
      </article>
                ))}

        {pageItems.length === 0 && (
          <div className="empty">Aucun résultat avec ces filtres.</div>
        )}
      </div>
      </div>
      {/* Pagination */}
      <nav className="pager" aria-label="Pagination">
        <button onClick={()=>setPage((p)=>Math.max(1,p-1))} disabled={pageSafe<=1}>‹</button>
        {Array.from({length: totalPages}, (_,i)=>i+1).slice(
          Math.max(0, pageSafe-3),
          Math.max(0, pageSafe-3)+5
        ).map((n)=>(
          <button key={n} className={n===pageSafe? "current": ""} onClick={()=>setPage(n)}>{n}</button>
        ))}
        <button onClick={()=>setPage((p)=>Math.min(totalPages,p+1))} disabled={pageSafe>=totalPages}>›</button>
      </nav>
    </section>
  );
}

function slug(s?: string) {
  return (s ?? "")
    .toLowerCase()
    .normalize("NFD")
    .replace(/\p{Diacritic}/gu, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}