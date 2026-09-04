<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quinn's POS — Point of Sale for Laundry Businesses</title>
    <meta name="description" content="Run your laundry business on one system: orders, branches, customers, expenses, and reports. Start free for 30 days, no card required.">
    <style>
@include('marketing.styles')
    </style>
</head>
<body>

<header>
    <nav class="wrap">
        <a href="/" class="logo">
            <span class="logo-mark"><img src="{{ asset('images/quinns-logo-white-40.png') }}" alt=""></span>
            Quinn's POS
        </a>
        <div class="nav-links">
            <a href="#features">Features</a>
            <a href="#get-started">Free Trial</a>
        </div>
        <a href="#get-started" class="btn btn-primary">Get Started <span class="btn-arrow">→</span></a>
    </nav>
</header>

<main>

    {{-- Hero --}}
    <section class="hero dotted-bg">
        <div class="wrap hero-grid">
            <div>
                <span class="eyebrow-pill"><span class="dot"></span> Free trial open now</span>
                <h1>Run your laundry shop, fully booked</h1>
                <p class="lead">Orders, branches, customers, expenses, and sales reports — all in one point-of-sale platform, with dedicated apps for your admin, staff, and delivery team.</p>
                <div class="hero-ctas">
                    <a href="#get-started" class="btn btn-primary">Start your free trial <span class="btn-arrow">→</span></a>
                    <a href="#features" class="btn btn-secondary">See what's included</a>
                </div>
                <p class="hero-note">30 days free · No credit card required</p>
            </div>
            <div class="device-stack">
                <div class="device device-tablet">
                    <div class="device-screen">
                        <div class="screen-topbar">
                            <span class="screen-logo"><img src="{{ asset('images/quinns-logo-white-40.png') }}" alt=""></span>
                            <span class="screen-topbar-title">Admin · Dashboard</span>
                        </div>
                        <div class="screen-stats">
                            <div class="screen-stat">
                                <span class="screen-stat-label">Today's Orders</span>
                                <span class="screen-stat-value">24</span>
                            </div>
                            <div class="screen-stat">
                                <span class="screen-stat-label">Revenue</span>
                                <span class="screen-stat-value">₱12,450</span>
                            </div>
                            <div class="screen-stat">
                                <span class="screen-stat-label">Branches</span>
                                <span class="screen-stat-value">3</span>
                            </div>
                        </div>
                        <div class="screen-list">
                            <div class="screen-row">
                                <span class="screen-row-bar"></span>
                                <span class="screen-chip chip-green">Ready</span>
                            </div>
                            <div class="screen-row">
                                <span class="screen-row-bar"></span>
                                <span class="screen-chip chip-blue">Washing</span>
                            </div>
                            <div class="screen-row">
                                <span class="screen-row-bar"></span>
                                <span class="screen-chip chip-gray">Picked up</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="device device-phone">
                    <div class="device-notch"></div>
                    <div class="device-screen">
                        <div class="screen-topbar">
                            <span class="screen-logo driver"><img src="{{ asset('images/quinns-logo-white-40.png') }}" alt=""></span>
                            <span class="screen-topbar-title">Driver</span>
                        </div>
                        <p class="screen-subtitle">Today's deliveries</p>
                        <div class="screen-cards">
                            <div class="screen-card">
                                <span class="screen-card-bar"></span>
                                <span class="screen-card-bar short"></span>
                                <span class="screen-chip chip-blue">Out for delivery</span>
                            </div>
                            <div class="screen-card">
                                <span class="screen-card-bar"></span>
                                <span class="screen-card-bar short"></span>
                                <span class="screen-chip chip-green">Delivered</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Services tag cloud --}}
    <section class="services-strip">
        <div class="wrap">
            <div class="section-head center" style="margin-bottom:0;">
                <div class="eyebrow-text">One app · Everything your shop offers</div>
            </div>
            <div class="services-tags">
                <span class="on">WASH &amp; FOLD</span>
                <span>DRY CLEANING</span>
                <span class="on">IRONING</span>
                <span>STAIN REMOVAL</span>
                <span class="on">PICKUP &amp; DELIVERY</span>
                <span>BULK ORDERS</span>
                <span class="on">EXPRESS SERVICE</span>
                <span>COMFORTER &amp; BEDDING</span>
                <span class="on">SUBSCRIPTION PLANS</span>
                <span>WALK-IN ORDERS</span>
            </div>
            <p class="foot">From walk-in orders to subscription pickups — Quinn's POS is where your shop runs its whole day.</p>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="alt">
        <div class="wrap">
            <div class="section-head">
                <div class="eyebrow-text">Quinn's POS for shop owners</div>
                <h2>Run your shop, fully booked</h2>
                <p>From walk-in orders to end-of-month reports, it's all built in — no spreadsheets required.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🧺</div>
                    <h3>Order Tracking</h3>
                    <p>Take orders, track their status from drop-off to pickup, and record payments as they come in.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🏬</div>
                    <h3>Multi-Branch Ready</h3>
                    <p>Running more than one location? Manage every branch from a single account.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Customer Records</h3>
                    <p>Keep a history of every customer and their orders, so repeat business is easy to serve.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🧾</div>
                    <h3>Expense Tracking</h3>
                    <p>Log expenses by category to see exactly where your money is going each month.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Sales Reports</h3>
                    <p>See sales by day, by item, and by payment type — no spreadsheets required.</p>
                </div>
                <div class="feature-card cta-card">
                    <div>
                        <p class="title">Curious what it can do for your shop?</p>
                        <p class="sub">Create your account and see it running in minutes.</p>
                    </div>
                    <a href="#get-started" class="btn btn-primary">Get started <span class="btn-arrow">→</span></a>
                </div>
            </div>
        </div>
    </section>

    {{-- Roles --}}
    <section>
        <div class="wrap">
            <div class="section-head">
                <div class="eyebrow-text">One account, your whole team</div>
                <h2>An app for every role</h2>
                <p>Everyone signs in to the same system, scoped to what they need to see.</p>
            </div>
            <div class="roles-strip">
                <div class="role-card">
                    <div class="app-icon admin role-icon"><img src="{{ asset('images/quinns-logo-white-64.png') }}" alt=""></div>
                    <span class="role-tag">Admin</span>
                    <p>Full visibility across every branch — orders, reports, and account settings.</p>
                </div>
                <div class="role-card">
                    <div class="app-icon staff role-icon"><img src="{{ asset('images/quinns-logo-white-64.png') }}" alt=""></div>
                    <span class="role-tag">Staff</span>
                    <p>Take and manage orders at their branch, without the distraction of admin tools.</p>
                </div>
                <div class="role-card">
                    <div class="app-icon driver role-icon"><img src="{{ asset('images/quinns-logo-white-64.png') }}" alt=""></div>
                    <span class="role-tag">Delivery</span>
                    <p>See what's ready for pickup or drop-off and update order status on the go.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Signup --}}
    <section id="get-started">
        <div class="wrap">
            <div class="section-head center">
                <div class="eyebrow-text">Get started</div>
                <h2>Create your account</h2>
                <p>Tell us about your business and we'll set everything up.</p>
            </div>

            <div class="form-card">
                <form id="register-form" novalidate>
                    <div class="field" data-field="business_name">
                        <label for="business_name">Business name</label>
                        <input type="text" id="business_name" name="business_name" placeholder="Quinn's Laundry" required>
                        <div class="field-error"></div>
                    </div>
                    <div class="field" data-field="owner_name">
                        <label for="owner_name">Your name</label>
                        <input type="text" id="owner_name" name="owner_name" placeholder="Juan dela Cruz" required>
                        <div class="field-error"></div>
                    </div>
                    <div class="field" data-field="email">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="you@yourbusiness.com" required>
                        <div class="field-error"></div>
                    </div>
                    <div class="field" data-field="phone">
                        <label for="phone">Phone <span class="optional-tag">(optional)</span></label>
                        <input type="tel" id="phone" name="phone" placeholder="0917 123 4567">
                        <div class="field-error"></div>
                    </div>
                    <div class="field" data-field="address">
                        <label for="address">Address <span class="optional-tag">(optional)</span></label>
                        <input type="text" id="address" name="address" placeholder="123 Main St, City">
                        <div class="field-error"></div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submit-btn">Start free trial</button>
                    <div class="form-message" id="form-message"></div>
                </form>
            </div>
        </div>
    </section>

    {{-- Dark CTA band --}}
    <section class="cta-band">
        <div class="cta-bubbles">
            <span class="bubble bubble-1"></span>
            <span class="bubble bubble-2"></span>
            <span class="bubble bubble-3"></span>
            <span class="bubble bubble-4"></span>
            <span class="bubble bubble-5"></span>
        </div>
        <div class="wrap">
            <span class="eyebrow-pill on-dark"><span class="dot"></span> 30 days free · No card required</span>
            <h2>Your shop deserves better than a notebook</h2>
            <p>Create your account in minutes and give your team the tools to run every order, every branch, and every peso — from one place.</p>
            <div class="hero-ctas">
                <a href="#get-started" class="btn btn-primary">Start your free trial <span class="btn-arrow">→</span></a>
            </div>
        </div>
    </section>

</main>

<footer>
    <div class="wrap">
        <div class="footer-top">
            <div class="footer-brand">
                <a href="/" class="logo">
                    <span class="logo-mark"><img src="{{ asset('images/quinns-logo-white-40.png') }}" alt=""></span>
                    Quinn's POS
                </a>
                <p>Point-of-sale software built for laundry businesses — orders, branches, customers, and reports in one place.</p>
            </div>
            <div class="footer-links">
                <a href="#features">Features</a>
                <a href="#get-started">Free Trial</a>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; {{ date('Y') }} Quinn's POS. All rights reserved.</span>
        </div>
    </div>
</footer>

<script>
@include('marketing.script')
</script>

</body>
</html>
