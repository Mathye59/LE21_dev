import { useEffect, useState } from 'react';
import GenericButton from './GenericButton';
import FilterRange from './FilterRange';
import FilterSection from './FilterSection';
import './FlashFilters.css';

const API = import.meta.env.VITE_API_URL as string;

type FlashFiltersProps = {
  filters: {
    prixMax: number;
    dureeMax: number;
    categories: string[];
  };
  setFilters: (filters: any) => void;
  onCloseMobile?: () => void;
  resetKey: number;
};

export default function FlashFilters({
  filters,
  setFilters,
  resetKey,
  onCloseMobile,
}: FlashFiltersProps) {
  const [prixMax, setPrixMax] = useState<number>(300);
  const [dureeMax, setDureeMax] = useState<number>(10);
  const [selectedCategories, setSelectedCategories] = useState<string[]>([]);
  const [availableCategories, setAvailableCategories] = useState<string[]>([]);

  // Récupérer les catégories depuis l'API
  useEffect(() => {
    let cancel = false;

    (async () => {
      try {
        const res = await fetch(`${API}/api/categories`, { credentials: 'omit' });
        if (!res.ok) return;
        const data = await res.json();
        
        const cats = (data.member || data['hydra:member'] || [])
          .map((c: any) => c.nom)
          .filter(Boolean)
          .sort();
        
        if (!cancel) setAvailableCategories(cats);
      } catch (err) {
        console.error('[FlashFilters] Erreur chargement catégories:', err);
      }
    })();

    return () => { cancel = true; };
  }, []);

  // Appliquer les filtres
  const handleApplyFilters = () => {
    setFilters({
      prixMax,
      dureeMax,
      categories: selectedCategories,
    });

    if (onCloseMobile) {
      onCloseMobile();
    }
  };

  // Réinitialiser les filtres
  const handleResetFilters = () => {
    setPrixMax(300);
    setDureeMax(10);
    setSelectedCategories([]);

    setFilters({
      prixMax: 300,
      dureeMax: 10,
      categories: [],
    });

    if (onCloseMobile) {
      onCloseMobile();
    }
  };

  // Reset automatique
  useEffect(() => {
    handleResetFilters();
  }, [resetKey]);

  return (
    <aside className="flash-filters">
      
      
      <div className="filter-card">
        <h3>Filtres</h3>
        {/* Prix */}
        <div className="filter-section">
          <FilterRange
            label="Prix maximum"
            min={0}
            max={500}
            value={prixMax}
            onChange={setPrixMax}
            aria-valuetext={`${prixMax} €`}
            step={10}
          />
        </div>

        {/* Catégories */}
        {availableCategories.length > 0 && (
          <div className="filter-section">
            <FilterSection
              title="Catégorie"
              options={availableCategories}
              selected={selectedCategories}
              onChange={setSelectedCategories}
            />
          </div>
        )}

        {/* Temps de réalisation */}
        <div className="filter-section">
          <FilterRange
            label="Temps réalisation maximum"
            min={0}
            max={20}
            value={dureeMax}
            onChange={setDureeMax}
            aria-valuetext={`${dureeMax} heures`}
            step={1}
          />
        </div>

        {/* Boutons */}
        <div className="filter-actions">
          <GenericButton
            text="Réinitialiser"
            onClick={handleResetFilters}
          />
          <GenericButton
            text="Appliquer"
            onClick={handleApplyFilters}
          />
        </div>
      </div>
    </aside>
  );
}