<?php
// ============================================================
//  SEARCH PAGE — search.php
// ============================================================

session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$query   = trim($_GET['q'] ?? '');
$results = [];

if ($query !== '') {
    $like    = '%' . $query . '%';
    $stmt    = $pdo->prepare("
        SELECT a.*, u.name AS author_name, c.name AS category_name
        FROM   articles a
        JOIN   users u      ON a.author_id   = u.id
        JOIN   categories c ON a.category_id = c.id
        WHERE  a.status = 'published'
          AND (a.title LIKE ? OR a.excerpt LIKE ?)
        ORDER  BY a.published_at DESC
        LIMIT  20
    ");
    $stmt->execute([$like, $like]);
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search<?= $query ? ' — ' . e($query) : '' ?> — The Daily Chronicle</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    .search-header { padding: 2.5rem 1rem 1.5rem; max-width:var(--max-width); margin:0 auto; }
    .search-bar { display:flex; gap:0.5rem; max-width:640px; }
    .search-bar input { flex:1; padding:0.75rem 1rem; border:1px solid var(--color-border); border-radius:4px 0 0 4px; font-size:1rem; font-family:var(--font-sans); outline:none; }
    .search-bar input:focus { border-color:var(--color-primary); }
    .search-bar button { padding:0.75rem 1.5rem; background:var(--color-primary); color:#fff; border:none; border-radius:0 4px 4px 0; font-weight:700; cursor:pointer; }
    .search-results { max-width:var(--max-width); margin:0 auto; padding:0 1rem 3rem; }
    .search-result-item { display:flex; gap:1.25rem; padding:1.25rem 0; border-bottom:1px solid var(--color-border); align-items:flex-start; }
    .search-result-item img { width:140px; height:90px; object-fit:cover; border-radius:4px; flex-shrink:0; }
    .search-result-item h3 { font-family:var(--font-serif); font-size:1.0625rem; margin-bottom:0.375rem; }
    .search-result-item p { font-size:0.875rem; color:var(--color-ink-muted); }
  </style>
</head>
<body>

  <!-- Ticker -->
  <div class="ticker" aria-label="Breaking news">
    <span class="ticker__label">Breaking</span>
    <div class="ticker__track">
      <p class="ticker__text">
        <?php
          $tk = $pdo->query("SELECT headline FROM breaking_news_ticker WHERE is_active=1 LIMIT 4")->fetchAll();
          echo implode(' &nbsp;•&nbsp; ', array_map(fn($r) => e($r['headline']), $tk));
        ?>
      </p>
    </div>
  </div>

  <!-- Header -->
  <header class="header">
    <div class="header__inner">
      <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
      <a href="index.php" class="logo">
        <span class="logo__mark">DC</span>
        <span class="logo__text">The Daily Chronicle</span>
      </a>
      <nav class="nav" id="mainNav"><ul class="nav__list">
        <li><a href="index.php" class="nav__link">Home</a></li>
      </ul></nav>
      <div class="header__actions">
        <?php if (isLoggedIn()): ?>
          <a href="logout.php" class="btn-subscribe">Logout</a>
        <?php else: ?>
          <a href="login.php" class="btn-subscribe">Sign In</a>
        <?php endif; ?>
      </div>
    </div>
  </header>
  <div class="nav-overlay" id="navOverlay" aria-hidden="true"></div>

  <!-- Search -->
  <div class="search-header">
    <h1 class="section-title" style="margin-bottom:1rem;">Search</h1>
    <form class="search-bar" method="GET" action="search.php">
      <input type="search" name="q" id="searchInput"
             value="<?= e($query) ?>"
             placeholder="Search articles..."
             aria-label="Search query"
             autofocus>
      <button type="submit">Search</button>
    </form>
  </div>

  <div class="search-results">
    <?php if ($query === ''): ?>
      <p style="color:var(--color-ink-muted);">Enter a search term above to find articles.</p>

    <?php elseif (empty($results)): ?>
      <p style="color:var(--color-ink-muted);">No articles found for "<strong><?= e($query) ?></strong>".</p>

    <?php else: ?>
      <p style="color:var(--color-ink-muted); margin-bottom:1.5rem;">
        <?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?> for "<strong><?= e($query) ?></strong>"
      </p>

      <?php foreach ($results as $r): ?>
      <div class="search-result-item">
        <?php if ($r['image_url']): ?>
        <img src="<?= e($r['image_url']) ?>" alt="<?= e($r['title']) ?>">
        <?php endif; ?>
        <div>
          <span class="card__category"><?= e($r['category_name']) ?></span>
          <h3><a href="article.php?slug=<?= e($r['slug']) ?>"><?= e($r['title']) ?></a></h3>
          <p><?= e($r['excerpt']) ?></p>
          <time class="card__date"><?= formatDate($r['published_at']) ?> — By <?= e($r['author_name']) ?></time>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <footer class="footer">
    <div class="footer__bottom">
      <p>&copy; <?= date('Y') ?> The Daily Chronicle. All rights reserved.</p>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
