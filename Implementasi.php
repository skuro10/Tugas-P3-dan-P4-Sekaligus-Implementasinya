<?php
declare(strict_types=1);

/**
 * Tugas Rumah - Pemrograman Berorientasi Objek (TI033015)
 * Topik : Model OOP (Interface, Abstract Class, Inheritance, Composition,
 *         Overriding, Polymorphism)
 * Kasus : Sistem Peminjaman Perpustakaan (pengembangan class Buku Pertemuan 3-4)
 * Nama  : Ayyasy
 * NIM   : 20251020002
 *
 * DOMAIN
 * Perpustakaan menyimpan koleksi (buku, majalah) dan daftar anggota
 * (mahasiswa, dosen). Anggota meminjam koleksi; setiap peminjaman punya
 * jatuh tempo dan denda yang aturannya berbeda per jenis koleksi/anggota.
 *
 * KANDIDAT CLASS & RELASI
 *  - DapatDipinjam (interface)        : kontrak perilaku "bisa dipinjam"
 *  - ItemPerpustakaan (abstract)      : implements DapatDipinjam
 *      |- Buku    (extends)           : denda 1.000/hari, durasi 7 hari
 *      |- Majalah (extends)           : denda 2.000/hari, durasi 3 hari
 *  - Anggota (abstract)
 *      |- Mahasiswa (extends)         : batas 3 item
 *      |- Dosen     (extends)         : batas 5 item, diskon denda 50%
 *  - Peminjaman     : association ke Anggota & ItemPerpustakaan (memakai, bukan memiliki)
 *  - Perpustakaan   : composition ke Peminjaman (dibuat & dimiliki di dalamnya),
 *                     aggregation ke koleksi & anggota (dapat ada tanpa perpustakaan)
 */

// =====================================================================
// 1. INTERFACE
// =====================================================================
interface DapatDipinjam
{
    public function pinjam(): void;
    public function kembalikan(): void;
    public function isTersedia(): bool;
}

// =====================================================================
// 2. ABSTRACT CLASS: ItemPerpustakaan (hasil generalisasi class Buku P3/P4)
// =====================================================================
abstract class ItemPerpustakaan implements DapatDipinjam
{
    private const TAHUN_MINIMAL = 1900;

    private string $judul;
    private int $tahunTerbit;
    private bool $tersedia = true;

    public function __construct(string $judul, int $tahunTerbit)
    {
        $this->ubahJudul($judul);
        $this->ubahTahunTerbit($tahunTerbit);
    }

    // --- getter ---
    public function getJudul(): string { return $this->judul; }
    public function getTahunTerbit(): int { return $this->tahunTerbit; }
    public function isTersedia(): bool { return $this->tersedia; }

    // --- validasi (encapsulation dari Pertemuan 4) ---
    private function ubahJudul(string $judul): void
    {
        $judul = trim($judul);
        if ($judul === '') {
            throw new InvalidArgumentException("Judul tidak boleh kosong");
        }
        $this->judul = $judul;
    }

    private function ubahTahunTerbit(int $tahun): void
    {
        if ($tahun < self::TAHUN_MINIMAL || $tahun > (int) date('Y')) {
            throw new InvalidArgumentException(
                "Tahun terbit tidak valid (1900 s.d. tahun berjalan)"
            );
        }
        $this->tahunTerbit = $tahun;
    }

    // --- implementasi interface DapatDipinjam ---
    public function pinjam(): void
    {
        if (!$this->tersedia) {
            throw new RuntimeException("Item sedang dipinjam");
        }
        $this->tersedia = false;
    }

    public function kembalikan(): void
    {
        if ($this->tersedia) {
            throw new RuntimeException("Item belum dipinjam, tidak bisa dikembalikan");
        }
        $this->tersedia = true;
    }

    // --- method ABSTRACT: wajib diimplementasikan subclass ---
    abstract public function getJenis(): string;
    abstract public function getTarifDendaPerHari(): float;

    // --- method konkret yang BOLEH di-override ---
    public function getDurasiPinjamHari(): int
    {
        return 7;
    }

    // Template method: algoritma tetap, tarif ditentukan subclass
    public function hitungDenda(int $hariTerlambat): float
    {
        return max(0, $hariTerlambat) * $this->getTarifDendaPerHari();
    }

    public function getInfo(): string
    {
        $status = $this->tersedia ? 'Tersedia' : 'Sedang dipinjam';
        return "[{$this->getJenis()}] {$this->judul} ({$this->tahunTerbit}) | Status: {$status}";
    }
}

