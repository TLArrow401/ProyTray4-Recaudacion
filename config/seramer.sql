-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.5 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para tabla seramermvc.academic_degrees
CREATE TABLE IF NOT EXISTS `academic_degrees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de grados académicos (licenciatura, maestría, etc.)';

-- Volcando datos para la tabla seramermvc.academic_degrees: ~5 rows (aproximadamente)
DELETE FROM `academic_degrees`;
INSERT INTO `academic_degrees` (`id`, `name`) VALUES
	(1, 'Bachiller'),
	(2, 'Técnico Superior'),
	(3, 'Licenciatura'),
	(4, 'Maestría'),
	(5, 'Doctorado');

-- Volcando estructura para tabla seramermvc.academic_specializations
CREATE TABLE IF NOT EXISTS `academic_specializations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de especializaciones o menciones académicas';

-- Volcando datos para la tabla seramermvc.academic_specializations: ~5 rows (aproximadamente)
DELETE FROM `academic_specializations`;
INSERT INTO `academic_specializations` (`id`, `name`) VALUES
	(1, 'Administración'),
	(2, 'Contabilidad'),
	(3, 'Derecho'),
	(4, 'Informática'),
	(5, 'Recursos Humanos');

-- Volcando estructura para tabla seramermvc.attendance
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `is_special` tinyint(1) DEFAULT '0' COMMENT 'Para marcar asistencias especiales o excepcionales',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_staff_date` (`staff_id`,`date`),
  KEY `idx_staff_id` (`staff_id`),
  KEY `idx_date` (`date`),
  KEY `idx_staff_date` (`staff_id`,`date`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registra las asistencias del personal';

-- Volcando datos para la tabla seramermvc.attendance: ~0 rows (aproximadamente)
DELETE FROM `attendance`;

-- Volcando estructura para tabla seramermvc.audit_log
CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Tipo de acción realizada (login, insert, update, delete)',
  `table_affected` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `record_id` int DEFAULT NULL COMMENT 'ID del registro afectado',
  `old_values` json DEFAULT NULL COMMENT 'Valores anteriores en formato JSON',
  `new_values` json DEFAULT NULL COMMENT 'Valores nuevos en formato JSON',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_table_affected` (`table_affected`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_record_id` (`record_id`),
  CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registra todas las acciones importantes realizadas por los usuarios en el sistema';

-- Volcando datos para la tabla seramermvc.audit_log: ~0 rows (aproximadamente)
DELETE FROM `audit_log`;

-- Volcando estructura para tabla seramermvc.awardees
CREATE TABLE IF NOT EXISTS `awardees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Primer nombre',
  `middle_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Segundo nombre',
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Primer apellido',
  `second_last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Segundo apellido',
  `id_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Número de cédula o identificación',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Número de teléfono',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Correo electrónico',
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'Dirección completa',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_number` (`id_number`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Personas a quienes se adjudican espacios en el mercado';

-- Volcando datos para la tabla seramermvc.awardees: ~0 rows (aproximadamente)
DELETE FROM `awardees`;
INSERT INTO `awardees` (`id`, `first_name`, `middle_name`, `last_name`, `second_last_name`, `id_number`, `phone`, `email`, `address`) VALUES
	(1, 'Mariela', 'del Carmen', 'Romero', 'Listas', 'V24569874', '584148035352', 'mariela@example.com', 'test'),
	(2, 'Daniel', NULL, 'Fiiguera', NULL, 'V14852369', NULL, 'danielfiguera@example.com', 'test');

-- Volcando estructura para tabla seramermvc.cash_registers
CREATE TABLE IF NOT EXISTS `cash_registers` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único de la caja',
  `user_id` int NOT NULL COMMENT 'ID del usuario asignado a la caja',
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nombre descriptivo de la caja (ej: Caja 1 - Recepción)',
  `status` enum('active','inactive','maintenance') COLLATE utf8mb4_general_ci DEFAULT 'active' COMMENT 'Estado operativo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización',
  PRIMARY KEY (`id`),
  KEY `idx_cash_register_user` (`user_id`),
  KEY `idx_cash_register_status` (`status`),
  CONSTRAINT `cash_registers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de cajas de cobro asignadas a personal';

-- Volcando datos para la tabla seramermvc.cash_registers: ~1 rows (aproximadamente)
DELETE FROM `cash_registers`;
INSERT INTO `cash_registers` (`id`, `user_id`, `name`, `status`, `created_at`, `updated_at`) VALUES
	(1, 12, 'Caja 1 - Cobranza', 'active', '2025-08-04 16:54:44', '2025-08-04 16:57:29');

-- Volcando estructura para tabla seramermvc.contracts
CREATE TABLE IF NOT EXISTS `contracts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `awardee_id` int NOT NULL COMMENT 'Adjudicatario del contrato',
  `fiscal_year_id` int NOT NULL COMMENT 'Año fiscal asociado al contrato',
  `start_date` date NOT NULL COMMENT 'Fecha de inicio del contrato',
  `end_date` date NOT NULL COMMENT 'Fecha de finalización',
  `type` enum('simultaneous','advance') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Tipo de contrato',
  `contract_mode` enum('monthly','weekly') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Modalidad de pago',
  PRIMARY KEY (`id`),
  KEY `awardee_id` (`awardee_id`),
  KEY `fk_contracts_fiscal_year` (`fiscal_year_id`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`awardee_id`) REFERENCES `awardees` (`id`),
  CONSTRAINT `fk_contracts_fiscal_year` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_year` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Contratos de adjudicación de espacios en el mercado';

-- Volcando datos para la tabla seramermvc.contracts: ~0 rows (aproximadamente)
DELETE FROM `contracts`;
INSERT INTO `contracts` (`id`, `awardee_id`, `fiscal_year_id`, `start_date`, `end_date`, `type`, `contract_mode`) VALUES
	(1, 1, 1, '2025-08-04', '2026-04-30', 'simultaneous', 'monthly'),
	(2, 2, 1, '2025-08-04', '2026-04-30', 'simultaneous', 'monthly');

-- Volcando estructura para tabla seramermvc.contract_business_categories
CREATE TABLE IF NOT EXISTS `contract_business_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL COMMENT 'Contrato relacionado',
  `external_category_id` int DEFAULT NULL COMMENT 'Categoría externa (si aplica)',
  `internal_category_id` int DEFAULT NULL COMMENT 'Categoría interna (si aplica)',
  `type` enum('internal','external') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Tipo de categoría',
  PRIMARY KEY (`id`),
  KEY `contract_id` (`contract_id`),
  KEY `external_category_id` (`external_category_id`),
  KEY `internal_category_id` (`internal_category_id`),
  CONSTRAINT `contract_business_categories_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`),
  CONSTRAINT `contract_business_categories_ibfk_2` FOREIGN KEY (`external_category_id`) REFERENCES `external_business_categories` (`id`),
  CONSTRAINT `contract_business_categories_ibfk_3` FOREIGN KEY (`internal_category_id`) REFERENCES `internal_business_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Relaciona contratos con categorías de negocio';

-- Volcando datos para la tabla seramermvc.contract_business_categories: ~0 rows (aproximadamente)
DELETE FROM `contract_business_categories`;
INSERT INTO `contract_business_categories` (`id`, `contract_id`, `external_category_id`, `internal_category_id`, `type`) VALUES
	(1, 1, NULL, 41, 'internal'),
	(2, 1, NULL, 22, 'internal'),
	(5, 2, NULL, 21, 'internal'),
	(6, 2, NULL, 33, 'internal');

-- Volcando estructura para tabla seramermvc.contract_locations
CREATE TABLE IF NOT EXISTS `contract_locations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL COMMENT 'Contrato relacionado',
  `stall_id` int NOT NULL COMMENT 'Local/puesto asignado',
  PRIMARY KEY (`id`),
  KEY `contract_id` (`contract_id`),
  KEY `stall_id` (`stall_id`),
  CONSTRAINT `contract_locations_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`),
  CONSTRAINT `contract_locations_ibfk_2` FOREIGN KEY (`stall_id`) REFERENCES `market_stalls` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Asignación de locales específicos a contratos';

