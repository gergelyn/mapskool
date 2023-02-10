<?php
    require 'vendor/autoload.php';

    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $inputFileName = './db/iskolak.xlsx';
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($inputFileName);
    $worksheet = $spreadsheet->getSheet(0);

    $headers = [];

    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

    $data = [];
    for ($row = 1; $row <= $highestRow; $row++) {
        $values = [];

        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $values[] = $worksheet->getCell([$col, $row])->getValue();
        }

        if ($row === 1) {
            $keys = $values;
            continue;
        }

        $data[] = array_combine($keys, $values);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <title>mapSkool</title>
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/node_modules/leaflet/dist/leaflet.css">
    <!-- Scripts -->
    <script src="/node_modules/leaflet/dist/leaflet-src.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        #map {
            width: 100vw;
            height: 100vh;
        }
    </style>
</head>
<body>
    <div id="map"></div>
    <script>

        let map = L.map('map').setView([47.53, 21.6391], 13);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        const markerObjects = <?php echo json_encode($data); ?>;

        markerObjects.forEach(markerObject => {
            if (markerObject.GPS !== null) {
                const gps = markerObject.GPS.split(',');
                const marker = L.marker(gps).addTo(map);
                let markerStr = "";
                for (const [key, value] of Object.entries(markerObject)) {
                    if (value !== null && key !== null) {
                        if (key.toLowerCase().includes("telefon")) {
                            markerStr += `<b>${key}</b><br><a href="tel:${value}">${value}</a><br>`;
                        } else if (value.toLowerCase().includes("@")) {
                            markerStr += `<b>${key}</b><br><a href="mailto:${value}">${value}</a><br>`;
                        } else {
                            markerStr += `<b>${key}</b><br>${value}<br>`;
                        }
                    }
                }
                marker.bindPopup(markerStr);
            }
        });
    </script>
</body>
</html>