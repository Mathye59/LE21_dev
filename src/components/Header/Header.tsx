import { useEffect, useMemo, useState } from "react";
import "./Header.css";
import { NavLink } from "react-router-dom";

const API = import.meta.env.VITE_API_URL as string;

type Entreprise = { logoUrl?: string; logoName?: string };

const abs = (p?: string) =>
  !p ? undefined : /^https?:\/\//i.test(p) ? p : `${API.replace(/\/+$/, "")}/${p.replace(/^\/+/, "")}`;

export default function Header() {
  const [open, setOpen] = useState(false);
  const [logo, setLogo] = useState<string | undefined>();

  useEffect(() => {
    let stop = false;
    (async () => {
      try {
        const r = await fetch(`${API}/api/entreprise`, { credentials: "omit" });
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        const e: Entreprise = await r.json();
        const url = abs(e.logoUrl) ?? (e.logoName ? abs(`/uploads/logos/${e.logoName}`) : undefined);
        if (!stop && url) setLogo(url);
      } catch (err) {
        console.error("[Header] /api/entreprise:", err);
      }
    })();
    return () => { stop = true; };
  }, []);

  const burgerSrc = useMemo(() => `/images/menuBurger.png`, []);
  const separatorSrc = useMemo(() => `${API}/images/separateur.png`, []);

  return (
    <header className="luxury-header">
      <div className="header-content">
        {/* Nav gauche */}
        <nav className="nav-left" aria-label="Navigation principale gauche">
          <ul>
            <li><NavLink to="/" className={({isActive}) => isActive ? "active" : undefined}>
                Accueil
              </NavLink>
            </li>
            <li><NavLink to="/flash" className={({isActive}) => isActive ? "active" : undefined}>
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
            <li> <NavLink to="/blog" className={({isActive}) => isActive ? "active" : undefined}>
                Blog
              </NavLink>
          </li>
            <li> <NavLink to="/contact" className={({isActive}) => isActive ? "active" : undefined}>
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