-- Ajouter des coordonnées GPS aux bibliothèques existantes
-- Coordonnées pour des bibliothèques en Tunisie

-- Bibliothèque Nationale de Tunisie (Tunis)
UPDATE libraries SET 
    latitude = 36.8065, 
    longitude = 10.1815,
    address = 'Boulevard du 9 Avril 1938, Tunis'
WHERE name LIKE '%National%' OR id = 1;

-- Bibliothèque Municipale de Tunis
UPDATE libraries SET 
    latitude = 36.8008, 
    longitude = 10.1865,
    address = 'Avenue Habib Bourguiba, Tunis'
WHERE name LIKE '%Municipal%' OR id = 2;

-- Bibliothèque de la Cité des Sciences
UPDATE libraries SET 
    latitude = 36.8189, 
    longitude = 10.1658,
    address = 'Boulevard Mohamed Bouazizi, Tunis'
WHERE name LIKE '%Science%' OR id = 3;

-- University of Tunis Library (El Manar)
UPDATE libraries SET 
    latitude = 36.8425, 
    longitude = 10.1923,
    address = 'Campus Universitaire El Manar, Tunis 2092, Tunisia'
WHERE id = 4 OR name LIKE '%University of Tunis%';

-- Carthage Library
UPDATE libraries SET 
    latitude = 36.8531, 
    longitude = 10.3233,
    address = 'Avenue Habib Bourguiba, Carthage, Tunisia'
WHERE id = 5 OR name LIKE '%Carthage%';

-- Sousse Library
UPDATE libraries SET 
    latitude = 35.8256, 
    longitude = 10.6411,
    address = 'Boulevard Yahia Ibn Omar, Sousse 4000, Tunisia'
WHERE id = 7 OR name LIKE '%Sousse%';

-- Bizerte Library
UPDATE libraries SET 
    latitude = 37.2744, 
    longitude = 9.8672,
    address = 'Avenue Habib Bourguiba, Bizerte 7000, Tunisia'
WHERE id = 8 OR name LIKE '%Bizerte%';

-- Kairouan Library
UPDATE libraries SET 
    latitude = 35.6781, 
    longitude = 10.0963,
    address = 'Avenue de la République, Kairouan 3100, Tunisia'
WHERE id = 9 OR name LIKE '%Kairouan%';

-- Monastir Library
UPDATE libraries SET 
    latitude = 35.7781, 
    longitude = 10.8264,
    address = 'Avenue Habib Bourguiba, Monastir 5000, Tunisia'
WHERE id = 10 OR name LIKE '%Monastir%';

-- Si vous n'avez pas encore de bibliothèques, en créer quelques-unes
INSERT INTO libraries (name, address, latitude, longitude) VALUES
('Bibliothèque Nationale de Tunisie', 'Boulevard du 9 Avril 1938, Tunis', 36.8065, 10.1815),
('Bibliothèque Municipale de Tunis', 'Avenue Habib Bourguiba, Tunis', 36.8008, 10.1865),
('Bibliothèque de la Cité des Sciences', 'Boulevard Mohamed Bouazizi, Tunis', 36.8189, 10.1658),
('Bibliothèque Universitaire El Manar', 'Campus Universitaire El Manar, Tunis', 36.8425, 10.1923),
('Bibliothèque de Carthage', 'Avenue Habib Bourguiba, Carthage', 36.8531, 10.3233),
('Bibliothèque de Sousse', 'Boulevard Yahia Ibn Omar, Sousse 4000', 35.8256, 10.6411),
('Bibliothèque de Bizerte', 'Avenue Habib Bourguiba, Bizerte 7000', 37.2744, 9.8672),
('Bibliothèque de Kairouan', 'Avenue de la République, Kairouan 3100', 35.6781, 10.0963),
('Bibliothèque de Monastir', 'Avenue Habib Bourguiba, Monastir 5000', 35.7781, 10.8264)
ON DUPLICATE KEY UPDATE 
    latitude = VALUES(latitude),
    longitude = VALUES(longitude),
    address = VALUES(address);
