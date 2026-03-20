# copilot-instructions.md

## Γενικές Οδηγίες
Το όνομά μου είναι Δημήτρης. Να μου μιλάς στον ενικό. Μην επεξεργάζεσαι πολλά αρχεία χωρίς έγκριση από μένα. Όταν θέλεις να επεξεργαστείς ένα αρχείο που δεν σου έχω επισημάνει και θέλεις να το επεξεργαστείς, ζήτα την έγκρισή μου πρώτα. Αν σου το έχω επισημάνει στο prompt, επεξεργάσου το χωρίς έγκριση. Μην ξεκινάς κάποιο server μετά από κάποια αλλαγή - Ζήτησέ μου να ελέγξω εγώ αν κάτι δουλεύει ή όχι. Μην υλοποιήσεις κώδικα για testing.

## Project
Tο project υλοποιεί μια custom φόρμα για το osTicket της εταιρίας Nipponia (nipponia.com) η οποία, κατά τη συμπλήρωσή της, θα αντλεί συμπληρωματικά δεδομένα από ERP συστήματα της εταιρίας και, κατά την υποβολή της, θα δημιουργεί ένα ticket στο osTicket API. 

## Δομή και φάκελοι
Το root αυτού του project είναι ο root φάκελος του osTicket. Είναι σε development περιβάλλον και σερβίρεται από το xampp. Το αντίστοιχο production website σερβίρεται στη διεύθυνση https://ticketing.nipponia.com από έναν Apache server που τρέχει στο cPanel. Η φόρμα θα είναι προσβάσιμη στο `new.php`.

## Database
Θα χρησιμοποιείται η Βάση Δεδομένων του osTicket για την είσοδο των χρηστών κάποια άλλη αποθήκευση (αν χρειαστεί, σε ξεχωριστό πίνακα).

## Login
Η φόρμα θα είναι προσβάσιμη μόνο σε εγγεγραμμένους χρήστες. Θα χρησιμοποιεί το σύστημα login του osTicket. 

## Πολυγλωσσικότητα
Η φόρμα θα πρέπει να υποστηρίζει πολλαπλές γλώσσες. Προς το παρόν, θα υποσττηρίζονται τα Αγγλικά και τα Ισπανικά, αλλά θα πρέπει να είναι εύκολο να προστεθούν και άλλες γλώσσες στο μέλλον. Θα πρέπει να υλοποιηθεί ένα σύστημα για τη διαχείριση των μεταφράσεων, ώστε να είναι εύκολο να προστεθούν νέες γλώσσες και να ενημερωθούν οι υπάρχουσες μεταφράσεις.

## Δομή Αρχείων (υλοποιημένη)

**Μόνο τα παρακάτω αρχεία ανήκουν στο custom project και επιτρέπεται να υποστούν επεξεργασία.** Οτιδήποτε άλλο στο workspace είναι αρχείο του osTicket και δεν πρέπει να πειραχτεί.

```
new.php                  ← root entry point, κάνει require form/form.php
form/
  form-bootstrap.php     ← αρχικοποίηση auth, session και translations για τη φόρμα
  config.php             ← constants, API keys, settings (αποκλεισμένο από git και browser)
  config.example.php     ← template για το config.php (συμπεριλαμβάνεται στο git)
  form.php               ← HTML page skeleton (Bootstrap 5, session, lang resolution)
  header.php             ← κοινό HTML header / navbar
  footer.php             ← κοινό HTML footer
  submit.php             ← POST endpoint που στέλνει στο osTicket API
  translations.php       ← t(), form_load_language(), form_resolve_language()
  lang/
    en.php               ← English translation strings
  css/
    style.css            ← custom styles
  resources/             ← static assets (εικόνες, κλπ.)
  .gitignore             ← εξαιρεί config.php και debug.log
  .htaccess              ← Apache: Require all denied για config.php
```

Τα υπόλοιπα αρχεία στο workspace (`login.php`, `open.php`, `client.inc.php`, `secure.inc.php`, `include/`, κλπ.) είναι αρχεία του osTicket που συμπεριλαμβάνονται **μόνο για αναφορά** κατά την ανάπτυξη.