-- Volcando datos para la tabla seramermvc.contract_locations: ~0 rows (aproximadamente)
DELETE FROM `contract_locations`;
INSERT INTO `contract_locations` (`id`, `contract_id`, `stall_id`) VALUES
	(1, 1, 1),
	(3, 2, 1);

-- Volcando estructura para tabla seramermvc.contract_payments
CREATE TABLE IF NOT EXISTS `contract_payments` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del pago',
  `contract_id` int NOT NULL COMMENT 'ID del contrato asociado a este pago',
  `payment_reference` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Número o referencia del pago',
  `euro_rate_id` int DEFAULT NULL COMMENT 'ID de la tasa de cambio del euro aplicada',
  `payment_date` date NOT NULL COMMENT 'Fecha en que se realizó el pago',
  `amount` decimal(12,2) NOT NULL COMMENT 'Monto del pago',
  `status` enum('pending','paid','cancelled','refunded') COLLATE utf8mb4_general_ci DEFAULT 'pending' COMMENT 'Estado actual del pago',
  PRIMARY KEY (`id`),
  KEY `euro_rate_id` (`euro_rate_id`),
  KEY `idx_contract_payments_contract` (`contract_id`),
  KEY `idx_contract_payments_date` (`payment_date`),
  CONSTRAINT `contract_payments_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contract_payments_ibfk_2` FOREIGN KEY (`euro_rate_id`) REFERENCES `euro_rates` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de pagos asociados a los contratos del mercado';

