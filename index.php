<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Proses ETL Data Warehouse</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            text-align: center;
            padding-top: 50px;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            display: inline-block;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 15px 25px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            margin-top: 20px;
            font-size: 18px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Proses ETL Data Warehouse</h1>
        <form method="post" action="">
            <button type="submit" name="import_produk">Import Data Produk</button>
            <button type="submit" name="import_wilayah">Import Data Wilayah</button>
            <button type="submit" name="import_tanggal">Import Data Tanggal</button>
            <button type="submit" name="import_penjualan">Import Data Penjualan</button>
        </form>
        <div class="message">
            <?php
            if (isset($_POST['import_produk'])) {
                importProduk();
            } elseif (isset($_POST['import_wilayah'])) {
                importWilayah();
            } elseif (isset($_POST['import_tanggal'])) {
                importTanggal();
            } elseif (isset($_POST['import_penjualan'])) {
                importPenjualan();
            }

            // Fungsi untuk mengimpor data produk
            function importProduk() {
                include 'etl_functions.php';
                $result = importCSV('produk.csv', 'produk', ['NamaProduk', 'Kategori', 'Harga']);
                echo $result;
            }

            // Fungsi untuk mengimpor data wilayah
            function importWilayah() {
                include 'etl_functions.php';
                $result = importCSV('wilayah.csv', 'wilayah', ['NamaWilayah', 'Negara']);
                echo $result;
            }

            // Fungsi untuk mengimpor data tanggal
            function importTanggal() {
                include 'etl_functions.php';
                $result = importTanggalCSV('tanggal.csv');
                echo $result;
            }

            // Fungsi untuk mengimpor data penjualan
            function importPenjualan() {
                include 'etl_functions.php';
                $result = importPenjualanCSV('penjualan.csv');
                echo $result;
            }
            ?>
        </div>
    </div>
</body>
</html>