// =====================================================================
// 3. SUBCLASS KOLEKSI (inheritance + overriding)
// =====================================================================
class Buku extends ItemPerpustakaan
{
    private string $penulis;

    public function __construct(string $judul, string $penulis, int $tahunTerbit)
    {
        parent::__construct($judul, $tahunTerbit);
        $penulis = trim($penulis);
        if (strlen($penulis) < 3) {
            throw new InvalidArgumentException("Nama penulis minimal 3 karakter");
        }
        $this->penulis = $penulis;
    }

    public function getPenulis(): string { return $this->penulis; }

    public function getJenis(): string { return 'Buku'; }

    public function getTarifDendaPerHari(): float { return 1000.0; }

    // OVERRIDING: memperluas getInfo() milik parent
    public function getInfo(): string
    {
        return parent::getInfo() . " | Penulis: {$this->penulis}";
    }
}

class Majalah extends ItemPerpustakaan
{
    private int $edisi;

    public function __construct(string $judul, int $edisi, int $tahunTerbit)
    {
        parent::__construct($judul, $tahunTerbit);
        if ($edisi < 1) {
            throw new InvalidArgumentException("Edisi minimal 1");
        }
        $this->edisi = $edisi;
    }

    public function getJenis(): string { return 'Majalah'; }

    public function getTarifDendaPerHari(): float { return 2000.0; }

    // OVERRIDING: majalah hanya boleh dipinjam 3 hari (default parent = 7)
    public function getDurasiPinjamHari(): int
    {
        return 3;
    }

    // OVERRIDING: menambahkan informasi edisi
    public function getInfo(): string
    {
        return parent::getInfo() . " | Edisi: {$this->edisi}";
    }
}

// =====================================================================
// 4. ABSTRACT CLASS: Anggota + subclass
// =====================================================================
abstract class Anggota
{
    private string $nama;
    private string $idAnggota;

    public function __construct(string $nama, string $idAnggota)
    {
        $nama = trim($nama);
        $idAnggota = trim($idAnggota);
        if ($nama === '' || $idAnggota === '') {
            throw new InvalidArgumentException("Nama dan ID anggota tidak boleh kosong");
        }
        $this->nama = $nama;
        $this->idAnggota = $idAnggota;
    }

    public function getNama(): string { return $this->nama; }
    public function getIdAnggota(): string { return $this->idAnggota; }

    abstract public function getPeran(): string;
    abstract public function getBatasPinjam(): int;

    // Default: tanpa diskon. Subclass boleh meng-override.
    public function hitungDendaAkhir(float $dendaDasar): float
    {
        return $dendaDasar;
    }

    public function getInfo(): string
    {
        return "{$this->getPeran()} {$this->nama} ({$this->idAnggota}) | Batas pinjam: {$this->getBatasPinjam()}";
    }
}

class Mahasiswa extends Anggota
{
    public function getPeran(): string { return 'Mahasiswa'; }
    public function getBatasPinjam(): int { return 3; }
}

class Dosen extends Anggota
{
    public function getPeran(): string { return 'Dosen'; }
    public function getBatasPinjam(): int { return 5; }

    // OVERRIDING: dosen mendapat diskon denda 50%
    public function hitungDendaAkhir(float $dendaDasar): float
    {
        return $dendaDasar * 0.5;
    }
}

// =====================================================================
// 5. PEMINJAMAN (association: memakai Anggota & ItemPerpustakaan)
// =====================================================================
final class Peminjaman
{
    private Anggota $anggota;
    private ItemPerpustakaan $item;
    private DateTimeImmutable $tanggalPinjam;
    private DateTimeImmutable $jatuhTempo;
    private ?DateTimeImmutable $tanggalKembali = null;

    public function __construct(Anggota $anggota, ItemPerpustakaan $item, DateTimeImmutable $tanggalPinjam)
    {
        $this->anggota = $anggota;
        $this->item = $item;
        $this->tanggalPinjam = $tanggalPinjam;
        // POLYMORPHISM: durasi ditentukan class konkret item (Buku=7, Majalah=3)
        $this->jatuhTempo = $tanggalPinjam->modify("+{$item->getDurasiPinjamHari()} days");
    }

    public function getAnggota(): Anggota { return $this->anggota; }
    public function getItem(): ItemPerpustakaan { return $this->item; }
    public function getJatuhTempo(): DateTimeImmutable { return $this->jatuhTempo; }
    public function isAktif(): bool { return $this->tanggalKembali === null; }

