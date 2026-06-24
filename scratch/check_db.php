<?php
$conn = mysqli_connect('127.0.0.1', 'root', '', 'tagihan_lotus2');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "TABLES:\n";
$res = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($res)) {
    echo " - " . $row[0] . "\n";
}

echo "\nSTRUCTURE OF tb_user:\n";
$res = mysqli_query($conn, "DESCRIBE tb_user");
while ($row = mysqli_fetch_assoc($res)) {
    echo "  " . $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . "\n";
}

echo "\nSTRUCTURE OF tb_pelanggan:\n";
$res = mysqli_query($conn, "DESCRIBE tb_pelanggan");
while ($row = mysqli_fetch_assoc($res)) {
    echo "  " . $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . "\n";
}

mysqli_close($conn);
