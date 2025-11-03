import { useEffect, useState, useRef } from "react";
import "./Footer.css";
import { Link } from "react-router-dom";

const API = import.meta.env.VITE_API_URL as string;

type Entreprise = {
  nom?: string;
  adresse?: string;
  facebook?: string;
  instagram?: string;
  horairesOuverture?: string;
  horairesFermeture?: string;
  horairePlus?: string;
  telephone?: string;
  email?: string;
  logoUrl?: string;
};

export default function Footer() {
  const [entreprise, setEntreprise] = useState<Entreprise | null>(null);
  const [horairesMobileOpen, setHorairesMobileOpen] = useState(false);
  const overlayRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    let mounted = true;
    
    fetch(`${API}/api/entreprise`, { credentials: "omit" })
      .then(r => r.ok ? r.json() : Promise.reject(`HTTP ${r.status}`))
      .then((data: Entreprise) => {
        if (mounted) setEntreprise(data);
      })
      .catch(err => console.error("[Footer] Erreur lors de la récupération des données:", err));

    return () => { mounted = false; };
  }, []);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (overlayRef.current && !overlayRef.current.contains(event.target as Node)) {
        setHorairesMobileOpen(false);
      }
    };

    if (horairesMobileOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [horairesMobileOpen]);

  const logoUrl = entreprise?.logoUrl ? `${API}${entreprise.logoUrl}` : undefined;

  const toggleHorairesMobile = () => {
    setHorairesMobileOpen(prev => !prev);
  };

  return (
    <>
      <footer className="main-footer">
        {/* Overlay horaires au-dessus du footer */}
        {horairesMobileOpen && (
          <div ref={overlayRef} className="horaires-mobile-overlay">
            <h3 className="horaires-overlay-title">HORAIRES</h3>
            
            <div className="horaires-overlay-info">
              {entreprise?.horairesOuverture && (
                <p dangerouslySetInnerHTML={{ __html: entreprise.horairesOuverture }} />
              )}
              {entreprise?.horairesFermeture && (
                <p dangerouslySetInnerHTML={{ __html: entreprise.horairesFermeture }} />
              )}
              {entreprise?.horairePlus && (
                <p dangerouslySetInnerHTML={{ __html: entreprise.horairePlus }} />
              )}
            </div>
          </div>
        )}

        <div className="footer-content">
          {/* Section Logo - Cliquable sur mobile pour afficher la popup horaires */}
          <div className="footer-section footer-logo-section size-footer">
            <div 
              className="footer-logo-container"
              onClick={toggleHorairesMobile}
              role="button"
              tabIndex={0}
              onKeyDown={(e) => e.key === 'Enter' && toggleHorairesMobile()}
              aria-expanded={horairesMobileOpen}
              aria-label="Afficher/masquer les horaires"
            >
              {logoUrl ? (
                <img 
                  src={logoUrl} 
                  alt={entreprise?.nom || "Logo"} 
                  className="logo-footer" 
                />
              ) : (
                <div className="logo-placeholder-footer">LOGO</div>
              )}
            </div>
          </div>

          <div className="footer-sections-group">
            {/* Section Adresse */}
            <div className="footer-section size-footer">
              <h4>Adresse</h4>
              <div className="contact-info">
                {entreprise?.adresse ? (
                  <div dangerouslySetInnerHTML={{ __html: entreprise.adresse }} />
                ) : (
                  <>
                    <p>21 Rue de la République</p>
                    <p>59500 Douai</p>
                    <p>France</p>
                  </>
                )}
              </div>
            </div>

            {/* Section Contact + Réseaux sociaux */}
            <div className="footer-section size-footer">
              <h4>Contact</h4>
              <div className="contact-info">
                {entreprise?.telephone && (
                  <p>Tél: {entreprise.telephone}</p>
                )}
                {entreprise?.email && (
                  <p>Email: {entreprise.email}</p>
                )}

                {/* Boutons réseaux sociaux */}
                <div className="follow-row">
                  {entreprise?.facebook && (
                    <a 
                      className="follow-btn" 
                      href={entreprise.facebook} 
                      target="_blank" 
                      rel="noopener noreferrer"
                      aria-label="Facebook"
                    >
                      <svg 
                        enableBackground="new 0 0 430.113 430.114"
                        version="1.1" 
                        viewBox="0 0 430.113 430.114" 
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path d="m158.08 83.3v59.218h-43.385v72.412h43.385v215.18h89.122v-215.18h59.805s5.601-34.721 8.316-72.685h-67.784s0-42.127 0-49.511c0-7.4 9.717-17.354 19.321-17.354h48.557v-75.385h-66.021c-93.519-5e-3 -91.316 72.479-91.316 83.299z" />
                      </svg>
                    </a>
                  )}

                  {entreprise?.instagram && (
                    <a 
                      className="follow-btn" 
                      href={entreprise.instagram} 
                      target="_blank" 
                      rel="noopener noreferrer"
                      aria-label="Instagram"
                    >
                      <svg
                        enableBackground="new 0 0 512 512" 
                        version="1.1" 
                        viewBox="0 0 512 512"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path d="M0,0H160C71.648,0,0,71.648,0,160v192c0,88.352,71.648,160,160,160h192c88.352,0,160-71.648,160-160V160 C512,71.648,440.352,0,352,0z M464,352c0,61.76-50.24,112-112,112H160c-61.76,0-112-50.24-112-112V160C48,98.24,98.24,48,160,48 h192c61.76,0,112,50.24,112,112V352z" />
                        <path d="m256 128c-70.688 0-128 57.312-128 128s57.312 128 128 128 128-57.312 128-128-57.312-128-128-128zm0 208c-44.096 0-80-35.904-80-80 0-44.128 35.904-80 80-80s80 35.872 80 80c0 44.096-35.904 80-80 80z" />
                        <circle cx="393.6" cy="118.4" r="17.056" />
                      </svg>
                    </a>
                  )}
                </div>
              </div>
            </div>
          </div>

          {/* Section Horaires - Cachée sur mobile, visible sur desktop */}
          <div className="footer-section horaires-desktop size-footer">
            <h4>Horaires</h4>
            <div className="contact-info">
              {entreprise?.horairesOuverture && (
                <p dangerouslySetInnerHTML={{ __html: entreprise.horairesOuverture }} />
              )}
              {entreprise?.horairesFermeture && (
                <p dangerouslySetInnerHTML={{ __html: entreprise.horairesFermeture }} />
              )}
              {entreprise?.horairePlus && (
                <p dangerouslySetInnerHTML={{ __html: entreprise.horairePlus }} />
              )}
            </div>
          </div>
        </div>

        {/* Footer bottom - Copyright */}
        <div className="footer-bottom">
          <div className="footer-separator"></div>
          <p>&copy; {new Date().getFullYear()} {entreprise?.nom || "Le 21 Tattoo & Piercing"}. Tous droits réservés.</p>
          <Link to="/mentions-legales">Mentions Légales & RGPD</Link>
        </div>
      </footer>
    </>
  );
}