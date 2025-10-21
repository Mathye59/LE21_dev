import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import './Contact.css';

const API = import.meta.env.VITE_API_URL as string;

type Tatoueur = {
  id: number;
  pseudo: string;
  email: string;
};

export default function Contact() {
  const [tatoueurs, setTatoueurs] = useState<Tatoueur[]>([]);
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');

  const [formData, setFormData] = useState({
    nomPrenom: '',
    email: '',
    telephone: '',
    sujet: '',
    message: '',
    tatoueur: '',
    rgpdAccepted: false
  });

  useEffect(() => {
    (async () => {
      try {
        const res = await fetch(`${API}/api/tatoueurs`, { credentials: 'omit' });
        if (!res.ok) throw new Error('Erreur chargement tatoueurs');
        const data = await res.json();
        const items = data.member || data['hydra:member'] || [];
        setTatoueurs(items);
      } catch (err) {
        console.error('[Contact] Erreur:', err);
      }
    })();
  }, []);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value, type } = e.target;
    const checked = (e.target as HTMLInputElement).checked;
    
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.rgpdAccepted) {
      setError('Vous devez accepter la politique de confidentialité');
      return;
    }
    
    setLoading(true);
    setError('');
    setSuccess(false);

    try {
      const payload = {
        nomPrenom: formData.nomPrenom,
        email: formData.email,
        telephone: formData.telephone,
        sujet: formData.sujet,
        message: formData.message,
        tatoueur: `/api/tatoueurs/${formData.tatoueur}`,
        date: new Date().toISOString()
      };

      const res = await fetch(`${API}/api/form_contacts`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/ld+json' },
        credentials: 'omit',
        body: JSON.stringify(payload)
      });

      if (!res.ok) throw new Error('Erreur envoi formulaire');

      setSuccess(true);
      setFormData({
        nomPrenom: '',
        email: '',
        telephone: '',
        sujet: '',
        message: '',
        tatoueur: '',
        rgpdAccepted: false
      });
    } catch (err: any) {
      setError(err.message || 'Une erreur est survenue');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="contact-container">
      <div className="contact-wrap">
        <h3 className="contact-title">Contact</h3>

        <div className="separator-corner-contact">
          <img src={`${API}/images/coin-gauche-h.png`} alt="coin" />
        </div>

        {success && (
          <div className="alert alert-success">
            ✓ Votre message a été envoyé avec succès !
          </div>
        )}

        {error && (
          <div className="alert alert-error">
            ✗ {error}
          </div>
        )}

        <form className="contact-form" onSubmit={handleSubmit}>
          <div className="form-row">
            <div className="form-group">
              <input
                type="text"
                id="nomPrenom"
                name="nomPrenom"
                placeholder="Nom-prénom"
                value={formData.nomPrenom}
                onChange={handleChange}
                required
                maxLength={50}
              />
            </div>

            <div className="form-group">
              <input
                type="email"
                id="email"
                name="email"
                placeholder="Email"
                value={formData.email}
                onChange={handleChange}
                required
                maxLength={255}
              />
            </div>

            <div className="form-group">
              <input
                type="tel"
                id="telephone"
                name="telephone"
                placeholder="Numéro téléphone"
                value={formData.telephone}
                onChange={handleChange}
                required
                maxLength={20}
              />
            </div>

            <div className="form-group">
              <select
                id="tatoueur"
                name="tatoueur"
                value={formData.tatoueur}
                onChange={handleChange}
                required
              >
                <option value="">Tatoueur</option>
                {tatoueurs.map(t => (
                  <option key={t.id} value={t.id}>
                    {t.pseudo}
                  </option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <input
                type="text"
                id="sujet"
                name="sujet"
                placeholder="Objet message"
                value={formData.sujet}
                onChange={handleChange}
                required
                maxLength={50}
              />
            </div>

            <div className="form-group">
              <textarea
                id="message"
                name="message"
                placeholder="Message"
                value={formData.message}
                onChange={handleChange}
                required
                rows={6}
              />
            </div>

            {/* RGPD Checkbox */}
            <div className="form-group-checkbox">
              <label className="checkbox-label">
                <input
                  type="checkbox"
                  name="rgpdAccepted"
                  checked={formData.rgpdAccepted}
                  onChange={handleChange}
                  required
                />
                <span className="checkbox-text">
                  J'accepte que mes données soient utilisées dans le cadre de ma demande. 
                  Consultez notre{' '}
                  <Link to="/mentions-legales" className="rgpd-link">
                    politique de confidentialité
                  </Link>
                  {' '}pour plus d'informations.
                </span>
              </label>
            </div>
          </div>

          <button type="submit" className="submit-btn" disabled={loading || !formData.rgpdAccepted}>
            {loading ? 'Envoi en cours...' : 'Envoyer'}
          </button>
        </form>
      </div>
    </div>
  );
}