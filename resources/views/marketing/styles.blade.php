:root {
    --ink: #0b1220;
    --muted: #5b6472;
    --muted-2: #8a93a3;
    --primary: #0284c7;
    --primary-dark: #026aa2;
    --primary-tint: #e6f4fc;
    --green: #16a34a;
    --green-tint: #e8f7ec;
    --border: #e3e9ef;
    --surface-alt: #f4f8fb;
    --navy: #0b1b2b;
    --navy-2: #12293f;

    /* App brand colors */
    --admin-color: #560591;
    --staff-color: #000000;
    --driver-color: #0284c7;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: #ffffff;
    color: var(--ink);
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
}

a { color: inherit; }

.wrap {
    max-width: 1160px;
    margin: 0 auto;
    padding: 0 24px;
}

.dotted-bg {
    background-image: radial-gradient(circle, #c3d3e0 1.4px, transparent 1.4px);
    background-size: 22px 22px;
}

.dotted-bg-dark {
    background-image: radial-gradient(circle, rgba(255, 255, 255, .16) 1.4px, transparent 1.4px);
    background-size: 22px 22px;
}

/* Nav */
header {
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    background: rgba(255, 255, 255, .92);
    backdrop-filter: blur(8px);
    z-index: 20;
}

nav.wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 16px;
    padding-bottom: 16px;
}

.logo {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 800;
    font-size: 17px;
    letter-spacing: -.01em;
    text-decoration: none;
}

.logo .logo-mark {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo .logo-mark img { width: 12px; height: auto; display: block; }

/* App icon tiles */
.app-icon {
    border-radius: 22%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.app-icon img { width: 38%; height: auto; display: block; }

.app-icon.admin { background: var(--admin-color); }
.app-icon.staff { background: var(--staff-color); }
.app-icon.driver { background: var(--driver-color); }

.nav-links {
    display: flex;
    align-items: center;
    gap: 30px;
}

.nav-links a {
    text-decoration: none;
    color: var(--muted);
    font-size: 14px;
    font-weight: 500;
}

.nav-links a:hover { color: var(--ink); }

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
    border-radius: 999px;
    padding: 13px 24px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
    transition: transform .12s ease, box-shadow .12s ease, background-color .12s ease;
}

.btn:hover { transform: translateY(-1px); }

.btn-primary {
    background: var(--primary);
    color: #ffffff;
    box-shadow: 0 8px 20px -8px rgba(2, 132, 199, .55);
}

.btn-primary:hover { background: var(--primary-dark); }

.btn-secondary {
    background: #ffffff;
    color: var(--ink);
    border: 1.5px solid var(--border);
}

.btn-secondary:hover { border-color: #c7d1db; }

.btn-on-dark {
    background: transparent;
    color: #ffffff;
    border: 1.5px solid rgba(255, 255, 255, .35);
}

.btn-on-dark:hover { border-color: rgba(255, 255, 255, .65); }

.btn-arrow { font-size: 15px; }

/* Eyebrow */
.eyebrow-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--primary-tint);
    color: var(--primary-dark);
    padding: 6px 14px 6px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
}

.eyebrow-pill .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--green);
    flex-shrink: 0;
}

.eyebrow-pill.on-dark {
    background: rgba(255, 255, 255, .1);
    color: #cfe8f7;
}

.eyebrow-text {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--primary);
    margin-bottom: 10px;
}

/* Hero */
.hero {
    padding: 80px 0 64px;
    position: relative;
    overflow: hidden;
}

.hero-grid {
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 40px;
    align-items: center;
}

h1 {
    font-size: clamp(36px, 5vw, 54px);
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1.06;
    margin-top: 20px;
}

.hero p.lead {
    max-width: 480px;
    margin-top: 20px;
    color: var(--muted);
    font-size: 17px;
}

.hero-ctas {
    display: flex;
    gap: 12px;
    margin-top: 32px;
    flex-wrap: wrap;
}

.hero-note {
    margin-top: 18px;
    font-size: 13px;
    color: var(--muted-2);
    font-weight: 600;
}

/* Hero device mockups */
.device-stack {
    position: relative;
    height: 400px;
}

.device {
    position: absolute;
    background: #f4f6f8;
    border-radius: 22px;
    box-shadow: 0 28px 48px -18px rgba(11, 27, 43, .35);
    padding: 10px;
}

.device-tablet {
    width: 340px;
    height: 240px;
    top: 10px;
    left: 0;
    transform: rotate(-4deg);
    animation: float-a 7s ease-in-out infinite;
    z-index: 1;
}

