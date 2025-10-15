import ArticlesAccueil from '../components/Acceuil/ArticlesAccueil';
import Carousel3D from '../components/Acceuil/Carousel3D';
import './Acceuil.css';

const API = import.meta.env.VITE_API_URL as string;

export default function Acceuil() {
    
  return (
    // Accueil.tsx (exemple)
    <div className="home-container">
         <section className="carousel-section">
        <h2>Nos Réalisations</h2>
        <Carousel3D
          apiUrl={`${API}/api/carrousels`}
          selectItems={(data) => data.member || data['hydra:member'] || []}
          resolveIri={false}
          mapItem={(item) => {
            console.log('Item reçu:', item);
            
            // Filtre les inactifs
            if (!item?.active) {
              console.log('  → Filtré (inactif)');
              return null;
            }
            
            // Utilise mediaFilename ou mediaPublicPath
            const filename = item.mediaFilename;
            if (!filename) {
              console.log('  → Filtré (pas de filename)');
              return null;
            }
            
            return {
              src: `${API}/uploads/media/${filename}`,
              alt: item.title || filename
            };
          }}
          requestInit={{ credentials: 'omit' }}
        />
      </section>

        {/* Section Articles */}
        <ArticlesAccueil />
    
    </div>
  );
}