CREATE DATABASE IF NOT EXISTS unibite
  CHARACTER SET utf8
  COLLATE utf8_unicode_ci;

USE unibite;

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    firstName  VARCHAR(100) NOT NULL,
    lastName   VARCHAR(100) NOT NULL,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('cook', 'consumer', 'admin') NOT NULL DEFAULT 'consumer',
    points     INT NOT NULL DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS meals (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    cook_id             INT NOT NULL,
    title               VARCHAR(255) NOT NULL,
    description         TEXT,
    photo               VARCHAR(255) DEFAULT NULL,
    portions_total      INT NOT NULL,
    portions_available  INT NOT NULL,
    pickup_location     VARCHAR(255) NOT NULL,
    pickup_time         VARCHAR(100) NOT NULL,
    allergens           VARCHAR(500) DEFAULT '',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cook_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS requests (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    meal_id     INT NOT NULL,
    consumer_id INT NOT NULL,
    status      ENUM('pending','approved','rejected','collected','not_collected')
                NOT NULL DEFAULT 'pending',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meal_id)     REFERENCES meals(id)  ON DELETE CASCADE,
    FOREIGN KEY (consumer_id) REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS ratings (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL UNIQUE,
    rating     TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO users (firstName, lastName, email, password, role, points)
VALUES ('Admin', 'UniBite', 'admin@unibite.gr', MD5('admin123'), 'admin', 0);