-- Volcando datos para la tabla seramermvc.contract_payments: ~0 rows (aproximadamente)
DELETE FROM `contract_payments`;
INSERT INTO `contract_payments` (`id`, `contract_id`, `payment_reference`, `euro_rate_id`, `payment_date`, `amount`, `status`) VALUES
	(10, 1, 'PAY-1-001', 1, '2025-08-04', 5.50, 'pending'),
	(11, 1, 'PAY-1-002', NULL, '2025-09-04', 5.50, 'pending'),
	(12, 1, 'PAY-1-003', NULL, '2025-10-04', 5.50, 'pending'),
	(13, 1, 'PAY-1-004', NULL, '2025-11-04', 5.50, 'pending'),
	(14, 1, 'PAY-1-005', NULL, '2025-12-04', 5.50, 'pending'),
	(15, 1, 'PAY-1-006', NULL, '2026-01-04', 5.50, 'pending'),
	(16, 1, 'PAY-1-007', NULL, '2026-02-04', 5.50, 'pending'),
	(17, 1, 'PAY-1-008', NULL, '2026-03-04', 5.50, 'pending'),
	(18, 1, 'PAY-1-009', NULL, '2026-04-04', 5.50, 'pending'),
	(28, 2, 'PAY-2-001', 1, '2025-08-04', 1167.04, 'pending'),
	(29, 2, 'PAY-2-002', NULL, '2025-09-04', 0.00, 'pending'),
	(30, 2, 'PAY-2-003', NULL, '2025-10-04', 0.00, 'pending'),
	(31, 2, 'PAY-2-004', NULL, '2025-11-04', 0.00, 'pending'),
	(32, 2, 'PAY-2-005', NULL, '2025-12-04', 0.00, 'pending'),
	(33, 2, 'PAY-2-006', NULL, '2026-01-04', 0.00, 'pending'),
	(34, 2, 'PAY-2-007', NULL, '2026-02-04', 0.00, 'pending'),
	(35, 2, 'PAY-2-008', NULL, '2026-03-04', 0.00, 'pending'),
	(36, 2, 'PAY-2-009', NULL, '2026-04-04', 0.00, 'pending');