## Πλάνο Υλοποίησης
1. ✅ **ΟΛΟΚΛΗΡΩΘΗΚΕ** — Δομή αρχείων, config, translation layer, skeleton φόρμα, submit endpoint stub.
2. ✅ **ΟΛΟΚΛΗΡΩΘΗΚΕ** — Υλοποίηση της φόρμας χωρίς login (ελεύθερη δηλαδή), χωρίς να αντλεί δεδομένα από τα ERP και μόνο στα Αγγλικά, καθαρά ως αρχικό prototype για επιβεβαίωση του flow δημιουργίας ticket. Να μπορεί να υποβάλλει ένα απλό ticket στο osTicket API με τα δεδομένα που συμπληρώθηκαν από τον χρήστη. Τα πεδία θα είναι λίγα και ενδεικτικά για να επιβεβαιωθεί η λειτουργικότητα δημιουργίας ticket. Το documentation του osTicket API για τη δημιουργία ticket βρίσκεται στο https://docs.osticket.com/en/latest/Developer%20Documentation/API/Tickets.html αλλά το έχω κατεβάσει και βρίσκεται στο `.docs\api-documentation.md` και θα χρησιμοποιηθεί το `api/tickets.json` endpoint. Έχω συμπεριλάβει το αρχείο `docs\api_ticket_create_sample.php` με ένα επίσημο δείγμα κώδικα που δίνει το osTicket για τη δημιουργία ticket μέσω του API, και με χρήση API key. 
3. ✅ **ΟΛΟΚΛΗΡΩΘΗΚΕ** — Απαίτηση του login για την προβολή της σελίδας και την υποβολή της φόρμας. Θα χρησιμοποιεί το native σύστημα login του osTicket, με χρήση για παράδειγμα του `require __DIR__ . '/secure.inc.php';`, ώστε αν δεν υπάρχει valid client να φορτώνεται το `login.php`, να γίνεται το login με τον native τρόπο και στη συνέχεια ο χρήστης να επιστρέφει στο requested URI της custom σελίδας. Το CSRF του login flow καλύπτεται από το osTicket, οπότε δεν χρειάζεται να υλοποιήσουμε κάτι με CSRF token. Η custom φόρμα έχει υλοποιηθεί και δεν θα πειραχτεί η λειτουργία της αν δεν απαιτείται, παρά μόνο η δυνατότητα προβολής της. Σε αυτό το βήμα, το custom endpoint θα επαληθεύει ότι ο χρήστης έχει valid authenticated session και θα εφαρμόζει έλεγχο origin ως αρχικό protection layer, χωρίς ακόμα ενσωμάτωση CSRF token.
4. ✅ **ΟΛΟΚΛΗΡΩΘΗΚΕ** — Προσθήκη των υπόλοιπων πεδίων που απαιτούνται (χωρίς να αντλούνται ακόμα από τα ERP).
5. ✅ **ΟΛΟΚΛΗΡΩΘΗΚΕ** — Χρήση πολυγλωσσικότητας για την προσθήκη υποστήριξης Ισπανικών. 
6. ✅ **ΟΛΟΚΛΗΡΩΘΗΚΕ** — Σελίδα απάντησης (Πχ `success.php`) με την επιτυχή υποβολής του ticket με το ID του ticket που δημιουργήθηκε και ένα link για να το δει ο χρήστης στο osTicket client portal. .
7. Separate auth integration για δυνατότητα να κάνουν login και οι agents του osTicket. Αυτό θα αντιμετωπιστεί ως ξεχωριστό authentication flow και θα χρειαστεί ενσωμάτωση με άλλα αρχεία του osTicket, τα οποία θα τα δούμε τότε. 
8. Ανάπτυξη της λειτουργικότητας για την άντληση συμπληρωματικών δεδομένων από τα ERP συστήματα της εταιρίας και την προσθήκη τους στη φόρμα. 
9. Προαιρετικό. Ενσωμάτωση CSRF token protection και στο custom form submit flow, ώστε το endpoint της φόρμας να προστατεύεται και με explicit anti-CSRF μηχανισμό πέρα από το login/session validation.

## Backend
- Use modern PHP.
- Prefer concise and clean code, with clear structure over clever one-liners.

## Frontend
- Use `Bootstrap 5`.
- Avoid inline CSS. Use `style.css`. Prefer element classes from Bootstrap, if possible.
- Prefer reusing the same custom CSS classes for similar elements. Do not create multiple similar classes for different elements; If needed (different margin for example), modify them with additional utility classes .
- Keep frontend logic simple and enhance progressively only when necessary.

## Comments & Documentation
- For internal-only functions, use a short inline comment to describe the purpose.
- Prefer self-documenting code over verbose comments.
- Do not overcomment obvious logic.

