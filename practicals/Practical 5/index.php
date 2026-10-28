<?php
// ============================================================
//  HOMEPAGE — index.php
//  Replaces index.html — pulls all content from MySQL via PDO
// ============================================================

session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// ── 1. Breaking News Ticker ──────────────────────────────────
$tickerStmt = $pdo->query("SELECT headline FROM breaking_news_ticker WHERE is_active = 1 ORDER BY created_at DESC");
$tickerItems = $tickerStmt->fetchAll();

// ── 2. Featured Article (Hero) ───────────────────────────────
$featuredStmt = $pdo->query("
    SELECT a.*, u.name AS author_name, c.name AS category_name
    FROM   articles a
    JOIN   users u      ON a.author_id   = u.id
    JOIN   categories c ON a.category_id = c.id
    WHERE  a.is_featured = 1 AND a.status = 'published'
    ORDER  BY a.published_at DESC
    LIMIT  1
");
$featured = $featuredStmt->fetch();

// ── 3. Top Stories Sidebar (3 articles, excluding featured) ──
$topStmt = $pdo->prepare("
    SELECT a.*, u.name AS author_name, c.name AS category_name
    FROM   articles a
    JOIN   users u      ON a.author_id   = u.id
    JOIN   categories c ON a.category_id = c.id
    WHERE  a.status = 'published' AND a.id != :featured_id
    ORDER  BY a.published_at DESC
    LIMIT  3
");
$topStmt->execute([':featured_id' => $featured['id'] ?? 0]);
$topStories = $topStmt->fetchAll();

// ── 4. Category Filter ────────────────────────────────────────
$activeCategory = $_GET['category'] ?? 'all';
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// ── 5. News Grid (6 articles, optionally filtered by category) ─
if ($activeCategory === 'all') {
    $gridStmt = $pdo->prepare("
        SELECT a.*, u.name AS author_name, c.name AS category_name, c.slug AS category_slug
        FROM   articles a
        JOIN   users u      ON a.author_id   = u.id
        JOIN   categories c ON a.category_id = c.id
        WHERE  a.status = 'published' AND a.id != :featured_id
        ORDER  BY a.published_at DESC
        LIMIT  6
    ");
    $gridStmt->execute([':featured_id' => $featured['id'] ?? 0]);
} else {
    $gridStmt = $pdo->prepare("
        SELECT a.*, u.name AS author_name, c.name AS category_name, c.slug AS category_slug
        FROM   articles a
        JOIN   users u      ON a.author_id   = u.id
        JOIN   categories c ON a.category_id = c.id
        WHERE  a.status = 'published' AND c.slug = :cat
        ORDER  BY a.published_at DESC
        LIMIT  6
    ");
    $gridStmt->execute([':cat' => $activeCategory]);
}
$gridArticles = $gridStmt->fetchAll();

// ── 6. Opinion Articles ───────────────────────────────────────
$opinionStmt = $pdo->prepare("
    SELECT a.*, u.name AS author_name
    FROM   articles a
    JOIN   users u      ON a.author_id   = u.id
    JOIN   categories c ON a.category_id = c.id
    WHERE  a.status = 'published' AND c.slug = 'opinion'
    ORDER  BY a.published_at DESC
    LIMIT  2
");
$opinionStmt->execute();
$opinions = $opinionStmt->fetchAll();

// ── 7. In Brief (5 most recent short headlines) ───────────────
$briefStmt = $pdo->query("
    SELECT a.title, a.slug, a.published_at
    FROM   articles a
    WHERE  a.status = 'published'
    ORDER  BY a.published_at DESC
    LIMIT  5
");
$briefs = $briefStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>The Daily Chronicle — News Portal</title>
  <meta name="description" content="The Daily Chronicle — Independent journalism delivering the day's top stories in politics, business, tech, sports and culture.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <!-- ── Breaking News Ticker (DB-driven) ─────────────────── -->
  <div class="ticker" aria-label="Breaking news">
    <span class="ticker__label">Breaking</span>
    <div class="ticker__track">
      <p class="ticker__text">
        <?php foreach ($tickerItems as $i => $item): ?>
          <?= e($item['headline']) ?><?= ($i < count($tickerItems) - 1) ? ' &nbsp;•&nbsp; ' : '' ?>
        <?php endforeach; ?>
      </p>
    </div>
  </div>

  <!-- ── Header ────────────────────────────────────────────── -->
  <header class="header">
    <div class="header__inner">
      <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>

      <a href="index.php" class="logo">
        <span class="logo__mark">DC</span>
        <span class="logo__text">The Daily Chronicle</span>
      </a>

      <nav class="nav" id="mainNav" aria-label="Main navigation">
        <ul class="nav__list">
          <li><a href="index.php" class="nav__link nav__link--active">Home</a></li>
          <?php foreach ($categories as $cat): ?>
            <li><a href="index.php?category=<?= e($cat['slug']) ?>" class="nav__link"><?= e($cat['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <div class="header__actions">
        <a href="search.php" class="btn-search" aria-label="Search">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </a>
        <?php if (isLoggedIn()): ?>
          <a href="logout.php" class="btn-subscribe">Logout</a>
        <?php else: ?>
          <a href="login.php" class="btn-subscribe">Sign In</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <div class="nav-overlay" id="navOverlay" aria-hidden="true"></div>

  <!-- ── Main Content ─────────────────────────────────────── -->
  <main class="main">

    <!-- ── Hero Section ─────────────────────────────────────── -->
    <?php if ($featured): ?>
    <section class="hero" aria-labelledby="hero-heading">
      <article class="hero__featured card">
        <a href="article.php?slug=<?= e($featured['slug']) ?>">
          <div class="card__image-wrap">
            <img src="<?= e($featured['image_url']) ?>"
                 alt="<?= e($featured['title']) ?>"
                 class="card__image">
            <span class="card__badge"><?= e($featured['category_name']) ?></span>
          </div>
          <div class="card__body">
            <time datetime="<?= e($featured['published_at']) ?>" class="card__date">
              <?= formatDate($featured['published_at']) ?>
            </time>
            <h1 id="hero-heading" class="card__title card__title--hero">
              <?= e($featured['title']) ?>
            </h1>
            <p class="card__excerpt"><?= e($featured['excerpt']) ?></p>
            <span class="card__author">By <?= e($featured['author_name']) ?></span>
          </div>
        </a>
      </article>

      <aside class="hero__sidebar">
        <h2 class="section-label">Top Stories</h2>
        <?php foreach ($topStories as $story): ?>
        <article class="card card--compact">
          <?php if ($story['image_url']): ?>
          <img src="<?= e($story['image_url']) ?>" alt="<?= e($story['title']) ?>" class="card__thumb">
          <?php endif; ?>
          <div class="card__body">
            <span class="card__category"><?= e($story['category_name']) ?></span>
            <h3 class="card__title">
              <a href="article.php?slug=<?= e($story['slug']) ?>"><?= e($story['title']) ?></a>
            </h3>
            <time datetime="<?= e($story['published_at']) ?>" class="card__date">
              <?= timeAgo($story['published_at']) ?>
            </time>
          </div>
        </article>
        <?php endforeach; ?>
      </aside>
    </section>
    <?php endif; ?>

    <!-- ── Category Filter Strip ────────────────────────────── -->
    <nav class="categories" aria-label="News categories">
      <a href="index.php"
         class="categories__item <?= $activeCategory === 'all' ? 'categories__item--active' : '' ?>">All</a>
      <?php foreach ($categories as $cat): ?>
        <a href="index.php?category=<?= e($cat['slug']) ?>"
           class="categories__item <?= $activeCategory === $cat['slug'] ? 'categories__item--active' : '' ?>">
          <?= e($cat['name']) ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <!-- ── News Grid ─────────────────────────────────────────── -->
    <section class="news-grid" aria-labelledby="latest-heading">
      <div class="news-grid__header">
        <h2 id="latest-heading" class="section-title">Latest News</h2>
        <a href="search.php" class="view-all">View all →</a>
      </div>

      <div class="news-grid__layout">
        <?php if (empty($gridArticles)): ?>
          <p style="color:var(--color-ink-muted); padding:2rem 0;">No articles found in this category.</p>
        <?php endif; ?>

        <?php foreach ($gridArticles as $article): ?>
        <article class="card card--grid">
          <a href="article.php?slug=<?= e($article['slug']) ?>">
            <?php if ($article['image_url']): ?>
            <div class="card__image-wrap">
              <img src="<?= e($article['image_url']) ?>" alt="<?= e($article['title']) ?>" class="card__image">
              <span class="card__badge"><?= e($article['category_name']) ?></span>
            </div>
            <?php endif; ?>
            <div class="card__body">
              <h3 class="card__title"><?= e($article['title']) ?></h3>
              <p class="card__excerpt"><?= e($article['excerpt']) ?></p>
              <time datetime="<?= e($article['published_at']) ?>" class="card__date">
                <?= timeAgo($article['published_at']) ?>
              </time>
            </div>
          </a>
        </article>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ── Opinion & In Brief ───────────────────────────────── -->
    <section class="features" aria-labelledby="features-heading">
      <div class="features__column">
        <h2 id="features-heading" class="section-title">Opinion</h2>
        <?php foreach ($opinions as $op): ?>
        <article class="card card--horizontal">
          <div class="card__body">
            <span class="card__category">Editorial</span>
            <h3 class="card__title">
              <a href="article.php?slug=<?= e($op['slug']) ?>"><?= e($op['title']) ?></a>
            </h3>
            <p class="card__excerpt"><?= e($op['excerpt']) ?></p>
            <span class="card__author"><?= e($op['author_name']) ?></span>
          </div>
        </article>
        <?php endforeach; ?>
      </div>

      <div class="features__column">
        <h2 class="section-title">In Brief</h2>
        <ul class="brief-list">
          <?php foreach ($briefs as $brief): ?>
          <li class="brief-list__item">
            <time datetime="<?= e($brief['published_at']) ?>">
              <?= formatDate($brief['published_at'], 'd M') ?>
            </time>
            <p><a href="article.php?slug=<?= e($brief['slug']) ?>"><?= e($brief['title']) ?></a></p>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>

    <!-- ── Newsletter CTA ───────────────────────────────────── -->
    <section class="newsletter" aria-labelledby="newsletter-heading">
      <div class="newsletter__content">
        <h2 id="newsletter-heading" class="newsletter__title">Stay Informed</h2>
        <p class="newsletter__text">Get the day's top stories delivered to your inbox every morning.</p>
      </div>
      <form class="newsletter__form" id="newsletterForm">
        <input type="email" id="newsletterEmail" class="newsletter__input"
               placeholder="Your email address" aria-label="Email address" required>
        <button type="submit" class="newsletter__btn">Sign Up</button>
      </form>
      <p id="newsletterMsg" style="display:none; color:#fff; font-weight:600; margin-top:0.5rem;"></p>
    </section>

  </main>

  <!-- ── Footer ───────────────────────────────────────────── -->
  <footer class="footer">
    <div class="footer__grid">
      <div class="footer__brand">
        <span class="logo__mark">DC</span>
        <p class="footer__tagline">Independent journalism since 1892.</p>
      </div>
      <div class="footer__links">
        <h3>Sections</h3>
        <ul>
          <?php foreach (array_slice($categories, 0, 4) as $cat): ?>
            <li><a href="index.php?category=<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="footer__links">
        <h3>Company</h3>
        <ul>
          <li><a href="#">About Us</a></li>
          <li><a href="#">Careers</a></li>
          <li><a href="#">Advertise</a></li>
          <li><a href="#">Contact</a></li>
        </ul>
      </div>
      <div class="footer__links">
        <h3>Account</h3>
        <ul>
          <li><a href="login.php">Sign In</a></li>
          <li><a href="register.php">Register</a></li>
          <?php if (isAdmin()): ?>
          <li><a href="admin/index.php">Admin Panel</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    <div class="footer__bottom">
      <p>&copy; <?= date('Y') ?> The Daily Chronicle. All rights reserved.</p>
    </div>
  </footer>

  <script src="script.js"></script>
  <script>
    // Newsletter AJAX submit
    document.getElementById('newsletterForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const email = document.getElementById('newsletterEmail').value;
      const msg   = document.getElementById('newsletterMsg');
      try {
        const res  = await fetch('api/subscribe.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'email=' + encodeURIComponent(email)
        });
        const data = await res.json();
        msg.textContent = data.message;
        msg.style.display = 'block';
        if (data.success) this.reset();
      } catch {
        msg.textContent = 'Something went wrong. Please try again.';
        msg.style.display = 'block';
      }
    });
  </script>
</body>
</html>
