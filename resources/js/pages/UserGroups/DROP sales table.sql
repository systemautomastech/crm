DROP TABLE `sales_accounts`, `sales_account_industries`, `sales_account_types`, `sales_calls`, `sales_cases`, `sales_case_types`, `sales_contacts`, `sales_documents`, `sales_document_folders`, `sales_document_types`, `sales_meetings`, `sales_opportunities`, `sales_opportunity_stages`, `sales_orders`, `sales_order_deliveries`, `sales_order_delivery_items`, `sales_order_items`, `sales_order_item_taxes`, `sales_order_settings`, `sales_order_users`, `sales_quotes`, `sales_quote_items`, `sales_quote_item_taxes`, `sales_shipping_providers`, `sales_streams`;

DELETE FROM `migrations` WHERE `migrations`.`id` = 12;
DELETE FROM `migrations` WHERE `migrations`.`id` = 13;
DELETE FROM `migrations` WHERE `migrations`.`id` = 14;
DELETE FROM `migrations` WHERE `migrations`.`id` = 15;
DELETE FROM `migrations` WHERE `migrations`.`id` = 16;
DELETE FROM `migrations` WHERE `migrations`.`id` = 17;
DELETE FROM `migrations` WHERE `migrations`.`id` = 19;
DELETE FROM `migrations` WHERE `migrations`.`id` = 20;
DELETE FROM `migrations` WHERE `migrations`.`id` = 21;
DELETE FROM `migrations` WHERE `migrations`.`id` = 22;
DELETE FROM `migrations` WHERE `migrations`.`id` = 23;
DELETE FROM `migrations` WHERE `migrations`.`id` = 24;
DELETE FROM `migrations` WHERE `migrations`.`id` = 25;
DELETE FROM `migrations` WHERE `migrations`.`id` = 31;
DELETE FROM `migrations` WHERE `migrations`.`id` = 33;
DELETE FROM `migrations` WHERE `migrations`.`id` = 34;
DELETE FROM `migrations` WHERE `migrations`.`id` = 39;
DELETE FROM `migrations` WHERE `migrations`.`id` = 41;
DELETE FROM `migrations` WHERE `migrations`.`id` = 116;
DELETE FROM `migrations` WHERE `migrations`.`id` = 117;
DELETE FROM `migrations` WHERE `migrations`.`id` = 118;
DELETE FROM `migrations` WHERE `migrations`.`id` = 237;
DELETE FROM `migrations` WHERE `migrations`.`id` = 265;
DELETE FROM `migrations` WHERE `migrations`.`id` = 269;
DELETE FROM `migrations` WHERE `migrations`.`id` = 313;
DELETE FROM `migrations` WHERE `migrations`.`id` = 314;
DELETE FROM `migrations` WHERE `migrations`.`id` = 315;
DELETE FROM `migrations` WHERE `migrations`.`id` = 316;
DELETE FROM `migrations` WHERE `migrations`.`id` = 321;
DELETE FROM `migrations` WHERE `migrations`.`id` = 322;
DELETE FROM `migrations` WHERE `migrations`.`id` = 323;

DELETE FROM `permissions` WHERE add_on='Sales' ORDER BY `add_on` DESC




INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `module`, `label`, `add_on`, `created_at`, `updated_at`) VALUES
('manage-sales-orders', 'web', 'sales-orders', 'Manage Sales Orders', 'SalesOrder', NOW(), NOW()),
('manage-any-sales-orders', 'web', 'sales-orders', 'Manage Any Sales Orders', 'SalesOrder', NOW(), NOW()),
('manage-own-sales-orders', 'web', 'sales-orders', 'Manage Own Sales Orders', 'SalesOrder', NOW(), NOW()),
('create-sales-orders', 'web', 'sales-orders', 'Create Sales Orders', 'SalesOrder', NOW(), NOW()),
('view-sales-orders', 'web', 'sales-orders', 'View Sales Orders', 'SalesOrder', NOW(), NOW()),
('edit-sales-orders', 'web', 'sales-orders', 'Edit Sales Orders', 'SalesOrder', NOW(), NOW()),
('delete-sales-orders', 'web', 'sales-orders', 'Delete Sales Orders', 'SalesOrder', NOW(), NOW()),
('print-sales-orders', 'web', 'sales-orders', 'Print / PDF Sales Orders', 'SalesOrder', NOW(), NOW()),
('manage-sales-order-deliveries', 'web', 'sales-order-deliveries', 'Manage Sales Order Deliveries', 'SalesOrder', NOW(), NOW()),
('create-sales-order-deliveries', 'web', 'sales-order-deliveries', 'Create Sales Order Deliveries', 'SalesOrder', NOW(), NOW()),
('view-sales-order-deliveries', 'web', 'sales-order-deliveries', 'View Sales Order Deliveries', 'SalesOrder', NOW(), NOW()),
('cancel-sales-order-deliveries', 'web', 'sales-order-deliveries', 'Cancel Sales Order Deliveries', 'SalesOrder', NOW(), NOW()),
('print-delivery-challans', 'web', 'sales-order-deliveries', 'Print Delivery Challans', 'SalesOrder', NOW(), NOW()),
('convert-quotation-to-sales-order', 'web', 'sales-orders', 'Convert Quotation to Sales Order', 'SalesOrder', NOW(), NOW()),
('manage-sales-order-settings', 'web', 'sales-order-settings', 'Manage Sales Order Settings', 'SalesOrder', NOW(), NOW()),
('assign-group-sales-orders', 'web', 'sales-orders', 'Assign Group to Sales Orders', 'SalesOrder', NOW(), NOW()),
('acquire-sales-orders', 'web', 'sales-orders', 'Acquire Sales Orders', 'SalesOrder', NOW(), NOW()),
('release-sales-orders', 'web', 'sales-orders', 'Release Sales Orders', 'SalesOrder', NOW(), NOW()),
('reassign-sales-orders', 'web', 'sales-orders', 'Reassign Sales Orders', 'SalesOrder', NOW(), NOW());


INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p
CROSS JOIN `roles` r
WHERE p.add_on = 'SalesOrder'
  AND r.name IN ('company', 'admin', 'superadmin');
