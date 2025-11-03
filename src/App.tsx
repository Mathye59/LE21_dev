import { BrowserRouter, Routes, Route } from 'react-router-dom';
import Header from './components/Header/Header';
import Footer from './components/Footer/Footer';
import Acceuil from './pages/Acceuil';
import Blog from './pages/Blog';
import ArticleDetail from './components/Blog/ArticleDetail';
import Flash from './pages/Flash';
import Contact from './pages/Contact';
import MentionsLegales from './components/Footer/MentionsLegales';
import CookieConsent from './components/CookieConsent';
// ... autres imports

function App() {
  return (
    <BrowserRouter>
      <div className="app-container">
        <Header />
        
         <main className="app-main">
          <Routes>
            <Route path="/" element={<Acceuil />} />
            <Route path="/flash" element={<Flash />} />
            <Route path="/blog" element={<Blog />} />
            <Route path="/blog/:id" element={<ArticleDetail />} />
            <Route path="/contact" element={<Contact />} />
            <Route path="/mentions-legales" element={<MentionsLegales />} />
          </Routes>
          <CookieConsent />
        </main>
        
        <Footer />
      </div>
    </BrowserRouter>
  );
}

export default App;