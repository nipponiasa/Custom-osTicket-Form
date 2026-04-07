# Custom φόρμα osTicket για τη Nipponia

Custom φόρμα υποβολής ticket με authentication για τη Nipponia, χτισμένη πάνω στο osTicket. Η φόρμα σερβίρεται από το root του osTicket στο `new.php`, εμπλουτίζει τα δεδομένα του χρήστη με VIN data από ERP και δημιουργεί tickets μέσω του osTicket API.

## Επισκόπηση

Το repository αυτό είναι το root του osTicket για μια υλοποίηση προσαρμοσμένη στις ανάγκες της Nipponia. Το custom project είναι σκόπιμα μικρό και απομονωμένο:

- Το `new.php` είναι το public entry point της custom φόρμας.
- Ο φάκελος `form/` περιέχει όλη την υλοποίηση του custom feature.
- Το υπόλοιπα αρχεία του repository αποτελούν documentation ή άλλα μη απαραίτητα αρχεία. 

Η εφαρμογή προορίζεται για εσωτερική χρήση με authentication. Υποστηρίζει τόσο osTicket clients όσο και osTicket agents, χρησιμοποιεί το native login σύστημα του osTicket και αποθηκεύει την ενεργή γλώσσα της φόρμας στο PHP session.

## Κύρια Χαρακτηριστικά

- Πρόσβαση με authentication μέσω των native login flows του osTicket.
- Ξεχωριστό entry flow για clients και agents.
- Δημιουργία ticket μέσω του osTicket API endpoint `api/tickets.json`.
- Αναζήτηση ERP δεδομένων με VIN μέσω custom JSON endpoint.
- Αυτόματος εμπλουτισμός της φόρμας με `model`, `color` και `order_no`.
- Υποστήριξη Αγγλικών και Ισπανικών, με σχεδιασμό που επιτρέπει εύκολη προσθήκη και άλλων γλωσσών.
- Σελίδα αποτελέσματος με πληροφορίες για το ticket που δημιουργήθηκε και link προς το client portal.
- Agent-specific ροή επιλογής organization και user.
- Οι Agents μπορούν να επιλέξουν χρήστη για τον οποίο θα δημιουργήσουν το ticket. 

## Entry Points

- Client form: `new.php`
- Agent form: `new.php?role=agent`

Η custom φόρμα απαιτεί έγκυρο authenticated session:

- Οι clients αυθεντικοποιούνται μέσω του native osTicket client login flow.
- Οι agents αυθεντικοποιούνται μέσω του native osTicket staff login flow.

## Πώς Λειτουργεί

1. Ο χρήστης ανοίγει την custom φόρμα μέσω του `new.php`.
2. Το authentication επιβάλλεται μέσω session validation του osTicket.
3. Η γλώσσα της φόρμας γίνεται resolve και φορτώνεται από το `$_SESSION['form_lang']`.
4. Ο χρήστης συμπληρώνει τη φόρμα και μπορεί να αναζητήσει VIN data.
5. Το `form/vin_lookup.php` φορτώνει ERP-backed data από το database view `vin_view` και επιστρέφει JSON.
6. Το frontend συμπληρώνει τα σχετικά πεδία της φόρμας με τα δεδομένα που επιστρέφονται.
7. Κατά το submit, το `form/submit.php` στέλνει το τελικό payload στο osTicket API.
8. Ο χρήστης ανακατευθύνεται σε σελίδα αποτελέσματος με την κατάσταση της υποβολής και τα στοιχεία του ticket.

## Μοντέλο Authentication

Υποστηρίζονται δύο access modes:

### Client flow

- Χρησιμοποιεί το native osTicket client session.
- Αν ο χρήστης δεν είναι logged in, το osTicket κάνει redirect στη συνηθισμένη client login σελίδα.
- Μετά το login, ο χρήστης επιστρέφει στο requested URL της custom φόρμας.

### Agent flow

- Χρησιμοποιεί ξεχωριστό native osTicket staff authentication flow.
- Η είσοδος γίνεται μέσω του `new.php?role=agent`.
- Μετά από επιτυχημένο staff login, ο agent επιστρέφει στο requested URL.

## Πολυγλωσσικότητα

Η φόρμα υποστηρίζει αυτή τη στιγμή:

- Αγγλικά
- Ισπανικά

Η διαχείριση της γλώσσας γίνεται server-side:

- Η ενεργή γλώσσα αποθηκεύεται στο `$_SESSION['form_lang']`.
- Γίνεται resolve από τη `form_resolve_language()` στο `form/translations.php`.
- Μπορεί να αλλάξει μέσω GET parameter όπως `?lang=es`.
- Endpoints όπως το `form/vin_lookup.php` φορτώνουν translations από την τρέχουσα γλώσσα του session.

