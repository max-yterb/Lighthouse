<?php

declare(strict_types=1);

// Check if user is logged in
if (!auth_user()) {
    header('Location: /login');
    exit;
}

// Get user data
/** @var array<string, mixed>|null $user */
$user = db_select_one('users', ['id' => auth_user()]);
?>

<div class="lighthouse-hero" style="padding: 3rem 0;">
    <div class="container">
        <h1>Welcome to your Dashboard</h1>
        <p>Hello, <?= htmlspecialchars($user['email'] ?? 'User') ?>! 👋</p>
    </div>
</div>

<div class="container dashboard">
    <div class="lighthouse-feature-grid">
        <div class="lighthouse-feature">
            <div class="lighthouse-feature-icon">📊</div>
            <h3>Analytics</h3>
            <p>View your application metrics and performance data.</p>
            <a href="#" class="lighthouse-btn-secondary">View Analytics</a>
        </div>
        
        <div class="lighthouse-feature">
            <div class="lighthouse-feature-icon">⚙️</div>
            <h3>Settings</h3>
            <p>Manage your account settings and preferences.</p>
            <a href="#" class="lighthouse-btn-secondary">Manage Settings</a>
        </div>
        
        <div class="lighthouse-feature">
            <div class="lighthouse-feature-icon">🚀</div>
            <h3>Deploy</h3>
            <p>Deploy your applications with one click.</p>
            <a href="#" class="lighthouse-btn-secondary">Deploy Now</a>
        </div>
        
        <div class="lighthouse-feature">
            <div class="lighthouse-feature-icon">📝</div>
            <h3>Documentation</h3>
            <p>Learn how to build amazing applications with Lighthouse.</p>
            <a href="#" class="lighthouse-btn-secondary">Read Docs</a>
        </div>
    </div>
    
    <div class="lighthouse-card" style="margin-top: 3rem;">
        <h2>Quick Stats</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 2rem; margin-top: 1rem;">
            <div style="text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: var(--lighthouse-signal-blue);">42</div>
                <div style="color: var(--lighthouse-sea-slate);">Projects</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: var(--lighthouse-beacon-red);">1.2k</div>
                <div style="color: var(--lighthouse-sea-slate);">Page Views</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: var(--lighthouse-signal-blue);">99.9%</div>
                <div style="color: var(--lighthouse-sea-slate);">Uptime</div>
            </div>
        </div>
    </div>
</div>
