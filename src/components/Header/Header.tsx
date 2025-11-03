import { useEffect, useMemo, useState } from "react";
import "./Header.css";
import { NavLink } from "react-router-dom";

const API = import.meta.env.VITE_API_URL as string;

type Entreprise = { 
  logoUrl?: string;
};

export default function Header() {
  const [open, setOpen] = useState(false);
  const [logo, setLogo] = useState<string>();

  useEffect(() => {
    let mounted = true;
    
    fetch(`${API}/api/entreprise`, { credentials: "omit" })
      .then(r => r.ok ? r.json() : Promise.reject(`HTTP ${r.status}`))
      .then((data: Entreprise) => {
        if (mounted && data.logoUrl) {
          setLogo(`${API}${data.logoUrl}`);
        }
      })
      .catch(err => console.error("[Header] /api/entreprise:", err));

    return () => { mounted = false; };
  }, []);

  const burgerSrc = useMemo(() => `/images/menuBurger.png`, []);
  const separatorSrc = useMemo(() => `${API}/images/separateur.png`, []);

  return (
    <header className="luxury-header">
      <div className="header-content">
        {/* Nav gauche */}
        <nav className="nav-left" aria-label="Navigation principale gauche">
          <ul>
            <li>
              <NavLink to="/" className={({isActive}) => isActive ? "active" : undefined}>
                Accueil
              </NavLink>
            </li>
            <li>
              <NavLink to="/flash" className={({isActive}) => isActive ? "active" : undefined}>
                Flash
              </NavLink>
            </li>
          </ul>
        </nav>

        {/* Logo centré */}
        <div className="logo-center">
          <div className="main-logo">
            {logo ? (
              <img src={logo} alt="Le 21 Tattoo & Piercing" className="logo" />
            ) : (
              <div className="logo-placeholder">LOGO</div>
            )}
          </div>
        </div>

        {/* Nav droite */}
        <nav className="nav-right" aria-label="Navigation principale droite">
          <ul>
            <li>
              <NavLink to="/blog" className={({isActive}) => isActive ? "active" : undefined}>
                Blog
              </NavLink>
            </li>
            <li>
              <NavLink to="/contact" className={({isActive}) => isActive ? "active" : undefined}>
                Contact
              </NavLink>
            </li>
          </ul>
        </nav>

        {/* Burger (mobile) */}
        <button
          type="button"
          className={`mobile-menu-toggle${open ? " active" : ""}`}
          aria-expanded={open}
          aria-label="Menu"
          onClick={() => setOpen(v => !v)}
        >
          <img src={burgerSrc} alt="" className="burger-icon" />
        </button>
      </div>

      {/* Menu mobile overlay avec séparateurs */}
      <div className={`mobile-menu${open ? " active" : ""}`}>
        <ul onClick={() => setOpen(false)}>
          <li>
            <NavLink to="/" className={({isActive}) => isActive ? "active" : undefined}>
              Accueil
            </NavLink>
            <img src={separatorSrc} alt="" style={{ width: '200px', margin: '10px auto', display: 'block', opacity: 0.7 }} />
          </li>
          <li>
            <NavLink to="/flash" className={({isActive}) => isActive ? "active" : undefined}>
              Flash
            </NavLink>
            <img src={separatorSrc} alt="" style={{ width: '200px', margin: '10px auto', display: 'block', opacity: 0.7 }} />
          </li>
          <li>
            <NavLink to="/blog" className={({isActive}) => isActive ? "active" : undefined}>
              Blog
            </NavLink>
            <img src={separatorSrc} alt="" style={{ width: '200px', margin: '10px auto', display: 'block', opacity: 0.7 }} />
          </li>
          <li>
            <NavLink to="/contact" className={({isActive}) => isActive ? "active" : undefined}>
              Contact
            </NavLink>
          </li>
        </ul>
      </div>
    </header>
  );
}