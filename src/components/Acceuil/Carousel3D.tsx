// @ts-nocheck
import React, { useEffect, useMemo, useRef, useState } from "react";
import './Carousel3D.css';

export default function Carousel3D({
    apiUrl,
    mapItem,
    requestInit,
    selectItems, 
    resolveIri = true, 
    radius = 900,
    perspective = 1800,
    imgWidth = 280,
    imgHeight = 180,
    autoPlay = true,
    autoPlayDelay = 1500,
    pauseOnHover = true,
}: {
  apiUrl: string;
  mapItem?: (it: any) => { src?: string; alt?: string } | null;
  requestInit?: RequestInit;
  selectItems?: (data:any)=>any[]; 
  resolveIri?: boolean;
  radius?: number;
  perspective?: number;
  imgWidth?: number;
  imgHeight?: number;
  autoPlay?: boolean;
  autoPlayDelay?: number;
  pauseOnHover?: boolean;
}) {
  const [items, setItems] = useState<{ src: string; alt?: string }[]>([]);
  const [angle, setAngle] = useState(0);
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState<string | null>(null);
  const [selectedImage, setSelectedImage] = useState<string | null>(null);
  const [isHover, setIsHover] = useState(false);
  const [isDragging, setIsDragging] = useState(false);

  useEffect(() => {
    let cancel = false;
    async function run() {
      setLoading(true);
      setErr(null);
      try {
        const res = await fetch(apiUrl, requestInit);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();

        let raw: any[] = [];
        if (selectItems) {
          raw = selectItems(data) ?? [];
        } else {
          raw = Array.isArray(data?.['hydra:member'])
            ? data['hydra:member']
            : Array.isArray(data) ? data : [];
        }

        if (resolveIri && raw.length && typeof raw[0] === 'string') {
          raw = await Promise.all(
            raw.map((iri: string) =>
              fetch(iri, requestInit).then(r => r.ok ? r.json() : null)
            )
          );
          raw = raw.filter(Boolean);
        }

        const mapped = raw
          .map((it:any) => {
            if (mapItem) return mapItem(it);
            const url =
              it.fullUrl ??
              it.url ??
              it.imageUrl ??
              it.media?.url ??
              (it.filePath ? `/uploads/${it.filePath}` : undefined);
            if (!url) return null;
            return { src: absolutize(apiUrl, url), alt: it.titre ?? it.alt ?? "" };
          })
          .filter(Boolean) as { src: string; alt?: string }[];

        if (!cancel) setItems(mapped);
      } catch (e: any) {
        if (!cancel) setErr(e.message || "Erreur inconnue");
      } finally {
        if (!cancel) setLoading(false);
      }
    }
    run();
    return () => {
      cancel = true;
    };
  }, [apiUrl]);

  const count = items.length;
  const step = useMemo(() => (count ? 360 / count : 0), [count]);
  const ringRef = useRef<HTMLDivElement>(null);

  const drag = useRef({ active: false, startX: 0, startAngle: 0 });
  function onDown(e: React.MouseEvent | React.TouchEvent) {
    const x = "touches" in e ? e.touches[0].clientX : (e as any).clientX;
    drag.current = { active: true, startX: x, startAngle: angle };
    setIsDragging(true);                 // NEW
  } 
  function onMove(e: React.MouseEvent | React.TouchEvent) {
    if (!drag.current.active) return;
    const x = "touches" in e ? e.touches[0].clientX : (e as any).clientX;
    const dx = x - drag.current.startX;
    const sensibility = 0.10;
    setAngle(drag.current.startAngle + dx * sensibility);
  }
  function onUp() {
  if (!drag.current.active || !count) return;
  drag.current.active = false;
  setIsDragging(false);                // NEW
  const snapped = Math.round(angle / step) * step;
  setAngle(snapped);
}
useEffect(() => {
  if (!autoPlay || !count) return;
  if ((pauseOnHover && isHover) || isDragging || !!selectedImage) return;
  const id = setInterval(() => {
    setAngle(a => a + step); // avance d’un panneau
  }, autoPlayDelay);
  return () => clearInterval(id);
}, [autoPlay, autoPlayDelay, pauseOnHover, isHover, isDragging, selectedImage, count, step]);

  function prev() {
    setAngle((a) => a - step);
  }
  function next() {
    setAngle((a) => a + step);
  }

  if (loading) {
    return (
      <div style={{ color: "#d4af37", textAlign: "center", padding: "24px 0" }}>
        Chargement du carrousel…
      </div>
    );
  }
  if (err) {
    return (
      <div style={{ color: "#ff8f8f", textAlign: "center", padding: "24px 0" }}>
        Erreur: {err}
      </div>
    );
  }
  if (!count) {
    return (
      <div style={{ color: "#bbb", textAlign: "center", padding: "24px 0" }}>
        Aucun média actif à afficher.
      </div>
    );
  }

  return (
    <>
      {/* Modal pour agrandir l'image */}
      {selectedImage && (
        <div className="image-modal" onClick={() => setSelectedImage(null)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <button className="close-modal" onClick={() => setSelectedImage(null)}>×</button>
            <img src={selectedImage} alt="Agrandie" />
          </div>
        </div>
      )}

      <div
        className="carousel3d"
        onMouseDown={onDown}
        onMouseMove={onMove}
        onMouseLeave={onUp}
        onMouseUp={onUp}
        onTouchStart={onDown}
        onTouchMove={onMove}
        onTouchEnd={onUp}
        onMouseEnter={() => setIsHover(true)}
        onMouseLeave={() => { setIsHover(false); onUp(); }}
        role="region"
        aria-label="Carrousel 3D"
      >
        <div
          className="ring"
          ref={ringRef}
          style={{
            transform: `translateZ(${-radius}px) rotateY(${angle}deg)`,
          }}
        >
          {items.map((it, i) => (
            <figure
              key={i}
              className="panel"
              style={{
                transform: `rotateY(${i * step}deg) translateZ(${radius}px)`,
                width: imgWidth,
                height: imgHeight,
              }}
              onClick={() => setSelectedImage(it.src)}
            >
              <img src={it.src} alt={it.alt ?? `media-${i + 1}`} />
            </figure>
          ))}
        </div>

        <button className="nav prev" onClick={prev} aria-label="Précédent">
          ‹
        </button>
        <button className="nav next" onClick={next} aria-label="Suivant">
          ›
        </button>

        
      </div>
    </>
  );
}

function absolutize(baseApiUrl: string, path: string) {
  if (!path) return path;
  if (/^https?:\/\//i.test(path)) return path;
  try {
    const u = new URL(baseApiUrl, window.location.href);
    return new URL(path, `${u.protocol}//${u.host}`).toString();
  } catch {
    return path;
  }
}