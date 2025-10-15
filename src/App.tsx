import { BrowserRouter, Routes, Route } from 'react-router-dom';
import Header from './components/Header';
import Footer from './components/Footer';
import Acceuil from './pages/Acceuil';
import Blog from './pages/Blog';
import ArticleDetail from './components/Blog/ArticleDetail';
// ... autres imports

function App() {
  return (
    <BrowserRouter>
      <div className="app-container">
        <Header />
        
         <main className="app-main">
          <Routes>
            <Route path="/" element={<Acceuil />} />
            <Route path="/blog" element={<Blog />} />
            <Route path="/blog/:id" element={<ArticleDetail />} />
            {/* ajoute tes autres routes ici */}
            {/* <Route path="/contact" element={<Contact />} /> */}
            <Route path="*" element={<div>Page introuvable</div>} />
          </Routes>
        </main>
        
        <Footer />
      </div>
    </BrowserRouter>
  );
}

export default App;