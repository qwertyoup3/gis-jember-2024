<?php
require 'koneksi.php';

// Ringkasan untuk kartu statistik
$ringkas = $conn->query("SELECT COUNT(*) AS jml_kec, SUM(jumlah_penduduk) AS total,
                                MAX(jumlah_penduduk) AS maks, MIN(jumlah_penduduk) AS min
                         FROM kecamatan")->fetch_assoc();
$terbanyak = $conn->query("SELECT nama FROM kecamatan ORDER BY jumlah_penduduk DESC LIMIT 1")->fetch_assoc()['nama'];
$tercepat  = $conn->query("SELECT nama, laju_pertumbuhan FROM kecamatan ORDER BY laju_pertumbuhan DESC LIMIT 1")->fetch_assoc();
$turun     = $conn->query("SELECT COUNT(*) AS n FROM kecamatan WHERE laju_pertumbuhan < 0")->fetch_assoc()['n'];

// Data tabel
$tabel = $conn->query("SELECT * FROM kecamatan ORDER BY nama");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Peta Penduduk Kabupaten Jember 2024</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; color: #333; }
    header { background: #1e3a5f; color: #fff; padding: 18px 24px; }
    header h1 { margin: 0; font-size: 22px; }
    header p { margin: 4px 0 0; font-size: 13px; opacity: .8; }
    .container { max-width: 1200px; margin: auto; padding: 20px; }
    .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px; }
    .card { background: #fff; border-radius: 10px; padding: 16px; box-shadow: 0 2px 6px rgba(0,0,0,.08); }
    .card .label { font-size: 12px; color: #777; text-transform: uppercase; }
    .card .value { font-size: 22px; font-weight: bold; color: #1e3a5f; margin-top: 4px; }
    .card .sub { font-size: 12px; color: #999; }
    .panel { background: #fff; border-radius: 10px; padding: 16px; box-shadow: 0 2px 6px rgba(0,0,0,.08); margin-bottom: 20px; }
    .panel h2 { margin: 0 0 12px; font-size: 17px; color: #1e3a5f; }
    #map { height: 560px; border-radius: 8px; }
    .toolbar { margin-bottom: 10px; }
    .toolbar button { border: 1px solid #1e3a5f; background: #fff; color: #1e3a5f; padding: 6px 12px;
                      border-radius: 6px; cursor: pointer; margin-right: 6px; }
    .toolbar button.active { background: #1e3a5f; color: #fff; }
    .legend { background: #fff; padding: 10px 12px; border-radius: 6px; line-height: 20px; font-size: 12px;
              box-shadow: 0 1px 4px rgba(0,0,0,.3); }
    .legend i { width: 14px; height: 14px; float: left; margin-right: 6px; margin-top: 3px; border-radius: 3px; opacity: .85; }
    .info { background: rgba(255,255,255,.95); padding: 8px 12px; border-radius: 6px; font-size: 13px;
            box-shadow: 0 1px 4px rgba(0,0,0,.3); min-width: 190px; }
    .info h4 { margin: 0 0 4px; font-size: 14px; color: #1e3a5f; }
    .label-kec { background: transparent; border: none; box-shadow: none; font-size: 10px; font-weight: 600;
                 color: #222; text-shadow: 0 0 3px #fff, 0 0 3px #fff, 0 0 3px #fff; }
    .label-kec::before { display: none; }
    .leaflet-control-layers-expanded { font-size: 13px; }
    .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 800px) { .grid2 { grid-template-columns: 1fr; } }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th, td { padding: 8px 10px; border-bottom: 1px solid #eee; text-align: left; }
    th { background: #f0c419; color: #333; }
    td.num { text-align: right; }
    .neg { color: #c0392b; font-weight: bold; }
    .pos { color: #27ae60; }
    footer { text-align: center; font-size: 12px; color: #888; padding: 16px; }
</style>
</head>
<body>

<header>
    <h1>Visualisasi Penduduk Kabupaten Jember 2024</h1>
    <p>Jumlah penduduk dan laju pertumbuhan per kecamatan (2020–2024) — Sumber: BPS Kabupaten Jember</p>
</header>

<div class="container">

    <!-- Kartu ringkasan -->
    <div class="cards">
        <div class="card">
            <div class="label">Total Penduduk</div>
            <div class="value"><?= number_format($ringkas['total'], 0, ',', '.') ?></div>
            <div class="sub"><?= $ringkas['jml_kec'] ?> kecamatan</div>
        </div>
        <div class="card">
            <div class="label">Kecamatan Terpadat</div>
            <div class="value"><?= htmlspecialchars($terbanyak) ?></div>
            <div class="sub"><?= number_format($ringkas['maks'], 0, ',', '.') ?> jiwa</div>
        </div>
        <div class="card">
            <div class="label">Pertumbuhan Tercepat</div>
            <div class="value"><?= htmlspecialchars($tercepat['nama']) ?></div>
            <div class="sub"><?= number_format($tercepat['laju_pertumbuhan'], 2, ',', '.') ?>% per tahun</div>
        </div>
        <div class="card">
            <div class="label">Kecamatan Penduduk Menurun</div>
            <div class="value"><?= $turun ?></div>
            <div class="sub">laju pertumbuhan negatif</div>
        </div>
    </div>

    <!-- Peta -->
    <div class="panel">
        <h2>Peta Sebaran Penduduk per Kecamatan</h2>
        <div class="toolbar">
            <button id="btnJumlah" class="active" onclick="gantiMode('jumlah')">Jumlah Penduduk</button>
            <button id="btnLaju" onclick="gantiMode('laju')">Laju Pertumbuhan</button>
        </div>
        <div id="map"></div>
    </div>

    <!-- Grafik -->
    <div class="grid2">
        <div class="panel">
            <h2>Jumlah Penduduk per Kecamatan</h2>
            <canvas id="chartJumlah" height="420"></canvas>
        </div>
        <div class="panel">
            <h2>Laju Pertumbuhan per Tahun (%)</h2>
            <canvas id="chartLaju" height="420"></canvas>
        </div>
    </div>

    <!-- Tabel -->
    <div class="panel">
        <h2>Tabel Data</h2>
        <table>
            <thead>
                <tr><th>No</th><th>Kecamatan</th><th style="text-align:right">Jumlah Penduduk (jiwa)</th><th style="text-align:right">Laju Pertumbuhan (%)</th></tr>
            </thead>
            <tbody>
            <?php $no = 1; while ($r = $tabel->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($r['nama']) ?></td>
                    <td class="num"><?= number_format($r['jumlah_penduduk'], 0, ',', '.') ?></td>
                    <td class="num <?= $r['laju_pertumbuhan'] < 0 ? 'neg' : 'pos' ?>">
                        <?= number_format($r['laju_pertumbuhan'], 2, ',', '.') ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<footer>Data penduduk: BPS Kabupaten Jember, Tabel 3.1.1. Batas kecamatan: batas-administrasi-indonesia (Alf-Anas, update Juni 2023), disederhanakan untuk web.</footer>

<script>
const fmt = n => n.toLocaleString('id-ID');
let dataKec = [], dataByNama = {}, mode = 'jumlah', legend;

// ---------- Peta dasar (base layer, pilih salah satu) ----------
const map = L.map('map');
const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18, attribution: '&copy; OpenStreetMap contributors'
});
const satelit = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    maxZoom: 18, attribution: 'Tiles &copy; Esri'
});
osm.addTo(map);

// ---------- Layer tematik (overlay, bisa di-enable/disable) ----------
let layerBatas;                          // poligon choropleth
const layerTitik = L.layerGroup();       // lingkaran proporsional
const layerLabel = L.layerGroup();       // label nama kecamatan

// Warna berdasarkan jumlah penduduk
function warnaJumlah(n) {
    return n > 120000 ? '#800026' :
           n > 100000 ? '#BD0026' :
           n >  80000 ? '#E31A1C' :
           n >  60000 ? '#FC4E2A' :
           n >  40000 ? '#FD8D3C' : '#FEB24C';
}
// Warna berdasarkan laju pertumbuhan
function warnaLaju(p) {
    return p <  0    ? '#d73027' :
           p <  0.3  ? '#fee08b' :
           p <  0.6  ? '#91cf60' : '#1a9850';
}
function warna(d) {
    if (!d) return '#cccccc';
    return mode === 'jumlah' ? warnaJumlah(d.jumlah) : warnaLaju(d.laju);
}

function isiPopup(d) {
    return `<b>Kec. ${d.nama}</b><br>
            Jumlah penduduk: <b>${fmt(d.jumlah)}</b> jiwa<br>
            Laju pertumbuhan: <b>${d.laju.toLocaleString('id-ID')}%</b> / tahun`;
}

// Gaya poligon
function gayaBatas(feature) {
    return {
        fillColor: warna(dataByNama[feature.properties.nama]),
        weight: 1.2, color: '#ffffff', opacity: 1, fillOpacity: 0.75
    };
}

// ---------- Panel info (muncul saat kursor di atas wilayah) ----------
const info = L.control({ position: 'topleft' });
info.onAdd = function () { this._div = L.DomUtil.create('div', 'info'); this.update(); return this._div; };
info.update = function (d) {
    this._div.innerHTML = d
        ? `<h4>Kec. ${d.nama}</h4>
           Penduduk: <b>${fmt(d.jumlah)}</b> jiwa<br>
           Laju: <b>${d.laju.toLocaleString('id-ID')}%</b> / tahun`
        : '<h4>Kabupaten Jember</h4>Arahkan kursor ke sebuah kecamatan';
};

function sorot(e) {
    const l = e.target;
    l.setStyle({ weight: 3, color: '#1e3a5f', fillOpacity: 0.9 });
    l.bringToFront();
    info.update(dataByNama[l.feature.properties.nama]);
}
function lepas(e) {
    layerBatas.resetStyle(e.target);
    info.update();
}

function buatLayerBatas(geojson) {
    layerBatas = L.geoJSON(geojson, {
        style: gayaBatas,
        onEachFeature: (feature, layer) => {
            const d = dataByNama[feature.properties.nama];
            if (d) layer.bindPopup(isiPopup(d));
            layer.on({ mouseover: sorot, mouseout: lepas });

            // Label nama di titik yang dijamin berada di dalam poligon
            L.tooltip({ permanent: true, direction: 'center', className: 'label-kec' })
                .setLatLng(feature.properties.label)
                .setContent(feature.properties.nama)
                .addTo(layerLabel);
        }
    });
}

function buatLayerTitik() {
    layerTitik.clearLayers();
    dataKec.forEach(d => {
        L.circleMarker([d.lat, d.lng], {
            radius: Math.sqrt(d.jumlah) / 18,   // luas lingkaran sebanding jumlah penduduk
            fillColor: warna(d), color: '#333', weight: 1, fillOpacity: 0.85
        }).bindPopup(isiPopup(d)).addTo(layerTitik);
    });
}

// ---------- Legenda ----------
function buatLegenda() {
    if (legend) map.removeControl(legend);
    legend = L.control({ position: 'bottomright' });
    legend.onAdd = function () {
        const div = L.DomUtil.create('div', 'legend');
        if (mode === 'jumlah') {
            div.innerHTML = '<b>Jumlah Penduduk (jiwa)</b><br>';
            const batas = [0, 40000, 60000, 80000, 100000, 120000];
            batas.forEach((b, i) => {
                div.innerHTML += `<i style="background:${warnaJumlah(b + 1)}"></i>` +
                    (batas[i + 1] ? `${fmt(b)} – ${fmt(batas[i + 1])}` : `> ${fmt(b)}`) + '<br>';
            });
        } else {
            div.innerHTML = '<b>Laju Pertumbuhan</b><br>' +
                `<i style="background:${warnaLaju(-1)}"></i> Negatif (&lt; 0%)<br>` +
                `<i style="background:${warnaLaju(0.1)}"></i> 0 – 0,3%<br>` +
                `<i style="background:${warnaLaju(0.4)}"></i> 0,3 – 0,6%<br>` +
                `<i style="background:${warnaLaju(0.8)}"></i> ≥ 0,6%`;
        }
        return div;
    };
    legend.addTo(map);
}

function gantiMode(m) {
    mode = m;
    document.getElementById('btnJumlah').classList.toggle('active', m === 'jumlah');
    document.getElementById('btnLaju').classList.toggle('active', m === 'laju');
    if (layerBatas) layerBatas.setStyle(gayaBatas);
    buatLayerTitik();
    buatLegenda();
}

// ---------- Grafik ----------
function buatGrafik() {
    new Chart(document.getElementById('chartJumlah'), {
        type: 'bar',
        data: {
            labels: dataKec.map(d => d.nama),
            datasets: [{ label: 'Jiwa', data: dataKec.map(d => d.jumlah),
                         backgroundColor: dataKec.map(d => warnaJumlah(d.jumlah)) }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } },
                   scales: { y: { ticks: { autoSkip: false, font: { size: 10 } } } } }
    });

    const urutLaju = [...dataKec].sort((a, b) => b.laju - a.laju);
    new Chart(document.getElementById('chartLaju'), {
        type: 'bar',
        data: {
            labels: urutLaju.map(d => d.nama),
            datasets: [{ label: '% per tahun', data: urutLaju.map(d => d.laju),
                         backgroundColor: urutLaju.map(d => warnaLaju(d.laju)) }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } },
                   scales: { y: { ticks: { autoSkip: false, font: { size: 10 } } } } }
    });
}

// ---------- Ambil data MySQL (api.php) + batas wilayah (GeoJSON) ----------
Promise.all([
    fetch('api.php').then(r => r.json()),
    fetch('jember_kecamatan.geojson').then(r => r.json())
]).then(([data, geojson]) => {
    dataKec = data;
    dataKec.forEach(d => dataByNama[d.nama] = d);

    buatLayerBatas(geojson);
    buatLayerTitik();

    // Layer yang aktif saat halaman dibuka
    layerBatas.addTo(map);
    layerLabel.addTo(map);
    map.fitBounds(layerBatas.getBounds(), { padding: [10, 10] });

    // Kontrol layer: base map (radio) + overlay (checkbox enable/disable)
    L.control.layers(
        { 'OpenStreetMap': osm, 'Citra Satelit': satelit },
        {
            'Batas Kecamatan (choropleth)': layerBatas,
            'Label Nama Kecamatan': layerLabel,
            'Lingkaran Jumlah Penduduk': layerTitik
        },
        { collapsed: false, position: 'topright' }
    ).addTo(map);

    info.addTo(map);
    L.control.scale({ imperial: false }).addTo(map);
    buatLegenda();
    buatGrafik();
}).catch(err => alert('Gagal memuat data: ' + err));
</script>
</body>
</html>
<?php $conn->close(); ?>
