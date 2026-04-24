DROP DATABASE IF EXISTS template;

CREATE DATABASE template;
USE template;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username varchar(50) NOT NULL UNIQUE,
    email varchar(150) NOT NULL UNIQUE,
    password varchar(250) NOT NULL,
    remember_token varchar(255) DEFAULT NULL,
    reset_token varchar(255) DEFAULT NULL,
    reset_expires_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name varchar(100) NOT NULL UNIQUE
);

CREATE TABLE permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name varchar(100) NOT NULL UNIQUE
);

CREATE TABLE user_roles (
    user_id INT UNSIGNED,
    role_id INT UNSIGNED,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE role_permissions (
    role_id INT UNSIGNED,
    permissions_id INT UNSIGNED,
    PRIMARY KEY (role_id, permissions_id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (permissions_id) REFERENCES permissions(id)
);