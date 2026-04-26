-- ============================================
-- BASE DE DATOS: Sistema ITL Titulación
-- Instituto Tecnológico de La Laguna
-- ============================================

CREATE DATABASE IF NOT EXISTS itl_titulacion
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_spanish_ci;

USE itl_titulacion;

-- ============================================
-- TABLA: alumnos (registro e inicio de sesión)
-- ============================================
CREATE TABLE IF NOT EXISTS alumnos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    no_control      VARCHAR(20)  NOT NULL UNIQUE,
    carrera         VARCHAR(100) NOT NULL,
    opcion_titulacion VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    celular         VARCHAR(15)  NOT NULL,
    password        VARCHAR(255) NOT NULL,
    fecha_registro  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================
-- ÍNDICES útiles para búsquedas
-- ============================================
CREATE INDEX idx_email      ON alumnos(email);
CREATE INDEX idx_no_control ON alumnos(no_control);
