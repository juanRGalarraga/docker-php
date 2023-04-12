ALTER TABLE `cuotas_hist` ADD `restante` DECIMAL(16,4) NULL COMMENT 'En el caso de haber un pago menor al pago de la cuota, se va a almacenar el restante a pagar' AFTER `operacion`;
ALTER TABLE `cuotas_hist` ADD `sobrante` DECIMAL(16,4) NULL COMMENT 'En el caso de haber un pago mayor al total de la cuota, se va a almacenar el sobrante pagado' AFTER `restante`;

