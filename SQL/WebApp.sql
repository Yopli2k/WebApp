/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

 /**
 * Script for database creation.
 *
 * @author José Antonio Cuello Principal <yopli2000@gmail.com>
 * @version 1.0
 */

/* create database */
CREATE DATABASE webapp
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_spanish_ci
    DEFAULT ENCRYPTION='N';

/**
 * Create users table.
 * The users are the people who can access to the application
 * for manage the users, members, books and other data.
 */
CREATE TABLE users (
    email varchar(100) NOT NULL,
    enabled bool NOT NULL DEFAULT true,
    logkey varchar(100),
    name varchar(100) NOT NULL,
    username varchar(50) NOT NULL,
    password varchar(255) NOT NULL,
    CONSTRAINT users_pk PRIMARY KEY (username)
);

CREATE UNIQUE INDEX users_idx_email ON users (email);

INSERT INTO users (email, name, username, password) VALUES ('admin@webapp.com', 'Administrador', 'admin', '$2y$10$/dO7uCXqeqVe32O1GRDFT.f6tN3VCSaKvF9oFwLfOsxzn9NF7HDJy');


/**
 * Create members table.
 */
CREATE TABLE members (
     address varchar(100),
     creationdate date NOT NULL,
     document varchar(30) NOT NULL,
     email varchar(100) NOT NULL,
     enabled bool NOT NULL DEFAULT true,
     id int NOT NULL AUTO_INCREMENT,
     logkey varchar(100),
     name varchar(100) NOT NULL,
     notes text,
     password varchar(255),
     phone varchar(10) NOT NULL,
     verified bool DEFAULT false,
     CONSTRAINT members_pk PRIMARY KEY (id)
);

CREATE UNIQUE INDEX members_idx_email ON members (email);
CREATE INDEX members_idx_name ON members (name);