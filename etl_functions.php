<?php
// Fungsi untuk mendapatkan koneksi database
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

// Fungsi untuk mengimpor data dari CSV ke tabel tertentu
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

// Fungsi untuk mengimpor data tanggal
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

// Fungsi untuk mengimpor data penjualan
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
                $wilayahID = $rowWilayah['WilayahID'];
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
?>
