<?php
declare(strict_types=1);

/**
 * Tugas Rumah Pertemuan 4 - Pemrograman Berorientasi Objek (TI033015)
 * Topik : Encapsulation (refactoring class Buku dari Pertemuan 3)
 * Kasus : Sistem Peminjaman Buku
 * Nama  : Ayyasy
 * NIM   : 20251020002
 */

class Buku
{
    private const TAHUN_MINIMAL = 1900;

    // A. REFACTOR: semua property menjadi private (state dilindungi)
    private string $judul;
    private string $penulis;
    private int $tahunTerbit;
    private bool $tersedia = true;

    // Constructor memakai method ubah*() agar validasi berlaku sejak objek dibuat
    public function __construct(string $judul, string $penulis, int $tahunTerbit)
    {
        $this->ubahJudul($judul);
        $this->ubahPenulis($penulis);
        $this->ubahTahunTerbit($tahunTerbit);
    }

    // B. ACCESS: getter hanya untuk data yang memang perlu dibaca dari luar
    public function getJudul(): string
    {
        return $this->judul;
    }

    public function getPenulis(): string
    {
        return $this->penulis;
    }

    public function getTahunTerbit(): int
    {
        return $this->tahunTerbit;
    }

    public function isTersedia(): bool
    {
        return $this->tersedia;
    }

    // B. ACCESS: perubahan state lewat method yang punya aturan (C. VALIDATION)

    // Validasi 1: judul tidak boleh kosong
    public function ubahJudul(string $judul): void
    {
        $judul = trim($judul);
        if ($judul === '') {
            throw new InvalidArgumentException("Judul tidak boleh kosong");
        }
        $this->judul = $judul;
    }

    // Validasi 2: nama penulis minimal 3 karakter
    public function ubahPenulis(string $penulis): void
    {
        $penulis = trim($penulis);
        if (strlen($penulis) < 3) {
            throw new InvalidArgumentException("Nama penulis minimal 3 karakter");
        }
        $this->penulis = $penulis;
    }

    // Validasi 3: tahun terbit harus masuk akal
    public function ubahTahunTerbit(int $tahun): void
    {
        if ($tahun < self::TAHUN_MINIMAL || $tahun > (int) date('Y')) {
            throw new InvalidArgumentException("Tahun terbit tidak valid (1900 s.d. tahun berjalan)");
        }
        $this->tahunTerbit = $tahun;
    }

    // Validasi 4: buku yang sedang dipinjam tidak boleh dipinjam lagi
    public function pinjam(): void
    {
        if (!$this->tersedia) {
            throw new RuntimeException("Buku sedang dipinjam");
        }
        $this->tersedia = false;
    }

    // Validasi 5: buku yang belum dipinjam tidak boleh dikembalikan
    public function kembalikan(): void
    {
        if ($this->tersedia) {
            throw new RuntimeException("Buku belum dipinjam, tidak bisa dikembalikan");
        }
        $this->tersedia = true;
    }

    public function getInfo(): string
    {
        $status = $this->tersedia ? "Tersedia" : "Sedang dipinjam";
        return "{$this->judul} - {$this->penulis} ({$this->tahunTerbit}) | Status: {$status}";
    }

    public function tampilkanInfo(): void
    {
        echo $this->getInfo() . "\n";
    }
}

// ===================== D. TEST =====================

// Pembantu pengujian: menjalankan skenario dan menampilkan hasilnya
function uji(int $no, string $skenario, callable $aksi): void
{
    echo "[Tes {$no}] {$skenario}\n";
    try {
        $aksi();
        echo "   Hasil : BERHASIL\n";
    } catch (InvalidArgumentException | RuntimeException $e) {
        echo "   Hasil : DITOLAK (" . get_class($e) . ") -> " . $e->getMessage() . "\n";
    } catch (Error $e) {
        echo "   Hasil : DITOLAK (" . get_class($e) . ") -> " . $e->getMessage() . "\n";
    }
    echo "\n";
}

$buku = new Buku("Pemrograman Berorientasi Objek Dasar", "Budi Santoso", 2022);

echo "=== Data Awal ===\n";
$buku->tampilkanInfo();
echo "\n=== Pengujian ===\n";

// --- Skenario normal ---
uji(1, "Pinjam buku yang tersedia (normal)", function () use ($buku) {
    $buku->pinjam();
    echo "   Info  : " . $buku->getInfo() . "\n";
});

// --- Skenario error: aturan peminjaman ---
uji(2, "Pinjam buku yang sedang dipinjam", function () use ($buku) {
    $buku->pinjam();
});

uji(3, "Kembalikan buku yang sedang dipinjam (normal)", function () use ($buku) {
    $buku->kembalikan();
    echo "   Info  : " . $buku->getInfo() . "\n";
});

uji(4, "Kembalikan buku yang belum dipinjam", function () use ($buku) {
    $buku->kembalikan();
});

// --- Skenario error: validasi input ---
uji(5, "Membuat buku dengan judul kosong", function () {
    new Buku("   ", "Budi Santoso", 2022);
});

uji(6, "Membuat buku dengan nama penulis 2 karakter", function () {
    new Buku("Algoritma", "Al", 2022);
});

uji(7, "Membuat buku dengan tahun terbit 3000", function () {
    new Buku("Algoritma", "Budi Santoso", 3000);
});

// --- Skenario valid: membuktikan property private tidak bisa diubah langsung ---
uji(8, "Memeriksa visibilitas property private", function () use ($buku) {
    $refleksi = new ReflectionProperty(Buku::class, 'tersedia');
    echo "   Akses : " . ($refleksi->isPrivate() ? 'private' : 'bukan private') . "\n";
    echo "   Info  : " . $buku->getInfo() . "\n";
});

// --- Skenario normal: ubah data lewat method yang valid ---
uji(9, "Ubah judul lewat ubahJudul() dengan nilai valid", function () use ($buku) {
    $buku->ubahJudul("Algoritma dan Struktur Data");
    echo "   Info  : " . $buku->getInfo() . "\n";
});

uji(10, "Ubah judul lewat ubahJudul() dengan nilai kosong", function () use ($buku) {
    $buku->ubahJudul("");
});

echo "=== Data Akhir (state tetap konsisten) ===\n";
$buku->tampilkanInfo();