/** Keep map.ir's jQuery/Leaflet SDK inside its own document, isolated from React. */
export function mapIrDocument(coordinates, apiKey) {
  const options = JSON.stringify({
    element: '#office-map',
    presets: { latlng: coordinates, zoom: 16 },
    apiKey,
  }).replace(/</g, '\\u003c');
  const marker = JSON.stringify({
    latlng: coordinates,
    popup: false,
    pan: false,
    draggable: false,
    history: false,
  });
  return `<!doctype html>
<html lang="fa" dir="rtl"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdn.map.ir/web-sdk/1.4.2/css/mapp.min.css">
<link rel="stylesheet" href="https://cdn.map.ir/web-sdk/1.4.2/css/fa/style.css">
<style>html,body,#office-map{width:100%;height:100%;margin:0;padding:0}body{background:#eef2f1}#map-status{position:absolute;inset:0;display:grid;place-items:center;margin:0;padding:16px;font:13px Tahoma,sans-serif;color:#5f7480;background:#eef2f1;z-index:1000}#map-status[hidden]{display:none}</style>
<script>function mapFailed(){var status=document.getElementById('map-status');status.hidden=false;status.textContent='نقشه در حال حاضر در دسترس نیست.'}</script>
</head><body><div id="office-map"></div><p id="map-status" role="status">در حال بارگذاری نقشه…</p>
<script src="https://cdn.map.ir/web-sdk/1.4.2/js/jquery-3.2.1.min.js" onerror="mapFailed()"></script>
<script src="https://cdn.map.ir/web-sdk/1.4.2/js/mapp.env.js" onerror="mapFailed()"></script>
<script src="https://cdn.map.ir/web-sdk/1.4.2/js/mapp.min.js" onerror="mapFailed()"></script>
<script>
try {
  var app = new Mapp(${options});
  app.addLayers();
  app.addZoomControls();
  app.addMarker(${marker});
  app.map.eachLayer(function(layer) {
    if (layer.getTileUrl) layer.on('tileerror', mapFailed);
  });
  document.getElementById('map-status').hidden = true;
} catch (error) { mapFailed(); }
</script></body></html>`;
}
