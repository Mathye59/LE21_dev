// @ts-nocheck
import React, { useEffect, useMemo, useRef, useState } from "react";
import "./Carousel3D.css";

export default function Carousel3D({
  apiUrl,
  mapItem,
  requestInit,
  selectItems,
  resolveIri = true,
  radius = 900,
  perspective = 1800,
  imgWidth = 320,
  imgHeight = 210,
  autoPlay = true,
  autoPlayDelay = 3500,
  pauseOnHover = true,
  direction = 1,
}: {
  apiUrl: string;
  mapItem?: (it: any) => { src?: string; alt?: string } | null;
  requestInit?: RequestInit;
  selectItems?: (data: any) => any[];
  resolveIri?: boolean;
  radius?: number;
  perspective?: number;
  imgWidth?: number;
  imgHeight?: number;
  autoPlay?: boolean;
  autoPlayDelay?: number;
  pauseOnHover?: boolean;
  direction?: 1 | -1;
}) {
  const [items, setItems] = useState<{ src: string; alt?: string }[]>([]);
  const [angle, setAngle] = useState(0);
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState<string | null>(null);
  const [selectedImage, setSelectedImage] = useState<string | null>(null);
  const [isHover, setIsHover] = useState(false);
  const [isDragging, setIsDragging] = useState(false);

  // Fetch des items
  useEffect(() => {
    let cancel = false;
    (async () => {
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
          raw = Array.isArray(data?.["hydra:member"])
            ? data["hydra:member"]
            : Array.isArray(data)
            ? data
            : [];
        }

        if (resolveIri && raw.length && typeof raw[0] === "string") {
          raw = await Promise.all(
            raw.map((iri: string) =>
              fetch(iri, requestInit).then((r) => (r.ok ? r.json() : null))
            )
          );
          raw = raw.filter(Boolean);
        }

        const mapped = raw
          .map((it: any) => {
            if (mapItem) return mapItem(it);
            const url =
              it.fullUrl ??
              it.url ??
              it.imageUrl ??
              it.media?.url ??
              (it.filePath ? `/uploads/${it.filePath}` : undefined);
            if (!url) return null;
            return {
              src: absolutize(apiUrl, url),
              alt: it.titre ?? it.alt ?? "",
            };
          })
          .filter(Boolean) as { src: string; alt?: string }[];

        if (!cancel) setItems(mapped);
      } catch (e: any) {
        if (!cancel) setErr(e.message || "Erreur inconnue");
      } finally {
        if (!cancel) setLoading(false);
      }
    })();

    return () => {
      cancel = true;
    };
  }, [apiUrl, requestInit, selectItems, resolveIri]);

  const count = items.length;
  const step = useMemo(() => (count ? 360 / count : 0), [count]);

  // Largeur viewport

  const [vw, setVw] = useState<number>(
    typeof window !== "undefined" ? window.innerWidth : 1200
  );

  useEffect(() => {
    const onR = () => {
      const newVw = window.innerWidth;
      console.log('📏 Largeur viewport:', newVw);
      setVw(newVw);
    };
    window.addEventListener("resize", onR);
    return () => window.removeEventListener("resize", onR);
  }, []);
  // Détection mobile : dome gallery entre 450px et 900px


  const isMobile = vw <= 900;

  // Radius responsive
  const responsiveRadius = useMemo(() => {
    if (isMobile) return 0;
    if (vw <= 900) return 900;
    if (vw <= 1200) return 1000;
    if (vw <= 1600) return 1050;
    return 1100;
  }, [vw, isMobile]);

  const responsivePerspective = useMemo(() => {
    return responsiveRadius * 2;
  }, [responsiveRadius]);

  // Drag
  const drag = useRef({ active: false, startX: 0, startAngle: 0 });
  function onDown(e: React.MouseEvent | React.TouchEvent) {
    const x = "touches" in e ? e.touches[0].clientX : (e as any).clientX;
    drag.current = { active: true, startX: x, startAngle: angle };
    setIsDragging(true);
  }
  function onMove(e: React.MouseEvent | React.TouchEvent) {
    if (!drag.current.active) return;
    const x = "touches" in e ? e.touches[0].clientX : (e as any).clientX;
    const dx = x - drag.current.startX;
    const PX_PER_STEP = isMobile ? 150 : 240;
    const degPerPx = (step * direction) / PX_PER_STEP;
    setAngle(drag.current.startAngle + dx * degPerPx);
  }
  function onUp() {
    if (!drag.current.active || !count) return;
    drag.current.active = false;
    setIsDragging(false);
    const snapped = Math.round(angle / step) * step;
    setAngle(snapped);
  }

  // Auto slide
  useEffect(() => {
    if (!autoPlay || !count) return;
    if ((pauseOnHover && isHover) || isDragging || !!selectedImage) return;
    const id = setInterval(() => {
      setAngle((a) => a + step * direction);
    }, autoPlayDelay);
    return () => clearInterval(id);
  }, [
    autoPlay,
    autoPlayDelay,
    pauseOnHover,
    isHover,
    isDragging,
    selectedImage,
    count,
    step,
    direction,
  ]);

  // Index central pour mobile
  const centerIndex = useMemo(() => {
    if (!isMobile || !count) return 0;
    const normalized = (((-angle) % 360) + 360) % 360;
    return Math.round(normalized / step) % count;
  }, [angle, step, count, isMobile]);

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

  // Mobile: rendu style "dome gallery"
  if (isMobile) {
    return (
      <>
        {selectedImage && (
          <div className="image-modal" onClick={() => setSelectedImage(null)}>
            <div className="modal-content" onClick={(e) => e.stopPropagation()}>
              <button
                className="close-modal"
                aria-label="Fermer"
                onClick={() => setSelectedImage(null)}
              >
                ×
              </button>
              <img src={selectedImage} alt="Agrandie" draggable={false} />
            </div>
          </div>
        )}

        <div
          className="dome-gallery"
          onTouchStart={onDown}
          onTouchMove={onMove}
          onTouchEnd={onUp}
        >
          <div className="dome-container">
            {items.map((it, i) => {
              const offset = (i - centerIndex + count) % count;
              const normalizedOffset = offset > count / 2 ? offset - count : offset;
              
              const isCurrent = i === centerIndex;
              const isAdjacent = Math.abs(normalizedOffset) === 1;
              const isVisible = Math.abs(normalizedOffset) <= 2;

              if (!isVisible) return null;

              let scale, opacity, translateX, translateY, zIndex;

              if (isCurrent) {
                scale = 1;
                opacity = 1;
                translateX = 0;
                translateY = 0;
                zIndex = 30;
              } else if (isAdjacent) {
                scale = 0.7;
                opacity = 0.6;
                translateX = normalizedOffset * 40;
                translateY = 10;
                zIndex = 20;
              } else {
                scale = 0.5;
                opacity = 0.35;
                translateX = normalizedOffset * 50;
                translateY = 18;
                zIndex = 10;
              }

              return (
                <div
                  key={i}
                  className="dome-item"
                  style={{
                    transform: `translateX(${translateX}%) translateY(${translateY}%) scale(${scale})`,
                    opacity,
                    zIndex,
                  }}
                  onClick={() => {
                    if (isCurrent) {
                      setSelectedImage(it.src);
                    } else {
                      setAngle(i * step);
                    }
                  }}
                >
                  <img src={it.src} alt={it.alt ?? `media-${i + 1}`} draggable={false} />
                </div>
              );
            })}
          </div>
        </div>
      </>
    );
  }

  // Desktop: carrousel 3D classique
  return (
    <>
      {selectedImage && (
        <div className="image-modal" onClick={() => setSelectedImage(null)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <button
              className="close-modal"
              aria-label="Fermer"
              onClick={() => setSelectedImage(null)}
            >
              ×
            </button>
            <img src={selectedImage} alt="Agrandie" draggable={false} />
          </div>
        </div>
      )}

      <div
        className="carousel3d"
        style={{ perspective: `${responsivePerspective}px` }}
        onMouseDown={onDown}
        onMouseMove={onMove}
        onMouseLeave={onUp}
        onMouseUp={onUp}
        onTouchStart={onDown}
        onTouchMove={onMove}
        onTouchEnd={onUp}
        onMouseEnter={() => setIsHover(true)}
        role="region"
        aria-roledescription="carrousel"
        aria-label="Carrousel 3D"
      >
        <div
          className="ring"
          style={{
            transform: `translateZ(${-responsiveRadius}px) rotateY(${angle}deg)`,
          }}
        >
          {items.map((it, i) => (
            <figure
              key={i}
              className="panel"
              style={{
                transform: `rotateY(${i * step}deg) translateZ(${responsiveRadius}px)`,
                width: imgWidth,
                height: imgHeight,
              }}
              onClick={() => setSelectedImage(it.src)}
            >
              <img
                src={it.src}
                alt={it.alt ?? `media-${i + 1}`}
                draggable={false}
              />
            </figure>
          ))}
        </div>
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