    /** Menyelesaikan peminjaman, mengembalikan denda akhir (rupiah). */
    public function selesai(DateTimeImmutable $tanggalKembali): float
    {
        if (!$this->isAktif()) {
            throw new RuntimeException("Peminjaman sudah selesai");
        }
        if ($tanggalKembali < $this->tanggalPinjam) {
            throw new InvalidArgumentException("Tanggal kembali sebelum tanggal pinjam");
        }

        $this->item->kembalikan();
        $this->tanggalKembali = $tanggalKembali;

        $hariTerlambat = $tanggalKembali > $this->jatuhTempo
            ? (int) $this->jatuhTempo->diff($tanggalKembali)->days
            : 0;

        // POLYMORPHISM: tarif dari item, diskon dari anggota
        $dendaDasar = $this->item->hitungDenda($hariTerlambat);
        return $this->anggota->hitungDendaAkhir($dendaDasar);
    }
}

// =====================================================================
// 6. PERPUSTAKAAN (composition ke Peminjaman, aggregation ke koleksi/anggota)
// =====================================================================
final class Perpustakaan
{
    /** @var ItemPerpustakaan[] */
    private array $koleksi = [];
    /** @var Anggota[] */
    private array $anggota = [];
    /** @var Peminjaman[] */
    private array $riwayat = [];

    public function tambahItem(ItemPerpustakaan $item): void
    {
        $this->koleksi[] = $item;
    }

    public function daftarkanAnggota(Anggota $anggota): void
    {
        $this->anggota[] = $anggota;
    }

    public function pinjamkan(Anggota $anggota, ItemPerpustakaan $item, DateTimeImmutable $tanggal): Peminjaman
    {
        if (!in_array($anggota, $this->anggota, true)) {
            throw new RuntimeException("Anggota belum terdaftar");
        }
        if (!in_array($item, $this->koleksi, true)) {
            throw new RuntimeException("Item bukan koleksi perpustakaan ini");
        }
        if ($this->hitungPinjamanAktif($anggota) >= $anggota->getBatasPinjam()) {
            throw new RuntimeException(
                "Batas pinjam {$anggota->getPeran()} ({$anggota->getBatasPinjam()}) tercapai"
            );
        }

        $item->pinjam();                                    // gagal -> exception, riwayat tetap bersih
        $peminjaman = new Peminjaman($anggota, $item, $tanggal);
        $this->riwayat[] = $peminjaman;                     // composition: objek dibuat & dimiliki di sini
        return $peminjaman;
    }

    public function kembalikan(Peminjaman $peminjaman, DateTimeImmutable $tanggal): float
    {
        if (!in_array($peminjaman, $this->riwayat, true)) {
            throw new RuntimeException("Peminjaman bukan milik perpustakaan ini");
        }
        return $peminjaman->selesai($tanggal);
    }

    private function hitungPinjamanAktif(Anggota $anggota): int
    {
        $jumlah = 0;
        foreach ($this->riwayat as $p) {
            if ($p->isAktif() && $p->getAnggota() === $anggota) {
                $jumlah++;
            }
        }
        return $jumlah;
    }

    public function tampilkanKatalog(): void
    {
        // POLYMORPHISM: satu loop, tiap object memakai getInfo() versinya sendiri
        foreach ($this->koleksi as $item) {
            echo "  - " . $item->getInfo() . "\n";
        }
    }
}

// =====================================================================
// 7. PENGUJIAN
// =====================================================================
function rupiah(float $nilai): string
{
    return 'Rp' . number_format($nilai, 0, ',', '.');
}

