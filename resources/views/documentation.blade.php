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
    <a href="#customers">Customers</a>
    <a href="#orders">Orders</a>
    <a href="#expenses">Expenses</a>
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
                    <td class="desc">List all customers</td>
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
                    <td class="path">/orders</td>
                    <td class="desc">List all orders (with items)</td>
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
                <tr><td class="field-name">paymentStatus</td><td>string</td><td class="optional">No</td><td>unpaid | pending | paid_gcash | paid_cash (default: unpaid)</td></tr>
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
                <tr><td class="field-name">unit</td><td>string</td><td class="required">Yes</td><td></td></tr>
                <tr><td class="field-name">qty</td><td>integer</td><td class="required">Yes</td><td></td></tr>
                <tr><td class="field-name">price</td><td>decimal</td><td class="required">Yes</td><td></td></tr>
            </tbody>
        </table>

        <div class="fields-title">Response</div>
        <div class="code-block"><span class="key">"id"</span>: <span class="num">1</span>,
<span class="key">"orderNumber"</span>: <span class="str">"ORD-00001"</span>,
<span class="key">"customer_id"</span>: <span class="num">1</span>,
<span class="key">"customer"</span>: {
  <span class="key">"mobile"</span>: <span class="str">"09171234567"</span>,
  <span class="key">"nickname"</span>: <span class="str">"Maria Santos"</span>,
  <span class="key">"address"</span>: <span class="str">"123 Main St"</span>,
  <span class="key">"notes"</span>: <span class="str">""</span>,
  <span class="key">"deliveryFee"</span>: <span class="num">75</span>
},
<span class="key">"fulfillmentType"</span>: <span class="str">"delivery"</span>,
<span class="key">"subtotal"</span>: <span class="num">150</span>,
<span class="key">"deliveryFee"</span>: <span class="num">75</span>,
<span class="key">"total"</span>: <span class="num">225</span>,
<span class="key">"createdAt"</span>: <span class="str">"2026-03-10T00:00:00.000000Z"</span>,
<span class="key">"paymentStatus"</span>: <span class="str">"unpaid"</span>,
<span class="key">"orderStatus"</span>: <span class="str">"in_progress"</span>,
<span class="key">"items"</span>: [
  {
    <span class="key">"id"</span>: <span class="num">1</span>,
    <span class="key">"itemId"</span>: <span class="str">"item-uuid-123"</span>,
    <span class="key">"label"</span>: <span class="str">"Polo Shirt"</span>,
    <span class="key">"unit"</span>: <span class="str">"piece"</span>,
    <span class="key">"qty"</span>: <span class="num">2</span>,
    <span class="key">"price"</span>: <span class="num">75</span>
  }
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
            </tbody>
        </table>

        <div class="fields-title">Response</div>
        <div class="code-block"><span class="key">"id"</span>: <span class="num">1</span>,
<span class="key">"description"</span>: <span class="str">"Detergent supply"</span>,
<span class="key">"amount"</span>: <span class="num">500</span>,
<span class="key">"expense_date"</span>: <span class="str">"2026-03-09"</span>,
<span class="key">"note"</span>: <span class="null">null</span>,
<span class="key">"user_id"</span>: <span class="num">1</span>,
<span class="key">"created_at"</span>: <span class="str">"2026-03-09 00:00:00"</span>,
<span class="key">"updated_at"</span>: <span class="str">"2026-03-09 00:00:00"</span></div>
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
                <tr><td class="field-name">color</td><td>string</td><td class="optional">No</td><td>e.g. #FF5733</td></tr>
                <tr><td class="field-name">is_active</td><td>boolean</td><td class="optional">No</td><td>Defaults to true</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Response</div>
        <div class="code-block"><span class="key">"id"</span>: <span class="num">1</span>,
<span class="key">"name"</span>: <span class="str">"Dry Cleaning"</span>,
<span class="key">"color"</span>: <span class="str">"#FF5733"</span>,
<span class="key">"is_active"</span>: <span class="bool">true</span>,
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
                <tr><td class="field-name">image</td><td>string</td><td class="optional">No</td><td>Image path or URL</td></tr>
                <tr><td class="field-name">is_active</td><td>boolean</td><td class="optional">No</td><td>Defaults to true</td></tr>
                <tr><td class="field-name">category_id</td><td>integer</td><td class="optional">No</td><td>Must exist in categories</td></tr>
            </tbody>
        </table>

        <div class="fields-title">Response</div>
        <div class="code-block"><span class="key">"id"</span>: <span class="num">1</span>,
<span class="key">"name"</span>: <span class="str">"Polo Shirt"</span>,
<span class="key">"description"</span>: <span class="str">"Regular wash and press"</span>,
<span class="key">"image"</span>: <span class="null">null</span>,
<span class="key">"price"</span>: <span class="str">"50.00"</span>,
<span class="key">"cost"</span>: <span class="str">"20.00"</span>,
<span class="key">"is_active"</span>: <span class="bool">true</span>,
<span class="key">"category"</span>: {
  <span class="key">"id"</span>: <span class="num">1</span>,
  <span class="key">"name"</span>: <span class="str">"Dry Cleaning"</span>,
  <span class="key">"color"</span>: <span class="str">"#FF5733"</span>,
  <span class="key">"is_active"</span>: <span class="bool">true</span>
},
<span class="key">"created_at"</span>: <span class="str">"2024-01-01 00:00:00"</span>,
<span class="key">"updated_at"</span>: <span class="str">"2024-01-01 00:00:00"</span></div>
    </section>

</main>

</body>
</html>
