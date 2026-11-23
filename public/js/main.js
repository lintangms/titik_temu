// ==========================================================
// TITIKTEMU - SISTEM INFORMASI BARANG HILANG
// Skrip Interaktivitas Client & Keamanan Sesi
// ==========================================================

// 1. PENGELOLAAN MODAL
function bukaModal(modalId) {
    const el = document.getElementById(modalId);
    if (el) {
        el.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Mencegah scrolling latar belakang saat modal aktif
    }
}

function tutupModal(modalId) {
    const el = document.getElementById(modalId);
    if (el) {
        el.classList.add('hidden');
        document.body.style.overflow = ''; // Pulihkan scroll
    }
}

// Tutup modal jika klik di luar area konten
window.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('modal')) {
        e.target.classList.add('hidden');
        document.body.style.overflow = '';
    }
});

// 2. TOGGLE SIDEBAR UNTUK MOBILE
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

// 3. KONFIRMASI TINDAKAN KRUSIAL
function konfirmasiLogout() {
    if (confirm('Apakah Anda yakin ingin logout dari sistem TitikTemu?')) {
        window.location.href = 'logout.php';
    }
}

function konfirmasiHapus(url, itemNama) {
    if (confirm(`Apakah Anda yakin ingin menghapus data ${itemNama}? Tindakan ini tidak dapat dibatalkan.`)) {
        window.location.href = url;
    }
}

// 4. PENCARIAN & FILTER TABEL CEPAT (CLIENT-SIDE)
function filterTabel(inputId, tableBodyId) {
    const input = document.getElementById(inputId);
    const filter = input ? input.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll(`#${tableBodyId} tr`);

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

// 5. KEAMANAN STATE SETELAH LOGOUT (ANTI BROWSER BACK CACHE)
// Jika pengguna logout lalu menekan tombol Back di browser, paksa reload dari server.
// auth_check.php di server akan langsung mendeteksi sesi kosong dan mengalihkan ke login.php.
window.addEventListener('pageshow', function (event) {
    if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2)) {
        window.location.reload();
    }
});
