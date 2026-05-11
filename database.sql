DROP DATABASE IF EXISTS cerberus;

CREATE DATABASE cerberus;
USE cerberus;

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

CREATE TABLE oauth_clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id VARCHAR(100) UNIQUE NOT NULL,
    client_secret VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE oauth_client_redirects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_internal_id INT NOT NULL,
    redirect_uri VARCHAR(255) NOT NULL,
    FOREIGN KEY (client_internal_id) REFERENCES oauth_clients(id) ON DELETE CASCADE
);

CREATE TABLE oauth_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(128) UNIQUE NOT NULL,
    client_internal_id INT NOT NULL,
    user_id INT NOT NULL,
    expires_at DATETIME NOT NULL,
    scope VARCHAR(255),
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_internal_id) REFERENCES oauth_clients(id) ON DELETE CASCADE
);

CREATE TABLE oauth_scopes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scope_name VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255),
    is_default TINYINT(1) DEFAULT 0
);