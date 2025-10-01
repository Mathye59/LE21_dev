// Importe la classe de base des contrôleurs Stimulus
import { Controller } from '@hotwired/stimulus';

// Déclare un contrôleur Stimulus
export default class extends Controller {
  // Déclare deux "targets" que le contrôleur va manipuler :
  // - select : l'élément <select> qui contient les médias
  // - img    : l'élément <img> qui affiche l’aperçu
  static targets = ['select', 'img'];

  // Déclare une "value" optionnelle nommée "placeholder"
  // (contiendra l’URL d’une image par défaut si rien n’est sélectionné)
  static values = { placeholder: String };

  // Méthode appelée automatiquement quand le contrôleur est connecté au DOM
  // On force une première mise à jour de l’aperçu
  connect() { this.update(); }

  // Met à jour l’aperçu selon l’option actuellement sélectionnée
  update() {
    // Récupère l’option sélectionnée dans le <select> (si le target existe)
    const opt = this.hasSelectTarget ? this.selectTarget.selectedOptions[0] : null;

    // Lit l’URL de l’image stockée dans l’attribut data-src de l’option
    const url = opt ? opt.dataset.src : '';

    if (url) {
      // Si on a une URL, on l’applique à l’<img> d’aperçu et on l’affiche
      this.imgTarget.src = url;
      this.imgTarget.style.display = '';
    } else {
      // Sinon, on affiche le placeholder s’il a été fourni, ou on masque l’<img>
      this.imgTarget.src = this.placeholderValue || '';
      this.imgTarget.style.display = this.placeholderValue ? '' : 'none';
    }
  }
}

