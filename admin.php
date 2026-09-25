<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Kelola Data Kecamatan</title>
<style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; color: #333; }
    header { background: #1e3a5f; color: #fff; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; }
    header h1 { margin: 0; font-size: 20px; }
    header a { color: #fff; text-decoration: none; font-size: 13px; border: 1px solid #fff; padding: 6px 12px; border-radius: 6px; }
    .container { max-width: 1100px; margin: auto; padding: 20px; }
    .panel { background: #fff; border-radius: 10px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,.08); margin-bottom: 20px; }
    .panel h2 { margin: 0 0 14px; font-size: 16px; color: #1e3a5f; }
    form.grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; align-items: end; }
    label { font-size: 12px; color: #666; display: block; margin-bottom: 3px; }
    input { width: 100%; padding: 7px 8px; border: 1px solid #ccc; border-radius: 6px; font-size: 13px; }
    button { border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; }
    .btn-simpan { background: #1e3a5f; color: #fff; }
    .btn-batal { background: #eee; color: #333; }
    .btn-hapus { background: #c0392b; color: #fff; }
    .btn-edit { background: #f0c419; color: #333; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 8px; }
    th, td { padding: 8px 10px; border-bottom: 1px solid #eee; text-align: left; }
    th { background: #f0c419; color: #333; }
    td.num { text-align: right; }
    .aksi { display: flex; gap: 6px; }
    .aksi button { padding: 4px 10px; font-size: 12px; }
    .pesan { font-size: 13px; margin-bottom: 10px; padding: 8px 10px; border-radius: 6px; display: none; }
    .pesan.sukses { background: #d4edda; color: #155724; display: block; }
    .pesan.gagal { background: #f8d7da; color: #721c24; display: block; }
    .search-box { margin-bottom: 14px; }
    .search-box input { max-width: 300px; display: inline-block; }
</style>
</head>
<body>

<header>
    <h1>Admin — Kelola Data Kecamatan (CRUD)</h1>
    <a href="index.php">&larr; Kembali ke Peta</a>
</header>

<div class="container">

    <div class="panel">
        <h2 id="judulForm">Tambah Kecamatan Baru</h2>
        <div id="pesan" class="pesan"></div>
        <form class="grid" id="formKec">
            <input type="hidden" id="id" value="">
            <div>
                <label>Nama Kecamatan</label>
                <input type="text" id="nama" required>
            </div>
            <div>
                <label>Jumlah Penduduk</label>
                <input type="number" id="jumlah" required>
            </div>
            <div>
                <label>Laju Pertumbuhan (%)</label>
                <input type="number" step="0.01" id="laju" required>
            </div>
            <div>
                <label>Latitude</label>
                <input type="number" step="0.00000001" id="lat" required>
            </div>
            <div>
                <label>Longitude</label>
                <input type="number" step="0.00000001" id="lng" required>
            </div>
            <div style="grid-column: 1 / -1; display:flex; gap:8px;">
                <button type="submit" class="btn-simpan">Simpan</button>
                <button type="button" class="btn-batal" onclick="resetForm()">Batal / Reset</button>
            </div>
        </form>
    </div>

    <div class="panel">
        <h2>Cari &amp; Daftar Kecamatan</h2>
        <div class="search-box" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <input type="text" id="kotakCari" placeholder="Cari nama kecamatan..." oninput="muatData(this.value)">
            <span style="font-size:12px; color:#777;">Urutkan:</span>
            <select id="sortBy" onchange="muatData(document.getElementById('kotakCari').value)"
                    style="padding:6px 8px; border:1px solid #ccc; border-radius:6px; font-size:13px;">
                <option value="nama">Nama</option>
                <option value="jumlah">Jumlah Penduduk</option>
                <option value="laju">Laju Pertumbuhan</option>
            </select>
            <select id="sortOrder" onchange="muatData(document.getElementById('kotakCari').value)"
                    style="padding:6px 8px; border:1px solid #ccc; border-radius:6px; font-size:13px;">
                <option value="asc">Naik (A-Z / kecil-besar)</option>
                <option value="desc">Turun (Z-A / besar-kecil)</option>
            </select>
        </div>
        <table>
            <thead>
                <tr>
                    <th>No</th><th>Nama</th>
                    <th style="text-align:right">Penduduk</th>
                    <th style="text-align:right">Laju (%)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>
</div>

<script>
const API = 'backend/api.php';

function tampilPesan(teks, jenis) {
    const el = document.getElementById('pesan');
    el.textContent = teks;
    el.className = 'pesan ' + jenis;
    setTimeout(() => { el.className = 'pesan'; }, 3000);
}

function resetForm() {
    document.getElementById('id').value = '';
    document.getElementById('formKec').reset();
    document.getElementById('judulForm').textContent = 'Tambah Kecamatan Baru';
}

async function muatData(cari = '') {
    const sortBy = document.getElementById('sortBy').value;
    const order = document.getElementById('sortOrder').value;

    const params = new URLSearchParams();
    if (cari) params.set('search', cari);
    params.set('sort_by', sortBy);
    params.set('order', order);

    const res = await fetch(`${API}?${params.toString()}`);
    const data = await res.json();
    const tbody = document.getElementById('tbody');
    tbody.innerHTML = '';
    data.forEach((d, i) => {
        tbody.innerHTML += `
            <tr>
                <td>${i + 1}</td>
                <td>${d.nama}</td>
                <td class="num">${d.jumlah.toLocaleString('id-ID')}</td>
                <td class="num">${d.laju}</td>
                <td class="aksi">
                    <button class="btn-edit" onclick='isiForm(${JSON.stringify(d)})'>Edit</button>
                    <button class="btn-hapus" onclick="hapusData(${d.id}, '${d.nama.replace(/'/g, "\\'")}')">Hapus</button>
                </td>
            </tr>`;
    });
    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#999;">Data tidak ditemukan</td></tr>';
    }
}

function isiForm(d) {
    document.getElementById('id').value = d.id;
    document.getElementById('nama').value = d.nama;
    document.getElementById('jumlah').value = d.jumlah;
    document.getElementById('laju').value = d.laju;
    document.getElementById('lat').value = d.lat;
    document.getElementById('lng').value = d.lng;
    document.getElementById('judulForm').textContent = 'Edit Kecamatan: ' + d.nama;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function hapusData(id, nama) {
    if (!confirm(`Yakin ingin menghapus kecamatan "${nama}"?`)) return;
    const res = await fetch(`${API}?id=${id}`, { method: 'DELETE' });
    if (res.ok) {
        tampilPesan('Data berhasil dihapus.', 'sukses');
        muatData(document.getElementById('kotakCari').value);
    } else {
        tampilPesan('Gagal menghapus data.', 'gagal');
    }
}

document.getElementById('formKec').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('id').value;
    const body = {
        nama: document.getElementById('nama').value,
        jumlah: Number(document.getElementById('jumlah').value),
        laju: Number(document.getElementById('laju').value),
        lat: Number(document.getElementById('lat').value),
        lng: Number(document.getElementById('lng').value),
    };

    const url = id ? `${API}?id=${id}` : API;
    const method = id ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    });

    if (res.ok) {
        tampilPesan(id ? 'Data berhasil diupdate.' : 'Data berhasil ditambahkan.', 'sukses');
        resetForm();
        muatData();
    } else {
        const err = await res.json();
        tampilPesan(err.error || 'Terjadi kesalahan.', 'gagal');
    }
});

muatData();
</script>
</body>
</html>