import React from 'react';

/** Build the same key-free OpenStreetMap embed URL used across public pages. */
export function openStreetMapUrl(coordinates) {
  const lat = Number(coordinates?.lat);
  const lng = Number(coordinates?.lng);

  if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;

  const box = [lng - 0.004, lat - 0.003, lng + 0.004, lat + 0.003].join('%2C');
  return `https://www.openstreetmap.org/export/embed.html?bbox=${box}&layer=mapnik&marker=${lat}%2C${lng}`;
}

export default function OpenStreetMap({ coordinates, title = 'نقشه محل مطب', className = '', style }) {
  const src = openStreetMapUrl(coordinates);
  if (!src) return null;

  return (
    <iframe
      className={className}
      title={title}
      loading="lazy"
      src={src}
      style={style}
    />
  );
}