Αυτό κρατά τη γλωσσική κατάσταση συνεπή ανάμεσα σε page loads και endpoint requests, χωρίς εξάρτηση από browser storage.

## VIN Lookup και ERP Integration

Η φόρμα περιλαμβάνει VIN search flow που ανακτά συμπληρωματικά δεδομένα από records συνδεδεμένα με ERP.

- Το frontend στέλνει JSON `POST` request στο `form/vin_lookup.php`.
- Το endpoint επιστρέφει `model`, `color` και `order_no`.
- Το `order_no` κρυπτογραφείται πριν σταλεί στον browser και αποκρυπτογραφείται ξανά κατά την υποβολή της φόρμας.
- Τα encryption helpers βρίσκονται στο `form/utils.php` και χρησιμοποιούν OpenSSL.
- Η πηγή δεδομένων είναι το database view `vin_view`.

Αν χρησιμοποιηθούν στο μέλλον περισσότερα από ένα ERP συστήματα, τα VIN-related δεδομένα τους θα πρέπει να ενοποιηθούν πίσω από το ίδιο database view `vin_view`, ώστε η λογική της φόρμας να παραμείνει αμετάβλητη.

## Συμπεριφορά για Agents

Όταν η φόρμα ανοίγει σε agent mode:

- Κρύβονται standard client-facing πεδία όπως email, user και company.
- Το UI εμφανίζει αντί γι' αυτά organization και user `<select>` inputs.
- Οι επιλογές χρηστών φορτώνονται κατά το page render.
- Η client-side JavaScript φιλτράρει τους χρήστες ανά organization χωρίς επιπλέον AJAX calls.

## Σημειώσεις Frontend Integration

Η φόρμα χρησιμοποιεί shared JavaScript αρχείο από το `form/resources/form.js`. Το αρχείο αυτό χρησιμοποιείται και εκτός της φόρμας, για integration links και navigation στο frontend του osTicket.

Σε αυτό το setup, το script γίνεται inject globally μέσω custom osTicket plugin με όνομα `Custom FrontEnd HTML`, το οποίο προσθέτει:

```html
<script src="/form/resources/form.js" defer></script>
```

Γι' αυτόν τον λόγο, η custom φόρμα δεν χρειάζεται να φορτώνει το script ξεχωριστά.

## Δομή Project

Μόνο τα παρακάτω αρχεία ανήκουν στην custom υλοποίηση:

```text
new.php
form/
	.htaccess
	auth.php
	config.php
	config.example.php
	footer.php
	form-bootstrap.php
	form.php
	header.php
	result.php
	submit.php
	translations.php
	utils.php
	vin_lookup.php
	lang/
		en.php
		es.php
	resources/
```



## Σημαντικοί Επιχειρησιακοί Κανόνες

- Η φόρμα προορίζεται μόνο για authenticated users.
- Το submitted name μπορεί να είναι οποιοδήποτε.
- Το submitted email πρέπει να αντιστοιχεί σε υπάρχοντα osTicket user αν θέλουμε να γίνει reuse του ίδιου user.
- Αν το email δεν υπάρχει, το osTicket δημιουργεί νέο user.
- Αν το email υπάρχει ήδη, το osTicket χρησιμοποιεί το πραγματικό αποθηκευμένο όνομα του user αντί για αυτό που πληκτρολογήθηκε.

## Περιβάλλον Ανάπτυξης

- Development environment: XAMPP / Apache με το osTicket root ως web root.
- Production environment: Apache σε cPanel.
- Production URL: `https://ticketing.nipponia.com`

Το project δεν είναι standalone εφαρμογή. Είναι customization layer μέσα σε εγκατάσταση osTicket.

## Σημειώσεις Configuration

- Το sensitive configuration βρίσκεται στο `form/config.php` και δεν πρέπει να γίνεται commit.
- Χρησιμοποίησε το `form/config.example.php` ως template για local setup.
- Τα custom endpoints και helpers βασίζονται στο υπάρχον database και authentication context του osTicket.

## API Reference

Η ροή υποβολής ticket βασίζεται στο osTicket API ticket creation endpoint:

- `api/tickets.json`

Reference material που περιλαμβάνεται στο repository (και προέρχονται από το documentation του osTicket, όχι της custom φόρμας):

- `docs/api-documentation.md`
- `docs/api_ticket_create_sample.php`

## Σημειώσεις για Contributors

- Να αντιμετωπίζεις αυτό το repository ως εγκατάσταση osTicket με ένα μικρό custom project ενσωματωμένο μέσα της.
- Όταν δουλεύεις πάνω στο custom feature, να περιορίζεις τις αλλαγές στο `new.php` και στον φάκελο `form/`.
- Να επαναχρησιμοποιείς τα υπάρχοντα Bootstrap 5 styles και να κρατάς το frontend logic απλό.
- Να προτιμάς την επέκταση του translation layer αντί για hardcoded γλωσσικά strings.

