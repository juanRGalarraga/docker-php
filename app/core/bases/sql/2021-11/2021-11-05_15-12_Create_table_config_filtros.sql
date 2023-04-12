DROP TABLE IF EXISTS `config_filtros`;
CREATE TABLE IF NOT EXISTS `config_filtros` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  `data` json DEFAULT NULL,
  `omision` tinyint(1) DEFAULT '1',
  `sys_fecha_alta` datetime NOT NULL,
  `sys_fecha_modif` datetime NOT NULL,
  `sys_usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `config_filtros`
--

INSERT INTO `config_filtros`(
    `id`,
    `nombre`,
    `estado`,
    `data`,
    `omision`,
    `sys_fecha_alta`,
    `sys_fecha_modif`,
    `sys_usuario_id`
)
VALUES(
    1,
    'Mora menor a 30 días',
    'HAB',
    '[{\"field\":\"estado\",\"cond\":\"=\",\"value\":\"\'MORA\'\"},{\"field\":\"dias_mora\",\"cond\":\">=\",\"value\":1},{\"field\":\"dias_mora\",\"cond\":\"<=\",\"value\":7}]',
    '1',
    '',
    '',
    ''
),(
    2,
    'Mora de 8 a 30 días',
    'HAB',
    '[{\"field\":\"estado\",\"cond\":\"=\",\"value\":\"MORA\"},{\"field\":\"dias_mora\",\"cond\":\">=\",\"value\":8},{\"field\":\"dias_mora\",\"cond\":\"<=\",\"value\":30}]',
    '1',
    '',
    '',
    ''
),(
    3,
    'Mora entre 60 y 90 días',
    'HAB',
    '[{\"field\":\"estado\",\"cond\":\"=\",\"value\":\"MORA\"},{\"field\":\"dias_mora\",\"cond\":\">=\",\"value\":60},{\"field\":\"dias_mora\",\"cond\":\"<=\",\"value\":90}]',
    '1',
    '',
    '',
    ''
),(
    4,
    'Préstamos en mora.',
    'HAB',
    '[{\"field\":\"estado\",\"cond\":\"NOT IN\",\"value\":\"(\'PEND\',\'SOLIC\',\'CANC\',\'ANUL\',\'HDAL\')\"}]',
    '1',
    '',
    '',
    ''
),(
    5,
    'Préstamos que vencen hoy.',
    'HAB',
    '[{\"field\":\"estado\",\"cond\":\"IN\",\"value\":\"(\'PEND\',\'MORA\')\"},{\"field\":\"$DATE(`fecha_vencimiento`)\",\"cond\":\"=\",\"value\":\"$CURDATE()\"}]',
    '1',
    '',
    '',
    ''
),(
    6,
    'Préstamos que vencen en los próximos 3 días.',
    'HAB',
    '[{\"field\":\"estado\",\"cond\":\"IN\",\"value\":\"(\'PEND\',\'MORA\')\"},{\"field\":\"$DATE(`fecha_vencimiento`)\",\"cond\":\">=\",\"value\":\"$CURDATE()\"},{\"field\":\"$DATE(`fecha_vencimiento`)\",\"cond\":\"<=\",\"value\":\"$DATE_ADD(CURDATE(), INTERVAL 3 DAY)\"}]',
    '1',
    '',
    '',
    ''
),(
    7,
    'Mora mayor a 90 días',
    'HAB',
    '[{\"field\":\"estado\",\"cond\":\"=\",\"value\":\"MORA\"},{\"field\":\"dias_mora\",\"cond\":\">\",\"value\":90}]',
    '1',
    '',
    '',
    ''
)