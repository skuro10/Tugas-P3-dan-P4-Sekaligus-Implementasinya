<?php
/**
 * Tugas Rumah Pertemuan 3 - Pemrograman Berorientasi Objek (TI033015)
 * Topik : PHP OOP Fundamental (Class, Object, Property, Method, Constructor)
 * Kasus : Sistem Peminjaman Buku
 * Nama  : Ayyasy
 * NIM   : 20251020002
 */

class Buku
{
    // Property (data / state object)
    public string $judul;
    public string $penulis;
    public int $tahunTerbit;
    public bool $tersedia = true;

    // Constructor: dipanggil otomatis saat object dibuat dengan "new"
    public function __construct(string $judul, string $penulis, int $tahunTerbit)
    {
        $this->judul       = $judul;
        $this->penulis     = $penulis;
        $this->tahunTerbit = $tahunTerbit;
    }

    // Method: menampilkan informasi buku beserta statusnya
    public function tampilkanInfo(): void
    {
        $status = $this->tersedia ? "Tersedia" : "Sedang dipinjam";
        echo "{$this->judul} - {$this->penulis} ({$this->tahunTerbit}) | Status: {$status}\n";
    }

    // Method: mengubah status buku menjadi dipinjam
    public function pinjam(): void
    {
        if ($this->tersedia) {
            $this->tersedia = false;
            echo "Berhasil: buku \"{$this->judul}\" dipinjam.\n";
        } else {
            echo "Gagal: Buku sedang dipinjam.\n";
        }
    }

    // Method: mengubah status buku menjadi tersedia kembali
    public function kembalikan(): void
    {
        $this->tersedia = true;
        echo "Berhasil: buku \"{$this->judul}\" dikembalikan.\n";
    }
}

// ===== Instantiation: 1 object dari class Buku =====
$buku1 = new Buku("Pemrograman Berorientasi Objek Dasar", "Budi Santoso", 2022);

// ===== Pengujian =====
echo "=== Data Awal ===\n";
$buku1->tampilkanInfo();

echo "\n=== Pinjam Buku 1 (dua kali) ===\n";
$buku1->pinjam();
$buku1->pinjam();   // harus gagal karena sudah dipinjam

echo "\n=== Status Setelah Dipinjam ===\n";
$buku1->tampilkanInfo();

echo "\n=== Kembalikan Buku 1 ===\n";
$buku1->kembalikan();
$buku1->tampilkanInfo();