<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quinn's Laundry POS — API Docs</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #ffffff;
            color: #111111;
            line-height: 1.6;
        }

        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 220px;
            height: 100vh;
            background: #f5f5f5;
            border-right: 1px solid #e0e0e0;
            padding: 24px 0;
            overflow-y: auto;
        }

        .sidebar-title {
            font-size: 13px;
            font-weight: 700;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 0 20px 16px;
        }

        .sidebar a {
            display: block;
            padding: 7px 20px;
            color: #444444;
            text-decoration: none;
            font-size: 14px;
            transition: color .15s, background .15s;
        }

        .sidebar a:hover {
            color: #000000;
            background: #e8e8e8;
        }

        .main {
            margin-left: 220px;
            padding: 48px 56px;
            max-width: 900px;
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #000000;
        }

        .subtitle {
            color: #666666;
            margin-top: 6px;
            font-size: 15px;
        }

        .base-url {
            display: inline-block;
            margin-top: 16px;
            background: #f5f5f5;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 8px 14px;
            font-family: monospace;
            font-size: 13px;
            color: #111111;
        }

        .badges {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .badge {
            font-size: 12px;
            padding: 3px 10px;
            border-radius: 999px;
            font-weight: 600;
        }

        .badge-200 { background: #e6f4ea; color: #1a7f37; }
        .badge-201 { background: #e8f0fe; color: #1a56db; }
        .badge-404 { background: #fde8e8; color: #b91c1c; }
        .badge-422 { background: #fef3cd; color: #92400e; }
        .badge-500 { background: #f3e8ff; color: #6b21a8; }

        .divider {
            border: none;
            border-top: 1px solid #e0e0e0;
            margin: 40px 0;
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: #000000;
            margin-bottom: 20px;
            scroll-margin-top: 32px;
        }

        .endpoint-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-bottom: 28px;
        }

        .endpoint-table th {
            text-align: left;
            padding: 10px 14px;
            background: #f5f5f5;
            color: #888888;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .06em;
            border-bottom: 1px solid #e0e0e0;
        }

        .endpoint-table td {
            padding: 11px 14px;
            border-bottom: 1px solid #eeeeee;
            vertical-align: top;
        }

        .endpoint-table tr:last-child td { border-bottom: none; }

        .method {
            font-family: monospace;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            min-width: 54px;
            text-align: center;
        }

        .get    { background: #e6f4ea; color: #1a7f37; }
        .post   { background: #e8f0fe; color: #1a56db; }
        .put    { background: #fef3cd; color: #92400e; }
        .patch  { background: #fef3cd; color: #92400e; }
        .delete { background: #fde8e8; color: #b91c1c; }

        .path {
            font-family: monospace;
            font-size: 13px;
            color: #111111;
        }

        .desc { color: #555555; font-size: 13px; }

        .fields-title {
            font-size: 13px;
            font-weight: 600;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 10px;
        }

        .fields-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 28px;
        }

        .fields-table th {
            text-align: left;
            padding: 8px 12px;
            background: #f5f5f5;
            color: #888888;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .06em;
            border-bottom: 1px solid #e0e0e0;
        }

        .fields-table td {
            padding: 9px 12px;
            border-bottom: 1px solid #eeeeee;
            color: #444444;
        }

        .fields-table tr:last-child td { border-bottom: none; }

        .field-name {
            font-family: monospace;
            color: #111111;
            font-size: 13px;
        }

        .required { color: #1a7f37; font-weight: 600; }
        .optional { color: #aaaaaa; }

        .code-block {
            background: #f5f5f5;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 16px 20px;
            font-family: monospace;
            font-size: 13px;
            color: #444444;
            overflow-x: auto;
            white-space: pre;
            margin-bottom: 28px;
            line-height: 1.7;
        }

        .key   { color: #1a56db; }
        .str   { color: #1a7f37; }
        .num   { color: #92400e; }
        .bool  { color: #6b21a8; }
        .null  { color: #aaaaaa; }
    </style>
</head>
<body>

<nav class="sidebar">
    <div class="sidebar-title">Quinn's Laundry</div>
    <a href="#overview">Overview</a>
    <a href="#auth">Authentication</a>
    <a href="#branches">Branches</a>
    <a href="#customers">Customers</a>
    <a href="#orders">Orders</a>
    <a href="#expenses">Expenses</a>
    <a href="#expense-categories">Expense Categories</a>
    <a href="#reports">Reports</a>
    <a href="#categories">Categories</a>
    <a href="#items">Items</a>
</nav>

<main class="main">

    {{-- Overview --}}
    <section id="overview">
        <h1>Quinn's Laundry POS API</h1>
        <p class="subtitle">REST API for the Quinn's Laundry Point of Sale system.</p>
        <div class="base-url">https://laundryappapi-production.up.railway.app/api/v1</div>

        <div class="badges" style="margin-top: 24px;">
            <span class="badge badge-200">200 Success</span>
            <span class="badge badge-201">201 Created</span>
            <span class="badge badge-404">404 Not Found</span>
            <span class="badge badge-422">422 Validation Error</span>
            <span class="badge badge-500">500 Server Error</span>
        </div>
    </section>

    <hr class="divider">

    {{-- Authentication --}}
    <section id="auth">
        <div class="section-title">Authentication</div>

        <p class="desc" style="margin-bottom: 20px;">
            All endpoints except <code style="font-family:monospace;background:#f5f5f5;padding:1px 5px;border-radius:3px;">POST /login</code> require a Bearer token in the <code style="font-family:monospace;background:#f5f5f5;padding:1px 5px;border-radius:3px;">Authorization</code> header.
        </p>

        <table class="endpoint-table">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Endpoint</th>
                    <th>Description</th>
                    <th>Auth</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="method post">POST</span></td>
                    <td class="path">/login</td>
                    <td class="desc">Obtain an API token</td>
                    <td class="desc">Public</td>
                </tr>
                <tr>
                    <td><span class="method post">POST</span></td>
                    <td class="path">/logout</td>
                    <td class="desc">Revoke the current token</td>
                    <td class="desc">Required</td>
                </tr>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/me</td>
                    <td class="desc">Get the authenticated user</td>
                    <td class="desc">Required</td>
                </tr>
            </tbody>
        </table>

        <div class="fields-title">Login Request</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">username</td><td>string</td><td class="required">Yes</td><td></td></tr>
                <tr><td class="field-name">password</td><td>string</td><td class="required">Yes</td><td></td></tr>
            </tbody>
        </table>

        <div class="fields-title">Login Response</div>
        <div class="code-block"><span class="key">"token"</span>: <span class="str">"1|abc123..."</span>,
<span class="key">"expires_at"</span>: <span class="str">"2026-04-17 12:00:00"</span>,
<span class="key">"user"</span>: {
  <span class="key">"id"</span>: <span class="num">1</span>,
  <span class="key">"name"</span>: <span class="str">"Juan dela Cruz"</span>,
  <span class="key">"username"</span>: <span class="str">"juan"</span>,
  <span class="key">"role"</span>: <span class="str">"admin"</span>,
  <span class="key">"tenant_id"</span>: <span class="num">1</span>,
  <span class="key">"branch_id"</span>: <span class="null">null</span>
}</div>

        <div class="fields-title">GET /me Response</div>
        <div class="code-block"><span class="key">"id"</span>: <span class="num">1</span>,
<span class="key">"name"</span>: <span class="str">"Juan dela Cruz"</span>,
<span class="key">"username"</span>: <span class="str">"juan"</span>,
<span class="key">"role"</span>: <span class="str">"admin"</span>,
<span class="key">"tenant_id"</span>: <span class="num">1</span>,
<span class="key">"branch_id"</span>: <span class="null">null</span>,
<span class="key">"token_expires_at"</span>: <span class="str">"2026-04-17 12:00:00"</span></div>

        <div class="fields-title">Using the Token</div>
        <div class="code-block">Authorization: Bearer 1|abc123...</div>

        <div class="fields-title">Error Responses</div>
        <table class="fields-table">
            <thead>
                <tr><th>Status</th><th>Reason</th></tr>
            </thead>
            <tbody>
                <tr><td>422</td><td>Invalid credentials</td></tr>
                <tr><td>403</td><td>Account is inactive</td></tr>
                <tr><td>401</td><td>Missing or invalid token on protected routes</td></tr>
            </tbody>
        </table>
    </section>

    <hr class="divider">

    {{-- Branches --}}
    <section id="branches">
        <div class="section-title">Branches</div>

        <p class="desc" style="margin-bottom: 20px;">
            All authenticated users can list and view branches. Create, update, and delete are restricted to <strong>admin</strong> role only.
        </p>

        <table class="endpoint-table">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Endpoint</th>
                    <th>Description</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/branches</td>
                    <td class="desc">List active branches for the tenant</td>
                    <td class="desc">All</td>
                </tr>
                <tr>
                    <td><span class="method post">POST</span></td>
                    <td class="path">/branches</td>
                    <td class="desc">Create a new branch</td>
                    <td class="desc">Admin</td>
                </tr>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/branches/{id}</td>
                    <td class="desc">Get a branch</td>
                    <td class="desc">All</td>
                </tr>
                <tr>
                    <td><span class="method patch">PATCH</span></td>
                    <td class="path">/branches/{id}</td>
                    <td class="desc">Update a branch</td>
                    <td class="desc">Admin</td>
                </tr>
                <tr>
                    <td><span class="method delete">DELETE</span></td>
                    <td class="path">/branches/{id}</td>
                    <td class="desc">Delete a branch</td>
                    <td class="desc">Admin</td>
                </tr>
            </tbody>
        </table>

        <div class="fields-title">Fields</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">name</td><td>string</td><td class="required">Yes</td><td>Max 255 characters</td></tr>
                <tr><td class="field-name">address</td><td>string</td><td class="optional">No</td><td>Max 500 characters</td></tr>
                <tr><td class="field-name">phone</td><td>string</td><td class="optional">No</td><td>Max 20 characters</td></tr>
                <tr><td class="field-name">is_active</td><td>boolean</td><td class="optional">No</td><td>Defaults to true</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Response</div>
        <div class="code-block"><span class="key">"id"</span>: <span class="num">1</span>,
<span class="key">"tenant_id"</span>: <span class="num">1</span>,
<span class="key">"name"</span>: <span class="str">"Main Branch"</span>,
<span class="key">"address"</span>: <span class="str">"123 Main St"</span>,
<span class="key">"phone"</span>: <span class="str">"09171234567"</span>,
<span class="key">"is_active"</span>: <span class="bool">true</span>,
<span class="key">"created_at"</span>: <span class="str">"2024-01-01 00:00:00"</span>,
<span class="key">"updated_at"</span>: <span class="str">"2024-01-01 00:00:00"</span></div>
    </section>

    <hr class="divider">

    {{-- Customers --}}
    <section id="customers">
        <div class="section-title">Customers</div>

        <table class="endpoint-table">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Endpoint</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/customers</td>
                    <td class="desc">List all customers — supports <code>?search=</code> to filter by nickname or mobile</td>
                </tr>
                <tr>
                    <td><span class="method post">POST</span></td>
                    <td class="path">/customers</td>
                    <td class="desc">Create a customer</td>
                </tr>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/customers/{id}</td>
                    <td class="desc">Get a customer</td>
                </tr>
                <tr>
                    <td><span class="method put">PUT</span></td>
                    <td class="path">/customers/{id}</td>
                    <td class="desc">Update a customer</td>
                </tr>
                <tr>
                    <td><span class="method delete">DELETE</span></td>
                    <td class="path">/customers/{id}</td>
                    <td class="desc">Delete a customer (soft delete)</td>
                </tr>
            </tbody>
        </table>

        <div class="fields-title">Query Parameters — GET /customers</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">search</td><td>string</td><td class="optional">No</td><td>Case-insensitive partial match on nickname or mobile</td></tr>
                <tr><td class="field-name">branch_id</td><td>integer</td><td class="optional">No</td><td>Filter results to a specific branch (admin only; ignored for staff/delivery)</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Fields</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">nickname</td><td>string</td><td class="required">Yes</td><td>Must be unique</td></tr>
                <tr><td class="field-name">mobile</td><td>string</td><td class="optional">No</td><td>Must be unique</td></tr>
                <tr><td class="field-name">address</td><td>string</td><td class="optional">No</td><td></td></tr>
                <tr><td class="field-name">notes</td><td>string</td><td class="optional">No</td><td></td></tr>
                <tr><td class="field-name">delivery_fee</td><td>decimal</td><td class="optional">No</td><td>Defaults to 75.00</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Response</div>
        <div class="code-block"><span class="key">"mobile"</span>: <span class="str">"09171234567"</span>,
<span class="key">"nickname"</span>: <span class="str">"Maria Santos"</span>,
<span class="key">"address"</span>: <span class="str">"123 Main St"</span>,
<span class="key">"notes"</span>: <span class="str">""</span>,
<span class="key">"deliveryFee"</span>: <span class="num">75</span>,
<span class="key">"created_at"</span>: <span class="str">"2024-01-01 00:00:00"</span>,
<span class="key">"updated_at"</span>: <span class="str">"2024-01-01 00:00:00"</span></div>
    </section>

    <hr class="divider">

    {{-- Orders --}}
    <section id="orders">
        <div class="section-title">Orders</div>

        <table class="endpoint-table">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Endpoint</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/orders?from=YYYY-MM-DD&amp;to=YYYY-MM-DD</td>
                    <td class="desc">List orders (cursor-paginated, 30 per page); optionally filter by date range, status, or customer name</td>
                </tr>
                <tr>
                    <td><span class="method post">POST</span></td>
                    <td class="path">/orders</td>
                    <td class="desc">Create an order</td>
                </tr>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/orders/{orderNumber}</td>
                    <td class="desc">Get an order with items</td>
                </tr>
                <tr>
                    <td><span class="method put">PUT</span></td>
                    <td class="path">/orders/{orderNumber}</td>
                    <td class="desc">Update an order</td>
                </tr>
                <tr>
                    <td><span class="method delete">DELETE</span></td>
                    <td class="path">/orders/{orderNumber}</td>
                    <td class="desc">Delete an order</td>
                </tr>
                <tr>
                    <td><span class="method patch">PATCH</span></td>
                    <td class="path">/orders/{orderNumber}/payment-status</td>
                    <td class="desc">Update only the payment status of an order</td>
                </tr>
                <tr>
                    <td><span class="method patch">PATCH</span></td>
                    <td class="path">/orders/{orderNumber}/order-status</td>
                    <td class="desc">Update only the order status of an order</td>
                </tr>
                <tr>
                    <td><span class="method post">POST</span></td>
                    <td class="path">/orders/statuses</td>
                    <td class="desc">Bulk-fetch payment and order status for a list of order numbers (mobile sync)</td>
                </tr>
            </tbody>
        </table>

        <div class="fields-title">GET /orders — Query Parameters</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">from</td><td>date</td><td class="optional">No</td><td>YYYY-MM-DD — start of date range (inclusive). Must be used with <code>to</code></td></tr>
                <tr><td class="field-name">to</td><td>date</td><td class="optional">No</td><td>YYYY-MM-DD — end of date range (inclusive). Must be used with <code>from</code></td></tr>
                <tr><td class="field-name">payment_status</td><td>string</td><td class="optional">No</td><td>Filter by payment status. Values: <code>unpaid</code>, <code>paid</code></td></tr>
                <tr><td class="field-name">order_status</td><td>string</td><td class="optional">No</td><td>Filter by order status. Values: <code>in_progress</code>, <code>completed</code></td></tr>
                <tr><td class="field-name">fulfillment_type</td><td>string</td><td class="optional">No</td><td>Filter by fulfillment type (e.g. <code>pickup</code>, <code>delivery</code>)</td></tr>
                <tr><td class="field-name">search</td><td>string</td><td class="optional">No</td><td>Case-insensitive partial match on customer nickname</td></tr>
                <tr><td class="field-name">cursor</td><td>string</td><td class="optional">No</td><td>Opaque cursor string returned in <code>nextCursor</code> / <code>prevCursor</code> of a previous response. Omit for the first page.</td></tr>
                <tr><td class="field-name">branch_id</td><td>integer</td><td class="optional">No</td><td>Filter results to a specific branch (admin only; ignored for staff/delivery)</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Order Fields</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">customer_id</td><td>integer</td><td class="required">Yes</td><td>Must exist in customers</td></tr>
                <tr><td class="field-name">fulfillmentType</td><td>string</td><td class="required">Yes</td><td>walk-in | pickup-deliver</td></tr>
                <tr><td class="field-name">subtotal</td><td>decimal</td><td class="required">Yes</td><td></td></tr>
                <tr><td class="field-name">deliveryFee</td><td>decimal</td><td class="required">Yes</td><td></td></tr>
                <tr><td class="field-name">total</td><td>decimal</td><td class="required">Yes</td><td></td></tr>
                <tr><td class="field-name">createdAt</td><td>string</td><td class="optional">No</td><td>ISO 8601 client timestamp</td></tr>
                <tr><td class="field-name">paymentStatus</td><td>string</td><td class="optional">No</td><td>unpaid | paid_gcash | paid_cash | paid_others | paid_bank (default: unpaid)</td></tr>
                <tr><td class="field-name">orderStatus</td><td>string</td><td class="optional">No</td><td>in_progress | ready | completed (default: in_progress)</td></tr>
                <tr><td class="field-name">items</td><td>array</td><td class="required">Yes</td><td>At least 1 item required</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Item Fields</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">itemId</td><td>string</td><td class="required">Yes</td><td>Snapshot — not validated against items table</td></tr>
                <tr><td class="field-name">label</td><td>string</td><td class="required">Yes</td><td>Snapshot of item name</td></tr>
                <tr><td class="field-name">qty</td><td>integer</td><td class="required">Yes</td><td></td></tr>
                <tr><td class="field-name">price</td><td>decimal</td><td class="required">Yes</td><td></td></tr>
            </tbody>
        </table>

        <div class="fields-title">PATCH /orders/{orderNumber}/payment-status — Request Body</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">paymentStatus</td><td>string</td><td class="required">Yes</td><td>unpaid | paid_cash | paid_gcash | paid_others | paid_bank</td></tr>
            </tbody>
        </table>

        <div class="fields-title">PATCH /orders/{orderNumber}/order-status — Request Body</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">orderStatus</td><td>string</td><td class="required">Yes</td><td>in_progress | ready | completed</td></tr>
            </tbody>
        </table>

        <div class="fields-title">GET /orders Response (cursor-paginated)</div>
        <div class="code-block"><span class="key">"data"</span>: [
  {
    <span class="key">"id"</span>: <span class="str">"uuid-..."</span>,
    <span class="key">"orderNumber"</span>: <span class="str">"ORD-00001"</span>,
    <span class="key">"customer_id"</span>: <span class="num">1</span>,
    <span class="key">"customer"</span>: { <span class="key">"mobile"</span>: <span class="str">"09171234567"</span>, <span class="key">"nickname"</span>: <span class="str">"Maria Santos"</span>, ... },
    <span class="key">"fulfillmentType"</span>: <span class="str">"delivery"</span>,
    <span class="key">"subtotal"</span>: <span class="num">150</span>,
    <span class="key">"deliveryFee"</span>: <span class="num">75</span>,
    <span class="key">"total"</span>: <span class="num">225</span>,
    <span class="key">"createdAt"</span>: <span class="str">"2026-03-10T00:00:00.000000Z"</span>,
    <span class="key">"paymentStatus"</span>: <span class="str">"unpaid"</span>,
    <span class="key">"orderStatus"</span>: <span class="str">"in_progress"</span>,
    <span class="key">"items"</span>: [ { <span class="key">"itemId"</span>: <span class="str">"item-uuid-123"</span>, <span class="key">"label"</span>: <span class="str">"Polo Shirt"</span>, <span class="key">"qty"</span>: <span class="num">2</span>, <span class="key">"price"</span>: <span class="num">75</span>, <span class="key">"color"</span>: <span class="str">"white"</span>, <span class="key">"shape"</span>: <span class="str">"round"</span> } ],
    <span class="key">"paymentHistory"</span>: [ { <span class="key">"fromStatus"</span>: <span class="str">"unpaid"</span>, <span class="key">"toStatus"</span>: <span class="str">"paid_cash"</span>, <span class="key">"changedAt"</span>: <span class="str">"2026-04-10T08:30:00.000000Z"</span>, <span class="key">"updatedBy"</span>: { <span class="key">"id"</span>: <span class="num">3</span>, <span class="key">"name"</span>: <span class="str">"Juan dela Cruz"</span>, <span class="key">"role"</span>: <span class="str">"staff"</span> } } ],
    <span class="key">"orderStatusHistory"</span>: [ { <span class="key">"fromStatus"</span>: <span class="str">"in_progress"</span>, <span class="key">"toStatus"</span>: <span class="str">"completed"</span>, <span class="key">"changedAt"</span>: <span class="str">"2026-04-10T09:00:00.000000Z"</span>, <span class="key">"updatedBy"</span>: { <span class="key">"id"</span>: <span class="num">3</span>, <span class="key">"name"</span>: <span class="str">"Juan dela Cruz"</span>, <span class="key">"role"</span>: <span class="str">"staff"</span> } } ]
  }
],
<span class="key">"nextCursor"</span>: <span class="str">"eyJjcmVhdGVkX2F0IjoiMjAyNi0wMy0xMCIsImlkIjoiLi4uIn0"</span>,
<span class="key">"prevCursor"</span>: <span class="str">null</span>,
<span class="key">"hasMore"</span>: <span class="bool">true</span>,
<span class="key">"perPage"</span>: <span class="num">30</span></div>

        <div class="fields-title">GET /orders/{orderNumber} Response (order detail)</div>
        <div class="code-block">{
  <span class="key">"orderNumber"</span>: <span class="str">"ORD-00001"</span>,
  <span class="key">"customer_id"</span>: <span class="str">"uuid-..."</span>,
  <span class="key">"customer"</span>: { ... },
  <span class="key">"fulfillmentType"</span>: <span class="str">"delivery"</span>,
  <span class="key">"subtotal"</span>: <span class="num">150</span>,
  <span class="key">"deliveryFee"</span>: <span class="num">75</span>,
  <span class="key">"discountAmount"</span>: <span class="num">0</span>,
  <span class="key">"total"</span>: <span class="num">225</span>,
  <span class="key">"createdAt"</span>: <span class="str">"2026-03-10T00:00:00.000000Z"</span>,
  <span class="key">"paymentStatus"</span>: <span class="str">"paid_cash"</span>,
  <span class="key">"orderStatus"</span>: <span class="str">"completed"</span>,
  <span class="key">"branch"</span>: { <span class="key">"id"</span>: <span class="num">1</span>, <span class="key">"name"</span>: <span class="str">"Main Branch"</span> },
  <span class="key">"items"</span>: [ ... ],
  <span class="key">"paymentHistory"</span>: [
    {
      <span class="key">"fromStatus"</span>: <span class="str">"unpaid"</span>,
      <span class="key">"toStatus"</span>: <span class="str">"paid_cash"</span>,
      <span class="key">"changedAt"</span>: <span class="str">"2026-04-10T08:30:00.000000Z"</span>,
      <span class="key">"updatedBy"</span>: { <span class="key">"id"</span>: <span class="num">3</span>, <span class="key">"name"</span>: <span class="str">"Juan dela Cruz"</span>, <span class="key">"role"</span>: <span class="str">"staff"</span> }
    }
  ],
  <span class="key">"orderStatusHistory"</span>: [
    {
      <span class="key">"fromStatus"</span>: <span class="str">"in_progress"</span>,
      <span class="key">"toStatus"</span>: <span class="str">"ready"</span>,
      <span class="key">"changedAt"</span>: <span class="str">"2026-04-10T07:00:00.000000Z"</span>,
      <span class="key">"updatedBy"</span>: { <span class="key">"id"</span>: <span class="num">3</span>, <span class="key">"name"</span>: <span class="str">"Juan dela Cruz"</span>, <span class="key">"role"</span>: <span class="str">"staff"</span> }
    },
    {
      <span class="key">"fromStatus"</span>: <span class="str">"ready"</span>,
      <span class="key">"toStatus"</span>: <span class="str">"completed"</span>,
      <span class="key">"changedAt"</span>: <span class="str">"2026-04-10T08:30:00.000000Z"</span>,
      <span class="key">"updatedBy"</span>: { <span class="key">"id"</span>: <span class="num">3</span>, <span class="key">"name"</span>: <span class="str">"Juan dela Cruz"</span>, <span class="key">"role"</span>: <span class="str">"staff"</span> }
    }
  ]
}</div>
        <p style="margin-top:8px;font-size:13px;color:#666;"><strong>Note:</strong> <code>updatedBy</code> is <code>null</code> for history records created before this feature was added.</p>

        <div class="fields-title">POST /orders/statuses — Request Body</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">orderNumbers</td><td>string[]</td><td class="required">Yes</td><td>Array of order number UUIDs to look up. Min 1, max 200.</td></tr>
            </tbody>
        </table>

        <div class="fields-title">POST /orders/statuses — Response</div>
        <p style="font-size:13px;color:#666;margin-bottom:6px;">Returns only orders that exist and belong to the authenticated tenant. Unknown order numbers are silently skipped.</p>
        <div class="code-block">[
  { <span class="key">"orderNumber"</span>: <span class="str">"uuid1"</span>, <span class="key">"paymentStatus"</span>: <span class="str">"paid_cash"</span>, <span class="key">"orderStatus"</span>: <span class="str">"ready"</span> },
  { <span class="key">"orderNumber"</span>: <span class="str">"uuid2"</span>, <span class="key">"paymentStatus"</span>: <span class="str">"unpaid"</span>, <span class="key">"orderStatus"</span>: <span class="str">"in_progress"</span> }
]</div>
    </section>

    <hr class="divider">

    {{-- Expenses --}}
    <section id="expenses">
        <div class="section-title">Expenses</div>

        <table class="endpoint-table">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Endpoint</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/expenses</td>
                    <td class="desc">List all expenses</td>
                </tr>
                <tr>
                    <td><span class="method post">POST</span></td>
                    <td class="path">/expenses</td>
                    <td class="desc">Create an expense</td>
                </tr>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/expenses/{id}</td>
                    <td class="desc">Get an expense</td>
                </tr>
                <tr>
                    <td><span class="method put">PUT</span></td>
                    <td class="path">/expenses/{id}</td>
                    <td class="desc">Update an expense</td>
                </tr>
                <tr>
                    <td><span class="method delete">DELETE</span></td>
                    <td class="path">/expenses/{id}</td>
                    <td class="desc">Delete an expense</td>
                </tr>
            </tbody>
        </table>

        <div class="fields-title">Query Parameters — GET /expenses</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">branch_id</td><td>integer</td><td class="optional">No</td><td>Filter results to a specific branch (admin only; ignored for staff/delivery)</td></tr>
                <tr><td class="field-name">expense_category_id</td><td>integer</td><td class="optional">No</td><td>Filter results to a specific expense category</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Fields</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">description</td><td>string</td><td class="required">Yes</td><td></td></tr>
                <tr><td class="field-name">amount</td><td>decimal</td><td class="required">Yes</td><td>Min 0</td></tr>
                <tr><td class="field-name">expense_date</td><td>date</td><td class="required">Yes</td><td>YYYY-MM-DD</td></tr>
                <tr><td class="field-name">note</td><td>string</td><td class="optional">No</td><td></td></tr>
                <tr><td class="field-name">expense_category_id</td><td>integer</td><td class="optional">No*</td><td>Must reference an <a href="#expense-categories">expense category</a> belonging to the tenant. *Temporarily optional for backward compatibility — omitted values default to the tenant's "Uncategorized" category. New clients should always send this; it will become strictly required once all clients are updated.</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Response</div>
        <div class="code-block"><span class="key">"id"</span>: <span class="num">1</span>,
<span class="key">"description"</span>: <span class="str">"Detergent supply"</span>,
<span class="key">"amount"</span>: <span class="num">500</span>,
<span class="key">"expense_date"</span>: <span class="str">"2026-03-09"</span>,
<span class="key">"note"</span>: <span class="null">null</span>,
<span class="key">"expense_category_id"</span>: <span class="num">2</span>,
<span class="key">"category"</span>: { <span class="key">"id"</span>: <span class="num">2</span>, <span class="key">"name"</span>: <span class="str">"Supplies"</span>, <span class="key">"is_active"</span>: <span class="bool">true</span>, <span class="key">"sort_order"</span>: <span class="num">1</span>, <span class="key">"created_at"</span>: <span class="str">"2026-03-09 00:00:00"</span>, <span class="key">"updated_at"</span>: <span class="str">"2026-03-09 00:00:00"</span> },
<span class="key">"user_id"</span>: <span class="num">1</span>,
<span class="key">"created_at"</span>: <span class="str">"2026-03-09 00:00:00"</span>,
<span class="key">"updated_at"</span>: <span class="str">"2026-03-09 00:00:00"</span></div>
    </section>

    <hr class="divider">

    {{-- Expense Categories --}}
    <section id="expense-categories">
        <div class="section-title">Expense Categories</div>
        <p style="font-size:13px;color:#666;margin-bottom:6px;">User-managed list used to categorize expenses. Every tenant is seeded with the defaults below; users may rename, deactivate, reorder, or add their own. <code>expense_category_id</code> should be sent on every new expense (temporarily optional — see <a href="#expenses">Expenses</a>).</p>

        <table class="endpoint-table">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Endpoint</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/expense-categories</td>
                    <td class="desc">List all expense categories</td>
                </tr>
                <tr>
                    <td><span class="method post">POST</span></td>
                    <td class="path">/expense-categories</td>
                    <td class="desc">Create an expense category</td>
                </tr>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/expense-categories/{id}</td>
                    <td class="desc">Get an expense category</td>
                </tr>
                <tr>
                    <td><span class="method put">PUT</span></td>
                    <td class="path">/expense-categories/{id}</td>
                    <td class="desc">Update an expense category</td>
                </tr>
                <tr>
                    <td><span class="method delete">DELETE</span></td>
                    <td class="path">/expense-categories/{id}</td>
                    <td class="desc">Delete an expense category (fails if any expenses use it)</td>
                </tr>
            </tbody>
        </table>

        <div class="fields-title">Fields</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">name</td><td>string</td><td class="required">Yes</td><td>Must be unique per tenant</td></tr>
                <tr><td class="field-name">is_active</td><td>boolean</td><td class="optional">No</td><td>Defaults to true</td></tr>
                <tr><td class="field-name">sort_order</td><td>integer</td><td class="optional">No</td><td>Display priority; lower = first. Defaults to next available position</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Response</div>
        <div class="code-block"><span class="key">"id"</span>: <span class="num">1</span>,
<span class="key">"name"</span>: <span class="str">"Utilities"</span>,
<span class="key">"is_active"</span>: <span class="bool">true</span>,
<span class="key">"sort_order"</span>: <span class="num">0</span>,
<span class="key">"created_at"</span>: <span class="str">"2026-03-09 00:00:00"</span>,
<span class="key">"updated_at"</span>: <span class="str">"2026-03-09 00:00:00"</span></div>
        <p style="font-size:13px;color:#666;margin-top:6px;">Default categories seeded per tenant: <code>Utilities</code>, <code>Supplies</code>, <code>Maintenance</code>, <code>Salaries</code>, <code>Others</code>. Tenants that had expenses before this feature shipped also received an <code>Uncategorized</code> category, assigned to their pre-existing expenses.</p>
    </section>

    <hr class="divider">

    {{-- Reports --}}
    <section id="reports">
        <div class="section-title">Reports</div>

        <table class="endpoint-table">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Endpoint</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/reports/sales</td>
                    <td class="desc">Sales summary (Gross Sales, Discounts, Net Sales, COGS, Gross Profit)</td>
                </tr>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/reports/sales-by-item</td>
                    <td class="desc">Sales breakdown by item — qty sold, revenue, cost, and profit per item</td>
                </tr>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/reports/sales-by-payment-type</td>
                    <td class="desc">Sales breakdown by payment type — transaction count, payment amount, and net amount per method</td>
                </tr>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/reports/expenses-by-category</td>
                    <td class="desc">Expense breakdown by category — transaction count and total amount per category</td>
                </tr>
            </tbody>
        </table>

        <div class="fields-title">Query Parameters — all report endpoints</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">from</td><td>datetime</td><td class="required">Yes</td><td>YYYY-MM-DD or YYYY-MM-DD HH:MM:SS — start of range; defaults to 00:00:00 when no time given</td></tr>
                <tr><td class="field-name">to</td><td>datetime</td><td class="required">Yes</td><td>YYYY-MM-DD or YYYY-MM-DD HH:MM:SS — end of range, must be ≥ from; defaults to 23:59:59 when no time given</td></tr>
                <tr><td class="field-name">branch_id</td><td>integer</td><td class="optional">No</td><td>Filter results to a specific branch (admin only; ignored for staff/delivery)</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Query Parameters — GET /reports/sales-by-payment-type</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">date_basis</td><td>string</td><td class="optional">No</td><td><code>created</code> (default) filters by order creation date, same as other report endpoints. <code>paid</code> filters by the date each order most recently transitioned into its current paid status — see notes below</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Response — GET /reports/sales</div>
        <div class="code-block"><span class="key">"from"</span>: <span class="str">"2026-03-01 00:00:00"</span>,
<span class="key">"to"</span>: <span class="str">"2026-03-31 23:59:59"</span>,
<span class="key">"branch"</span>: { <span class="key">"id"</span>: <span class="num">1</span>, <span class="key">"name"</span>: <span class="str">"Main Branch"</span>, <span class="key">"address"</span>: <span class="str">"123 St"</span>, <span class="key">"phone"</span>: <span class="str">"09xx"</span> },
<span class="key">"grossSales"</span>: <span class="num">12500.00</span>,
<span class="comm">// grossSales = sum of subtotal for ALL orders (paid + unpaid)</span>
<span class="key">"discounts"</span>: <span class="num">500.00</span>,
<span class="comm">// discounts = sum of discount_amount for all orders</span>
<span class="key">"netSales"</span>: <span class="num">12000.00</span>,
<span class="comm">// netSales = grossSales - discounts</span>
<span class="key">"costOfGoods"</span>: <span class="num">5000.00</span>,
<span class="key">"grossProfit"</span>: <span class="num">7000.00</span>,
<span class="comm">// grossProfit = netSales - costOfGoods</span>
<span class="key">"expenses"</span>: <span class="num">1200.00</span>,
<span class="key">"netProfit"</span>: <span class="num">5800.00</span>,
<span class="comm">// netProfit = grossProfit - expenses</span>
<span class="key">"collected"</span>: <span class="num">10500.00</span>,
<span class="comm">// collected = net amount (subtotal - discount) from paid orders</span>
<span class="key">"outstanding"</span>: <span class="num">1500.00</span>,
<span class="comm">// outstanding = net amount (subtotal - discount) from unpaid orders; collected + outstanding = netSales</span>
<span class="key">"unpaidOrders"</span>: <span class="num">3</span>,
<span class="comm">// unpaidOrders = count of unpaid orders</span>
<span class="key">"group_by"</span>: <span class="str">"day"</span>,
<span class="comm">// "hour" when from == to (single day), "day" when range ≤ 31 days, "month" when range > 31 days</span>
<span class="key">"series"</span>: [
  { <span class="key">"label"</span>: <span class="str">"2026-03-01"</span>, <span class="key">"net_sales"</span>: <span class="num">400.00</span> },
  { <span class="key">"label"</span>: <span class="str">"2026-03-02"</span>, <span class="key">"net_sales"</span>: <span class="num">750.00</span> }
  <span class="comm">// label format: "HH:00" (hour), "YYYY-MM-DD" (day), "YYYY-MM" (month)</span>
  <span class="comm">// zero-filled — all periods in range are included even if no sales</span>
]</div>

        <div class="fields-title">Response — GET /reports/sales-by-item</div>
        <div class="code-block"><span class="key">"from"</span>: <span class="str">"2026-03-01"</span>,
<span class="key">"to"</span>: <span class="str">"2026-03-31"</span>,
<span class="key">"branch"</span>: { <span class="key">"id"</span>: <span class="num">1</span>, <span class="key">"name"</span>: <span class="str">"Main Branch"</span>, <span class="key">"address"</span>: <span class="str">"123 St"</span>, <span class="key">"phone"</span>: <span class="str">"09xx"</span> },
<span class="key">"items"</span>: [
  {
    <span class="key">"item_id"</span>: <span class="str">"5"</span>,
    <span class="key">"label"</span>: <span class="str">"Wash &amp; Fold"</span>,
    <span class="key">"category"</span>: <span class="str">"Laundry"</span>,
    <span class="key">"qty"</span>: <span class="num">50</span>,
    <span class="key">"net_sales"</span>: <span class="num">2500.00</span>,
    <span class="key">"cost_of_goods"</span>: <span class="num">500.00</span>,
    <span class="key">"gross_profit"</span>: <span class="num">2000.00</span>
  }
],
<span class="key">"top_items"</span>: [
  {
    <span class="key">"item_id"</span>: <span class="str">"5"</span>,
    <span class="key">"label"</span>: <span class="str">"Wash &amp; Fold"</span>,
    <span class="key">"category"</span>: <span class="str">"Laundry"</span>,
    <span class="key">"qty"</span>: <span class="num">50</span>,
    <span class="key">"net_sales"</span>: <span class="num">2500.00</span>,
    <span class="key">"cost_of_goods"</span>: <span class="num">500.00</span>,
    <span class="key">"gross_profit"</span>: <span class="num">2000.00</span>
  }
]</div>

        <div class="fields-title">Response — GET /reports/sales-by-payment-type</div>
        <div class="code-block"><span class="key">"from"</span>: <span class="str">"2026-03-01"</span>,
<span class="key">"to"</span>: <span class="str">"2026-03-31"</span>,
<span class="key">"branch"</span>: { <span class="key">"id"</span>: <span class="num">1</span>, <span class="key">"name"</span>: <span class="str">"Main Branch"</span>, <span class="key">"address"</span>: <span class="str">"123 St"</span>, <span class="key">"phone"</span>: <span class="str">"09xx"</span> },
<span class="key">"breakdown"</span>: [
  {
    <span class="key">"payment_method"</span>: <span class="str">"paid_cash"</span>,
    <span class="key">"transactions"</span>: <span class="num">80</span>,
    <span class="key">"payment_amount"</span>: <span class="num">8000.00</span>
  },
  {
    <span class="key">"payment_method"</span>: <span class="str">"paid_gcash"</span>,
    <span class="key">"transactions"</span>: <span class="num">30</span>,
    <span class="key">"payment_amount"</span>: <span class="num">4500.00</span>
  },
  {
    <span class="key">"payment_method"</span>: <span class="str">"paid_bank"</span>,
    <span class="key">"transactions"</span>: <span class="num">10</span>,
    <span class="key">"payment_amount"</span>: <span class="num">2000.00</span>
  },
  {
    <span class="key">"payment_method"</span>: <span class="str">"paid_others"</span>,
    <span class="key">"transactions"</span>: <span class="num">5</span>,
    <span class="key">"payment_amount"</span>: <span class="num">750.00</span>
  },
  {
    <span class="key">"payment_method"</span>: <span class="str">"unpaid"</span>,
    <span class="key">"transactions"</span>: <span class="num">3</span>,
    <span class="key">"payment_amount"</span>: <span class="num">1500.00</span>
  }
],
<span class="key">"unpaid"</span>: {
  <span class="key">"transactions"</span>: <span class="num">3</span>,
  <span class="key">"amount"</span>: <span class="num">1500.00</span>
}</div>
<span class="comm">// unpaid block is included only for the default date_basis=created</span>

        <div class="fields-title">Response — GET /reports/sales-by-payment-type?date_basis=paid</div>
        <div class="code-block"><span class="key">"from"</span>: <span class="str">"2026-03-01"</span>,
<span class="key">"to"</span>: <span class="str">"2026-03-31"</span>,
<span class="key">"branch"</span>: { <span class="key">"id"</span>: <span class="num">1</span>, <span class="key">"name"</span>: <span class="str">"Main Branch"</span>, <span class="key">"address"</span>: <span class="str">"123 St"</span>, <span class="key">"phone"</span>: <span class="str">"09xx"</span> },
<span class="key">"breakdown"</span>: [
  {
    <span class="key">"payment_method"</span>: <span class="str">"paid_cash"</span>,
    <span class="key">"transactions"</span>: <span class="num">78</span>,
    <span class="key">"payment_amount"</span>: <span class="num">7800.00</span>
  },
  {
    <span class="key">"payment_method"</span>: <span class="str">"paid_gcash"</span>,
    <span class="key">"transactions"</span>: <span class="num">31</span>,
    <span class="key">"payment_amount"</span>: <span class="num">4650.00</span>
  }
]</div>
<span class="comm">// date_basis=paid: breakdown is keyed by an order's CURRENT payment_status, bucketed by the</span>
<span class="comm">// changed_at of the most recent payment_history row where to_status = that current status</span>
<span class="comm">// (so a correction like unpaid -&gt; paid_cash -&gt; unpaid -&gt; paid_gcash counts once, under</span>
<span class="comm">// paid_gcash, on the date of the second transition). payment_amount = subtotal - discount_amount,</span>
<span class="comm">// matching "collected" in /reports/sales. Orders currently unpaid have no payment date and are</span>
<span class="comm">// excluded entirely — the "unpaid" key is omitted from the response under this basis.</span>

        <div class="fields-title">Response — GET /reports/expenses-by-category</div>
        <div class="code-block"><span class="key">"from"</span>: <span class="str">"2026-03-01"</span>,
<span class="key">"to"</span>: <span class="str">"2026-03-31"</span>,
<span class="key">"branch"</span>: { <span class="key">"id"</span>: <span class="num">1</span>, <span class="key">"name"</span>: <span class="str">"Main Branch"</span>, <span class="key">"address"</span>: <span class="str">"123 St"</span>, <span class="key">"phone"</span>: <span class="str">"09xx"</span> },
<span class="key">"breakdown"</span>: [
  {
    <span class="key">"category_id"</span>: <span class="num">1</span>,
    <span class="key">"category"</span>: <span class="str">"Utilities"</span>,
    <span class="key">"transactions"</span>: <span class="num">4</span>,
    <span class="key">"amount"</span>: <span class="num">6700.00</span>
  },
  {
    <span class="key">"category_id"</span>: <span class="num">2</span>,
    <span class="key">"category"</span>: <span class="str">"Supplies"</span>,
    <span class="key">"transactions"</span>: <span class="num">6</span>,
    <span class="key">"amount"</span>: <span class="num">4250.00</span>
  }
],
<span class="key">"total"</span>: <span class="num">10950.00</span></div>
    </section>

    <hr class="divider">

    {{-- Categories --}}
    <section id="categories">
        <div class="section-title">Categories</div>

        <table class="endpoint-table">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Endpoint</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/categories</td>
                    <td class="desc">List all categories</td>
                </tr>
                <tr>
                    <td><span class="method post">POST</span></td>
                    <td class="path">/categories</td>
                    <td class="desc">Create a category</td>
                </tr>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/categories/{id}</td>
                    <td class="desc">Get a category</td>
                </tr>
                <tr>
                    <td><span class="method put">PUT</span></td>
                    <td class="path">/categories/{id}</td>
                    <td class="desc">Update a category</td>
                </tr>
                <tr>
                    <td><span class="method delete">DELETE</span></td>
                    <td class="path">/categories/{id}</td>
                    <td class="desc">Delete a category</td>
                </tr>
            </tbody>
        </table>

        <div class="fields-title">Fields</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">name</td><td>string</td><td class="required">Yes</td><td>Must be unique</td></tr>
                <tr><td class="field-name">is_active</td><td>boolean</td><td class="optional">No</td><td>Defaults to true</td></tr>
                <tr><td class="field-name">sort_order</td><td>integer</td><td class="optional">No</td><td>Display priority; lower = first. Defaults to 0</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Response</div>
        <div class="code-block"><span class="key">"id"</span>: <span class="num">1</span>,
<span class="key">"name"</span>: <span class="str">"Dry Cleaning"</span>,
<span class="key">"is_active"</span>: <span class="bool">true</span>,
<span class="key">"sort_order"</span>: <span class="num">0</span>,
<span class="key">"created_at"</span>: <span class="str">"2024-01-01 00:00:00"</span>,
<span class="key">"updated_at"</span>: <span class="str">"2024-01-01 00:00:00"</span></div>
    </section>

    <hr class="divider">

    {{-- Items --}}
    <section id="items">
        <div class="section-title">Items</div>

        <table class="endpoint-table">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Endpoint</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/items</td>
                    <td class="desc">List all items</td>
                </tr>
                <tr>
                    <td><span class="method post">POST</span></td>
                    <td class="path">/items</td>
                    <td class="desc">Create an item</td>
                </tr>
                <tr>
                    <td><span class="method get">GET</span></td>
                    <td class="path">/items/{id}</td>
                    <td class="desc">Get an item</td>
                </tr>
                <tr>
                    <td><span class="method put">PUT</span></td>
                    <td class="path">/items/{id}</td>
                    <td class="desc">Update an item</td>
                </tr>
                <tr>
                    <td><span class="method delete">DELETE</span></td>
                    <td class="path">/items/{id}</td>
                    <td class="desc">Delete an item</td>
                </tr>
            </tbody>
        </table>

        <div class="fields-title">Query Parameters — GET /items</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">branch_id</td><td>integer</td><td class="optional">No</td><td>Filter results to a specific branch (admin only; ignored for staff/delivery)</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Fields</div>
        <table class="fields-table">
            <thead>
                <tr><th>Field</th><th>Type</th><th>Required</th><th>Notes</th></tr>
            </thead>
            <tbody>
                <tr><td class="field-name">name</td><td>string</td><td class="required">Yes</td><td></td></tr>
                <tr><td class="field-name">price</td><td>decimal</td><td class="required">Yes</td><td>Selling price</td></tr>
                <tr><td class="field-name">cost</td><td>decimal</td><td class="required">Yes</td><td>Cost price</td></tr>
                <tr><td class="field-name">description</td><td>string</td><td class="optional">No</td><td></td></tr>
                <tr><td class="field-name">color</td><td>string</td><td class="optional">No</td><td>e.g. #FF5733</td></tr>
                <tr><td class="field-name">shape</td><td>string</td><td class="optional">No</td><td>circle, square, star, diamond</td></tr>
                <tr><td class="field-name">is_active</td><td>boolean</td><td class="optional">No</td><td>Defaults to true</td></tr>
                <tr><td class="field-name">sort_order</td><td>integer</td><td class="optional">No</td><td>Display priority; lower = first. Auto-assigned if omitted</td></tr>
                <tr><td class="field-name">category_id</td><td>integer</td><td class="optional">No</td><td>Must exist in categories</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Response</div>
        <div class="code-block"><span class="key">"id"</span>: <span class="num">1</span>,
<span class="key">"name"</span>: <span class="str">"Polo Shirt"</span>,
<span class="key">"description"</span>: <span class="str">"Regular wash and press"</span>,
<span class="key">"color"</span>: <span class="str">"#3B82F6"</span>,
<span class="key">"shape"</span>: <span class="str">"circle"</span>,
<span class="key">"price"</span>: <span class="str">"50.00"</span>,
<span class="key">"cost"</span>: <span class="str">"20.00"</span>,
<span class="key">"is_active"</span>: <span class="bool">true</span>,
<span class="key">"sort_order"</span>: <span class="num">0</span>,
<span class="key">"category"</span>: {
  <span class="key">"id"</span>: <span class="num">1</span>,
  <span class="key">"name"</span>: <span class="str">"Dry Cleaning"</span>,
  <span class="key">"is_active"</span>: <span class="bool">true</span>
},
<span class="key">"created_at"</span>: <span class="str">"2024-01-01 00:00:00"</span>,
<span class="key">"updated_at"</span>: <span class="str">"2024-01-01 00:00:00"</span></div>
    </section>

</main>

</body>
</html>
