<div class="lighthouse-hero">
    <div class="container">
        <h1>Welcome to Lighthouse</h1>
        <p>A minimal, predictable PHP micro-framework for building modern web applications</p>
        <div style="margin-top: 2rem;">
            <a href="/register" class="lighthouse-btn" style="margin-right: 1rem;">Get Started</a>
            <a href="https://github.com/max-yterb/Lighthouse" class="lighthouse-btn-secondary">View on GitHub</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="lighthouse-feature-grid">
        <div class="lighthouse-feature">
            <div class="lighthouse-feature-icon">⚡</div>
            <h3>Lightning Fast</h3>
            <p>Built for speed and performance with minimal overhead. Get your applications running in seconds.</p>
        </div>

        <div class="lighthouse-feature">
            <div class="lighthouse-feature-icon">🎯</div>
            <h3>Simple & Focused</h3>
            <p>Clean, predictable API that gets out of your way. Focus on building features, not fighting the framework.</p>
        </div>

        <div class="lighthouse-feature">
            <div class="lighthouse-feature-icon">🔒</div>
            <h3>Secure by Default</h3>
            <p>Built-in CSRF protection, input validation, and authentication. Security best practices included.</p>
        </div>

        <div class="lighthouse-feature">
            <div class="lighthouse-feature-icon">📱</div>
            <h3>Modern Stack</h3>
            <p>PHP 8+ HTMX, Pico.css, and SQLite. Everything you need for modern web development.</p>
        </div>

        <div class="lighthouse-feature">
            <div class="lighthouse-feature-icon">🚀</div>
            <h3>Deploy Anywhere</h3>
            <p>Simple deployment. Works on any server with PHP. No complex setup or configuration required.</p>
        </div>

        <div class="lighthouse-feature">
            <div class="lighthouse-feature-icon">🛠️</div>
            <h3>Developer Friendly</h3>
            <p>Type hints, modern PHP features, and comprehensive documentation. Built for developer productivity.</p>
        </div>
    </div>

    <div class="lighthouse-card" style="text-align: center; margin: 4rem 0;">
        <h2>Ready to Build Something Amazing?</h2>
        <p>Join the Lighthouse community and start building modern web applications today.</p>
        <div style="margin-top: 2rem;">
            <?php if (!auth_user()): ?>
                <a href="/register" class="lighthouse-btn">Create Account</a>
                <a href="/login" class="lighthouse-btn-secondary" style="margin-left: 1rem;">Sign In</a>
            <?php else: ?>
                <a href="/dashboard" class="lighthouse-btn">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</div>
