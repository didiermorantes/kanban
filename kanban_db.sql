-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-01-2026 a las 23:05:46
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
-- Base de datos: `kanban_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `columns`
--

CREATE TABLE `columns` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` int(11) NOT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `columns`
--

INSERT INTO `columns` (`id`, `project_id`, `name`, `position`, `is_done`) VALUES
(1, 1, 'Por hacer', 1, 0),
(2, 1, 'En progreso', 2, 0),
(3, 1, 'Hecho', 3, 1),
(4, 2, 'Por hacer', 1, 0),
(5, 2, 'En progreso', 2, 0),
(6, 2, 'Hecho', 3, 1),
(7, 3, 'Por hacer', 1, 0),
(8, 3, 'En progreso', 2, 0),
(9, 3, 'Hecho', 3, 1),
(10, 4, 'Por hacer', 1, 0),
(11, 4, 'En progreso', 2, 0),
(12, 4, 'Hecho', 3, 1),
(22, 8, 'Por hacer', 1, 0),
(23, 8, 'En progreso', 2, 0),
(24, 8, 'Hecho', 3, 1),
(25, 9, 'Por hacer', 1, 0),
(26, 9, 'En progreso', 2, 0),
(27, 9, 'Hecho', 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `responsible` varchar(120) DEFAULT NULL,
  `responsible_user_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `projects`
--

INSERT INTO `projects` (`id`, `created_by`, `name`, `responsible`, `responsible_user_id`, `description`, `created_at`) VALUES
(1, NULL, 'Proyecto Demo', NULL, NULL, 'Este es un proyecto de demostración para el tablero Kanban', '2025-12-11 11:07:25'),
(2, NULL, 'Proyecto Didier', NULL, 2, 'Creación de Tablero Kanban', '2025-12-11 11:58:55'),
(3, NULL, 'Chatbot', NULL, NULL, 'descripción', '2025-12-11 12:15:02'),
(4, NULL, 'Proyecto Universidad Nacional Gestión del Riesgo', NULL, NULL, 'Proyecto Universidad Nacional Gestión del Riesgo', '2025-12-11 15:09:55'),
(8, 1, 'PROYECTIN', NULL, NULL, 'a', '2025-12-23 15:51:19'),
(9, 1, 'Prueba Auditoria D', 'Cristian Ochoa', 1, 'Prueba Auditoria', '2025-12-23 15:54:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_members`
--

CREATE TABLE `project_members` (
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('owner','admin','member','viewer') NOT NULL DEFAULT 'member',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `project_members`
--

INSERT INTO `project_members` (`project_id`, `user_id`, `role`, `created_at`) VALUES
(1, 1, 'owner', '2025-12-23 15:48:34'),
(2, 1, 'owner', '2025-12-23 15:48:34'),
(2, 2, 'member', '2026-01-13 16:28:49'),
(3, 1, 'owner', '2025-12-23 15:48:34'),
(4, 1, 'owner', '2025-12-23 15:48:34'),
(8, 1, 'owner', '2025-12-23 15:51:19'),
(9, 1, 'owner', '2025-12-23 15:54:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `column_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `responsible` varchar(120) DEFAULT NULL,
  `responsible_user_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `order` int(11) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `due_date` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `column_id`, `title`, `responsible`, `responsible_user_id`, `description`, `order`, `created_at`, `due_date`, `completed_at`, `created_by`, `updated_by`) VALUES
(1, 1, 1, 'Definir requerimientos', NULL, NULL, 'Reunión con el equipo para entender el alcance', 1, '2025-12-11 11:07:25', NULL, NULL, NULL, NULL),
(2, 1, 2, 'Diseñar base de datos', NULL, NULL, 'Modelo relacional y diagrama ER', 1, '2025-12-11 11:07:25', NULL, NULL, NULL, NULL),
(3, 1, 3, 'Configurar entorno de desarrollo', NULL, NULL, 'Instalar PHP, servidor web y BD', 1, '2025-12-11 11:07:25', NULL, NULL, NULL, NULL),
(4, 2, 6, 'Crear base de datos', NULL, NULL, 'Usar motor mysql', 8, '2025-12-11 12:10:31', NULL, '2025-12-22 14:39:49', NULL, NULL),
(5, 2, 6, 'Crear patrón MVC', 'Didier Morantes', NULL, 'Utilizar patrón MVC para mantenibilidad', 9, '2025-12-11 12:10:55', NULL, '2025-12-22 13:48:08', NULL, NULL),
(6, 2, 6, 'Crear Front End', 'Didier Morantes', NULL, 'Utilizar public/index.php como punto de entrada', 2, '2025-12-11 12:11:17', NULL, '2025-12-22 14:40:02', NULL, NULL),
(7, 2, 4, 'Crear Back End', NULL, NULL, 'Crear controladores para gestionar la información procesada', 1, '2025-12-11 12:11:38', NULL, NULL, NULL, NULL),
(8, 3, 7, 'recolección de requerimientos', NULL, NULL, 'es una pequeña recolección de requerimientos', 1, '2025-12-11 12:15:16', NULL, NULL, NULL, NULL),
(9, 3, 8, 'creación de diagramas', NULL, NULL, 'UML', 1, '2025-12-11 12:15:31', NULL, NULL, NULL, NULL),
(10, 1, 3, 'Definir estilos', NULL, NULL, 'Definir estilos CSS', 2, '2025-12-11 14:37:18', NULL, '2025-12-22 14:41:05', NULL, NULL),
(11, 4, 11, 'Recolección de requerimientos iniciales', 'Didier Morantes', NULL, 'Recolección de requerimientos universidad nacional', 1, '2025-12-11 15:11:24', NULL, NULL, NULL, NULL),
(12, 4, 10, 'Diseño de Diagramas UML', 'C                                          Cristian Ochoa', NULL, 'Diagramas UML', 1, '2025-12-11 15:12:24', NULL, NULL, NULL, NULL),
(13, 3, 9, 'Creación de Modelo entidad-relación', NULL, NULL, 'Creación de modelo lógico', 1, '2025-12-15 14:17:00', NULL, '2025-12-15 21:47:47', NULL, NULL),
(14, 3, 9, 'Pruebas Unitarias', NULL, NULL, 'Pruebas con JEST', 2, '2025-12-15 16:02:27', NULL, '2025-12-22 14:41:10', NULL, NULL),
(15, 4, 12, 'Desarrollo Front End', 'Didier Morantes', NULL, 'Desarrollo front end', 2, '2025-12-15 16:31:07', NULL, '2025-12-22 14:40:48', NULL, NULL),
(16, 4, 12, 'Prueba', 'Didier Morantes', NULL, 'Detalle fdff', 1, '2025-12-16 11:26:53', NULL, '2025-12-18 14:30:20', NULL, NULL),
(17, 2, 6, 'Tarea para metricas', 'Didier Morantes', NULL, 'Tarea de prueba para metricas', 3, '2025-12-17 11:22:59', NULL, '2025-12-22 14:39:55', NULL, NULL),
(18, 2, 5, 'Tarea 1', 'Didier Morantes', NULL, 'Prueba Tarea 1', 1, '2025-12-18 10:57:24', NULL, NULL, NULL, NULL),
(19, 2, 6, 'Prueba 2', 'Didier Morantes', NULL, 'Detalle Tarea 2', 6, '2025-12-18 10:57:38', NULL, '2025-12-22 14:39:50', NULL, NULL),
(20, 2, 6, 'Tarea 3', 'Daniel Silva', NULL, 'Descripcion Tarea 3', 5, '2025-12-18 10:57:53', NULL, '2025-12-22 14:39:55', NULL, NULL),
(21, 2, 6, 'Tarea 5', 'Didier Morantes', NULL, 'Nueva tarea', 4, '2025-12-18 13:39:02', NULL, '2025-12-22 13:47:25', NULL, NULL),
(22, 2, 6, 'Tarea 4', 'Didier Morantes', NULL, 'Tarea 4', 1, '2025-12-18 13:48:19', NULL, '2025-12-22 13:47:28', NULL, NULL),
(23, 2, 6, 'Tarea 6', 'Didier Morantes', NULL, 'Tarea 6', 7, '2025-12-18 14:17:14', NULL, '2025-12-22 13:47:21', NULL, NULL),
(30, 9, 27, 'Comenzar', 'D\n                                          Didier Morantes', NULL, 'Elfar', 1, '2025-12-23 15:54:42', NULL, '2026-01-13 14:55:02', 1, NULL),
(31, 9, 27, 'progresando', 'Cristian Ochoa', NULL, 'Cri', 2, '2025-12-23 15:57:13', NULL, '2025-12-23 15:57:26', 1, NULL),
(32, 8, 24, 'COMENZAR', 'AD', NULL, 'DES', 1, '2026-01-13 16:14:12', NULL, '2026-01-13 16:14:30', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `task_movements`
--

CREATE TABLE `task_movements` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `from_column_id` int(11) DEFAULT NULL,
  `to_column_id` int(11) NOT NULL,
  `moved_at` datetime NOT NULL DEFAULT current_timestamp(),
  `moved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `task_movements`
--

INSERT INTO `task_movements` (`id`, `task_id`, `project_id`, `from_column_id`, `to_column_id`, `moved_at`, `moved_by`) VALUES
(1, 17, 2, NULL, 4, '2025-12-17 11:22:59', NULL),
(2, 17, 2, 4, 5, '2025-12-17 11:23:07', NULL),
(3, 17, 2, 5, 6, '2025-12-17 11:23:11', NULL),
(4, 6, 2, 5, 6, '2025-12-18 10:28:45', NULL),
(5, 7, 2, 4, 5, '2025-12-18 10:31:40', NULL),
(6, 7, 2, 5, 4, '2025-12-18 10:38:39', NULL),
(7, 5, 2, 6, 5, '2025-12-18 10:38:51', NULL),
(8, 17, 2, 6, 5, '2025-12-18 10:48:40', NULL),
(9, 17, 2, 5, 6, '2025-12-18 10:48:43', NULL),
(10, 18, 2, NULL, 4, '2025-12-18 10:57:24', NULL),
(11, 19, 2, NULL, 4, '2025-12-18 10:57:38', NULL),
(12, 20, 2, NULL, 4, '2025-12-18 10:57:53', NULL),
(13, 18, 2, 4, 5, '2025-12-18 11:10:28', NULL),
(14, 18, 2, 5, 6, '2025-12-18 11:11:14', NULL),
(15, 4, 2, 5, 6, '2025-12-18 11:12:58', NULL),
(16, 4, 2, 6, 4, '2025-12-18 11:34:01', NULL),
(17, 20, 2, 4, 5, '2025-12-18 12:08:05', NULL),
(18, 20, 2, 5, 6, '2025-12-18 13:38:09', NULL),
(19, 21, 2, NULL, 4, '2025-12-18 13:39:02', NULL),
(20, 22, 2, NULL, 4, '2025-12-18 13:48:19', NULL),
(21, 22, 2, 4, 5, '2025-12-18 13:48:29', NULL),
(22, 21, 2, 4, 5, '2025-12-18 13:48:54', NULL),
(23, 22, 2, 5, 6, '2025-12-18 13:50:09', NULL),
(24, 23, 2, NULL, 4, '2025-12-18 14:17:14', NULL),
(25, 23, 2, 4, 5, '2025-12-18 14:25:04', NULL),
(26, 23, 2, 5, 6, '2025-12-18 14:26:09', NULL),
(27, 23, 2, 6, 6, '2025-12-18 14:26:13', NULL),
(28, 21, 2, 5, 6, '2025-12-18 14:28:33', NULL),
(29, 12, 4, 10, 11, '2025-12-18 14:30:15', NULL),
(30, 16, 4, 11, 12, '2025-12-18 14:30:20', NULL),
(31, 5, 2, 5, 6, '2025-12-18 14:40:07', NULL),
(32, 12, 4, 11, 10, '2025-12-18 15:05:29', NULL),
(33, 12, 4, 10, 11, '2025-12-18 15:06:36', NULL),
(34, 12, 4, 11, 10, '2025-12-18 15:06:42', NULL),
(35, 12, 4, 10, 11, '2025-12-18 15:08:12', NULL),
(36, 12, 4, 11, 10, '2025-12-18 15:23:54', NULL),
(37, 12, 4, 10, 11, '2025-12-18 15:24:00', NULL),
(38, 12, 4, 11, 10, '2025-12-18 15:32:17', NULL),
(39, 12, 4, 10, 11, '2025-12-18 15:32:19', NULL),
(40, 12, 4, 11, 12, '2025-12-18 15:32:29', NULL),
(41, 12, 4, 12, 11, '2025-12-18 15:32:38', NULL),
(42, 12, 4, 11, 10, '2025-12-18 15:32:39', NULL),
(43, 12, 4, 10, 11, '2025-12-18 15:32:43', NULL),
(44, 12, 4, 11, 10, '2025-12-18 15:35:53', NULL),
(45, 12, 4, 10, 11, '2025-12-18 15:35:57', NULL),
(46, 12, 4, 11, 12, '2025-12-18 15:35:59', NULL),
(47, 12, 4, 12, 10, '2025-12-18 15:36:01', NULL),
(48, 12, 4, 10, 11, '2025-12-18 15:38:10', NULL),
(49, 12, 4, 11, 11, '2025-12-18 15:38:50', NULL),
(50, 12, 4, 11, 10, '2025-12-18 15:44:29', NULL),
(51, 12, 4, 10, 11, '2025-12-18 15:44:30', NULL),
(52, 7, 2, 4, 5, '2025-12-22 10:32:27', NULL),
(53, 19, 2, 4, 5, '2025-12-22 10:40:51', NULL),
(54, 19, 2, 5, 4, '2025-12-22 10:40:54', NULL),
(55, 19, 2, 4, 5, '2025-12-22 10:40:55', NULL),
(56, 4, 2, 4, 5, '2025-12-22 10:41:10', NULL),
(57, 4, 2, 5, 5, '2025-12-22 10:41:12', NULL),
(58, 4, 2, 5, 6, '2025-12-22 10:41:13', NULL),
(59, 4, 2, 6, 5, '2025-12-22 10:41:17', NULL),
(60, 4, 2, 5, 4, '2025-12-22 10:41:45', NULL),
(61, 4, 2, 4, 5, '2025-12-22 10:41:47', NULL),
(62, 4, 2, 5, 4, '2025-12-22 10:41:49', NULL),
(63, 4, 2, 4, 5, '2025-12-22 10:41:51', NULL),
(64, 4, 2, 5, 4, '2025-12-22 10:43:10', NULL),
(65, 4, 2, 4, 5, '2025-12-22 10:43:12', NULL),
(66, 4, 2, 5, 4, '2025-12-22 10:43:56', NULL),
(67, 4, 2, 4, 5, '2025-12-22 10:43:59', NULL),
(68, 4, 2, 5, 4, '2025-12-22 10:44:13', NULL),
(69, 4, 2, 4, 5, '2025-12-22 10:44:15', NULL),
(70, 4, 2, 5, 6, '2025-12-22 10:44:16', NULL),
(71, 19, 2, 5, 4, '2025-12-22 10:44:41', NULL),
(72, 19, 2, 4, 5, '2025-12-22 10:44:42', NULL),
(73, 4, 2, 6, 5, '2025-12-22 10:44:44', NULL),
(74, 4, 2, 5, 6, '2025-12-22 10:44:45', NULL),
(75, 4, 2, 6, 6, '2025-12-22 10:44:49', NULL),
(76, 4, 2, 6, 5, '2025-12-22 10:44:50', NULL),
(77, 4, 2, 5, 4, '2025-12-22 10:44:52', NULL),
(78, 4, 2, 4, 4, '2025-12-22 10:44:55', NULL),
(79, 4, 2, 4, 4, '2025-12-22 10:44:57', NULL),
(80, 4, 2, 4, 5, '2025-12-22 10:44:58', NULL),
(81, 4, 2, 5, 4, '2025-12-22 10:45:00', NULL),
(82, 4, 2, 4, 5, '2025-12-22 10:45:32', NULL),
(83, 4, 2, 5, 4, '2025-12-22 10:45:42', NULL),
(84, 6, 2, 6, 5, '2025-12-22 10:45:45', NULL),
(85, 6, 2, 5, 6, '2025-12-22 10:45:46', NULL),
(86, 19, 2, 5, 4, '2025-12-22 10:46:18', NULL),
(87, 4, 2, 4, 4, '2025-12-22 10:50:16', NULL),
(88, 5, 2, 6, 5, '2025-12-22 10:50:20', NULL),
(89, 5, 2, 5, 6, '2025-12-22 10:50:21', NULL),
(90, 4, 2, 4, 5, '2025-12-22 10:56:49', NULL),
(91, 4, 2, 5, 4, '2025-12-22 10:56:54', NULL),
(92, 4, 2, 4, 6, '2025-12-22 10:56:56', NULL),
(93, 4, 2, 6, 5, '2025-12-22 10:56:59', NULL),
(94, 19, 2, 4, 5, '2025-12-22 10:59:16', NULL),
(95, 19, 2, 5, 4, '2025-12-22 10:59:17', NULL),
(96, 19, 2, 4, 5, '2025-12-22 10:59:21', NULL),
(97, 19, 2, 5, 4, '2025-12-22 10:59:22', NULL),
(98, 19, 2, 4, 5, '2025-12-22 10:59:44', NULL),
(99, 19, 2, 5, 4, '2025-12-22 10:59:46', NULL),
(100, 19, 2, 4, 5, '2025-12-22 10:59:47', NULL),
(101, 19, 2, 5, 4, '2025-12-22 10:59:49', NULL),
(102, 19, 2, 4, 5, '2025-12-22 11:19:19', NULL),
(103, 19, 2, 5, 4, '2025-12-22 11:19:20', NULL),
(104, 19, 2, 4, 5, '2025-12-22 11:19:24', NULL),
(105, 19, 2, 5, 6, '2025-12-22 11:19:25', NULL),
(106, 19, 2, 6, 5, '2025-12-22 11:19:29', NULL),
(107, 19, 2, 5, 4, '2025-12-22 11:19:31', NULL),
(108, 19, 2, 4, 5, '2025-12-22 11:19:37', NULL),
(109, 19, 2, 5, 4, '2025-12-22 11:30:28', NULL),
(110, 19, 2, 4, 5, '2025-12-22 11:30:32', NULL),
(111, 19, 2, 5, 6, '2025-12-22 11:30:34', NULL),
(112, 4, 2, 5, 4, '2025-12-22 11:44:37', NULL),
(113, 4, 2, 4, 5, '2025-12-22 11:46:51', NULL),
(114, 4, 2, 5, 4, '2025-12-22 11:46:56', NULL),
(115, 7, 2, 5, 4, '2025-12-22 11:49:05', NULL),
(116, 7, 2, 4, 6, '2025-12-22 11:49:16', NULL),
(117, 7, 2, 6, 5, '2025-12-22 11:49:20', NULL),
(118, 7, 2, 5, 4, '2025-12-22 11:53:14', NULL),
(119, 7, 2, 4, 5, '2025-12-22 11:53:20', NULL),
(120, 19, 2, 6, 5, '2025-12-22 11:53:22', NULL),
(121, 19, 2, 5, 4, '2025-12-22 11:53:25', NULL),
(122, 19, 2, 4, 5, '2025-12-22 11:53:31', NULL),
(123, 7, 2, 5, 4, '2025-12-22 11:57:49', NULL),
(124, 6, 2, 6, 5, '2025-12-22 11:57:51', NULL),
(125, 5, 2, 6, 5, '2025-12-22 13:39:19', NULL),
(126, 5, 2, 5, 6, '2025-12-22 13:39:23', NULL),
(127, 6, 2, 5, 6, '2025-12-22 13:39:30', NULL),
(128, 19, 2, 5, 6, '2025-12-22 13:39:31', NULL),
(129, 7, 2, 4, 6, '2025-12-22 13:39:32', NULL),
(130, 4, 2, 4, 6, '2025-12-22 13:39:34', NULL),
(131, 4, 2, 6, 5, '2025-12-22 13:40:03', NULL),
(132, 7, 2, 6, 5, '2025-12-22 13:40:07', NULL),
(133, 19, 2, 6, 5, '2025-12-22 13:40:08', NULL),
(134, 6, 2, 6, 5, '2025-12-22 13:40:10', NULL),
(135, 5, 2, 6, 5, '2025-12-22 13:40:12', NULL),
(136, 17, 2, 6, 5, '2025-12-22 13:40:13', NULL),
(137, 18, 2, 6, 5, '2025-12-22 13:40:14', NULL),
(138, 20, 2, 6, 5, '2025-12-22 13:40:16', NULL),
(139, 22, 2, 6, 5, '2025-12-22 13:40:17', NULL),
(140, 21, 2, 6, 5, '2025-12-22 13:40:18', NULL),
(141, 23, 2, 6, 5, '2025-12-22 13:40:19', NULL),
(142, 23, 2, 5, 6, '2025-12-22 13:47:21', NULL),
(143, 21, 2, 5, 6, '2025-12-22 13:47:25', NULL),
(144, 22, 2, 5, 6, '2025-12-22 13:47:28', NULL),
(145, 17, 2, 5, 5, '2025-12-22 13:47:41', NULL),
(146, 18, 2, 5, 5, '2025-12-22 13:47:48', NULL),
(147, 7, 2, 5, 4, '2025-12-22 13:48:00', NULL),
(148, 5, 2, 5, 6, '2025-12-22 13:48:08', NULL),
(149, 17, 2, 5, 4, '2025-12-22 13:48:17', NULL),
(150, 6, 2, 5, 4, '2025-12-22 13:48:23', NULL),
(151, 12, 4, 11, 10, '2025-12-22 14:08:45', NULL),
(152, 11, 4, 12, 11, '2025-12-22 14:08:47', NULL),
(154, 11, 4, 11, 12, '2025-12-22 14:39:23', NULL),
(155, 11, 4, 12, 10, '2025-12-22 14:39:34', NULL),
(156, 15, 4, 12, 10, '2025-12-22 14:39:36', NULL),
(157, 4, 2, 5, 6, '2025-12-22 14:39:49', NULL),
(158, 19, 2, 5, 6, '2025-12-22 14:39:50', NULL),
(159, 6, 2, 4, 5, '2025-12-22 14:39:53', NULL),
(160, 17, 2, 4, 5, '2025-12-22 14:39:54', NULL),
(161, 20, 2, 5, 6, '2025-12-22 14:39:55', NULL),
(162, 17, 2, 5, 6, '2025-12-22 14:39:55', NULL),
(163, 6, 2, 5, 6, '2025-12-22 14:40:02', NULL),
(164, 11, 4, 10, 11, '2025-12-22 14:40:47', NULL),
(165, 15, 4, 10, 12, '2025-12-22 14:40:48', NULL),
(166, 11, 4, 11, 12, '2025-12-22 14:40:54', NULL),
(167, 10, 1, 2, 3, '2025-12-22 14:41:05', NULL),
(168, 14, 3, 8, 9, '2025-12-22 14:41:10', NULL),
(172, 12, 4, 10, 11, '2025-12-22 14:42:26', NULL),
(185, 12, 4, 11, 10, '2025-12-22 15:36:49', NULL),
(186, 12, 4, 10, 12, '2025-12-22 15:37:24', NULL),
(187, 12, 4, 12, 11, '2025-12-22 15:37:41', NULL),
(188, 12, 4, 11, 10, '2025-12-22 15:37:43', NULL),
(189, 11, 4, 12, 11, '2025-12-22 15:37:44', NULL),
(190, 30, 9, NULL, 25, '2025-12-23 15:54:42', 1),
(191, 31, 9, NULL, 26, '2025-12-23 15:57:13', 1),
(192, 31, 9, 26, 27, '2025-12-23 15:57:26', NULL),
(193, 30, 9, 25, 27, '2025-12-23 16:39:03', NULL),
(194, 30, 9, 27, 26, '2026-01-13 14:52:57', NULL),
(195, 30, 9, 26, 25, '2026-01-13 14:52:59', NULL),
(196, 30, 9, 25, 27, '2026-01-13 14:53:14', NULL),
(197, 30, 9, 27, 26, '2026-01-13 14:54:56', NULL),
(198, 30, 9, 26, 27, '2026-01-13 14:55:02', NULL),
(199, 32, 8, NULL, 22, '2026-01-13 16:14:12', 1),
(200, 32, 8, 22, 23, '2026-01-13 16:14:24', NULL),
(201, 32, 8, 23, 24, '2026-01-13 16:14:31', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `created_at`) VALUES
(1, 'Elfar Morantes', 'elfar.morantes@cundinamarca.gov.co', '$2y$10$ln5hjmX7WCv7imXLh/TLt.5mFPEac8yEezEPgEfHK.XLzVbUrHZIS', '2025-12-23 15:20:48'),
(2, 'prueba', 'profesordidiermorantes@gmail.com', '$2y$10$e73zC7SGWEuM4R3hEQeYqe4wcc7mZPGg1VXfCVYuFpHKz69xhbubm', '2026-01-13 16:27:46');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `columns`
--
ALTER TABLE `columns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indices de la tabla `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_projects_created_by` (`created_by`),
  ADD KEY `fk_projects_responsible_user` (`responsible_user_id`);

--
-- Indices de la tabla `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`project_id`,`user_id`),
  ADD KEY `fk_pm_user` (`user_id`);

--
-- Indices de la tabla `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `column_id` (`column_id`),
  ADD KEY `idx_tasks_responsible` (`responsible`),
  ADD KEY `fk_tasks_created_by` (`created_by`),
  ADD KEY `fk_tasks_updated_by` (`updated_by`),
  ADD KEY `fk_tasks_responsible_user` (`responsible_user_id`);

--
-- Indices de la tabla `task_movements`
--
ALTER TABLE `task_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tm_project` (`project_id`,`moved_at`),
  ADD KEY `idx_tm_task` (`task_id`,`moved_at`),
  ADD KEY `fk_moves_moved_by` (`moved_by`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `columns`
--
ALTER TABLE `columns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `task_movements`
--
ALTER TABLE `task_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80824835;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `columns`
--
ALTER TABLE `columns`
  ADD CONSTRAINT `columns_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_projects_responsible_user` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `fk_pm_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tasks_responsible_user` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tasks_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`column_id`) REFERENCES `columns` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `task_movements`
--
ALTER TABLE `task_movements`
  ADD CONSTRAINT `fk_moves_moved_by` FOREIGN KEY (`moved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tm_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
