-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-05-2026 a las 04:29:42
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `titulacion`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id`, `usuario`, `password`, `nombre`) VALUES
(8, 'jose_angel', '$2y$10$nz.Dy3QjORD1Dw77XDTKheVRMq0DNR3PpT9Qd0YVo19vqd.tqlCP6', 'Angel Lira'),
(9, 'miguel_alv', '$2y$10$vsnFemB/YjQO.RTsS99i7eu0WnQh7HJTZo14kqQ5SaaxPrQ5Ft4za', 'Miguel Alvarez');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumnos`
--

CREATE TABLE `alumnos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `no_control` varchar(20) NOT NULL,
  `carrera` varchar(100) NOT NULL,
  `opcion_titulacion` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `celular` varchar(15) NOT NULL,
  `fecha_egreso` date NOT NULL,
  `graduacion` enum('Graduación 1','Graduación 2') NOT NULL,
  `anio_egreso` year(4) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `mencion_honorifica` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `alumnos`
--

INSERT INTO `alumnos` (`id`, `nombre`, `no_control`, `carrera`, `opcion_titulacion`, `email`, `celular`, `fecha_egreso`, `graduacion`, `anio_egreso`, `password`, `fecha_registro`, `mencion_honorifica`) VALUES
(1, 'Jose Angel Lira Hernandez', '23131038', 'ING. SISTEMAS COMPUTACIONALES', 'Informe de Residencia Profesional', 'alu.23131038@correo.itlalaguna.edu.mx', '8714486402', '2026-04-01', 'Graduación 1', '2026', '$2y$10$W86DSVWZmy1e98balx24kuaqyrt2MhDSKWoeoYPYthbXhPdOt5Jim', '2026-04-13 14:55:49', 0),
(2, 'Paola Yamillete Mesta Marin', '23131167', 'ING. INDUSTRIAL', 'Informe de Residencia Profesional', 'alu.23131167@correo.itlalaguna.edu.mx', '8714486402', '2026-04-01', 'Graduación 1', '2026', '$2y$10$yAW0VOX3LdB8YXjfTcSSGeyF7lVrim42XqoJVfGiSscuLhNu.psFG', '2026-04-14 11:19:32', 0),
(3, 'Migel Alvares Mallen', '23130550', 'ING. SISTEMAS COMPUTACIONALES', 'Proyecto Productivo', 'alu.23130550@correo.itlalaguna.edu.mx', '8714486402', '2026-04-01', 'Graduación 1', '2026', '$2y$10$aEc82tZTafOh4g3N2zAdnOSPGe2WlhI4Dx.5eALIflmsav5AT.vfm', '2026-04-14 15:22:18', 0),
(4, 'juan Carlos Ulloa', '23130541', 'ING. SISTEMAS COMPUTACIONALES', 'Tesis o Tesina', 'angel@gmail.com', '1234567890', '2026-12-11', 'Graduación 1', '2026', '$2y$10$l2s1IKtuz0xUB43WA3H3oOu7DxH0p8QxefJz2Rqn4kHnR.s5IWLA.', '2026-04-25 20:14:00', 0),
(5, 'paco', '23131040', 'ING. ELECTRÓNICA', 'Proyecto Integrador', 'paco@gmail.com', '1234567890', '2027-04-13', 'Graduación 2', '2027', '$2y$10$KDXyCV6VNI5ByRAHbS4nLu7Fa7Eej.V4Uf8YCpb2uG0qF6w1eLl7e', '2026-04-25 20:17:03', 0),
(6, 'carlos', '23130530', 'ING. ELECTRÓNICA', 'Proyecto Productivo', 'carlos@gmail.com', '1234567890', '2026-06-13', 'Graduación 2', '2026', '$2y$10$d6gQ3fn31VUnGHZz75YV6.P1TwUgLlxEzIMZ/K42Cah8VhEHfK1Fq', '2026-04-25 20:18:44', 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_control` (`no_control`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_no_control` (`no_control`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
