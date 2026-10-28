<?php
// ============================================================
//  ARTICLE DETAIL PAGE
//  article.php  — displays full article by slug
// ============================================================

session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// ── Fetch article by slug ────────────────────────────────────
$slug = $_GET['slug'] ?? '';
if (empty($slug)) redirect('index.php');

$stmt = $pdo->prepare("
    SELECT a.*, u.name AS author_name, u.bio AS author_bio,
           c.name AS category_name, c.slug AS category_slug
    FROM   articles a
    JOIN   users u      ON a.author_id   = u.id
    JOIN   categories c ON a.category_id = c.id
    WHERE  a.slug = ? AND a.status = 'published'
");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) redirect('index.php');

// ── Related articles (same category) ────────────────────────
$relStmt = $pdo->prepare("
    SELECT a.title, a.slug, a.image_url, a.published_at, c.name AS category_name
    FROM   articles a
    JOIN   categories c ON a.category_id = c.id
    WHERE  a.category_id = ? AND a.id != ? AND a.status = 'published'
    ORDER  BY a.published_at DESC
    LIMIT  3
");
$relStmt->execute([$article['category_id'], $article['id']]);
$related = $relStmt->fetchAll();

// ── Comments ────────────────────────────────────────────────
$commentsStmt = $pdo->prepare("
    SELECT cm.*, u.name AS user_name
    FROM   comments cm
    JOIN   users u ON cm.user_id = u.id
    WHERE  cm.article_id = ?
    ORDER  BY cm.created_at ASC
");
$commentsStmt->execute([$article['id']]);
$comments = $commentsStmt->fetchAll();

// ── Handle comment POST ──────────────────────────────────────
$commentError = '';
$commentSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $body = trim($_POST['body'] ?? '');
    if (strlen($body) < 3) {
        $commentError = 'Comment is too short.';
    } else {
        $ins = $pdo->prepare("INSERT INTO comments (article_id, user_id, body) VALUES (?, ?, ?)");
        $ins->execute([$article['id'], $_SESSION['user_id'], $body]);
        $commentSuccess = 'Comment posted!';
        // Refresh comments
        $commentsStmt->execute([$article['id']]);
        $comments = $commentsStmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($article['title']) ?> — The Daily Chronicle</title>
  <meta name="description" content="<?= e($article['excerpt']) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    .article-page { max-width: 760px; margin: 2rem auto; padding: 0 1rem; }
    .article-page h1 { font-family: var(--font-serif); font-size: 2rem; line-height: 1.25; margin: 1rem 0; }
    .article-page .article-meta { display:flex; gap:1.5rem; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; }
    .article-page .article-body { font-size:1.0625rem; line-height:1.8; color:var(--color-ink); }
    .article-page .article-body p { margin-bottom:1.25rem; }
    .article-hero-img { width:100%; height:420px; object-fit:cover; border-radius:8px; margin-bottom:1.5rem; }
    .author-box { background:var(--color-bg); border:1px solid var(--color-border); border-radius:8px; padding:1.5rem; margin:2.5rem 0; }
    .comments-section { margin-top:3rem; border-top:2px solid var(--color-border); padding-top:2rem; }
    .comment { padding:1rem 0; border-bottom:1px solid var(--color-border); }
    .comment strong { color:var(--color-ink); }
    .comment p { margin-top:0.375rem; color:var(--color-ink-muted); font-size:0.9375rem; }
    .comment-form textarea { width:100%; min-height:100px; padding:0.75rem; border:1px solid var(--color-border); border-radius:4px; font-family:var(--font-sans); font-size:0.9375rem; margin:0.75rem 0; resize:vertical; }
    .comment-form button { padding:0.625rem 1.5rem; background:var(--color-primary); color:#fff; border:none; border-radius:4px; font-weight:700; cursor:pointer; }
    .related-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:1.25rem; margin-top:1.5rem; }
    .back-link { display:inline-flex; align-items:center; gap:0.375rem; font-size:0.875rem; font-weight:600; color:var(--color-primary); margin-bottom:1.5rem; }
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
      <nav class="nav" id="mainNav" aria-label="Main navigation">
        <ul class="nav__list">
          <li><a href="index.php" class="nav__link nav__link--active">Home</a></li>
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

  <!-- Article Content -->
  <main class="article-page">

    <a href="index.php" class="back-link">← Back to Home</a>
    <span class="card__badge" style="position:static; display:inline-block; margin-bottom:0.5rem;">
      <?= e($article['category_name']) ?>
    </span>

    <h1><?= e($article['title']) ?></h1>

    <div class="article-meta">
      <span class="card__author">By <?= e($article['author_name']) ?></span>
      <time datetime="<?= e($article['published_at']) ?>" class="card__date">
        <?= formatDate($article['published_at'], 'F j, Y') ?>
      </time>
    </div>

    <?php if ($article['image_url']): ?>
    <img src="<?= e($article['image_url']) ?>" alt="<?= e($article['title']) ?>" class="article-hero-img">
    <?php endif; ?>

    <div class="article-body">
      <?= $article['body'] /* body is trusted HTML stored by authors */ ?>
    </div>

    <!-- Author Box -->
    <?php if ($article['author_bio']): ?>
    <div class="author-box">
      <strong><?= e($article['author_name']) ?></strong>
      <p style="margin-top:0.375rem; color:var(--color-ink-muted); font-size:0.875rem;"><?= e($article['author_bio']) ?></p>
    </div>
    <?php endif; ?>

    <!-- Related Articles -->
    <?php if (!empty($related)): ?>
    <section>
      <h2 class="section-title" style="margin-bottom:0.5rem;">Related Stories</h2>
      <div class="related-grid">
        <?php foreach ($related as $r): ?>
        <article class="card">
          <?php if ($r['image_url']): ?>
          <img src="<?= e($r['image_url']) ?>" alt="<?= e($r['title']) ?>" class="card__image" style="height:140px;">
          <?php endif; ?>
          <div class="card__body">
            <h3 class="card__title" style="font-size:0.9375rem;">
              <a href="article.php?slug=<?= e($r['slug']) ?>"><?= e($r['title']) ?></a>
            </h3>
            <time class="card__date"><?= timeAgo($r['published_at']) ?></time>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- Comments -->
    <section class="comments-section">
      <h2 class="section-title">Comments (<?= count($comments) ?>)</h2>

      <?php if (!empty($commentSuccess)): ?>
        <p style="color:green; margin:0.75rem 0;"><?= e($commentSuccess) ?></p>
      <?php endif; ?>

      <?php foreach ($comments as $c): ?>
      <div class="comment">
        <strong><?= e($c['user_name']) ?></strong>
        <span class="card__date" style="margin-left:0.75rem;"><?= timeAgo($c['created_at']) ?></span>
        <p><?= e($c['body']) ?></p>
      </div>
      <?php endforeach; ?>

      <?php if (isLoggedIn()): ?>
      <form class="comment-form" method="POST" style="margin-top:1.5rem;">
        <label for="commentBody" style="font-weight:600;">Leave a Comment</label>
        <?php if ($commentError): ?>
          <p style="color:red;"><?= e($commentError) ?></p>
        <?php endif; ?>
        <textarea id="commentBody" name="body" placeholder="Write your comment..." required></textarea>
        <button type="submit">Post Comment</button>
      </form>
      <?php else: ?>
      <p style="margin-top:1.5rem; color:var(--color-ink-muted);">
        <a href="login.php" style="color:var(--color-primary); font-weight:600;">Sign in</a> to leave a comment.
      </p>
      <?php endif; ?>
    </section>

  </main>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer__bottom">
      <p>&copy; <?= date('Y') ?> The Daily Chronicle. All rights reserved.</p>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
