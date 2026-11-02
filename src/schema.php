<?php
/** Embedded copy of database.sql used by Installer (auto-generated). */
return <<<'SQLDATA'
-- ============================================================================
-- DevFolio PHP - database schema + sample data
-- Import:  mysql -u root -p < database.sql      (or phpMyAdmin -> Import)
-- Requires MySQL 5.7+ / MariaDB 10.3+ (utf8mb4)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `devfolio` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `devfolio`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS login_attempts, admin_users, contact_messages, portfolio_images, portfolio_items, portfolio_categories,
  testimonials, faqs, pricing_plans, stats, services, resume_items, skills, social_links, nav_menu, site_settings;
SET FOREIGN_KEY_CHECKS = 1;

-- Key/value store: hero, about, section titles, contact info, footer text ...
CREATE TABLE site_settings (
  setting_key   VARCHAR(64)  NOT NULL PRIMARY KEY,
  setting_value TEXT         NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE skills (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  percentage TINYINT UNSIGNED NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  is_active  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- section: summary | education | experience. 'bullets' = one <li> per line.
CREATE TABLE resume_items (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  section     ENUM('summary','education','experience') NOT NULL,
  title       VARCHAR(255) NOT NULL,
  period      VARCHAR(100) NULL,
  subtitle    TEXT NULL,
  description TEXT NULL,
  bullets     TEXT NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  KEY idx_section (section, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  icon        VARCHAR(64)  NOT NULL COMMENT 'Bootstrap Icons class, e.g. bi-activity',
  title       VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  is_active   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stats (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  label      VARCHAR(100) NOT NULL,
  value      INT UNSIGNED NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  is_active  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE portfolio_categories (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  slug       VARCHAR(100) NOT NULL UNIQUE COMMENT 'lowercase letters/digits/hyphens - used as CSS class filter-<slug>',
  sort_order INT NOT NULL DEFAULT 0,
  is_active  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE testimonials (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  position   VARCHAR(100) NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  rating     TINYINT UNSIGNED NOT NULL DEFAULT 5,
  quote      TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE portfolio_items (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  category_id       INT UNSIGNED NOT NULL,
  title             VARCHAR(150) NOT NULL,
  short_description VARCHAR(255) NOT NULL,
  image_path        VARCHAR(255) NOT NULL,
  details_title     VARCHAR(255) NULL,
  details_text      TEXT NULL COMMENT 'paragraphs separated by a blank line',
  client            VARCHAR(150) NULL,
  project_date      DATE NULL,
  project_url       VARCHAR(255) NULL,
  testimonial_id    INT UNSIGNED NULL,
  sort_order        INT NOT NULL DEFAULT 0,
  is_active         TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT fk_pi_cat  FOREIGN KEY (category_id)    REFERENCES portfolio_categories(id) ON DELETE RESTRICT,
  CONSTRAINT fk_pi_test FOREIGN KEY (testimonial_id) REFERENCES testimonials(id)         ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE portfolio_images (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  portfolio_item_id INT UNSIGNED NOT NULL,
  image_path        VARCHAR(255) NOT NULL,
  sort_order        INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_img_item FOREIGN KEY (portfolio_item_id) REFERENCES portfolio_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pricing_plans (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title      VARCHAR(150) NOT NULL,
  price      DECIMAL(10,2) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE faqs (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  question   VARCHAR(255) NOT NULL,
  answer     TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE social_links (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  icon       VARCHAR(64)  NOT NULL COMMENT 'Bootstrap Icons class, e.g. bi-twitter-x',
  url        VARCHAR(255) NOT NULL DEFAULT '#',
  sort_order INT NOT NULL DEFAULT 0,
  is_active  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Main website navigation (managed in admin > Menu)
CREATE TABLE nav_menu (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  label      VARCHAR(50)  NOT NULL,
  url        VARCHAR(255) NOT NULL DEFAULT '#',
  sort_order INT NOT NULL DEFAULT 0,
  is_active  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Public contact form submissions land here
CREATE TABLE contact_messages (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL,
  subject    VARCHAR(200) NOT NULL,
  message    TEXT NOT NULL,
  ip_address VARCHAR(45)  NULL,
  user_agent VARCHAR(255) NULL,
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_ip_created (ip_address, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------- CMS / admin tables ---------------------------
-- Admin accounts. password_hash is created with PHP password_hash() (bcrypt).
-- The first time the admin login page is opened with an EMPTY table, the default
-- account  admin / Admin@12345  is created automatically - change it after login.
CREATE TABLE admin_users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  last_login_at DATETIME NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Failed-login log used for brute-force lockout (5 failures / 15 min / IP)
CREATE TABLE login_attempts (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ip_address   VARCHAR(45)  NOT NULL,
  username     VARCHAR(100) NULL,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================ SAMPLE DATA ===================================

INSERT INTO site_settings (setting_key, setting_value) VALUES
  ('site_name', 'DevFolio'),
  ('site_logo', ''),
  ('meta_description', ''),
  ('meta_keywords', ''),
  ('hero_image', 'img/hero-img.jpg'),
  ('hero_title', 'I am Morgan Freeman'),
  ('hero_typed_items', 'Designer, Developer, Freelancer, Photographer'),
  ('about_image', 'img/profile-img.jpg'),
  ('about_heading', 'About me'),
  ('about_name', 'Morgan Freeman'),
  ('about_profile', 'full stack developer'),
  ('about_email', 'contact@example.com'),
  ('about_phone', '(617) 557-0089'),
  ('about_text', 'Curabitur non nulla sit amet nisl tempus convallis quis ac lectus. Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem. Praesent sapien massa, convallis a pellentesque nec, egestas non nisi. Nulla porttitor accumsan tincidunt.

Mauris blandit aliquet elit, eget tincidunt nibh pulvinar a. Vivamus suscipit tortor eget felis porttitor volutpat. Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. porttitor at sem.

Nulla porttitor accumsan tincidunt. Quisque velit nisi, pretium ut lacinia in, elementum id enim. Nulla porttitor accumsan tincidunt. Mauris blandit aliquet elit, eget tincidunt nibh pulvinar a.'),
  ('resume_title', 'Resume'),
  ('resume_subtitle', 'Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit'),
  ('services_title', 'Services'),
  ('services_subtitle', 'Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit'),
  ('portfolio_title', 'Portfolio'),
  ('portfolio_subtitle', 'Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit'),
  ('pricing_title', 'Pricing'),
  ('pricing_subtitle', 'Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit'),
  ('contact_title', 'Contact'),
  ('contact_subtitle', 'Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit'),
  ('resume_summary_heading', 'Sumary'),
  ('resume_education_heading', 'Education'),
  ('resume_experience_heading', 'Professional Experience'),
  ('stats_image', 'img/stats-bg.jpg'),
  ('testimonials_bg', 'img/testimonials-bg.jpg'),
  ('faq_intro', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Duis aute irure dolor in reprehenderit'),
  ('contact_address', 'A108 Adam Street, New York, NY 535022'),
  ('contact_phone', '+1 5589 55488 55'),
  ('contact_email', 'info@example.com');

INSERT INTO skills (name, percentage, sort_order) VALUES
  ('HTML', 100, 1),
  ('CSS', 90, 2),
  ('JavaScript', 75, 3),
  ('Photoshop', 55, 4);

INSERT INTO resume_items (section, title, period, subtitle, description, bullets, sort_order) VALUES
  ('summary', 'Brandon Johnson', NULL, 'Innovative and deadline-driven Graphic Designer with 3+ years of experience designing and developing user-centered digital/print marketing material from initial concept to final, polished deliverable.', NULL, 'Portland par 127,Orlando, FL
(123) 456-7891
alice.barkley@example.com', 1),
  ('education', 'Master of Fine Arts & Graphic Design', '2015 - 2016', 'Rochester Institute of Technology, Rochester, NY', 'Qui deserunt veniam. Et sed aliquam labore tempore sed quisquam iusto autem sit. Ea vero voluptatum qui ut dignissimos deleniti nerada porti sand markend', NULL, 1),
  ('education', 'Bachelor of Fine Arts & Graphic Design', '2010 - 2014', 'Rochester Institute of Technology, Rochester, NY', 'Quia nobis sequi est occaecati aut. Repudiandae et iusto quae reiciendis et quis Eius vel ratione eius unde vitae rerum voluptates asperiores voluptatem Earum molestiae consequatur neque etlon sader mart dila', NULL, 2),
  ('experience', 'Senior graphic design specialist', '2019 - Present', 'Experion, New York, NY', NULL, 'Lead in the design, development, and implementation of the graphic, layout, and production communication materials
Delegate tasks to the 7 members of the design team and provide counsel on all aspects of the project.
Supervise the assessment of all graphic materials in order to ensure quality and accuracy of the design
Oversee the efficient use of production project budgets ranging from $2,000 - $25,000', 1),
  ('experience', 'Graphic design specialist', '2017 - 2018', 'Stepping Stone Advertising, New York, NY', NULL, 'Developed numerous marketing programs (logos, brochures,infographics, presentations, and advertisements).
Managed up to 5 projects or tasks at a given time while under pressure
Recommended and consulted with clients on the most appropriate graphic design
Created 4+ design presentations and proposals a month for clients and account managers', 2);

INSERT INTO services (icon, title, description, sort_order) VALUES
  ('bi-activity', 'Nesciunt Mete', 'Provident nihil minus qui consequatur non omnis maiores. Eos accusantium minus dolores iure perferendis tempore et consequatur.', 1),
  ('bi-broadcast', 'Eosle Commodi', 'Ut autem aut autem non a. Sint sint sit facilis nam iusto sint. Libero corrupti neque eum hic non ut nesciunt dolorem.', 2),
  ('bi-easel', 'Ledo Markt', 'Ut excepturi voluptatem nisi sed. Quidem fuga consequatur. Minus ea aut. Vel qui id voluptas adipisci eos earum corrupti.', 3),
  ('bi-bounding-box-circles', 'Asperiores Commodit', 'Non et temporibus minus omnis sed dolor esse consequatur. Cupiditate sed error ea fuga sit provident adipisci neque.', 4),
  ('bi-calendar4-week', 'Velit Doloremque', 'Cumque et suscipit saepe. Est maiores autem enim facilis ut aut ipsam corporis aut. Sed animi at autem alias eius labore.', 5),
  ('bi-chat-square-text', 'Dolori Architecto', 'Hic molestias ea quibusdam eos. Fugiat enim doloremque aut neque non et debitis iure. Corrupti recusandae ducimus enim.', 6);

INSERT INTO stats (label, value, sort_order) VALUES
  ('Clients', 232, 1),
  ('Projects', 521, 2),
  ('Hours Of Support', 1453, 3),
  ('Awards', 32, 4);

INSERT INTO portfolio_categories (name, slug, sort_order) VALUES
  ('App', 'app', 1),
  ('Product', 'product', 2),
  ('Branding', 'branding', 3),
  ('Books', 'books', 4);

INSERT INTO testimonials (name, position, image_path, rating, quote, sort_order) VALUES
  ('Saul Goodman', 'Ceo & Founder', 'img/testimonials-1.jpg', 5, 'Proin iaculis purus consequat sem cure digni ssim donec porttitora entum suscipit rhoncus. Accusantium quam, ultricies eget id, aliquam eget nibh et. Maecen aliquam, risus at semper.', 1),
  ('Sara Wilsson', 'Designer', 'img/testimonials-2.jpg', 5, 'Export tempor illum tamen malis malis eram quae irure esse labore quem cillum quid cillum eram malis quorum velit fore eram velit sunt aliqua noster fugiat irure amet legam anim culpa.', 2),
  ('Jena Karlis', 'Store Owner', 'img/testimonials-3.jpg', 5, 'Enim nisi quem export duis labore cillum quae magna enim sint quorum nulla quem veniam duis minim tempor labore quem eram duis noster aute amet eram fore quis sint minim.', 3),
  ('Matt Brandon', 'Freelancer', 'img/testimonials-4.jpg', 5, 'Fugiat enim eram quae cillum dolore dolor amet nulla culpa multos export minim fugiat minim velit minim dolor enim duis veniam ipsum anim magna sunt elit fore quem dolore labore illum veniam.', 4),
  ('John Larson', 'Entrepreneur', 'img/testimonials-5.jpg', 5, 'Quis quorum aliqua sint quem legam fore sunt eram irure aliqua veniam tempor noster veniam enim culpa labore duis sunt culpa nulla illum cillum fugiat legam esse veniam culpa fore nisi cillum quid.', 5);

INSERT INTO portfolio_items (category_id, title, short_description, image_path, details_title, details_text, client, project_date, project_url, testimonial_id, sort_order) VALUES
  (1, 'App 1', 'Lorem ipsum, dolor sit amet consectetur', 'img/app-1.jpg', 'This is an example of portfolio details', 'Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.

Amet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.

Impedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.

Sunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.', 'ASU Company', '2020-03-01', 'https://www.example.com', 2, 1),
  (2, 'Product 1', 'Lorem ipsum, dolor sit amet consectetur', 'img/product-1.jpg', 'This is an example of portfolio details', 'Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.

Amet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.

Impedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.

Sunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.', 'ASU Company', '2020-03-01', 'https://www.example.com', 2, 2),
  (3, 'Branding 1', 'Lorem ipsum, dolor sit amet consectetur', 'img/branding-1.jpg', 'This is an example of portfolio details', 'Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.

Amet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.

Impedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.

Sunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.', 'ASU Company', '2020-03-01', 'https://www.example.com', 2, 3),
  (4, 'Books 1', 'Lorem ipsum, dolor sit amet consectetur', 'img/books-1.jpg', 'This is an example of portfolio details', 'Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.

Amet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.

Impedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.

Sunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.', 'ASU Company', '2020-03-01', 'https://www.example.com', 2, 4),
  (1, 'App 2', 'Lorem ipsum, dolor sit amet consectetur', 'img/app-2.jpg', 'This is an example of portfolio details', 'Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.

Amet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.

Impedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.

Sunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.', 'ASU Company', '2020-03-01', 'https://www.example.com', 2, 5),
  (2, 'Product 2', 'Lorem ipsum, dolor sit amet consectetur', 'img/product-2.jpg', 'This is an example of portfolio details', 'Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.

Amet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.

Impedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.

Sunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.', 'ASU Company', '2020-03-01', 'https://www.example.com', 2, 6),
  (3, 'Branding 2', 'Lorem ipsum, dolor sit amet consectetur', 'img/branding-2.jpg', 'This is an example of portfolio details', 'Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.

Amet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.

Impedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.

Sunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.', 'ASU Company', '2020-03-01', 'https://www.example.com', 2, 7),
  (4, 'Books 2', 'Lorem ipsum, dolor sit amet consectetur', 'img/books-2.jpg', 'This is an example of portfolio details', 'Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.

Amet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.

Impedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.

Sunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.', 'ASU Company', '2020-03-01', 'https://www.example.com', 2, 8),
  (1, 'App 3', 'Lorem ipsum, dolor sit amet consectetur', 'img/app-3.jpg', 'This is an example of portfolio details', 'Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.

Amet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.

Impedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.

Sunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.', 'ASU Company', '2020-03-01', 'https://www.example.com', 2, 9),
  (2, 'Product 3', 'Lorem ipsum, dolor sit amet consectetur', 'img/product-3.jpg', 'This is an example of portfolio details', 'Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.

Amet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.

Impedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.

Sunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.', 'ASU Company', '2020-03-01', 'https://www.example.com', 2, 10),
  (3, 'Branding 3', 'Lorem ipsum, dolor sit amet consectetur', 'img/branding-3.jpg', 'This is an example of portfolio details', 'Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.

Amet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.

Impedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.

Sunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.', 'ASU Company', '2020-03-01', 'https://www.example.com', 2, 11),
  (4, 'Books 3', 'Lorem ipsum, dolor sit amet consectetur', 'img/books-3.jpg', 'This is an example of portfolio details', 'Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.

Amet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.

Impedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.

Sunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.', 'ASU Company', '2020-03-01', 'https://www.example.com', 2, 12);

INSERT INTO portfolio_images (portfolio_item_id, image_path, sort_order) VALUES
  (1, 'img/app-1.jpg', 1),
  (1, 'img/product-1.jpg', 2),
  (1, 'img/branding-1.jpg', 3),
  (1, 'img/books-1.jpg', 4),
  (2, 'img/app-1.jpg', 1),
  (2, 'img/product-1.jpg', 2),
  (2, 'img/branding-1.jpg', 3),
  (2, 'img/books-1.jpg', 4),
  (3, 'img/app-1.jpg', 1),
  (3, 'img/product-1.jpg', 2),
  (3, 'img/branding-1.jpg', 3),
  (3, 'img/books-1.jpg', 4),
  (4, 'img/app-1.jpg', 1),
  (4, 'img/product-1.jpg', 2),
  (4, 'img/branding-1.jpg', 3),
  (4, 'img/books-1.jpg', 4),
  (5, 'img/app-1.jpg', 1),
  (5, 'img/product-1.jpg', 2),
  (5, 'img/branding-1.jpg', 3),
  (5, 'img/books-1.jpg', 4),
  (6, 'img/app-1.jpg', 1),
  (6, 'img/product-1.jpg', 2),
  (6, 'img/branding-1.jpg', 3),
  (6, 'img/books-1.jpg', 4),
  (7, 'img/app-1.jpg', 1),
  (7, 'img/product-1.jpg', 2),
  (7, 'img/branding-1.jpg', 3),
  (7, 'img/books-1.jpg', 4),
  (8, 'img/app-1.jpg', 1),
  (8, 'img/product-1.jpg', 2),
  (8, 'img/branding-1.jpg', 3),
  (8, 'img/books-1.jpg', 4),
  (9, 'img/app-1.jpg', 1),
  (9, 'img/product-1.jpg', 2),
  (9, 'img/branding-1.jpg', 3),
  (9, 'img/books-1.jpg', 4),
  (10, 'img/app-1.jpg', 1),
  (10, 'img/product-1.jpg', 2),
  (10, 'img/branding-1.jpg', 3),
  (10, 'img/books-1.jpg', 4),
  (11, 'img/app-1.jpg', 1),
  (11, 'img/product-1.jpg', 2),
  (11, 'img/branding-1.jpg', 3),
  (11, 'img/books-1.jpg', 4),
  (12, 'img/app-1.jpg', 1),
  (12, 'img/product-1.jpg', 2),
  (12, 'img/branding-1.jpg', 3),
  (12, 'img/books-1.jpg', 4);

INSERT INTO pricing_plans (title, price, sort_order) VALUES
  ('Portrait Photography', 160.00, 1),
  ('Fashion Photography', 300.00, 2),
  ('Sports Photography', 200.00, 3),
  ('Still Life Photography', 120.00, 4),
  ('Wedding Photography', 500.00, 5),
  ('Photojournalism', 200.00, 6);

INSERT INTO faqs (question, answer, sort_order) VALUES
  ('Non consectetur a erat nam at lectus urna duis?', 'Feugiat pretium nibh ipsum consequat. Tempus iaculis urna id volutpat lacus laoreet non curabitur gravida. Venenatis lectus magna fringilla urna porttitor rhoncus dolor purus non.', 1),
  ('Feugiat scelerisque varius morbi enim nunc faucibus a pellentesque?', 'Dolor sit amet consectetur adipiscing elit pellentesque habitant morbi. Id interdum velit laoreet id donec ultrices. Fringilla phasellus faucibus scelerisque eleifend donec pretium. Est pellentesque elit ullamcorper dignissim. Mauris ultrices eros in cursus turpis massa tincidunt dui.', 2),
  ('Dolor sit amet consectetur adipiscing elit pellentesque?', 'Eleifend mi in nulla posuere sollicitudin aliquam ultrices sagittis orci. Faucibus pulvinar elementum integer enim. Sem nulla pharetra diam sit amet nisl suscipit. Rutrum tellus pellentesque eu tincidunt. Lectus urna duis convallis convallis tellus. Urna molestie at elementum eu facilisis sed odio morbi quis', 3),
  ('Ac odio tempor orci dapibus. Aliquam eleifend mi in nulla?', 'Dolor sit amet consectetur adipiscing elit pellentesque habitant morbi. Id interdum velit laoreet id donec ultrices. Fringilla phasellus faucibus scelerisque eleifend donec pretium. Est pellentesque elit ullamcorper dignissim. Mauris ultrices eros in cursus turpis massa tincidunt dui.', 4),
  ('Tempus quam pellentesque nec nam aliquam sem et tortor consequat?', 'Molestie a iaculis at erat pellentesque adipiscing commodo. Dignissim suspendisse in est ante in. Nunc vel risus commodo viverra maecenas accumsan. Sit amet nisl suscipit adipiscing bibendum est. Purus gravida quis blandit turpis cursus in', 5);

INSERT INTO social_links (icon, url, sort_order) VALUES
  ('bi-twitter-x', '#', 1),
  ('bi-facebook', '#', 2),
  ('bi-instagram', '#', 3),
  ('bi-linkedin', '#', 4);

-- One sample message so the table is not empty (delete if you like)
INSERT INTO contact_messages (name, email, subject, message, ip_address) VALUES
  ('Sample Visitor', 'visitor@example.com', 'Welcome', 'This is a sample message stored by the contact form.', '127.0.0.1');

-- View received messages:  SELECT * FROM contact_messages ORDER BY created_at DESC;

-- Optional hero extras (empty => hidden, so the public design stays identical to the template)
INSERT INTO site_settings (setting_key, setting_value) VALUES
  ('hero_subtitle', ''),
  ('hero_cta_text', ''),
  ('hero_cta_url', '#contact'),
  ('page_banner_image', 'img/hero-img.jpg');

-- Default navigation items (add/edit/delete in admin > Menu)
INSERT INTO nav_menu (label, url, sort_order) VALUES
  ('Home', 'index.php', 1),
  ('About', 'about.php', 2),
  ('Services', 'services.php', 3),
  ('Portfolio', 'portfolio.php', 4),
  ('Contact', 'contact.php', 5);
SQLDATA;
