# UniversityMedcialCenter

## Database Setup (Login & Signup)

- Import the seed SQL to create the `university_medical_center` database and `users` table.
- You can use phpMyAdmin (XAMPP) or the MySQL CLI.

### Using phpMyAdmin

- Open phpMyAdmin → Import → Select `database/seed.sql` → Execute.

### Using MySQL CLI (Windows/XAMPP)

```
mysql -u root < database/seed.sql
```

### Password Hashes

- The seed includes sample users with `CHANGE_ME_BCRYPT_HASH` placeholders.
- Replace those with real bcrypt hashes generated via PHP's `password_hash()` before trying to log in.
- Example (PHP):

```
<?php echo password_hash('Password123!', PASSWORD_DEFAULT); ?>
```

### App Connection

- The app reads DB settings from `includes/dp.php` (defaults: host `localhost`, user `root`, no password, DB `university_medical_center`).
