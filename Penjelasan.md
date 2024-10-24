# Penjelasan Kode PHP untuk Impor Data ke Database

Kode PHP di bawah ini berfungsi untuk mengimpor data dari file CSV ke dalam database MySQL. Terdapat beberapa fungsi utama yang digunakan dalam proses ini.

## 1. `getConnection()`

### Deskripsi
Fungsi ini digunakan untuk membuat dan mengembalikan koneksi ke database MySQL.

### Detail Implementasi
```php
function getConnection() {
    $servername = "localhost";
    $username = "root"; // Ganti dengan username MySQL Anda
    $password = "";     // Ganti dengan password MySQL Anda
    $dbname = "data_warehouse";

    // Membuat koneksi
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Memeriksa koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    return $conn;
}
```

### Penjelasan
- **Parameter Koneksi**: Mendefinisikan parameter koneksi seperti `servername`, `username`, `password`, dan `dbname`.
- **Membuat Koneksi**: Menggunakan objek `mysqli` untuk membuat koneksi ke database.
- **Pemeriksaan Koneksi**: Jika terjadi kesalahan dalam koneksi, skrip akan berhenti dan menampilkan pesan error.
- **Mengembalikan Koneksi**: Jika koneksi berhasil, objek koneksi `mysqli` dikembalikan untuk digunakan oleh fungsi lain.

## 2. `importCSV($csvFile, $tableName, $columns)`

### Deskripsi
Fungsi ini mengimpor data dari file CSV ke dalam tabel database yang ditentukan, berdasarkan kolom yang disediakan.

### Detail Implementasi
```php
function importCSV($csvFile, $tableName, $columns) {
    $conn = getConnection();
    if (!file_exists($csvFile) || !is_readable($csvFile)) {
        return "File <strong>$csvFile</strong> tidak ditemukan atau tidak dapat dibaca.<br>";
    }

    $count = 0;
    if (($handle = fopen($csvFile, 'r')) !== FALSE) {
        $header = fgetcsv($handle, 1000, ","); // Membaca header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Membuat array asosiatif
            $row = array_combine($header, $data);
            
            // Menyiapkan kolom dan nilai
            $cols = [];
            $vals = [];
            foreach ($columns as $col) {
                $cols[] = "`$col`";
                $vals[] = "'" . $conn->real_escape_string($row[$col]) . "'";
            }
            
            $sql = "INSERT INTO `$tableName` (" . implode(", ", $cols) . ") VALUES (" . implode(", ", $vals) . ")";
            
            if ($conn->query($sql)) {
                $count++;
            } else {
                echo "Error saat memasukkan data ke tabel <strong>$tableName</strong>: " . $conn->error . "<br>";
            }
        }
        fclose($handle);
    }
    $conn->close();
    return "Berhasil mengimpor <strong>$count</strong> record ke tabel <strong>$tableName</strong>.<br>";
}
```

### Penjelasan
- **Parameter**:
  - `$csvFile`: Path atau lokasi file CSV yang akan diimpor.
  - `$tableName`: Nama tabel dalam database tujuan.
  - `$columns`: Array yang berisi nama-nama kolom dalam tabel yang akan diisi.
- **Validasi File**: Memeriksa apakah file CSV ada dan dapat dibaca.
- **Membuka File CSV**: Menggunakan `fopen` untuk membuka file dan `fgetcsv` untuk membaca baris demi baris.
- **Proses Impor**:
  - Membaca header CSV untuk menentukan kolom.
  - Menggabungkan header dengan data untuk membuat array asosiatif.
  - Menyiapkan nama kolom dan nilai yang akan dimasukkan ke dalam query SQL.
  - Menjalankan query `INSERT INTO` untuk memasukkan data ke tabel.
  - Menangani dan menampilkan error jika terjadi kesalahan saat memasukkan data.
- **Penutupan Koneksi**: Menutup koneksi database setelah proses impor selesai.
- **Return**: Mengembalikan pesan yang menunjukkan jumlah record yang berhasil diimpor.

## 3. `importTanggalCSV($csvFile)`

### Deskripsi
Fungsi ini khusus digunakan untuk mengimpor data tanggal ke dalam tabel `tanggal_dim`.

### Detail Implementasi
```php
function importTanggalCSV($csvFile) {
    $conn = getConnection();
    if (!file_exists($csvFile) || !is_readable($csvFile)) {
        return "File <strong>$csvFile</strong> tidak ditemukan atau tidak dapat dibaca.<br>";
    }

    $count = 0;
    if (($handle = fopen($csvFile, 'r')) !== FALSE) {
        $header = fgetcsv($handle, 1000, ","); // Membaca header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row = array_combine($header, $data);
            $tanggal = date('Y-m-d', strtotime($row['Tanggal']));
            $bulan = date('n', strtotime($row['Tanggal']));
            $tahun = date('Y', strtotime($row['Tanggal']));

            $sql = "INSERT INTO `tanggal_dim` (`Tanggal`, `Bulan`, `Tahun`) VALUES ('$tanggal', $bulan, $tahun)";
            
            if ($conn->query($sql)) {
                $count++;
            } else {
                echo "Error saat memasukkan data ke tabel <strong>tanggal_dim</strong>: " . $conn->error . "<br>";
            }
        }
        fclose($handle);
    }
    $conn->close();
    return "Berhasil mengimpor <strong>$count</strong> record ke tabel <strong>tanggal_dim</strong>.<br>";
}
```

