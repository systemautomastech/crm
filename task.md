# SalesOrder Addon — Execution Checklist

## Phase 1 — Package Registration & Namespace
- [x] Add `Automas\SalesOrder` to root composer.json autoload
- [x] Fix module.json package_name
- [x] Fix package composer.json (provider reference already correct)

## Phase 2 — Service Provider & Event Provider
- [/] Create SalesOrderServiceProvider.php
- [/] Create EventServiceProvider.php
- [/] Delete/replace SalesServiceProvider.php

## Phase 3 — Models
- [/] Fix SalesOrder.php namespace + relations + delivery status
- [/] Fix SalesOrderItem.php namespace
- [/] Fix SalesOrderItemTax.php namespace
- [/] Fix/clean SalesUtility.php → SalesOrderUtility
- [/] Create SalesOrderSetting.php
- [/] Create SalesOrderDelivery.php
- [/] Create SalesOrderDeliveryItem.php

## Phase 4 — Controllers
- [/] Fix SalesOrderController.php namespace + confirm/cancel/print/pdf
- [/] Create SalesOrderDeliveryController.php
- [/] Create SalesOrderSettingController.php
- [/] Fix ReportController.php namespace
- [/] Delete unrelated API controllers
- [/] Fix SalesOrderApiController.php namespace

## Phase 5 — Form Requests
- [/] Fix StoreSalesOrderRequest.php
- [/] Fix UpdateSalesOrderRequest.php
- [/] Create StoreSalesOrderDeliveryRequest.php

## Phase 6 — Events & Listeners
- [/] Fix 3 relevant events (CreateSalesOrder, UpdateSalesOrder, DestroySalesOrder)
- [/] Add CreateSalesOrderDelivery, CancelSalesOrderDelivery events
- [/] Delete 45 unrelated events
- [/] Fix GiveRoleToPermission listener
- [/] Fix DataDefault listener
- [/] Create EventServiceProvider.php

## Phase 7 — Migrations
- [/] Fix sales_orders migration (remove FK constraints to Sales addon)
- [/] Fix sales_order_items migration namespace
- [/] Fix sales_order_item_taxes migration namespace
- [/] Delete 3 unrelated migrations
- [/] Create sales_order_users pivot migration
- [/] Create sales_order_deliveries migration
- [/] Create sales_order_delivery_items migration
- [/] Create sales_order_settings migration
- [/] Create alter migration for delivery_status, expected_delivery_date
- [/] Create quotation-side migration

## Phase 8 — Seeders
- [/] Fix + expand PermissionTableSeeder
- [/] Fix SalesDatabaseSeeder → SalesOrderDatabaseSeeder
- [/] Delete SalesOpportunityStageSeeder
- [/] Fix MarketplaceSettingSeeder namespace
- [/] Fix NotificationsTableSeeder namespace
- [/] Fix EmailTemplatesSeeder namespace

## Phase 9 — Routes
- [/] Rewrite web.php (clean)
- [/] Rewrite api.php (clean)

## Phase 10 — Frontend
- [/] Fix Index.tsx route names + delivery status
- [/] Fix Create.tsx
- [/] Fix Edit.tsx
- [/] Fix Show.tsx + delivery progress
- [/] Create Deliver.tsx (create delivery)
- [/] Create DeliveryShow.tsx
- [/] Create Challan.tsx
- [/] Create SystemSetup/Index.tsx
- [/] Fix menus/company-menu.ts

## Phase 11 — Quotation Integration
- [/] Add salesOrder() relation to SalesQuotation model
- [/] Add convertToSalesOrder() to QuotationController
- [/] Add quotation migration for sales_order_id

## Phase 12 — Translations
- [/] Update en.json with SalesOrder keys
