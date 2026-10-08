import React from 'react';
import { useSite } from '../lib/SiteContext.jsx';
import { mapIrDocument } from '../lib/mapIr';
import { Placeholder } from './States.jsx';

export default function MapIrMap({ coordinates, title = 'نقشه محل مطب', className = '' }) {
  const { site } = useSite();
  if (!site?.mapIrApiKey) {
    return <Placeholder className={className} label="نقشه در حال حاضر در دسترس نیست." />;
  }
  return <iframe className={className} title={title} loading="lazy"
    srcDoc={mapIrDocument(coordinates, site.mapIrApiKey)}
    referrerPolicy="no-referrer-when-downgrade" allowFullScreen />;
}
