-- Active: 1738214133896@@127.0.0.1@3306@anime_db
-- Database: anime_db
CREATE DATABASE ANIME_DB;
USE ANIME_DB;
-- Table: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: anime
CREATE TABLE anime (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    genre VARCHAR(100),
    studio VARCHAR(50),
    release_date DATE,
    episodes INT,
    cover_image VARCHAR(255),
    video_url VARCHAR(255),
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: episodes
CREATE TABLE episodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anime_id INT NOT NULL,
    episode_number INT NOT NULL,
    title VARCHAR(100),
    video_url VARCHAR(255) NOT NULL,
    duration INT,
    FOREIGN KEY (anime_id) REFERENCES anime(id) ON DELETE CASCADE
);

-- Table: user_watchlist
CREATE TABLE user_watchlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    anime_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (anime_id) REFERENCES anime(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, anime_id)
);

-- Insert sample admin user
INSERT INTO users (username, email, password, role) 
VALUES ('admin', 'admin@anime.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password: password




CREATE TABLE anime (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    cover_image VARCHAR(255),
    genre VARCHAR(255),
    release_date DATE,
    views INT DEFAULT 0
);

INSERT INTO anime (title, cover_image, genre, release_date, views)
VALUES 
('Naruto', 'naruto.jpg', 'Action, Adventure', '2002-10-03', 1000),
('One Piece', 'one_piece.jpg', 'Action, Comedy', '1999-10-20', 1500),
('Attack on Titan', 'aot.jpg', 'Action, Drama', '2013-04-07', 2000);