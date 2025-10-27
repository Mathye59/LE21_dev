import './MentionsLegales.css';

export default function MentionsLegales() {
  return (
    <div className="legal-container">
      <div className="legal-content">
        <h1 className="legal-title">MENTIONS LÉGALES</h1>

        <section className="legal-section">
          <p className="legal-intro">
            Conformément aux dispositions de la loi n° 2004-575 du 21 juin 2004 pour la confiance en l'économie numérique, 
            il est précisé aux utilisateurs du site Le Twenty One Tattoo & Piercing l'identité des différents intervenants 
            dans le cadre de sa réalisation et de son suivi.
          </p>
        </section>

        <section className="legal-section">
          <h2 className="legal-heading">Édition du site</h2>
          <p>
            Le présent site, accessible à l'URL <strong>www.LeTwentyOneTattoo&Piercing.fr</strong> (le « Site »), est édité par :
          </p>
          <p className="legal-indent">
            <strong>Antoine Lemaréchal</strong>, résidant 83 rue de l'égalité 59287 Guesnain, de nationalité Française, né le 21/06/1986.
          </p>
        </section>

        <section className="legal-section">
          <h2 className="legal-heading">Hébergement</h2>
          <p className="legal-indent">
            Le Site est hébergé par la société <strong>O2Switch</strong>, situé 222 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand 
            (contact téléphonique : <a href="tel:+33444446040">+33 4 44 44 60 40</a>).
          </p>
        </section>

        <section className="legal-section">
          <h2 className="legal-heading">Directeur de publication</h2>
          <p className="legal-indent">
            Le Directeur de la publication du Site est <strong>Antoine Lemaréchal</strong>.
          </p>
        </section>

        <section className="legal-section">
          <h2 className="legal-heading">Nous contacter</h2>
          <div className="legal-indent">
            <p>Par téléphone : <a href="tel:+33660975862">+33 6 60 97 58 62</a></p>
            <p>Par email : <a href="mailto:teeone.tattoo@gmail.com">teeone.tattoo@gmail.com</a></p>
            <p>Par courrier : 83 rue de l'égalité 59287 Guesnain</p>
          </div>
        </section>

        <section className="legal-section">
          <h2 className="legal-heading">Données personnelles</h2>
          <p className="legal-indent">
            Le traitement de vos données à caractère personnel est régi par notre Charte du respect de la vie privée, 
            disponible depuis la section "Charte de Protection des Données Personnelles", conformément au Règlement Général 
            sur la Protection des Données 2016/679 du 27 avril 2016 (« RGPD »).
          </p>
        </section>

        <section className="legal-section legal-rgpd">
          <h1 className="legal-title">POLITIQUE DE CONFIDENTIALITÉ (RGPD)</h1>

          <h2 className="legal-heading">Collecte des données personnelles</h2>
          <p className="legal-indent">
            Les données personnelles collectées sur ce site sont uniquement celles que vous nous fournissez volontairement 
            via le formulaire de contact. Ces données peuvent inclure : nom, prénom, adresse email, numéro de téléphone.
          </p>

          <h2 className="legal-heading">Finalité du traitement</h2>
          <p className="legal-indent">
            Vos données sont collectées dans le but de répondre à vos demandes de renseignements, de prise de rendez-vous 
            ou de devis pour nos prestations de tatouage et piercing.
          </p>

          <h2 className="legal-heading">Base légale du traitement</h2>
          <p className="legal-indent">
            Le traitement de vos données repose sur votre consentement (article 6.1.a du RGPD) et/ou sur l'exécution de mesures 
            précontractuelles (article 6.1.b du RGPD).
          </p>

          <h2 className="legal-heading">Durée de conservation</h2>
          <p className="legal-indent">
            Vos données sont conservées pendant la durée nécessaire à la réalisation de la prestation demandée, 
            puis archivées conformément aux obligations légales (3 ans maximum après le dernier contact).
          </p>

          <h2 className="legal-heading">Destinataires des données</h2>
          <p className="legal-indent">
            Vos données ne sont accessibles qu'au personnel habilité du studio Le Twenty One Tattoo & Piercing. 
            Elles ne sont ni vendues, ni louées, ni partagées avec des tiers sans votre consentement explicite.
          </p>

          <h2 className="legal-heading">Vos droits</h2>
          <p className="legal-indent">
            Conformément au RGPD, vous disposez des droits suivants concernant vos données personnelles :
          </p>
          <ul className="legal-list">
            <li><strong>Droit d'accès</strong> : Vous pouvez demander à consulter les données vous concernant</li>
            <li><strong>Droit de rectification</strong> : Vous pouvez demander la correction de données inexactes</li>
            <li><strong>Droit à l'effacement</strong> : Vous pouvez demander la suppression de vos données</li>
            <li><strong>Droit à la limitation</strong> : Vous pouvez demander la limitation du traitement</li>
            <li><strong>Droit d'opposition</strong> : Vous pouvez vous opposer au traitement de vos données</li>
            <li><strong>Droit à la portabilité</strong> : Vous pouvez récupérer vos données dans un format structuré</li>
          </ul>
          <p className="legal-indent">
            Pour exercer ces droits, contactez-nous par email à <a href="mailto:teeone.tattoo@gmail.com">teeone.tattoo@gmail.com</a> 
            ou par courrier à l'adresse mentionnée ci-dessus.
          </p>

          <h2 className="legal-heading">Cookies</h2>
          <p className="legal-indent">
            Ce site n'utilise pas de cookies de tracking ou de publicité. Seuls des cookies techniques strictement nécessaires 
            au fonctionnement du site peuvent être utilisés.
          </p>

          <h2 className="legal-heading">Sécurité</h2>
          <p className="legal-indent">
            Nous mettons en œuvre toutes les mesures techniques et organisationnelles appropriées pour protéger vos données 
            contre la perte, l'utilisation abusive, l'accès non autorisé ou la divulgation.
          </p>

          <h2 className="legal-heading">Réclamation</h2>
          <p className="legal-indent">
            Si vous estimez que vos droits ne sont pas respectés, vous pouvez introduire une réclamation auprès de la CNIL 
            (Commission Nationale de l'Informatique et des Libertés) : <a href="https://www.cnil.fr" target="_blank" rel="noopener noreferrer">www.cnil.fr</a>
          </p>
        </section>

        <footer className="legal-footer">
          <p>Génération des mentions légales par <a href="https://www.legalstart.fr" target="_blank" rel="noopener noreferrer">Legalstart</a>.</p>
          <p>Dernière mise à jour : Octobre 2025</p>
        </footer>
      </div>
    </div>
  );
}