.device-phone {
    width: 168px;
    height: 300px;
    bottom: 0;
    right: 10px;
    border-radius: 32px;
    padding: 10px 8px;
    transform: rotate(4deg);
    animation: float-b 6s ease-in-out infinite;
    animation-delay: -2.4s;
    z-index: 2;
}

.device-notch {
    position: absolute;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    width: 46px;
    height: 6px;
    border-radius: 999px;
    background: #d7dde3;
}

.device-screen {
    background: #ffffff;
    width: 100%;
    height: 100%;
    border-radius: 14px;
    overflow: hidden;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.device-phone .device-screen { padding-top: 20px; }

.screen-topbar {
    display: flex;
    align-items: center;
    gap: 8px;
}

.screen-logo {
    width: 20px;
    height: 20px;
    border-radius: 6px;
    background: var(--admin-color);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.screen-logo.driver { background: var(--driver-color); }
.screen-logo img { width: 9px; }

.screen-topbar-title { font-size: 11px; font-weight: 700; color: var(--ink); }

.screen-subtitle { font-size: 10px; color: var(--muted); font-weight: 600; }

.screen-stats {
    display: flex;
    gap: 8px;
}

.screen-stat {
    flex: 1;
    background: var(--surface-alt);
    border-radius: 10px;
    padding: 8px 10px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.screen-stat-label { font-size: 8px; color: var(--muted-2); font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
.screen-stat-value { font-size: 13px; font-weight: 800; color: var(--ink); }

.screen-list, .screen-cards {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.screen-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    background: var(--surface-alt);
    border-radius: 8px;
    padding: 7px 10px;
}

.screen-row-bar {
    height: 6px;
    width: 50%;
    border-radius: 4px;
    background: #d7dde3;
}

.screen-card {
    background: var(--surface-alt);
    border-radius: 10px;
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: flex-start;
}

.screen-card-bar {
    height: 6px;
    width: 80%;
    border-radius: 4px;
    background: #d7dde3;
}

.screen-card-bar.short { width: 50%; }

.screen-chip {
    font-size: 8px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 999px;
    white-space: nowrap;
}

.chip-green { background: var(--green-tint); color: var(--green); }
.chip-blue { background: var(--primary-tint); color: var(--primary-dark); }
.chip-gray { background: #e9edf1; color: var(--muted); }

@keyframes float-a {
    0%, 100% { transform: rotate(-4deg) translateY(0); }
    50% { transform: rotate(-4deg) translateY(-12px); }
}

@keyframes float-b {
    0%, 100% { transform: rotate(4deg) translateY(0); }
    50% { transform: rotate(4deg) translateY(-16px); }
}

@media (prefers-reduced-motion: reduce) {
    .device-tablet, .device-phone { animation: none; }
}

/* Sections */
section { padding: 80px 0; }
section.alt { background: var(--surface-alt); }

.section-head {
    max-width: 600px;
    margin-bottom: 44px;
}

.section-head.center {
    margin-left: auto;
    margin-right: auto;
    text-align: center;
}

.section-head h2 {
    font-size: clamp(26px, 3.4vw, 34px);
    font-weight: 800;
    letter-spacing: -.01em;
}

.section-head p {
    margin-top: 12px;
    color: var(--muted);
    font-size: 15.5px;
}

/* Services tag cloud */
.services-strip { padding: 56px 0; border-bottom: 1px solid var(--border); }

.services-tags {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px 18px;
    max-width: 820px;
    margin: 24px auto 0;
    text-align: center;
}

.services-tags span {
    font-size: 14px;
    font-weight: 600;
    color: var(--muted-2);
    text-transform: uppercase;
    letter-spacing: .02em;
}

.services-tags span.on {
    color: var(--ink);
    font-weight: 800;
}

.services-strip p.foot {
    text-align: center;
    color: var(--muted);
    font-size: 14.5px;
    margin-top: 24px;
}

/* Feature cards */
.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.feature-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 24px;
}

.feature-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--primary-tint);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-bottom: 16px;
}

.feature-card h3 {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 6px;
}

.feature-card p {
    font-size: 14px;
    color: var(--muted);
}

.feature-card.cta-card {
    background: var(--navy);
    border-color: var(--navy);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.feature-card.cta-card p.title {
    color: #ffffff;
    font-weight: 700;
    font-size: 15px;
    margin-bottom: 4px;
}

.feature-card.cta-card p.sub {
    color: rgba(255, 255, 255, .65);
    font-size: 13px;
    margin-bottom: 18px;
}

.feature-card.cta-card .btn { align-self: flex-start; }

/* Roles */
.roles-strip {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.role-card {
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 28px 24px;
}

.role-card .role-icon {
    width: 44px;
    height: 44px;
    margin-bottom: 16px;
}

.role-card .role-icon img { width: 17px; }

.role-card .role-tag {
    display: block;
    font-size: 15px;
    font-weight: 800;
    margin-bottom: 6px;
}

.role-card p { color: var(--muted); font-size: 14px; }

/* Signup */
#get-started { background: var(--surface-alt); }
#get-started .wrap { max-width: 520px; }

.form-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 36px;
    box-shadow: 0 24px 48px -28px rgba(11, 27, 43, .2);
}

.field { margin-bottom: 16px; }

.field label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 6px;
}

.field .optional-tag { font-weight: 400; color: var(--muted-2); }

.field input {
    width: 100%;
    padding: 11px 13px;
    border: 1px solid var(--border);
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
}

.field input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-tint);
}

