# Tugas Rumah Pertemuan 4 — Refactoring & Dokumentasi (Encapsulation)

- **Mata kuliah:** Pemrograman Berorientasi Objek (TI033015)
- **Nama / NIM:** Ayyasy / 20251020002
- **Kasus:** Sistem Peminjaman Buku
- **Class yang direfactor:** `Buku` (dari Pertemuan 3)
- **File:** `20251020002_Ayyasy_PBO_M4.php`

## Cara menjalankan

```
php 20251020002_Ayyasy_PBO_M4.php
```

## Apa yang berubah dan kenapa

**1. Refactor: property menjadi private.**
Pada Pertemuan 3 semua property `Buku` bersifat `public`, sehingga kode mana pun bisa
menulis `$buku->tersedia = false` tanpa melewati aturan peminjaman. Sekarang keempat
property (`judul`, `penulis`, `tahunTerbit`, `tersedia`) bersifat `private`, sehingga
hanya class `Buku` yang boleh mengubah state-nya. Percobaan mengubah `$tersedia` dari
luar class sekarang ditolak PHP dengan `Error`.

**2. Access: getter dan method aksi.**
Getter (`getJudul()`, `getPenulis()`, `getTahunTerbit()`, `isTersedia()`) hanya disediakan
untuk membaca data. Perubahan state tidak memakai setter sembarang, tetapi method yang
bermakna: `pinjam()`, `kembalikan()`, `ubahJudul()`, `ubahPenulis()`, dan
`ubahTahunTerbit()`. Dengan begitu pemanggil cukup menyatakan *aksi apa* yang diinginkan,
bukan *variabel apa* yang diubah.

**3. Validation: aturan dijaga di dalam objek.**

| No | Aturan | Method | Exception |
|----|--------|--------|-----------|
| 1 | Judul tidak boleh kosong | `ubahJudul()` | `InvalidArgumentException` |
| 2 | Nama penulis minimal 3 karakter | `ubahPenulis()` | `InvalidArgumentException` |
| 3 | Tahun terbit antara 1900 dan tahun berjalan | `ubahTahunTerbit()` | `InvalidArgumentException` |
| 4 | Buku yang sedang dipinjam tidak bisa dipinjam lagi | `pinjam()` | `RuntimeException` |
| 5 | Buku yang belum dipinjam tidak bisa dikembalikan | `kembalikan()` | `RuntimeException` |

Constructor juga memanggil method `ubah*()`, sehingga objek yang tidak valid tidak pernah
bisa terbentuk. Validasi memakai exception (bukan sekadar `echo`) agar kesalahan bisa
ditangani oleh pemanggil dan tidak diam-diam diabaikan.

**4. Test: 10 skenario pengujian.**

| No | Skenario | Jenis | Hasil yang diharapkan |
|----|----------|-------|-----------------------|
| 1 | Pinjam buku yang tersedia | Normal | Berhasil, status "Sedang dipinjam" |
| 2 | Pinjam buku yang sedang dipinjam | Error | Ditolak (`RuntimeException`) |
| 3 | Kembalikan buku yang dipinjam | Normal | Berhasil, status "Tersedia" |
| 4 | Kembalikan buku yang belum dipinjam | Error | Ditolak (`RuntimeException`) |
| 5 | Judul kosong saat membuat objek | Error | Ditolak (`InvalidArgumentException`) |
| 6 | Penulis hanya 2 karakter | Error | Ditolak (`InvalidArgumentException`) |
| 7 | Tahun terbit 3000 | Error | Ditolak (`InvalidArgumentException`) |
| 8 | `$buku->tersedia = false` dari luar | Error | Ditolak (`Error`, property private) |
| 9 | `ubahJudul()` dengan nilai valid | Normal | Berhasil, judul berubah |
| 10 | `ubahJudul("")` | Error | Ditolak, judul tidak berubah |

Bukti pengujian: lihat screenshot output terminal yang dikumpulkan bersama tugas ini.

## Kesimpulan

Sebelum refactoring, `Buku` hanya kumpulan variabel yang bisa diubah siapa saja. Setelah
refactoring, `Buku` menjaga konsistensi dirinya sendiri: data dilindungi (`private`),
akses lewat antarmuka yang jelas (`public method`), dan setiap perubahan state melewati
validasi. Program tetap berjalan seperti sebelumnya, tetapi state penting kini terkontrol.