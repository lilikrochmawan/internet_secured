import os
import re

files = [
    'c:/xampp/htdocs/internet/resources/views/admin/mapping/index.blade.php',
    'c:/xampp/htdocs/internet/resources/views/admin/odp/index.blade.php',
    'c:/xampp/htdocs/internet/resources/views/admin/odc/index.blade.php',
    'c:/xampp/htdocs/internet/resources/views/admin/pelanggan/index.blade.php',
    'c:/xampp/htdocs/internet/resources/views/admin/order_pemasangan/index.blade.php'
]

for filepath in files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Replace L.control.layers({"Streets": streets, "Satelit": satellite}).addTo(mapVar);
    # with addCustomMapToggle(mapVar, streets, satellite);
    # And replace {"Default (Streets)": googleStreets, "Satelit (Hybrid)": googleSatellite} with addCustomMapToggle(map, googleStreets, googleSatellite)

    # For mapping/index.blade.php
    content = re.sub(
        r'L\.control\.layers\(\{"Default \(Streets\)":\s*(.+?),\s*"Satelit \(Hybrid\)":\s*(.+?)\}\)\.addTo\((.+?)\);',
        r'addCustomMapToggle(\3, \1, \2);',
        content
    )
    
    # For others
    content = re.sub(
        r'L\.control\.layers\(\{"Streets":\s*(.+?),\s*"Satelit":\s*(.+?)\}\)\.addTo\((.+?)\);',
        r'addCustomMapToggle(\3, \1, \2);',
        content
    )

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