.field-error {
    color: #b91c1c;
    font-size: 12px;
    margin-top: 5px;
    display: none;
}

.field.has-error input { border-color: #b91c1c; }
.field.has-error .field-error { display: block; }

#register-form .btn { width: 100%; justify-content: center; margin-top: 6px; }

.form-message {
    margin-top: 16px;
    padding: 12px 14px;
    border-radius: 10px;
    font-size: 13px;
    display: none;
}

.form-message.success { display: block; background: var(--green-tint); color: var(--green); }
.form-message.error { display: block; background: #fde8e8; color: #b91c1c; }

/* Dark CTA band */
.cta-band {
    background: linear-gradient(165deg, #0b1b2b 0%, #0d2338 55%, #0a1826 100%);
    color: #ffffff;
    text-align: center;
    padding: 88px 0;
    position: relative;
    overflow: hidden;
}

.cta-band::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(560px 420px at 10% -10%, rgba(2, 132, 199, .35), transparent 60%),
        radial-gradient(520px 420px at 92% 112%, rgba(86, 5, 145, .28), transparent 60%);
    pointer-events: none;
}

.cta-bubbles {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.bubble {
    position: absolute;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, .16);
    background: radial-gradient(circle at 32% 28%, rgba(255, 255, 255, .18), rgba(255, 255, 255, .02) 65%);
    animation: bubble-float 9s ease-in-out infinite;
}

.bubble-1 { width: 72px; height: 72px; left: 8%; top: 18%; animation-duration: 9s; }
.bubble-2 { width: 38px; height: 38px; left: 21%; top: 66%; animation-duration: 7s; animation-delay: -2s; }
.bubble-3 { width: 116px; height: 116px; right: 9%; top: 12%; animation-duration: 11s; animation-delay: -4s; }
.bubble-4 { width: 50px; height: 50px; right: 21%; bottom: 14%; animation-duration: 8s; animation-delay: -1s; }
.bubble-5 { width: 26px; height: 26px; right: 38%; top: 42%; animation-duration: 6s; animation-delay: -3s; }

@keyframes bubble-float {
    0%, 100% { transform: translateY(0) translateX(0); }
    50% { transform: translateY(-18px) translateX(6px); }
}

.cta-band .wrap { position: relative; z-index: 2; }

.cta-band h2 {
    font-size: clamp(28px, 4vw, 42px);
    font-weight: 800;
    letter-spacing: -.01em;
    margin-top: 16px;
}

.cta-band p {
    color: rgba(255, 255, 255, .7);
    max-width: 480px;
    margin: 16px auto 0;
    font-size: 15.5px;
}

.cta-band .hero-ctas { justify-content: center; margin-top: 32px; }

@media (prefers-reduced-motion: reduce) {
    .bubble { animation: none; }
}

/* Footer */
footer {
    background: var(--navy);
    color: rgba(255, 255, 255, .55);
    padding: 48px 0 28px;
    border-top: 1px solid rgba(255, 255, 255, .08);
}

.footer-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 24px;
    padding-bottom: 32px;
}

.footer-brand .logo { color: #ffffff; }
.footer-brand p { margin-top: 10px; font-size: 13.5px; max-width: 260px; }

.footer-links { display: flex; gap: 28px; flex-wrap: wrap; }
.footer-links a { font-size: 13.5px; text-decoration: none; color: rgba(255, 255, 255, .65); }
.footer-links a:hover { color: #ffffff; }

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, .08);
    padding-top: 20px;
    font-size: 12.5px;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}

@media (max-width: 860px) {
    .hero-grid { grid-template-columns: 1fr; }
    .device-stack { height: 300px; margin-top: 24px; transform: scale(.85); transform-origin: top center; }
    .features-grid, .roles-strip { grid-template-columns: 1fr; }
    .nav-links { display: none; }
    section { padding: 56px 0; }
    .cta-band { padding: 64px 0; }
}

@media (max-width: 480px) {
    .device-stack { height: 260px; transform: scale(.68); }
}
