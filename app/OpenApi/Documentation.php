<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="StockManager API",
 *   description="Bearer-token inventory API with role authorization and an immutable stock ledger."
 * )
 *
 * @OA\Server(url="http://localhost:8000", description="Local development")
 *
 * @OA\SecurityScheme(securityScheme="bearerAuth", type="http", scheme="bearer", bearerFormat="Sanctum token")
 *
 * @OA\Schema(
 *   schema="ProductCreateInput", type="object", required={"name","price","minimum_quantity"},
 *
 *   @OA\Property(property="sku", type="string", nullable=true),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="opening_stock", type="integer", minimum=0, description="Store only; creates an opening ledger movement."),
 *   @OA\Property(property="price", type="number", format="float", minimum=0),
 *   @OA\Property(property="minimum_quantity", type="integer", minimum=0),
 *   @OA\Property(property="supplier_id", type="integer", nullable=true),
 *   @OA\Property(property="category", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="ProductUpdateInput", type="object", required={"name","price","minimum_quantity"},
 *
 *   @OA\Property(property="sku", type="string", nullable=true),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="price", type="number", format="float", minimum=0),
 *   @OA\Property(property="minimum_quantity", type="integer", minimum=0),
 *   @OA\Property(property="supplier_id", type="integer", nullable=true),
 *   @OA\Property(property="category", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="ProductPatchInput", type="object",
 *
 *   @OA\Property(property="sku", type="string", nullable=true),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="price", type="number", format="float", minimum=0),
 *   @OA\Property(property="minimum_quantity", type="integer", minimum=0),
 *   @OA\Property(property="supplier_id", type="integer", nullable=true),
 *   @OA\Property(property="category", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="SupplierInput", type="object", required={"name"},
 *
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="email", type="string", format="email", nullable=true),
 *   @OA\Property(property="phone", type="string", nullable=true),
 *   @OA\Property(property="address", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="SupplierPatchInput", type="object",
 *
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="email", type="string", format="email", nullable=true),
 *   @OA\Property(property="phone", type="string", nullable=true),
 *   @OA\Property(property="address", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="StockInput", type="object", required={"product_id","quantity"},
 *
 *   @OA\Property(property="product_id", type="integer"),
 *   @OA\Property(property="quantity", type="integer", minimum=1),
 *   @OA\Property(property="note", type="string", nullable=true)
 * )
 *
 * @OA\Post(
 *   path="/api/login", tags={"Authentication"}, summary="Create a bearer token",
 *
 *   @OA\RequestBody(required=true, @OA\JsonContent(type="object", required={"email","password"},
 *
 *     @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
 *     @OA\Property(property="password", type="string", format="password"),
 *     @OA\Property(property="device_name", type="string", example="local-client")
 *   )),
 *
 *   @OA\Response(response=200, description="Authenticated"),
 *   @OA\Response(response=401, description="Invalid credentials"),
 *   @OA\Response(response=422, description="Validation failed")
 * )
 *
 * @OA\Post(path="/api/logout", tags={"Authentication"}, summary="Revoke current token", security={{"bearerAuth":{}}}, @OA\Response(response=200, description="Token revoked"), @OA\Response(response=401, description="Unauthenticated"))
 *
 * @OA\Get(path="/api/user", tags={"Authentication"}, summary="Current user", security={{"bearerAuth":{}}}, @OA\Response(response=200, description="Current user"), @OA\Response(response=401, description="Unauthenticated"))
 * @OA\Get(path="/api/products", tags={"Products"}, summary="List products", security={{"bearerAuth":{}}}, @OA\Parameter(name="search", in="query", @OA\Schema(type="string")), @OA\Response(response=200, description="Paginated products"), @OA\Response(response=403, description="Role denied"))
 *
 * @OA\Post(path="/api/products", tags={"Products"}, summary="Create product and optional opening movement", security={{"bearerAuth":{}}}, @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ProductCreateInput")), @OA\Response(response=201, description="Product created"), @OA\Response(response=403, description="Admin required"), @OA\Response(response=422, description="Validation failed"))
 *
 * @OA\Get(path="/api/products/{product}", tags={"Products"}, summary="Show product", security={{"bearerAuth":{}}}, @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Product"), @OA\Response(response=404, description="Not found"))
 *
 * @OA\Put(path="/api/products/{product}", tags={"Products"}, summary="Update product metadata; quantity is prohibited", security={{"bearerAuth":{}}}, @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")), @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ProductUpdateInput")), @OA\Response(response=200, description="Updated"), @OA\Response(response=403, description="Admin required"), @OA\Response(response=422, description="Validation failed"))
 *
 * @OA\Patch(path="/api/products/{product}", tags={"Products"}, summary="Partially update product metadata; quantity is prohibited", security={{"bearerAuth":{}}}, @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")), @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ProductPatchInput")), @OA\Response(response=200, description="Updated"), @OA\Response(response=403, description="Admin required"), @OA\Response(response=422, description="Validation failed"))
 *
 * @OA\Delete(path="/api/products/{product}", tags={"Products"}, summary="Archive a zero-stock product", security={{"bearerAuth":{}}}, @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Archived"), @OA\Response(response=403, description="Admin required"), @OA\Response(response=422, description="Stock must be zero"))
 *
 * @OA\Get(path="/api/products/export", tags={"Products"}, summary="Export products as XLSX", security={{"bearerAuth":{}}}, @OA\Response(response=200, description="Excel file"))
 * @OA\Get(path="/api/suppliers", tags={"Suppliers"}, summary="List suppliers", security={{"bearerAuth":{}}}, @OA\Response(response=200, description="Paginated suppliers"))
 *
 * @OA\Post(path="/api/suppliers", tags={"Suppliers"}, summary="Create supplier", security={{"bearerAuth":{}}}, @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/SupplierInput")), @OA\Response(response=201, description="Created"), @OA\Response(response=403, description="Admin required"))
 *
 * @OA\Get(path="/api/suppliers/{supplier}", tags={"Suppliers"}, summary="Show supplier", security={{"bearerAuth":{}}}, @OA\Parameter(name="supplier", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Supplier"))
 *
 * @OA\Put(path="/api/suppliers/{supplier}", tags={"Suppliers"}, summary="Update supplier", security={{"bearerAuth":{}}}, @OA\Parameter(name="supplier", in="path", required=true, @OA\Schema(type="integer")), @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/SupplierInput")), @OA\Response(response=200, description="Updated"), @OA\Response(response=403, description="Admin required"))
 *
 * @OA\Patch(path="/api/suppliers/{supplier}", tags={"Suppliers"}, summary="Partially update supplier", security={{"bearerAuth":{}}}, @OA\Parameter(name="supplier", in="path", required=true, @OA\Schema(type="integer")), @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/SupplierPatchInput")), @OA\Response(response=200, description="Updated"), @OA\Response(response=403, description="Admin required"))
 *
 * @OA\Delete(path="/api/suppliers/{supplier}", tags={"Suppliers"}, summary="Delete supplier", security={{"bearerAuth":{}}}, @OA\Parameter(name="supplier", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Deleted"), @OA\Response(response=403, description="Admin required"))
 *
 * @OA\Post(path="/api/stock/in", tags={"Stock"}, summary="Record stock in atomically", security={{"bearerAuth":{}}}, @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StockInput")), @OA\Response(response=201, description="Movement created"), @OA\Response(response=422, description="Validation failed"))
 * @OA\Post(path="/api/stock/out", tags={"Stock"}, summary="Record stock out atomically", security={{"bearerAuth":{}}}, @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StockInput")), @OA\Response(response=201, description="Movement created"), @OA\Response(response=422, description="Insufficient stock or validation failed"))
 * @OA\Post(path="/api/stock-movements/{stockMovement}/reverse", tags={"Stock"}, summary="Create linked compensating movement", security={{"bearerAuth":{}}}, @OA\Parameter(name="stockMovement", in="path", required=true, @OA\Schema(type="integer")), @OA\RequestBody(required=true, @OA\JsonContent(type="object", required={"reason"}, @OA\Property(property="reason", type="string"))), @OA\Response(response=201, description="Reversal created"), @OA\Response(response=403, description="Admin required"), @OA\Response(response=422, description="Cannot reverse"))
 *
 * @OA\Get(path="/api/reports/low-stock", tags={"Reports"}, summary="Low stock report", security={{"bearerAuth":{}}}, @OA\Response(response=200, description="Paginated products"))
 * @OA\Get(path="/api/reports/top-products", tags={"Reports"}, summary="Top products by stock out", security={{"bearerAuth":{}}}, @OA\Response(response=200, description="Top products"))
 * @OA\Get(path="/api/reports/history", tags={"Reports"}, summary="Movement history", security={{"bearerAuth":{}}}, @OA\Response(response=200, description="Paginated movements"))
 * @OA\Get(path="/api/reports/stock/pdf", tags={"Reports"}, summary="Download inventory PDF", security={{"bearerAuth":{}}}, @OA\Response(response=200, description="PDF file"))
 */
final class Documentation {}
