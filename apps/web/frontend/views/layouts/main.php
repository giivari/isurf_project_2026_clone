<?php

/** @var \yii\web\View $this */
/** @var string $content */

use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-full">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - iSurf Lab IPB</title>
    
    <!-- Design System CSS -->
    <link href="<?= Url::to('@web/css/design-system.css') ?>" rel="stylesheet">
    
    <!-- Tailwind CSS v4 -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-primary-50:  #F0FDF4;
            --color-primary-500: #22C55E;
            --color-primary-600: #16A34A;
            --color-gray-50:  #F8FAFC;
            --color-gray-100: #F1F5F9;
            --color-gray-800: #1e293b;
            --color-gray-900: #0F172A;
        }
    </style>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <?php $this->head() ?>
</head>
<body class="h-full text-gray-900 antialiased flex overflow-hidden m-0 p-0" style="background-color: #f0fdf4; background-image: radial-gradient(at 10% 20%, hsla(140, 100%, 94%, 1) 0px, transparent 50%), radial-gradient(at 90% 10%, hsla(160, 100%, 92%, 1) 0px, transparent 50%), radial-gradient(at 50% 90%, hsla(200, 100%, 92%, 1) 0px, transparent 50%), radial-gradient(at 80% 80%, hsla(120, 100%, 90%, 1) 0px, transparent 50%);">
<?php $this->beginBody() ?>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="<?= Yii::$app->user->isGuest ? 'ds-overlay-guest' : 'ds-overlay' ?> transition-opacity duration-300"></div>

