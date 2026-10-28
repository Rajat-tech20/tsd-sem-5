-- ============================================================
--  THE DAILY CHRONICLE -- Database Schema + Seed Data
-- ============================================================

CREATE DATABASE IF NOT EXISTS chronicle_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE chronicle_db;

CREATE TABLE IF NOT EXISTS users (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100)  NOT NULL,
  email      VARCHAR(150)  NOT NULL UNIQUE,
  password   VARCHAR(255)  NOT NULL,
  role       ENUM('admin','author','reader') NOT NULL DEFAULT 'reader',
  avatar_url VARCHAR(300)  DEFAULT NULL,
  bio        TEXT          DEFAULT NULL,
  created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  slug VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS articles (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(300) NOT NULL,
  slug         VARCHAR(300) NOT NULL UNIQUE,
  excerpt      TEXT         DEFAULT NULL,
  body         LONGTEXT     DEFAULT NULL,
  image_url    VARCHAR(300) DEFAULT NULL,
  author_id    INT UNSIGNED NOT NULL,
  category_id  INT UNSIGNED NOT NULL,
  is_featured  TINYINT(1)   NOT NULL DEFAULT 0,
  status       ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  published_at DATETIME     DEFAULT NULL,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id)   REFERENCES users(id)      ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comments (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  article_id INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  body       TEXT         NOT NULL,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscribers (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(150) NOT NULL UNIQUE,
  subscribed_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS breaking_news_ticker (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  headline   VARCHAR(300) NOT NULL,
  is_active  TINYINT(1)   NOT NULL DEFAULT 1,
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (name, email, password, role, bio) VALUES
('Admin User','admin@chronicle.com','$2y$12$eImiTXuWVxfM37uY4JANjOe5XscKhbhMeRLYl3b49LiWBtcJqfx2K','admin','Site administrator.'),
('Sarah Mitchell','sarah@chronicle.com','$2y$12$eImiTXuWVxfM37uY4JANjOe5XscKhbhMeRLYl3b49LiWBtcJqfx2K','author','Senior political correspondent.'),
('James Carter','james@chronicle.com','$2y$12$eImiTXuWVxfM37uY4JANjOe5XscKhbhMeRLYl3b49LiWBtcJqfx2K','author','Business and markets reporter.'),
('Priya Nair','priya@chronicle.com','$2y$12$eImiTXuWVxfM37uY4JANjOe5XscKhbhMeRLYl3b49LiWBtcJqfx2K','author','Technology journalist.'),
('Dr. Elena Vasquez','elena@chronicle.com','$2y$12$eImiTXuWVxfM37uY4JANjOe5XscKhbhMeRLYl3b49LiWBtcJqfx2K','author','Guest contributor.');

INSERT INTO categories (name, slug) VALUES
('World','world'),('Politics','politics'),('Business','business'),
('Tech','tech'),('Sports','sports'),('Health','health'),
('Culture','culture'),('Opinion','opinion');

INSERT INTO articles (title, slug, excerpt, body, image_url, author_id, category_id, is_featured, status, published_at) VALUES
('Global Summit Reaches Historic Agreement on Carbon Emissions','global-summit-historic-carbon-agreement','World leaders commit to binding targets in a deal hailed as the most ambitious climate framework since the Paris Accord.','<p>In a landmark moment for global climate policy, world leaders have reached a historic agreement on carbon emissions. The deal commits all signatory nations to binding targets for carbon reduction, a 500 billion dollar climate finance fund, and net-zero by 2050.</p>','https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=1200&h=700&fit=crop',2,2,1,'published','2026-08-17 08:00:00'),
('Markets Rally as Inflation Data Beats Expectations','markets-rally-inflation-data','Global stock markets surged after new data showed inflation cooling faster than analysts predicted.','<p>Global stock markets rallied sharply after official inflation figures came in well below analyst forecasts, sparking optimism that central banks may begin easing interest rates sooner than expected.</p>','https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=400&h=300&fit=crop',3,3,0,'published','2026-08-17 06:00:00'),
('Underdogs Stun Champions in Season Opener','underdogs-stun-champions-season-opener','In a shocking result, the league newcomers defeated the reigning champions 3-1 in front of a sold-out crowd.','<p>Newly promoted Westford City defeated reigning champions Northern United 3-1 in the season opener, sending shockwaves through the league.</p>','https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=400&h=300&fit=crop',2,5,0,'published','2026-08-17 04:00:00'),
('AI Regulation Bill Clears First Hurdle in Senate','ai-regulation-bill-senate','The bipartisan AI Accountability Act passed committee vote 12-3, setting the stage for a full Senate floor debate.','<p>The AI Accountability Act passed the Senate Commerce Committee with a 12-3 vote, requiring AI companies to conduct independent safety audits.</p>','https://images.unsplash.com/photo-1518770660439-4636190af475?w=400&h=300&fit=crop',4,4,0,'published','2026-08-17 03:00:00'),
('Urban Renewal Project Transforms Historic District','urban-renewal-historic-district','A decade-long initiative brings green spaces and affordable housing to the city center.','<p>The city Urban Renewal Project has completed its first major phase, transforming the neglected historic district into a vibrant mixed-use neighbourhood.</p>','https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?w=600&h=400&fit=crop',2,1,0,'published','2026-08-17 02:00:00'),
('Breakthrough Vaccine Shows Promise in Clinical Trials','breakthrough-vaccine-clinical-trials','Researchers report 94% efficacy in phase-three trials for a new respiratory treatment.','<p>Scientists announced breakthrough results from phase-three trials for a next-generation respiratory vaccine with 94% efficacy across 45,000 participants.</p>','https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=600&h=400&fit=crop',3,6,0,'published','2026-08-16 10:00:00'),
('Remote Work Policies Reshape Corporate Real Estate','remote-work-corporate-real-estate','Major firms reduce office footprints as hybrid models become permanent.','<p>The hybrid work model is fundamentally reshaping how companies think about office space, according to a new industry report.</p>','https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&h=400&fit=crop',3,3,0,'published','2026-08-16 09:00:00'),
('Olympic Qualifiers Draw Record Global Audience','olympic-qualifiers-record-audience','Streaming platforms report viewership surpassing traditional broadcast numbers.','<p>Olympic qualifying events drew a record-breaking global audience with streaming platforms surpassing traditional broadcast viewership for the first time.</p>','https://images.unsplash.com/photo-1461896836934-ffe607ba8121?w=600&h=400&fit=crop',2,5,0,'published','2026-08-15 08:00:00'),
('Humanoid Robots Enter Mainstream Manufacturing','humanoid-robots-manufacturing','Automakers deploy bipedal robots on assembly lines for the first time at scale.','<p>Three of the world largest automakers have begun deploying bipedal humanoid robots on assembly lines in the first large-scale commercial rollout of such technology.</p>','https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=600&h=400&fit=crop',4,4,0,'published','2026-08-15 07:00:00'),
('International Film Festival Opens to Standing Ovations','film-festival-standing-ovations','Opening night premiere receives longest applause in the festival 30-year history.','<p>The 30th International Film Festival opened to rapturous applause, with the opening night premiere receiving an 11-minute standing ovation.</p>','https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=600&h=400&fit=crop',2,7,0,'published','2026-08-14 18:00:00'),
('Why Education Reform Cannot Wait Another Decade','education-reform-cannot-wait','Our education system was designed for a world that no longer exists. The time for incremental change is over.','<p>Children today will work in jobs that do not yet exist. Our curriculum remains frozen in the industrial age. We owe the next generation a system built for their world.</p>',NULL,2,8,0,'published','2026-08-17 07:00:00'),
('The Quiet Revolution in Renewable Energy Storage','quiet-revolution-renewable-energy-storage','Battery technology advances are outpacing predictions and reshaping the energy landscape faster than policy can follow.','<p>Battery storage is advancing at a pace that is outstripping even the most optimistic industry forecasts, quietly transforming how we store and distribute clean energy.</p>',NULL,5,8,0,'published','2026-08-16 08:00:00');

INSERT INTO breaking_news_ticker (headline, is_active) VALUES
('Parliament passes landmark climate bill',1),
('Tech giants report record quarterly earnings',1),
('National team advances to World Cup semifinals',1),
('New metro line opens in capital city',1);