### Penjelasan
- **Parameter**:
  - `$csvFile`: Path atau lokasi file CSV yang berisi data tanggal.
- **Validasi File**: Memeriksa apakah file CSV ada dan dapat dibaca.
- **Membuka File CSV**: Menggunakan `fopen` dan `fgetcsv` untuk membaca data.
- **Proses Impor**:
  - Membaca header CSV.
  - Membuat array asosiatif dari header dan data.
  - Mengonversi tanggal ke format `Y-m-d` dan mengekstrak bulan serta tahun.
  - Menyiapkan dan menjalankan query `INSERT INTO` untuk memasukkan data ke tabel `tanggal_dim`.
  - Menangani dan menampilkan error jika terjadi kesalahan saat memasukkan data.
- **Penutupan Koneksi**: Menutup koneksi database setelah proses impor selesai.
- **Return**: Mengembalikan pesan yang menunjukkan jumlah record yang berhasil diimpor.

## 4. `importPenjualanCSV($csvFile)`

### Deskripsi
Fungsi ini mengimpor data penjualan dari file CSV ke dalam tabel `penjualan`. Proses ini melibatkan pengambilan ID dari tabel terkait seperti `produk`, `wilayah`, dan `tanggal_dim`.

### Detail Implementasi
```php
function importPenjualanCSV($csvFile) {
    $conn = getConnection();
    if (!file_exists($csvFile) || !is_readable($csvFile)) {
        return "File <strong>$csvFile</strong> tidak ditemukan atau tidak dapat dibaca.<br>";
    }

    $count = 0;
    if (($handle = fopen($csvFile, 'r')) !== FALSE) {
        $header = fgetcsv($handle, 1000, ","); // Membaca header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row = array_combine($header, $data);
            $namaProduk = $conn->real_escape_string($row['NamaProduk']);
            $namaWilayah = $conn->real_escape_string($row['NamaWilayah']);
            $tanggal = date('Y-m-d', strtotime($row['Tanggal']));
            $jumlah = (int)$row['Jumlah'];
            $totalPenjualan = (float)$row['TotalPenjualan'];

            // Mendapatkan ProdukID
            $sqlProduk = "SELECT ProdukID FROM produk WHERE NamaProduk = '$namaProduk' LIMIT 1";
            $resultProduk = $conn->query($sqlProduk);
            if ($resultProduk->num_rows > 0) {
                $rowProduk = $resultProduk->fetch_assoc();
                $produkID = $rowProduk['ProdukID'];
            } else {
                echo "Produk '<strong>$namaProduk</strong>' tidak ditemukan.<br>";
                continue;
            }

            // Mendapatkan WilayahID
            $sqlWilayah = "SELECT WilayahID FROM wilayah WHERE NamaWilayah = '$namaWilayah' LIMIT 1";
            $resultWilayah = $conn->query($sqlWilayah);
            if ($resultWilayah->num_rows > 0) {
                $rowWilayah = $resultWilayah->fetch_assoc();
                $wilayahID = $row

Wilayah['WilayahID'];
            } else {
                echo "Wilayah '<strong>$namaWilayah</strong>' tidak ditemukan.<br>";
                continue;
            }

            // Mendapatkan TanggalID
            $sqlTanggal = "SELECT TanggalID FROM tanggal_dim WHERE Tanggal = '$tanggal' LIMIT 1";
            $resultTanggal = $conn->query($sqlTanggal);
            if ($resultTanggal->num_rows > 0) {
                $rowTanggal = $resultTanggal->fetch_assoc();
                $tanggalID = $rowTanggal['TanggalID'];
            } else {
                echo "Tanggal '<strong>$tanggal</strong>' tidak ditemukan.<br>";
                continue;
            }

            // Memasukkan data penjualan
            $sqlPenjualan = "INSERT INTO penjualan (ProdukID, WilayahID, TanggalID, Jumlah, TotalPenjualan) 
                             VALUES ($produkID, $wilayahID, $tanggalID, $jumlah, $totalPenjualan)";
            
            if ($conn->query($sqlPenjualan)) {
                $count++;
            } else {
                echo "Error saat memasukkan data ke tabel <strong>penjualan</strong>: " . $conn->error . "<br>";
            }
        }
        fclose($handle);
    }
    $conn->close();
    return "Berhasil mengimpor <strong>$count</strong> record ke tabel <strong>penjualan</strong>.<br>";
}
```

### Penjelasan
- **Parameter**:
  - `$csvFile`: Path atau lokasi file CSV yang berisi data penjualan.
- **Validasi File**: Memeriksa apakah file CSV ada dan dapat dibaca.
- **Membuka File CSV**: Menggunakan `fopen` dan `fgetcsv` untuk membaca data.
- **Proses Impor**:
  - Membaca header CSV.
  - Membuat array asosiatif dari header dan data.
  - Mengambil ID produk, wilayah, dan tanggal dari tabel terkait berdasarkan nama yang diberikan.
  - Menyiapkan dan menjalankan query `INSERT INTO` untuk memasukkan data penjualan ke tabel.
  - Menangani dan menampilkan error jika terjadi kesalahan saat memasukkan data.
- **Penutupan Koneksi**: Menutup koneksi database setelah proses impor selesai.
- **Return**: Mengembalikan pesan yang menunjukkan jumlah record yang berhasil diimpor.