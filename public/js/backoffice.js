// Backoffice SiGeRU: monitoreo del panel (cada 20 s) y mapas.
// El resto de las pantallas funciona sin JavaScript (formularios comunes).

const BASE = document.body.dataset.base.replace(/\/$/, '');
const LEAFLET_JS = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
const LEAFLET_CSS = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
// integridad (SRI): si el archivo de unpkg cambia, el navegador no lo ejecuta y se usa el plano de respaldo
const LEAFLET_JS_SRI = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
const LEAFLET_CSS_SRI = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=';
const COLORES = { abierta: '#D9534F', en_curso: '#F2A541', funcional: '#3E9B4F', desbordado: '#E67E22', roto: '#9B2C2C', fuera_servicio: '#6B7280', centro: '#2A7F8F', camion: '#1F5FAD' };

const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

async function pedir(ruta) {
  const r = await fetch(BASE + ruta, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
  if (r.redirected || !r.ok) throw new Error('HTTP ' + r.status);
  return r.json();
}

function cargarLeaflet() {
  if (window.L) return Promise.resolve(true);
  return new Promise((resolve) => {
    const css = Object.assign(document.createElement('link'),
      { rel: 'stylesheet', integrity: LEAFLET_CSS_SRI, crossOrigin: 'anonymous', href: LEAFLET_CSS });
    const js = Object.assign(document.createElement('script'), { integrity: LEAFLET_JS_SRI, crossOrigin: 'anonymous', src: LEAFLET_JS });
    js.onload = () => resolve(true);
    js.onerror = () => resolve(false);
    document.head.append(css, js);
    setTimeout(() => resolve(Boolean(window.L)), 4000);
  });
}

// Plano de respaldo cuando no hay Internet: proyecta los puntos en un SVG.
function plano(div, puntos) {
  if (!puntos.length) { div.innerHTML = '<p class="vacio">Sin datos para mostrar.</p>'; return; }
  const lats = puntos.map((p) => p.lat), lngs = puntos.map((p) => p.lng);
  const [minLat, maxLat, minLng, maxLng] = [Math.min(...lats), Math.max(...lats), Math.min(...lngs), Math.max(...lngs)];
  // con un solo punto (detalle de una incidencia) queda en el centro
  const x = (lng) => (maxLng === minLng ? 400 : 20 + ((lng - minLng) / (maxLng - minLng)) * 760);
  const y = (lat) => (maxLat === minLat ? 210 : 20 + ((maxLat - lat) / (maxLat - minLat)) * 380);
  div.innerHTML = `<svg class="bo-plano" viewBox="0 0 800 420">${puntos.map((p) =>
    `<circle cx="${x(p.lng).toFixed(1)}" cy="${y(p.lat).toFixed(1)}" r="${p.radio}" fill="${p.color}" stroke="#fff" stroke-width="1.5"><title>${esc(p.texto.replace(/<[^>]+>/g, ' '))}</title></circle>`).join('')}</svg>`;
}

async function dibujar(div, puntos) {
  if (await cargarLeaflet()) {
    const mapa = window.L.map(div).setView([Number(div.dataset.lat), Number(div.dataset.lng)], Number(div.dataset.zoom || 13));
    window.L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© colaboradores de OpenStreetMap' }).addTo(mapa);
    for (const p of puntos) {
      window.L.circleMarker([p.lat, p.lng], { radius: p.radio, color: '#fff', weight: 1.5, fillColor: p.color, fillOpacity: 0.95 }).bindPopup(p.texto).addTo(mapa);
    }
    return;
  }
  plano(div, puntos);
}

async function mapaPanel(div) {
  try {
    const d = await pedir('/api/mapa');
    const num = (o) => ({ lat: Number(o.latitud), lng: Number(o.longitud) });
    const puntos = [
      ...d.contenedores.map((c) => ({ ...num(c), radio: 4, color: COLORES[c.estado], texto: `<b>${esc(c.codigo)}</b><br>${esc(c.direccion)}<br>${esc(c.estado)} · ${c.nivel_llenado}%` })),
      ...d.centros.map((c) => ({ ...num(c), radio: 9, color: COLORES.centro, texto: `<b>${esc(c.nombre)}</b><br>Ocupación: ${c.porcentaje}%` })),
      ...d.camiones.map((c) => ({ ...num(c), radio: 7, color: COLORES.camion, texto: `<b>Camión ${esc(c.matricula)}</b><br>${esc(c.estado)}` })),
      ...d.incidencias.map((i) => ({ ...num(i), radio: 7, color: COLORES[i.estado], texto: `<b><a href="${BASE}/incidencias/${i.id}">#${i.id}</a> ${esc(i.tipo)}</b><br>${esc(i.contenedor_codigo)} · ${esc(i.estado)} · prioridad ${esc(i.prioridad)}` })),
    ];
    dibujar(div, puntos);
  } catch {
    div.innerHTML = '<p class="vacio">No se pudo cargar el mapa.</p>';
  }
}

// Monitoreo en tiempo real: consulta periódica y resaltado de cambios.
function monitoreo() {
  const cuerpo = document.getElementById('recientes');
  const hora = document.getElementById('hora-monitoreo');
  let conocidas = new Set([...cuerpo.querySelectorAll('a')].map((a) => a.textContent));
  async function actualizar() {
    try {
      const d = await pedir('/api/monitoreo');
      for (const [estado, el] of [['abierta', document.querySelector('[data-kpi=abierta]')], ['en_curso', document.querySelector('[data-kpi=en_curso]')]]) {
        const valor = String(d.indicadores.incidencias[estado] ?? 0);
        if (el.textContent !== valor) { el.textContent = valor; el.parentElement.classList.remove('cambio'); void el.offsetWidth; el.parentElement.classList.add('cambio'); }
      }
      cuerpo.innerHTML = d.recientes.map((r) => `<tr class="${conocidas.has('#' + r.id) ? '' : 'nueva'}"><td><a href="${BASE}/incidencias/${r.id}">#${r.id}</a></td><td>${esc(r.tipo.replace('_', ' '))}</td><td>${esc(r.contenedor_codigo)}</td><td>${esc(r.zona)}</td><td><span class="bo-badge ${esc(r.estado)}">${esc(r.estado.replace('_', ' '))}</span></td></tr>`).join('');
      conocidas = new Set(d.recientes.map((r) => '#' + r.id));
      hora.textContent = '· última actualización ' + new Date().toLocaleTimeString('es-UY');
    } catch {
      hora.textContent = '· sin conexión con el servidor';
    }
  }
  setInterval(actualizar, 20000);
}

const div = document.getElementById('mapa');
if (div && div.dataset.punto) {
  dibujar(div, [{ lat: Number(div.dataset.lat), lng: Number(div.dataset.lng), radio: 9, color: COLORES.abierta, texto: esc(div.dataset.punto) }]);
} else if (div) {
  mapaPanel(div);
}
if (document.getElementById('recientes')) monitoreo();