<!-- Sidebar -->
<aside id="main-sidebar" class="ds-sidebar <?= Yii::$app->user->isGuest ? 'ds-sidebar-collapsed' : 'ds-sidebar-mobile md:static' ?> flex flex-col h-[100dvh] shrink-0 border-r border-white/40 glass-panel">
    <div class="flex items-center gap-3 px-6" style="height: 80px; border-bottom: 1px solid var(--gray-100);">
        <div style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg viewBox="0 0 100 100" width="36" height="36" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="leafGradLeft" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#bef264" />
                        <stop offset="100%" stop-color="#84cc16" />
                    </linearGradient>
                    <linearGradient id="leafGradRight" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#84cc16" />
                        <stop offset="100%" stop-color="#4d7c0f" />
                    </linearGradient>
                </defs>
                <!-- Left Leaf -->
                <path d="M 45 85 C 5 70 0 35 15 25 C 35 25 50 50 45 85 Z" fill="url(#leafGradLeft)" />
                <!-- Left Vein -->
                <path d="M 15 25 Q 30 45 45 85" stroke="#ffffff" stroke-width="2.5" fill="none" stroke-linecap="round" />
                
                <!-- Right Leaf -->
                <path d="M 40 85 C 90 90 100 45 85 15 C 60 10 35 45 40 85 Z" fill="url(#leafGradRight)" />
                <!-- Right Vein -->
                <path d="M 85 15 Q 60 45 40 85" stroke="#ffffff" stroke-width="3" fill="none" stroke-linecap="round" />
            </svg>
        </div>
        <div>
            <h1 style="font-size: 18px; font-weight: 700; color: var(--gray-900); line-height: 1.2; margin: 0;">iSurf Lab</h1>
            <p style="font-size: 12px; color: var(--gray-500); margin: 0;">Smart Urban Farming IPB</p>
        </div>
        <button id="close-sidebar" class="md:hidden text-gray-400 hover:text-gray-900 ml-auto" aria-label="Close sidebar">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    
          <!-- Common Links (Both Admin & Guest) -->
          <a href="<?= Url::to(['site/index']) ?>" class="ds-sidebar-item <?= Yii::$app->controller->action->id == 'index' ? 'active' : '' ?>">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
              Dashboard
              <?php if (Yii::$app->controller->action->id == 'index'): ?><svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg><?php endif; ?>
          </a>
          <a href="<?= Url::to(['site/analytics']) ?>" class="ds-sidebar-item <?= Yii::$app->controller->action->id == 'analytics' ? 'active' : '' ?>">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
              Analytic Monitoring
              <?php if (Yii::$app->controller->action->id == 'analytics'): ?><svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg><?php endif; ?>
          </a>

          <?php if (!Yii::$app->user->isGuest): ?>
          <a href="<?= Url::to(['site/monitoring']) ?>" class="ds-sidebar-item <?= Yii::$app->controller->action->id == 'monitoring' ? 'active' : '' ?>">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
              Data Realtime
              <?php if (Yii::$app->controller->action->id == 'monitoring'): ?><svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg><?php endif; ?>
          </a>
          <a href="<?= Url::to(['site/areas']) ?>" class="ds-sidebar-item <?= Yii::$app->controller->action->id == 'areas' || Yii::$app->controller->action->id == 'area-details' ? 'active' : '' ?>">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
              Kontrol Area
              <?php if (Yii::$app->controller->action->id == 'areas' || Yii::$app->controller->action->id == 'area-details'): ?><svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg><?php endif; ?>
          </a>
          <a href="<?= Url::to(['site/alerts']) ?>" class="ds-sidebar-item <?= Yii::$app->controller->action->id == 'alerts' ? 'active' : '' ?>">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
              Alerts & Logs
              <?php if (Yii::$app->controller->action->id == 'alerts'): ?><svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg><?php endif; ?>
          </a>
          <?php else: ?>
          <a href="<?= Url::to(['site/request-data']) ?>" class="ds-sidebar-item <?= Yii::$app->controller->action->id == 'request-data' ? 'active' : '' ?>">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
              Request Data
              <?php if (Yii::$app->controller->action->id == 'request-data'): ?><svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg><?php endif; ?>
          </a>
          <?php endif; ?>
      </nav>
    
    <div style="padding: var(--space-4); border-top: 1px solid var(--gray-200);">
        <div class="flex items-center">
            <div class="rounded-full flex items-center justify-center text-sm font-bold shrink-0" style="width: 40px; height: 40px; background-color: var(--primary-100); color: var(--primary-700);">
                <?= Yii::$app->user->isGuest ? 'U' : strtoupper(substr(Yii::$app->user->identity->username, 0, 1)) ?>
            </div>
            <div class="ml-3 overflow-hidden">
                <p class="font-medium truncate" style="color: var(--gray-900) !important; font-size: 14px; margin: 0; line-height: 1.4;"><?= Yii::$app->user->isGuest ? 'Guest' : Yii::$app->user->identity->username ?></p>
                <?php if (!Yii::$app->user->isGuest): ?>
                    <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'inline', 'style' => 'margin: 0;']) ?>
                    <button type="submit" class="text-caption mt-1 cursor-pointer hover:text-primary-600" style="color: var(--gray-500); border: none; background: transparent; padding: 0;">Keluar</button>
                    <?= Html::endForm() ?>
                <?php else: ?>
                    <a href="<?= Url::to(['/site/login']) ?>" class="text-caption mt-1 hover:text-primary-600 block" style="color: var(--primary-500);">Masuk</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</aside>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    
    <!-- Top Header -->
    <header class="glass-panel flex items-center justify-between shrink-0 z-10" style="height: 64px; padding: 0 var(--space-5) 0 var(--space-3); border-bottom: 1px solid rgba(255,255,255,0.4); box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
        <div class="flex items-center gap-1">
            <button id="open-sidebar" class="<?= Yii::$app->user->isGuest ? '' : 'md:hidden' ?> text-gray-500 hover:text-gray-700 focus:outline-none p-1" aria-label="Open sidebar">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <h2 style="color: var(--gray-900); margin: 0; font-size: 20px; font-weight: 600;"><?= Html::encode($this->title) ?></h2>
        </div>
        
        <div class="flex items-center gap-6">
            <div class="hidden sm:flex items-center gap-2 text-gray-500">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span style="font-size: 14px;" id="header-time"><?= date('H:i:s') ?> - <?= date('d/m/Y') ?> (UTC<?= date('P') ?>)</span>
            </div>
            <div class="hidden sm:flex items-center gap-2 text-gray-500">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                <span style="font-size: 14px;">Connected</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto" style="padding: var(--space-5); md:padding: var(--space-6);">
        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="mb-4 p-4 rounded shadow-sm flex items-center" style="background-color: var(--primary-50); border-left: 4px solid var(--primary-500);">
                <svg class="h-5 w-5 mr-3" style="color: var(--primary-500);" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="text-body font-medium" style="color: var(--primary-700); margin: 0;"><?= Yii::$app->session->getFlash('success') ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="mb-4 p-4 rounded shadow-sm flex items-center" style="background-color: #FEE2E2; border-left: 4px solid var(--danger);">
                <svg class="h-5 w-5 mr-3" style="color: var(--danger);" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <p class="text-body font-medium" style="color: #991B1B; margin: 0;"><?= Yii::$app->session->getFlash('error') ?></p>
            </div>
        <?php endif; ?>

        <!-- Render View -->
        <div class="mx-auto" style="max-width: 1440px;">
            <?= $content ?>
        </div>
        
        <footer class="mt-12 pt-6 pb-4 text-center text-caption" style="border-top: 1px solid var(--gray-200); color: var(--gray-500);">
            <p style="margin: 0; font-weight: 500;">&copy; <?= date('Y') ?> Ilmu Komputer SSMI IPB.</p>
        </footer>
    </main>
</div>

<script>
    // Live Clock
    function updateClock() {
        const now = new Date();
        const timeEl = document.getElementById('header-time');
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        const mo = String(now.getMonth()+1).padStart(2, '0');
        const y = now.getFullYear();
        
        // Calculate UTC offset
        const offset = -now.getTimezoneOffset();
        const sign = offset >= 0 ? '+' : '-';
        const offsetHours = String(Math.floor(Math.abs(offset) / 60)).padStart(2, '0');
        const offsetMins = String(Math.abs(offset) % 60).padStart(2, '0');
        const tz = `UTC${sign}${offsetHours}:${offsetMins}`;
        
        if(timeEl) timeEl.textContent = `${h}:${m}:${s} - ${d}/${mo}/${y} (${tz})`;
    }
    setInterval(updateClock, 1000);

    // Mobile Sidebar Drawer logic
    const openBtn = document.getElementById('open-sidebar');
    const closeBtn = document.getElementById('close-sidebar');
    const sidebar = document.getElementById('main-sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function toggleSidebar() {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('open');
    }

    if(openBtn) openBtn.addEventListener('click', toggleSidebar);
    if(closeBtn) closeBtn.addEventListener('click', toggleSidebar);
    if(overlay) overlay.addEventListener('click', toggleSidebar);
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>
