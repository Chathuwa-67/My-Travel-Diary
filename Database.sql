CREATE DATABASE travel_map;

USE travel_map;

CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    place_name VARCHAR(255) NOT NULL,
    travel_date DATE NOT NULL,
    latitude FLOAT NOT NULL,
    longitude FLOAT NOT NULL,
    image_path VARCHAR(255) NOT NULL
);
