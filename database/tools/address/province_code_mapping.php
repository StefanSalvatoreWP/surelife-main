<?php
/**
 * Province Code Mapping Backup
 * 
 * This file contains the correct mapping between:
 * - tbladdress.province_code (refCitymun codes, e.g., '0722' for Cebu)
 * - philippine_provinces_and_cities.sql province_id (e.g., '25' for Cebu)
 * 
 * IMPORTANT: Do NOT use PSGC codes (e.g., '012200000') - they are WRONG!
 * Use refCitymun codes instead.
 * 
 * This mapping is used in smart_merge_zips.php for province-aware zip code matching.
 */

return [
    '0128' => '34',  // ILOCOS NORTE
    '0129' => '35',  // ILOCOS SUR
    '0133' => '39',  // LA UNION
    '0155' => '60',  // PANGASINAN
    '0209' => '11',  // BATANES
    '0215' => '18',  // CAGAYAN
    '0231' => '37',  // ISABELA
    '0250' => '55',  // NUEVA VIZCAYA
    '0257' => '62',  // QUIRINO
    '0308' => '10',  // BATAAN
    '0314' => '17',  // BULACAN
    '0349' => '54',  // NUEVA ECIJA
    '0354' => '59',  // PAMPANGA
    '0369' => '75',  // TARLAC
    '0371' => '77',  // ZAMBALES
    '0377' => '8',   // AURORA
    '0410' => '12',  // BATANGAS
    '0421' => '24',  // CAVITE
    '0434' => '40',  // LAGUNA
    '0456' => '61',  // QUEZON
    '0458' => '63',  // RIZAL
    '1740' => '45',  // MARINDUQUE
    '1751' => '56',  // OCCIDENTAL MINDORO
    '1752' => '57',  // ORIENTAL MINDORO
    '1753' => '58',  // PALAWAN
    '1759' => '64',  // ROMBLON
    '0505' => '5',   // ALBAY
    '0516' => '19',  // CAMARINES NORTE
    '0517' => '20',  // CAMARINES SUR
    '0520' => '23',  // CATANDUANES
    '0541' => '46',  // MASBATE
    '0562' => '68',  // SORSOGON
    '0604' => '4',   // AKLAN
    '0606' => '6',   // ANTIQUE
    '0619' => '22',  // CAPIZ
    '0630' => '36',  // ILOILO
    '0645' => '51',  // NEGROS OCCIDENTAL
    '0679' => '32',  // GUIMARAS
    '0712' => '15',  // BOHOL
    '0722' => '25',  // CEBU
    '0746' => '52',  // NEGROS ORIENTAL
    '0761' => '67',  // SIQUIJOR
    '0826' => '31',  // EASTERN SAMAR
    '0837' => '43',  // LEYTE
    '0848' => '53',  // NORTHERN SAMAR
    '0860' => '65',  // SAMAR (WESTERN SAMAR)
    '0864' => '70',  // SOUTHERN LEYTE
    '0878' => '14',  // BILIRAN
    '0972' => '78',  // ZAMBOANGA DEL NORTE
    '0973' => '79',  // ZAMBOANGA DEL SUR
    '0983' => '80',  // ZAMBOANGA SIBUGAY
    '1013' => '16',  // BUKIDNON
    '1018' => '21',  // CAMIGUIN
    '1035' => '41',  // LANAO DEL NORTE
    '1042' => '48',  // MISAMIS OCCIDENTAL
    '1043' => '49',  // MISAMIS ORIENTAL
    '1123' => '28',  // DAVAO DEL NORTE
    '1124' => '29',  // DAVAO DEL SUR
    '1125' => '30',  // DAVAO ORIENTAL
    '1182' => '26',  // COMPOSTELA VALLEY
    '1186' => '29',  // DAVAO OCCIDENTAL
    '1247' => '27',  // COTABATO (NORTH COTABATO)
    '1263' => '69',  // SOUTH COTABATO
    '1265' => '71',  // SULTAN KUDARAT
    '1280' => '66',  // SARANGANI
    '1401' => '1',   // ABRA
    '1411' => '13',  // BENGUET
    '1427' => '33',  // IFUGAO
    '1432' => '38',  // KALINGA
    '1444' => '50',  // MOUNTAIN PROVINCE
    '1481' => '7',   // APAYAO
    '1507' => '9',   // BASILAN
    '1536' => '42',  // LANAO DEL SUR
    '1538' => '44',  // MAGUINDANAO
    '1566' => '72',  // SULU
    '1570' => '76',  // TAWI-TAWI
    '1602' => '2',   // AGUSAN DEL NORTE
    '1603' => '3',   // AGUSAN DEL SUR
    '1667' => '73',  // SURIGAO DEL NORTE
    '1668' => '74',  // SURIGAO DEL SUR
    '1685' => '73',  // DINAGAT ISLANDS
];
