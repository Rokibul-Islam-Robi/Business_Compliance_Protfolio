# DevFolio – PHP + MySQL with Admin CMS

The public site keeps the original DevFolio design (CSS/JS/markup untouched). Everything on it is rendered from MySQL and managed from `/admin`.

## Requirements
XAMPP (or any Apache/Nginx + PHP 8.1+ with `pdo_mysql`, `mbstring`, `fileinfo`) and MySQL 5.7+ / MariaDB 10.3+.

## Install (XAMPP)
1. Copy the `devfolio` folder into `C:\xampp\htdocs\` (or `/opt/lampp/htdocs/`).
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open the website - the `devfolio` database, all tables and the sample data are **created automatically** from `database.sql` on first visit. (Manual alternative: phpMyAdmin → **Import** → `database.sql`. Warning: a manual import drops and recreates all tables.)
4. If your MySQL user/password is not `root` / empty, edit the constants at the top of `db.php`.
5. Website: <http://localhost/devfolio/> · Admin: <http://localhost/devfolio/admin/>

**Default admin login:** `admin` / `Admin@12345` (created automatically on first visit to the login page). The dashboard warns you until you change it under *My Account*.

The `uploads/` folder must be writable by the web server (it is on XAMPP by default; on Linux: `chmod -R 775 uploads`).

## Admin features
| Page | What you can manage |
|---|---|
| Dashboard | metric cards, recent messages, quick actions |
| Hero | title, typing text list, optional subtitle, background image, optional CTA button |
| About & Skills | bio, personal details, profile image; skills CRUD (progress bars) |
| Resume | headings + summary / education / experience entries CRUD |
| Services | heading + service cards CRUD (Bootstrap icon class, title, description) |
| Counters | background image + counters CRUD |
| Projects / Categories | projects CRUD (cover + gallery uploads, category, client, URL, date, description, linked testimonial); category CRUD |
| Pricing, FAQ | headings/intro + CRUD |
| Testimonials | background + reviews CRUD (photo, designation, 1–5 rating) |
| Contact & Social | address / phone / email, social links CRUD |
| Messages | contact-form inbox: read, mark read/unread, delete, filters, pagination |
| Site Settings / My Account | site name & SEO tags; change username, email, password |

Icons use **Bootstrap Icons** (`bi-…`) because that is the icon font the template ships with.
The hero subtitle and CTA button are hidden while empty, so the default public design is identical to the template.

## Security
PDO prepared statements everywhere · `password_hash`/`password_verify` · session cookie HttpOnly + SameSite, id regenerated on login, 30-min idle timeout · login lockout (5 failures / 15 min / IP) · CSRF token on every POST · all output escaped with `htmlspecialchars` · uploads: size limit, real MIME check (finfo), must decode as image, random server-side filename, no SVG, script execution disabled in `uploads/` via `.htaccess`.

## Structure
```
db.php                 Database class (PDO)           database.sql   schema + seed data
index.php, portfolio-details.php   public site         forms/contact.php  contact form -> MySQL
includes/              public header/footer/data helpers
src/                   OOP core: Auth, Csrf, Flash, Uploader, Crud, CrudPage, SettingsForm, helpers
admin/                 login, dashboard, section pages, partials/, assets/
uploads/               user uploads (protected)         css/ js/ img/ fonts/  original template assets
```
