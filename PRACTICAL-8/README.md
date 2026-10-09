# Student Hub Portal - Practical 7
**PHP Form Processing with Server-Side Validation and CSV/JSON File Storage**

## How to run
1. Copy the whole `PRACTICAL-7` folder into `C:\xampp\htdocs\` (or `/var/www/html/` on LAMP).
2. Start **Apache** from XAMPP/WAMP.
3. Open `http://localhost/PRACTICAL-7/reg.php` (registration) or `http://localhost/PRACTICAL-7/CONTACT.php` (contact form).
   > Do NOT open with VS Code Live Server (port 5501) - it cannot run PHP.
4. Stored data: `storage/registrations.csv|json`, `storage/contacts.csv|json`.
5. View stored records in the browser: `RECORDS.php`.

## Key questions mapping
| Key question | Where it is handled |
|---|---|
| 1. Is the form submitted using POST? | `method="POST"` in the forms; `$_SERVER['REQUEST_METHOD'] === 'POST'` check at the top of `CONTACT.php`, `reg.php`, `login.php` |
| 2. Are inputs validated and sanitized server side? | `clean_text()` (strip tags, control chars, whitespace) + per-field rules in `CONTACT.php` / `reg.php`; output escaped with `e()` |
| 3. Is file writing handled safely? | `save_record()` in `includes/helpers.php`: folder checks, exclusive `flock`, temp-file + rename for JSON, CSV formula-injection guard, `storage/.htaccess` blocks direct download, password saved only as `password_hash()` |
| 4. Are success/error responses clear? | Green/red alert boxes (`php-forms.css`), all errors listed, entered values kept, Post/Redirect/Get after success |

## Extensions
* **Intermediate** - `RECORDS.php` shows stored CSV/JSON records in a table (JSON/CSV toggle, passwords never displayed).
* **Advanced** - CSRF token: generated per session (`random_bytes`), hidden field in both forms, verified with `hash_equals()` on POST, rotated after success. Invalid token => HTTP 403.

## Test cases
| # | Form | Input | Expected |
|---|------|-------|----------|
| 1 | Contact | All fields valid | Green success message, row added to contacts.csv + json |
| 2 | Contact | Empty fields | Error list, nothing saved |
| 3 | Contact | Email `abc@` | "Please enter a valid email address." |
| 4 | Contact | Name `John123` | Name rule error |
| 5 | Contact | Message `Hi` (<10 chars) | Message length error |
| 6 | Contact | Subject `<script>alert(1)</script>Help` | Tags stripped (saved as `alert(1)Help`), never executed on RECORDS.php |
| 7 | Contact | Subject `=1+1` | Saved in JSON as-is, in CSV as `'=1+1` (formula-injection guard) |
| 8 | Contact | Open `CONTACT.php` URL directly (GET) | Form shown, nothing stored |
| 9 | Contact/Reg | Remove/alter `csrf_token` in dev tools then submit | "Security check failed" (403) |
| 10 | Reg | Weak password `abc` | Password rule errors |
| 11 | Reg | Password != Confirm | Mismatch error |
| 12 | Reg | Existing email or Student ID | "already registered" error |
| 13 | Reg | Valid data | Success, row in registrations.csv/json with hashed password |
| 14 | Login | Registered ID + correct password | Login Successful |
| 15 | Login | Wrong password | Login Failed |
| 16 | Any | Visit `/storage/contacts.json` in browser | 403 Forbidden (Apache) |

Note: `novalidate` is set on the forms so that the **server-side** messages can be demonstrated even though
browser/JS checks exist too. Remove it if you prefer browser pop-ups first.

## Folder structure
```
PRACTICAL-7/
 CONTACT.php  reg.php  login.php  RECORDS.php   <- PHP processors / pages
 LOGIN.html  INDEX.html ... (portal pages)
 includes/helpers.php                           <- CSRF, sanitize, safe storage
 storage/  contacts.csv|json  registrations.csv|json  .htaccess
 php-forms.css                                  <- message + records styles
```