function uji(int $no, string $skenario, callable $aksi): void
{
    echo "[Tes {$no}] {$skenario}\n";
    try {
        $aksi();
        echo "    Hasil : BERHASIL\n";
    } catch (InvalidArgumentException | RuntimeException $e) {
        echo "    Hasil : DITOLAK (" . get_class($e) . ") -> " . $e->getMessage() . "\n";
    } catch (Error $e) {
        echo "    Hasil : DITOLAK (" . get_class($e) . ") -> " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// --- Setup ---
$perpus = new Perpustakaan();

$buku1    = new Buku("Pemrograman Berorientasi Objek Dasar", "Budi Santoso", 2022);
$buku2    = new Buku("Belajar Laravel untuk Pemula", "Sari Wulandari", 2024);
$majalah1 = new Majalah("Tech Monthly", 10, 2025);
$majalah2 = new Majalah("Dev Weekly", 42, 2025);

foreach ([$buku1, $buku2, $majalah1, $majalah2] as $item) {
    $perpus->tambahItem($item);
}

$ayyasy = new Mahasiswa("Ayyasy", "20251020002");
$andi   = new Dosen("Andi Prasetyo", "D-001");
$perpus->daftarkanAnggota($ayyasy);
$perpus->daftarkanAnggota($andi);

echo "=== Katalog Awal (polymorphism: getInfo() berbeda per jenis) ===\n";
$perpus->tampilkanKatalog();

echo "\n=== Polymorphism: tarif & durasi per jenis (terlambat 3 hari) ===\n";
foreach ([$buku1, $majalah1] as $item) {
    echo "  {$item->getJenis()}: durasi {$item->getDurasiPinjamHari()} hari, denda 3 hari = "
        . rupiah($item->hitungDenda(3)) . "\n";
}

echo "\n=== Polymorphism: batas & diskon per jenis anggota ===\n";
foreach ([$ayyasy, $andi] as $a) {
    echo "  " . $a->getInfo() . " | Denda Rp10.000 menjadi " . rupiah($a->hitungDendaAkhir(10000)) . "\n";
}

echo "\n=== Pengujian ===\n\n";

$tgl = fn(string $s) => new DateTimeImmutable($s);
$pinjamBuku1 = null;
$pinjamMajalah1 = null;

uji(1, "Mahasiswa meminjam buku yang tersedia (1 Okt 2026)", function () use ($perpus, $ayyasy, $buku1, $tgl, &$pinjamBuku1) {
    $pinjamBuku1 = $perpus->pinjamkan($ayyasy, $buku1, $tgl('2026-10-01'));
    echo "    Info  : " . $buku1->getInfo() . "\n";
    echo "    Tempo : " . $pinjamBuku1->getJatuhTempo()->format('d M Y') . "\n";
});

uji(2, "Dosen meminjam buku yang sedang dipinjam", function () use ($perpus, $andi, $buku1, $tgl) {
    $perpus->pinjamkan($andi, $buku1, $tgl('2026-10-02'));
});

uji(3, "Mahasiswa mengembalikan buku terlambat 3 hari (Buku: 3 x Rp1.000)", function () use ($perpus, &$pinjamBuku1, $tgl) {
    $denda = $perpus->kembalikan($pinjamBuku1, $tgl('2026-10-11'));
    echo "    Denda : " . rupiah($denda) . "\n";
});

uji(4, "Dosen meminjam majalah lalu terlambat 4 hari (4 x Rp2.000, diskon 50%)", function () use ($perpus, $andi, $majalah1, $tgl, &$pinjamMajalah1) {
    $pinjamMajalah1 = $perpus->pinjamkan($andi, $majalah1, $tgl('2026-10-01'));
    echo "    Tempo : " . $pinjamMajalah1->getJatuhTempo()->format('d M Y') . " (durasi majalah 3 hari)\n";
    $denda = $perpus->kembalikan($pinjamMajalah1, $tgl('2026-10-08'));
    echo "    Denda : " . rupiah($denda) . "\n";
});

uji(5, "Mahasiswa meminjam 3 item (batas) lalu mencoba item ke-4", function () use ($perpus, $ayyasy, $buku1, $buku2, $majalah1, $majalah2, $tgl) {
    $perpus->pinjamkan($ayyasy, $buku1, $tgl('2026-10-12'));
    $perpus->pinjamkan($ayyasy, $buku2, $tgl('2026-10-12'));
    $perpus->pinjamkan($ayyasy, $majalah1, $tgl('2026-10-12'));
    echo "    3 item berhasil dipinjam\n";
    $perpus->pinjamkan($ayyasy, $majalah2, $tgl('2026-10-12'));
});

uji(6, "Mengembalikan peminjaman yang sudah selesai", function () use ($perpus, &$pinjamBuku1, $tgl) {
    $perpus->kembalikan($pinjamBuku1, $tgl('2026-10-13'));
});

uji(7, "Membuat Majalah dengan tahun terbit 3000", function () {
    new Majalah("Majalah Masa Depan", 1, 3000);
});

uji(8, "Membuat Buku dengan penulis 2 karakter", function () {
    new Buku("Algoritma", "Al", 2022);
});

uji(9, "Instansiasi abstract class ItemPerpustakaan langsung", function () {
    new ItemPerpustakaan("Item Umum", 2020);
});

uji(10, "Anggota yang belum terdaftar meminjam", function () use ($perpus, $buku2, $tgl) {
    $orangLuar = new Mahasiswa("Tamu", "X-999");
    $perpus->pinjamkan($orangLuar, $buku2, $tgl('2026-10-14'));
});

echo "=== Katalog Akhir ===\n";
$perpus->tampilkanKatalog();