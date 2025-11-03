import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import './CookieConsent.css';

export default function CookieConsent() {
  const [showBanner, setShowBanner] = useState(false);

  useEffect(() => {
    const consent = localStorage.getItem('cookiesAccepted');
    if (!consent) {
      setShowBanner(true);
    }
  }, []);

  const handleAccept = () => {
    localStorage.setItem('cookiesAccepted', 'true');
    setShowBanner(false);
  };

  const handleRefuse = () => {
    localStorage.setItem('cookiesAccepted', 'false');
    setShowBanner(false);
  };

  if (!showBanner) return null;

  return (
    <div className="cookie-banner">
      <div className="cookie-content">
        <p className="cookie-text">
          Nous utilisons des cookies pour améliorer votre expérience. 
          <Link to="/mentions-legales" className="cookie-link"> En savoir plus</Link>
        </p>
        <div className="cookie-buttons">
          <button onClick={handleRefuse} className="cookie-button cookie-refuse">
            Refuser
          </button>
          <button onClick={handleAccept} className="cookie-button cookie-accept">
            J'accepte
          </button>
        </div>
      </div>
    </div>
  );
}