-- Volcando estructura para tabla seramermvc.contract_payment_installments
CREATE TABLE IF NOT EXISTS `contract_payment_installments` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del abono',
  `contract_payment_id` int NOT NULL COMMENT 'ID del pago de contrato al que pertenece este abono',
  `payment_method_id` int NOT NULL COMMENT 'Método de pago utilizado',
  `date` date NOT NULL COMMENT 'Fecha en que se realizó el abono',
  `amount` decimal(12,2) NOT NULL COMMENT 'Monto del abono',
  `concept` text COLLATE utf8mb4_general_ci COMMENT 'Concepto o descripción del abono',
  PRIMARY KEY (`id`),
  KEY `payment_method_id` (`payment_method_id`),
  KEY `idx_installment_contract_payment` (`contract_payment_id`),
  KEY `idx_installment_date` (`date`),
  CONSTRAINT `contract_payment_installments_ibfk_1` FOREIGN KEY (`contract_payment_id`) REFERENCES `contract_payments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contract_payment_installments_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de abonos o pagos parciales realizados para un pago de contrato';

-- Volcando datos para la tabla seramermvc.contract_payment_installments: ~0 rows (aproximadamente)
DELETE FROM `contract_payment_installments`;

-- Volcando estructura para tabla seramermvc.departments
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `shift_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `manager_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_shift_type` (`shift_type`),
  KEY `idx_manager_id` (`manager_id`),
  CONSTRAINT `fk_departments_manager` FOREIGN KEY (`manager_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena los departamentos de la organización';

-- Volcando datos para la tabla seramermvc.departments: ~4 rows (aproximadamente)
DELETE FROM `departments`;
INSERT INTO `departments` (`id`, `name`, `description`, `shift_type`, `created_at`, `manager_id`) VALUES
	(1, 'Liquidacion', 'Departamento de liquidación de nóminas', 'Matutino', '2025-08-02 21:28:46', 1),
	(2, 'Cobranza', 'Departamento de gestión de cobros', 'Vespertino', '2025-08-02 21:28:46', 2),
	(3, 'Fiscalizacion', 'Departamento de control fiscal', 'Mixto', '2025-08-02 21:28:46', 3),
	(4, 'Recursos Humanos', 'Gestión del personal y talento humano', 'Administrativo', '2025-08-02 21:28:46', 4);

-- Volcando estructura para tabla seramermvc.department_schedules
CREATE TABLE IF NOT EXISTS `department_schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `department_id` int NOT NULL,
  `day` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_day` (`day`),
  KEY `idx_time_range` (`start_time`,`end_time`),
  CONSTRAINT `department_schedules_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Define los horarios de trabajo por departamento';

-- Volcando datos para la tabla seramermvc.department_schedules: ~0 rows (aproximadamente)
DELETE FROM `department_schedules`;

-- Volcando estructura para tabla seramermvc.divisions
CREATE TABLE IF NOT EXISTS `divisions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `department_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_name` (`name`),
  CONSTRAINT `divisions_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Contiene las divisiones que pertenecen a cada departamento';

-- Volcando datos para la tabla seramermvc.divisions: ~6 rows (aproximadamente)
DELETE FROM `divisions`;
INSERT INTO `divisions` (`id`, `department_id`, `name`, `description`) VALUES
	(1, 1, 'Liquidación de Nóminas', 'División encargada del cálculo de salarios'),
	(2, 1, 'Prestaciones Sociales', 'División de beneficios para empleados'),
	(3, 2, 'Cobranza Interna', 'Gestión de cobros a empleados'),
	(4, 2, 'Cobranza Externa', 'Gestión de cobros a clientes'),
	(5, 3, 'Auditoría', 'División de revisiones fiscales'),
	(6, 4, 'Reclutamiento', 'Selección de personal');

-- Volcando estructura para tabla seramermvc.euro_rates
CREATE TABLE IF NOT EXISTS `euro_rates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bs_value` decimal(10,2) NOT NULL COMMENT 'Valor en bolívares',
  `month` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Mes de la tasa',
  `year` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Año de la tasa',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro histórico de tasas de cambio del euro';

-- Volcando datos para la tabla seramermvc.euro_rates: ~0 rows (aproximadamente)
DELETE FROM `euro_rates`;
INSERT INTO `euro_rates` (`id`, `bs_value`, `month`, `year`) VALUES
	(1, 145.88, 'agosto', '2025'),
	(3, 150.25, 'enero', '2025'),
	(4, 152.10, 'febrero', '2025'),
	(5, 148.90, 'marzo', '2025'),
	(6, 147.65, 'abril', '2025');

-- Volcando estructura para tabla seramermvc.external_business_categories
CREATE TABLE IF NOT EXISTS `external_business_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nombre de la categoría externa',
  `installation_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Tipo de instalación requerida',
  `payment_count` decimal(10,2) DEFAULT NULL COMMENT 'Número de cobros requeridos',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Categorías de negocios externos al mercado';

-- Volcando datos para la tabla seramermvc.external_business_categories: ~57 rows (aproximadamente)
DELETE FROM `external_business_categories`;
INSERT INTO `external_business_categories` (`id`, `name`, `installation_type`, `payment_count`) VALUES
	(2, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'CAMIÓN / CAVAS', 22.78),
	(3, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (5X5)', 20.60),
	(4, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (4X4)', 18.50),
	(5, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (4X3)', 16.70),
	(6, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (3X3)', 15.25),
	(7, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (2X2)', 14.60),
	(8, 'CHARCUTERIA', 'TOLDO (5X5)', 19.30),
	(9, 'CHARCUTERIA', 'TOLDO (4X4)', 18.50),
	(10, 'CHARCUTERIA', 'TOLDO (4X3)', 17.70),
	(11, 'CHARCUTERIA', 'TOLDO (3X3)', 16.46),
	(12, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'CAMIÓN / CAVAS', 22.78),
	(13, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (5X5)', 20.60),
	(14, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (4X4)', 18.50),
	(15, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (4X3)', 16.70),
	(16, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (3X3)', 15.25),
	(17, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (2X2)', 14.60),
	(18, 'CHARCUTERIA', 'TOLDO (5X5)', 19.30),
	(19, 'CHARCUTERIA', 'TOLDO (4X4)', 18.50),
	(20, 'CHARCUTERIA', 'TOLDO (4X3)', 17.70),
	(21, 'CHARCUTERIA', 'TOLDO (3X3)', 16.46),
	(22, 'VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (2X2)', 37.60),
	(23, 'VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (5X5)', 15.50),
	(24, 'VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (4X4)', 14.30),
	(25, 'VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (4X3)', 13.40),
	(26, 'VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (3X3)', 12.60),
	(27, 'VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (2X2)', 11.89),
	(28, 'VIVERES', 'TOLDO (5X5)', 37.60),
	(29, 'VIVERES', 'TOLDO (4X4)', 15.40),
	(30, 'VIVERES', 'TOLDO (4X3)', 14.40),
	(31, 'VIVERES', 'TOLDO (3X3)', 13.60),
	(32, 'VIVERES', 'TOLDO (2X2)', 12.89),
	(33, 'LIMPIEZA E HIGIENE', 'TOLDO (5X5)', 17.60),
	(34, 'LIMPIEZA E HIGIENE', 'TOLDO (4X4)', 16.40),
	(35, 'LIMPIEZA E HIGIENE', 'TOLDO (4X3)', 15.40),
	(36, 'LIMPIEZA E HIGIENE', 'TOLDO (3X3)', 14.60),
	(37, 'LIMPIEZA E HIGIENE', 'TOLDO (2X2)', 13.89),
	(38, 'ALIMENTOS PROCESADOS', 'TOLDO (5X5)', 16.60),
	(39, 'ALIMENTOS PROCESADOS', 'TOLDO (4X4)', 15.60),
	(40, 'ALIMENTOS PROCESADOS', 'TOLDO (4X3)', 14.89),
	(41, 'ALIMENTOS PROCESADOS', 'TOLDO (3X3)', 13.70),
	(42, 'ALIMENTOS PROCESADOS', 'TOLDO (2X2)', 12.50),
	(43, 'DULCERIA/PANADERIA', 'TOLDO (5X5)', 15.30),
	(44, 'DULCERIA/PANADERIA', 'TOLDO (4X4)', 14.60),
	(45, 'DULCERIA/PANADERIA', 'TOLDO (4X3)', 13.30),
	(46, 'DULCERIA/PANADERIA', 'TOLDO (3X3)', 12.70),
	(47, 'DULCERIA/PANADERIA', 'TOLDO (2X2)', 10.30),
	(48, 'TEXTIL/CALZADO', 'TOLDO (5X5)', 15.30),
	(49, 'TEXTIL/CALZADO', 'TOLDO (4X4)', 14.60),
	(50, 'TEXTIL/CALZADO', 'TOLDO (4X3)', 13.30),
	(51, 'TEXTIL/CALZADO', 'TOLDO (3X3)', 12.70),
	(52, 'TEXTIL/CALZADO', 'TOLDO (2X2)', 11.30),
	(53, 'OTROS', 'TOLDO (5X5)', 3.30),
	(54, 'OTROS', 'TOLDO (4X4)', 2.70),
	(55, 'OTROS', 'TOLDO (4X3)', 2.20),
	(56, 'OTROS', 'TOLDO (3X3)', 1.60),
	(57, 'OTROS', 'TOLDO (2X2)', 1.30),
	(58, 'KIOSCOS', 'ESTANDAR', 16.70);

-- Volcando estructura para tabla seramermvc.fiscal_year
CREATE TABLE IF NOT EXISTS `fiscal_year` (
  `id` int NOT NULL AUTO_INCREMENT,
  `start_date` date DEFAULT NULL COMMENT 'Mes de inicio del año fiscal',
  `end_date` date DEFAULT NULL COMMENT 'Año de inicio del año fiscal',
  `year` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Año de finalización del año fiscal',
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena la información de los años fiscales';

-- Volcando datos para la tabla seramermvc.fiscal_year: ~0 rows (aproximadamente)
DELETE FROM `fiscal_year`;
INSERT INTO `fiscal_year` (`id`, `start_date`, `end_date`, `year`, `status`, `created_at`) VALUES
	(1, '2025-05-01', '2026-04-30', '2025', 'active', '2025-08-04 01:17:49');

-- Volcando estructura para tabla seramermvc.internal_business_categories
CREATE TABLE IF NOT EXISTS `internal_business_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nombre de la categoría',
  `payment_count` decimal(10,2) DEFAULT NULL COMMENT 'Número de cobros requeridos',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Categorías de negocios internos como puestos y mesones';

-- Volcando datos para la tabla seramermvc.internal_business_categories: ~40 rows (aproximadamente)
DELETE FROM `internal_business_categories`;
INSERT INTO `internal_business_categories` (`id`, `name`, `payment_count`) VALUES
	(1, 'Dulceria y Emprendedores', 2.00),
	(2, 'Varios', 2.00),
	(3, 'Viveres', 2.00),
	(4, 'Quincallas', 2.00),
	(6, 'Legumbre y Hortalizas', 2.00),
	(7, 'Verduras', 2.00),
	(8, 'Frutas', 2.00),
	(9, 'Pescados, Fresco o Salado', 2.00),
	(10, 'Condimentos y especias para cocinar', 2.00),
	(11, 'Arepera', 2.50),
	(12, 'Comidas', 2.50),
	(13, 'Refresquerias / cafeterias', 2.50),
	(14, 'Empanadas', 2.50),
	(15, 'Mercancia Seca', 2.50),
	(16, 'Puesto de Reparación de zapatos', 2.50),
	(17, 'Chatarreros', 2.50),
	(18, 'Carne de Res', 3.00),
	(19, 'Carne de Cochino', 3.00),
	(20, 'Pollos Beneficiados', 3.00),
	(21, 'Charcuteria', 3.00),
	(22, 'Floristerias', 3.00),
	(23, 'Periodico y Revistas', 3.00),
	(24, 'Papeleria y Libreria', 3.00),
	(25, 'Servicio Tecnico e Internet', 3.00),
	(26, 'Plantas Curativas y Ornamentales', 3.00),
	(27, 'Relojeria, joyas y Prendas', 3.00),
	(28, 'Ventas de equipos celulares y de computadoras informatica', 3.00),
	(29, 'Ventas de Ropa y Zapatos', 3.00),
	(30, 'Ventas de Animales vivos', 3.00),
	(31, 'Peladoras de pollos y gallinas', 3.00),
	(32, 'Juegos y Apuestas Licitas', 5.00),
	(33, 'Loterias y Animalitos', 5.00),
	(34, 'Deposito', 5.00),
	(35, 'Ferreterias', 5.00),
	(36, 'Barberia y Peluqueria', 5.00),
	(37, 'Ventas de Comidas y Refresqueria', 5.00),
	(38, 'Mini Panaderias', 5.00),
	(39, 'Venta de electrodomésticos, repuestos, equipos y derivados', 5.00),
	(40, 'Ventas de bicicletas y repuestos', 5.00),
	(41, 'Aliños', 2.50);

-- Volcando estructura para tabla seramermvc.job_positions
CREATE TABLE IF NOT EXISTS `job_positions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de puestos de trabajo en la organización';

-- Volcando datos para la tabla seramermvc.job_positions: ~5 rows (aproximadamente)
DELETE FROM `job_positions`;
INSERT INTO `job_positions` (`id`, `name`) VALUES
	(1, 'Director test'),
	(2, 'Gerente'),
	(3, 'Secretaria'),
	(5, 'Desarrollador'),
	(6, 'test');

-- Volcando estructura para tabla seramermvc.leave_requests
CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `document_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `request_date` date NOT NULL,
  `approval_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_staff_id` (`staff_id`),
  KEY `idx_request_date` (`request_date`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Gestiona las solicitudes de permisos del personal';

-- Volcando datos para la tabla seramermvc.leave_requests: ~0 rows (aproximadamente)
DELETE FROM `leave_requests`;

-- Volcando estructura para tabla seramermvc.market_stalls
CREATE TABLE IF NOT EXISTS `market_stalls` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sector_id` int NOT NULL COMMENT 'Sector donde está ubicado',
  `stall_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Número o identificador del local',
  `location_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Descripción detallada de la ubicación',
  PRIMARY KEY (`id`),
  KEY `sector_id` (`sector_id`),
  CONSTRAINT `market_stalls_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Puestos físicos o locales dentro del mercado';

-- Volcando datos para la tabla seramermvc.market_stalls: ~0 rows (aproximadamente)
DELETE FROM `market_stalls`;
INSERT INTO `market_stalls` (`id`, `sector_id`, `stall_number`, `location_description`) VALUES
	(1, 1, 'L-001', 'testing');

-- Volcando estructura para tabla seramermvc.payment_methods
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del método de pago',
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nombre del método de pago (Efectivo, Transferencia, etc.)',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Indica si el método está activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de métodos de pago aceptados';

-- Volcando datos para la tabla seramermvc.payment_methods: ~6 rows (aproximadamente)
DELETE FROM `payment_methods`;
INSERT INTO `payment_methods` (`id`, `name`, `is_active`) VALUES
	(1, 'Efectivo', 1),
	(2, 'Transferencia Bancaria', 1),
	(3, 'Pago Móvil', 1),
	(4, 'Tarjeta de Débito', 1),
	(5, 'Biopago', 1),
	(6, 'Dólares', 1);

-- Volcando estructura para tabla seramermvc.sectors
CREATE TABLE IF NOT EXISTS `sectors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `zone_id` int NOT NULL COMMENT 'ID de la zona a la que pertenece',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nombre del sector',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'Descripción del sector',
  PRIMARY KEY (`id`),
  KEY `zone_id` (`zone_id`),
  CONSTRAINT `sectors_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Sectores específicos dentro de cada zona del mercado';

-- Volcando datos para la tabla seramermvc.sectors: ~0 rows (aproximadamente)
DELETE FROM `sectors`;
INSERT INTO `sectors` (`id`, `zone_id`, `name`, `description`) VALUES
	(1, 1, 'Sector 1', 'test');

-- Volcando estructura para tabla seramermvc.staff
CREATE TABLE IF NOT EXISTS `staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `academic_degree_id` int DEFAULT NULL,
  `academic_specialization_id` int DEFAULT NULL,
  `job_position_id` int NOT NULL,
  `department_id` int NOT NULL,
  `division_id` int DEFAULT NULL,
  `id_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Número de cédula o identificación',
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `middle_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `second_last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` tinyint(1) DEFAULT NULL COMMENT 'TRUE para Femenino, FALSE para Masculino',
  `hire_date` date NOT NULL,
  `termination_date` date DEFAULT NULL,
  `status` enum('active','inactive','vacation','leave','suspended') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_number` (`id_number`),
  KEY `idx_id_number` (`id_number`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_division_id` (`division_id`),
  KEY `idx_status` (`status`),
  KEY `idx_hire_date` (`hire_date`),
  KEY `idx_full_name` (`first_name`,`last_name`),
  KEY `idx_job_position` (`job_position_id`),
  KEY `academic_degree_id` (`academic_degree_id`),
  KEY `academic_specialization_id` (`academic_specialization_id`),
  CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`academic_degree_id`) REFERENCES `academic_degrees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`academic_specialization_id`) REFERENCES `academic_specializations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `staff_ibfk_3` FOREIGN KEY (`job_position_id`) REFERENCES `job_positions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `staff_ibfk_4` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `staff_ibfk_5` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabla principal que almacena toda la información del personal';

-- Volcando datos para la tabla seramermvc.staff: ~9 rows (aproximadamente)
DELETE FROM `staff`;
INSERT INTO `staff` (`id`, `academic_degree_id`, `academic_specialization_id`, `job_position_id`, `department_id`, `division_id`, `id_number`, `first_name`, `middle_name`, `last_name`, `second_last_name`, `birth_date`, `gender`, `hire_date`, `termination_date`, `status`, `created_at`, `updated_at`) VALUES
	(1, 3, 2, 1, 1, NULL, 'V12345678', 'María', NULL, 'González', NULL, '1980-05-15', 1, '2015-03-10', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(2, 3, 1, 1, 2, NULL, 'V23456789', 'Carlos', NULL, 'Pérez', NULL, '1978-11-22', 0, '2016-07-20', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(3, 4, 3, 1, 3, NULL, 'V34567890', 'Ana', NULL, 'Rodríguez', NULL, '1982-08-30', 1, '2017-01-15', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(4, 4, 5, 1, 4, NULL, 'V45678901', 'Luis', NULL, 'Martínez', NULL, '1975-04-18', 0, '2014-09-05', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(5, 3, 4, 5, 1, NULL, 'V56789012', 'Pedro', NULL, 'López', NULL, '1990-07-25', 0, '2019-05-10', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(6, 3, 4, 5, 2, NULL, 'V67890123', 'Sofía', NULL, 'Hernández', NULL, '1992-03-18', 1, '2020-02-15', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(7, 3, 4, 5, 3, NULL, 'V78901234', 'Jorge', NULL, 'Díaz', NULL, '1988-11-30', 0, '2018-08-22', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(8, 3, 4, 5, 4, NULL, 'V89012345', 'Laura', NULL, 'Torres', NULL, '1991-09-05', 1, '2021-01-10', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(9, 3, 2, 3, 1, NULL, 'V21564385', 'Andres', NULL, 'Figueroa', NULL, '1997-02-12', 0, '2020-08-02', NULL, 'active', '2025-08-02 22:53:09', '2025-08-02 22:53:10'),
	(10, 3, 1, 6, 2, NULL, 'V28456987', 'Ana ', 'Laura', 'Rojas', 'Perez', '1994-08-04', 1, '2020-08-04', NULL, 'active', '2025-08-04 16:40:22', '2025-08-04 16:40:23'),
	(11, 2, 1, 3, 2, NULL, 'V22321456', 'Felipe', 'Alejandro', 'Rodriguez', 'Ordaz', '1993-08-04', 0, '2022-08-04', NULL, 'active', '2025-08-04 16:41:40', '2025-08-04 16:41:40');

-- Volcando estructura para vista seramermvc.staff_complete_info
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `staff_complete_info` (
	`id` INT NOT NULL,
	`id_number` VARCHAR(1) NOT NULL COMMENT 'Número de cédula o identificación' COLLATE 'utf8mb4_general_ci',
	`full_name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`first_name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`middle_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`last_name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`second_last_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`birth_date` DATE NULL,
	`gender_text` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`hire_date` DATE NOT NULL,
	`termination_date` DATE NULL,
	`status` ENUM('active','inactive','vacation','leave','suspended') NULL COLLATE 'utf8mb4_general_ci',
	`department_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`division_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`job_position_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`academic_degree_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`academic_specialization_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`manager_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`created_at` TIMESTAMP NULL,
	`updated_at` TIMESTAMP NULL
);

-- Volcando estructura para tabla seramermvc.staff_department_history
CREATE TABLE IF NOT EXISTS `staff_department_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `department_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_staff_id` (`staff_id`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_date_range` (`start_date`,`end_date`),
  CONSTRAINT `staff_department_history_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `staff_department_history_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registra los cambios de departamento del personal a lo largo del tiempo';

-- Volcando datos para la tabla seramermvc.staff_department_history: ~0 rows (aproximadamente)
DELETE FROM `staff_department_history`;

-- Volcando estructura para tabla seramermvc.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int DEFAULT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `password_reset_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_staff_id` (`staff_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena las credenciales de acceso al sistema';

-- Volcando datos para la tabla seramermvc.users: ~10 rows (aproximadamente)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `staff_id`, `username`, `password_hash`, `email`, `last_login`, `password_reset_token`, `password_reset_expires`, `status`, `created_at`, `updated_at`) VALUES
	(1, 1, 'mmaria', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'maria.gonzalez@empresa.com', '2025-08-04 11:37:31', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-04 15:37:31'),
	(2, 2, 'cperez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'carlos.perez@empresa.com', '2025-08-04 13:19:16', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-04 17:19:16'),
	(3, 3, 'arodriguez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'ana.rodriguez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:43'),
	(4, 4, 'lmartinez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'luis.martinez@empresa.com', '2025-08-03 22:23:17', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-04 02:23:17'),
	(5, NULL, 'devliq', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devliquidacion@empresa.com', '2025-08-03 16:50:17', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 20:50:17'),
	(6, NULL, 'devcob', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devcobranza@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:47'),
	(7, NULL, 'devrrhh', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devrrhh@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:48'),
	(8, NULL, 'devfisc', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devfiscalizacion@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:50'),
	(9, 9, 'afigueroa', '$2y$12$iFj3D7pQ3wCsdkCs4nU5O.Z0rBgK4ydNpbph5RumpqlqLj6q96SuO', 'Andres.Figueroa@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 02:56:10', '2025-08-03 02:56:10'),
	(10, 5, 'plopez', '$2y$12$.Xv3sGjkrCSNnlJmdyz1j.sxfCYf2C/09OvOa794nxeA2sWCwX6WC', 'pedro.lopez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 13:36:10', '2025-08-03 15:33:38'),
	(11, 10, 'arojas', '$2y$12$DxcEraAN3tao8.z.FaOgsuz5jP39VqoFpDSQU3qZDgioePvAK6vh6', 'ana.rojas@empresa.com', NULL, NULL, NULL, 'active', '2025-08-04 16:42:11', '2025-08-04 16:42:11'),
	(12, 11, 'frodriguez', '$2y$12$32yiS2OUJt5hgQzoB/NCiuIdgb2Yvvh8L56GOlYsY9Kh9iUnM4Ri2', 'felipe.rodriguez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-04 16:42:40', '2025-08-04 16:42:40');

-- Volcando estructura para tabla seramermvc.user_departments
CREATE TABLE IF NOT EXISTS `user_departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `department_id` int NOT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_department` (`user_id`,`department_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `user_departments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_departments_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Controla qué usuarios tienen acceso a qué departamentos';

-- Volcando datos para la tabla seramermvc.user_departments: ~10 rows (aproximadamente)
DELETE FROM `user_departments`;
INSERT INTO `user_departments` (`id`, `user_id`, `department_id`, `status`, `created_at`) VALUES
	(1, 1, 1, 'active', '2025-08-02 21:28:47'),
	(2, 2, 2, 'active', '2025-08-02 21:28:47'),
	(3, 3, 3, 'active', '2025-08-02 21:28:47'),
	(4, 4, 4, 'active', '2025-08-02 21:28:47'),
	(5, 5, 1, 'active', '2025-08-02 21:28:47'),
	(6, 6, 2, 'active', '2025-08-02 21:28:47'),
	(7, 7, 3, 'active', '2025-08-02 21:28:47'),
	(8, 8, 4, 'active', '2025-08-02 21:28:47'),
	(9, 9, 1, 'active', '2025-08-03 02:56:10'),
	(10, 10, 1, 'active', '2025-08-03 13:36:11'),
	(11, 11, 2, 'active', '2025-08-04 16:42:11'),
	(12, 12, 2, 'active', '2025-08-04 16:42:40');

-- Volcando estructura para tabla seramermvc.vacations
CREATE TABLE IF NOT EXISTS `vacations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('requested','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'requested',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_staff_id` (`staff_id`),
  KEY `idx_date_range` (`start_date`,`end_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `vacations_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Administra las solicitudes y estados de vacaciones del personal';

-- Volcando datos para la tabla seramermvc.vacations: ~0 rows (aproximadamente)
DELETE FROM `vacations`;

-- Volcando estructura para tabla seramermvc.zones
CREATE TABLE IF NOT EXISTS `zones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nombre de la zona',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'Descripción detallada de la zona',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Zonas o áreas principales del mercado';

-- Volcando datos para la tabla seramermvc.zones: ~0 rows (aproximadamente)
DELETE FROM `zones`;
INSERT INTO `zones` (`id`, `name`, `description`) VALUES
	(1, 'Zona Hortalizas', 'testing');

-- Volcando estructura para disparador seramermvc.staff_updated_at_trigger
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `staff_updated_at_trigger` BEFORE UPDATE ON `staff` FOR EACH ROW BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador seramermvc.users_updated_at_trigger
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `users_updated_at_trigger` BEFORE UPDATE ON `users` FOR EACH ROW BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador seramermvc.vacations_updated_at_trigger
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `vacations_updated_at_trigger` BEFORE UPDATE ON `vacations` FOR EACH ROW BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `staff_complete_info`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `staff_complete_info` AS select `s`.`id` AS `id`,`s`.`id_number` AS `id_number`,concat(`s`.`first_name`,' ',ifnull(`s`.`middle_name`,''),' ',`s`.`last_name`,' ',ifnull(`s`.`second_last_name`,'')) AS `full_name`,`s`.`first_name` AS `first_name`,`s`.`middle_name` AS `middle_name`,`s`.`last_name` AS `last_name`,`s`.`second_last_name` AS `second_last_name`,`s`.`birth_date` AS `birth_date`,(case when (`s`.`gender` = true) then 'Femenino' else 'Masculino' end) AS `gender_text`,`s`.`hire_date` AS `hire_date`,`s`.`termination_date` AS `termination_date`,`s`.`status` AS `status`,`d`.`name` AS `department_name`,`dv`.`name` AS `division_name`,`jp`.`name` AS `job_position_name`,`ad`.`name` AS `academic_degree_name`,`asp`.`name` AS `academic_specialization_name`,concat(`m`.`first_name`,' ',`m`.`last_name`) AS `manager_name`,`s`.`created_at` AS `created_at`,`s`.`updated_at` AS `updated_at` from ((((((`staff` `s` left join `departments` `d` on((`s`.`department_id` = `d`.`id`))) left join `divisions` `dv` on((`s`.`division_id` = `dv`.`id`))) left join `job_positions` `jp` on((`s`.`job_position_id` = `jp`.`id`))) left join `academic_degrees` `ad` on((`s`.`academic_degree_id` = `ad`.`id`))) left join `academic_specializations` `asp` on((`s`.`academic_specialization_id` = `asp`.`id`))) left join `staff` `m` on((`d`.`manager_id` = `m`.`id`)))
;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
